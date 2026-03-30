<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * Contrôleur de gestion des utilisateurs (espace admin).
 *
 * Permet de lister, créer et supprimer les comptes utilisateurs.
 * La suppression est bloquée si l'utilisateur est le dernier en base,
 * afin d'éviter de se retrouver sans accès à l'administration.
 *
 * Routes exposées : index, create, store, destroy.
 */
class UserController extends Controller
{
    /**
     * Affiche la liste de tous les utilisateurs, triés par nom.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Affiche le formulaire de création d'un utilisateur.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Valide et enregistre un nouvel utilisateur en base.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Supprime un utilisateur après avoir vérifié qu'il n'est pas le dernier.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
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
