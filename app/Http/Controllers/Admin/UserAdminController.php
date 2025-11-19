<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $locale, User $user)
    {
        $request->validate([
            'name'  => 'required|string',
            'email' => 'required|email',
            'roles' => 'array'
        ]);

        $user->update($request->only('name', 'email'));

        $user->syncRoles($request->roles); // Spatie magic ✨

        return redirect()->route('admin.users.index', $locale)
            ->with('success', 'User updated.');
    }
}
