<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants.
     */
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with('domains')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create(): View
    {
        return view('admin.tenants.create');
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('tenants', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'domain' => ['required', 'string', 'max:255', Rule::unique('domains', 'domain')],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', Password::min(8), 'confirmed'],
            'admin_password_confirmation' => ['required', 'string'],
        ]);

        $tenant = null;

        try {
            $tenant = Tenant::create([
                'id' => $validated['id'],
                'name' => $validated['name'],
                'data' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                ],
            ]);

            $tenant->domains()->create([
                'domain' => $validated['domain'],
            ]);

            /** @var \Stancl\Tenancy\Tenancy $tenancy */
            $tenancy = app(\Stancl\Tenancy\Tenancy::class);
            $tenancy->initialize($tenant);

            try {
                User::create([
                    'name' => $validated['admin_name'],
                    'email' => $validated['admin_email'],
                    'password' => Hash::make($validated['admin_password']),
                    'role' => User::ROLE_ADMINISTRATOR,
                ]);
            } finally {
                $tenancy->end();
            }
        } catch (\Throwable $e) {
            if ($tenant) {
                $tenant->delete();
            }

            throw $e;
        }

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant): View
    {
        $tenant->load('domains');

        return view('admin.tenants.edit', [
            'tenant' => $tenant,
            'primaryDomain' => $tenant->domains->first(),
        ]);
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant->load('domains');
        $primaryDomain = $tenant->domains->first();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'domain' => [
                'required',
                'string',
                'max:255',
                Rule::unique('domains', 'domain')->ignore($primaryDomain?->id),
            ],
        ]);

        $connection = config('tenancy.database.central_connection', config('database.default'));

        DB::connection($connection)->transaction(function () use ($tenant, $primaryDomain, $validated): void {
            $tenantData = $tenant->data ?? [];
            $tenantData['name'] = $validated['name'];
            $tenantData['email'] = $validated['email'] ?? null;

            $tenant->update([
                'name' => $validated['name'],
                'data' => $tenantData,
            ]);

            if ($primaryDomain) {
                $primaryDomain->update(['domain' => $validated['domain']]);
            } else {
                $tenant->domains()->create(['domain' => $validated['domain']]);
            }
        });

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant berhasil diperbarui.');
    }

    /**
     * Remove the specified tenant from storage.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant berhasil dihapus.');
    }
}