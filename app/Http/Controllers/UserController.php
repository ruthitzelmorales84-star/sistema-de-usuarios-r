<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function ensureAdmin(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login', ['intended' => $request->fullUrl()]);
        }

        if ($user->role !== 'admin') {
            abort(403, 'Solo el administrador puede realizar esta acción.');
        }

        return null;
    }

    private function recordMovement(Request $request, string $action, string $description): void
    {
        $actor = Auth::user()?->name ?? 'Sistema';
        $timestamp = now()->format('Y-m-d H:i:s');

        if (Schema::hasTable('user_movements')) {
            UserMovement::create([
                'action' => $action,
                'description' => $description,
                'actor_name' => $actor,
            ]);
        }

        $movements = $request->session()->get('user_movements', []);

        array_unshift($movements, [
            'action' => $action,
            'description' => $description,
            'actor' => $actor,
            'actor_name' => $actor,
            'created_at' => $timestamp,
        ]);

        $movements = array_slice($movements, 0, 50);
        $request->session()->put('user_movements', $movements);
    }

    public function index(Request $request)
    {
        $users = User::query()->orderBy('id', 'desc')->get();
        $trashedUsers = User::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        if (Schema::hasTable('user_movements')) {
            $movements = UserMovement::latest()->take(50)->get()->map(function ($m) {
                return [
                    'action' => $m->action,
                    'description' => $m->description,
                    'actor' => $m->actor_name ?? 'Sistema',
                    'actor_name' => $m->actor_name ?? 'Sistema',
                    'created_at' => $m->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ];
            })->all();
        } else {
            $movements = $request->session()->get('user_movements', []);
        }

        return view('users.index', compact('users', 'trashedUsers', 'movements'));
    }

    public function create(Request $request)
    {
        $guardRedirect = $this->ensureAdmin($request);
        if ($guardRedirect) {
            return $guardRedirect;
        }

        return view('users.create');
    }

    public function store(Request $request)
    {
        $guardRedirect = $this->ensureAdmin($request);
        if ($guardRedirect) {
            return $guardRedirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'empleado', 'profesor', 'alumno'])],
        ]);

        $createdUser = User::create($validated);

        $this->recordMovement(
            $request,
            'Creado',
            "Se creó el usuario {$createdUser->name} ({$createdUser->email}) con rol {$createdUser->role}."
        );

        return redirect()->route('users.index')->with('success', 'Usuario registrado correctamente.');
    }

    public function show(Request $request, User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(Request $request, User $user)
    {
        $guardRedirect = $this->ensureAdmin($request);
        if ($guardRedirect) {
            return $guardRedirect;
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $guardRedirect = $this->ensureAdmin($request);
        if ($guardRedirect) {
            return $guardRedirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at'),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'empleado', 'profesor', 'alumno'])],
        ]);

        $oldName = $user->name;
        $oldEmail = $user->email;
        $oldRole = $user->role;

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        $this->recordMovement(
            $request,
            'Modificado',
            "Se modificó el usuario #{$user->id} ({$oldName} / {$oldEmail} / {$oldRole} -> {$user->name} / {$user->email} / {$user->role})."
        );

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user)
    {
        $guardRedirect = $this->ensureAdmin($request);
        if ($guardRedirect) {
            return $guardRedirect;
        }

        if ((int) Auth::id() === (int) $user->id) {
            return back()->withErrors([
                'error' => 'No puedes eliminar tu propio usuario mientras tienes sesión activa.',
            ]);
        }

        $deletedName = $user->name;
        $deletedEmail = $user->email;
        $deletedId = $user->id;

        $user->delete();

        $this->recordMovement(
            $request,
            'Deshabilitado',
            "Se deshabilitó al usuario #{$deletedId} ({$deletedName} - {$deletedEmail})."
        );

        return redirect()->route('users.index')->with('success', 'Usuario deshabilitado correctamente (Soft Delete).');
    }

    public function forceDestroy(Request $request, int $id)
    {
        $guardRedirect = $this->ensureAdmin($request);
        if ($guardRedirect) {
            return $guardRedirect;
        }

        if ((int) Auth::id() === $id) {
            return back()->withErrors([
                'error' => 'No puedes eliminar permanentemente tu propio usuario activo.',
            ]);
        }

        $user = User::withTrashed()->findOrFail($id);
        $name = $user->name;
        $email = $user->email;

        $user->forceDelete();

        $this->recordMovement(
            $request,
            'Eliminado definitivo',
            "Se eliminó permanentemente al usuario #{$id} ({$name} - {$email})."
        );

        return redirect()->route('users.index')->with('success', 'Usuario eliminado permanentemente de la base de datos.');
    }

    public function restore(Request $request, int $id)
    {
        $guardRedirect = $this->ensureAdmin($request);
        if ($guardRedirect) {
            return $guardRedirect;
        }

        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        $this->recordMovement(
            $request,
            'Restaurado',
            "Se restauró al usuario #{$user->id} ({$user->name} - {$user->email})."
        );

        return redirect()->route('users.index')->with('success', 'Usuario restaurado correctamente.');
    }
}