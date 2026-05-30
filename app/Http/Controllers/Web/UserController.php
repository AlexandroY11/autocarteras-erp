<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Solo admin
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $stages = \App\Models\Stage::where('active', true)->orderBy('order')->get();

        return view('users.form', ['user' => new User(), 'stages' => $stages]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,director,worker',
            'password' => 'required|string|min:6|confirmed',
            'skills' => 'nullable|array',
            'skills.*' => 'exists:stages,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => $request->password,
            'active' => true,
        ]);

        if ($request->skills) {
            $user->skills()->sync($request->skills);
        }

        return redirect('/users')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $stages = \App\Models\Stage::where('active', true)->orderBy('order')->get();

        return view('users.form', compact('user', 'stages'));
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,director,worker',
            'password' => 'nullable|string|min:6|confirmed',
            'active' => 'boolean',
            'skills' => 'nullable|array',
            'skills.*' => 'exists:stages,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'active' => $request->boolean('active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);
        $user->skills()->sync($request->skills ?? []);

        return redirect('/users')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return redirect('/users')->withErrors(['error' => 'No puedes eliminarte a ti mismo.']);
        }

        $user->delete();

        return redirect('/users')->with('success', 'Usuario eliminado.');
    }

    public function show(User $user)
    {
        return redirect('/users');
    }
}
