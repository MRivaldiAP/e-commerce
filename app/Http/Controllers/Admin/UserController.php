<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $managedRoles = $this->managedRoles();

        $query = User::query();

        $view = $request->input('view');

        if ($view === 'team') {
            $query->whereIn('role', array_keys($managedRoles));
        } elseif ($view === 'customers') {
            $query->where('role', User::ROLE_BASIC);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'managedRoles' => $managedRoles,
            'currentView' => $view,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => $this->managedRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $roles = array_keys($this->managedRoles());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($roles)],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()
            ->route('admin.users.index', ['view' => 'team'])
            ->with('success', 'Pengguna berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        if ($user->isBasic()) {
            abort(404);
        }

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->managedRoles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->isBasic()) {
            abort(404);
        }

        $roles = array_keys($this->managedRoles());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($roles)],
        ]);

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index', ['view' => 'team'])
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isBasic()) {
            abort(404);
        }

        if (auth()->id() === $user->getKey()) {
            return redirect()
                ->route('admin.users.index', ['view' => 'team'])
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index', ['view' => 'team'])
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * @return array<string, string>
     */
    protected function managedRoles(): array
    {
        return [
            User::ROLE_ADMINISTRATOR => 'Administrator',
            User::ROLE_PRODUCT_MANAGER => 'Product Manager',
            User::ROLE_ORDER_MANAGER => 'Order Manager',
        ];
    }
}
