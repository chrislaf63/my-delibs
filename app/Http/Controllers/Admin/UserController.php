<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'first_name' => $request->first_name,
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'Utilisateur créé avec succès.');
    }

    public function destroy(User $user)
    {
        if (User::count() <= 1) {
            return back()->with('error', 'Impossible de supprimer le dernier utilisateur.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'Utilisateur supprimé.');
    }
}
