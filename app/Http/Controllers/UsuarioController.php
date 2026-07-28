<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    public function index(): Response
    {
        $usuarios = User::forCurrentCompany()
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('usuarios/Index', [
            'usuarios' => $usuarios,
        ]);
    }

    public function create(): Response
    {
        $roles = Role::forCurrentCompany()->orderBy('nombre')->get();

        return Inertia::render('usuarios/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user = User::create([
            'company_id' => auth()->user()->company_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        if (! empty($validated['role_id'])) {
            $role = Role::forCurrentCompany()->find($validated['role_id']);

            if ($role) {
                $user->roles()->attach($role->id);
            }
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $usuario): Response
    {
        $usuario->load('roles');

        $roles = Role::forCurrentCompany()->orderBy('nombre')->get();

        return Inertia::render('usuarios/Edit', [
            'usuario' => $usuario,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$usuario->id}",
            'password' => 'nullable|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $usuario->update($data);

        if (array_key_exists('role_id', $validated)) {
            $usuario->roles()->sync(
                $validated['role_id'] ? [$validated['role_id']] : []
            );
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $usuario->update(['estado' => 'inactivo']);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario desactivado exitosamente.');
    }

    public function toggleEstado(User $usuario): RedirectResponse
    {
        $usuario->update([
            'estado' => $usuario->estado === 'activo' ? 'inactivo' : 'activo',
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Estado del usuario actualizado exitosamente.');
    }
}
