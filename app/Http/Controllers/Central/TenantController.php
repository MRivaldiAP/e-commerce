<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with('domains')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('central.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('central.tenants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
        ]);

        $tenant = Tenant::create([
            'id' => $validated['id'],
            'name' => $validated['name'],
        ]);

        $tenant->domains()->create([
            'domain' => $validated['domain'],
        ]);

        return redirect()
            ->route('central.admin.tenants.index')
            ->with('status', 'Tenant berhasil dibuat.');
    }

    public function edit(Tenant $tenant): View
    {
        $tenant->load('domains');

        return view('central.tenants.edit', [
            'tenant' => $tenant,
            'domain' => optional($tenant->domains->first())->domain,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant->load('domains');
        $primaryDomain = $tenant->domains->first();
        $currentDomain = optional($primaryDomain)->domain;
        $domainId = optional($primaryDomain)->getKey();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain,' . ($domainId ?? 'NULL')],
        ]);

        $tenant->name = $validated['name'];
        $tenant->save();

        if ($currentDomain !== $validated['domain']) {
            if ($primaryDomain) {
                $primaryDomain->update(['domain' => $validated['domain']]);
            } else {
                $tenant->domains()->create(['domain' => $validated['domain']]);
            }
        }

        return redirect()
            ->route('central.admin.tenants.index')
            ->with('status', 'Tenant berhasil diperbarui.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()
            ->route('central.admin.tenants.index')
            ->with('status', 'Tenant berhasil dihapus.');
    }
}
