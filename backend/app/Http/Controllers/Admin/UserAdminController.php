<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index()
    {
        Gate::authorize('view', User::class);

        $users = User::with(['roles', 'permissions'])
            ->latest()
            ->paginate(5);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        Gate::authorize('view', User::class);
        $user->load(['roles', 'permissions']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        Gate::authorize('edit', User::class);
        $user->load(['roles', 'permissions']);
        $roles = \App\Models\Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('edit', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->update($validated);

        // Sync roles if roles data is provided
        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', User::class);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}