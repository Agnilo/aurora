<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit($locale, User $user)
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', [
            'user'  => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, $locale, User $user)
    {
        $request->validate([
            'name'  => 'required|string',
            'email' => 'required|email',
            'roles' => 'array'
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // Assign roles
        $user->syncRoles($request->roles ?? []);

        return redirect()
            ->route('admin.users.index', $locale)
            ->with('success', t('dashboard.user_updated'));
    }
}
