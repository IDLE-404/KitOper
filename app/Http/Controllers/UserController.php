<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->orderBy('name');

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->paginate(30)->withQueryString();
        $roles = [
            User::ROLE_STUDENT => 'Ученик',
            User::ROLE_TEACHER => 'Преподаватель',
            User::ROLE_DISPATCHER => 'Диспетчер',
        ];

        return view('users.index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['q', 'role']),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => 'required|in:' . implode(',', [User::ROLE_STUDENT, User::ROLE_TEACHER, User::ROLE_DISPATCHER]),
        ]);

        $newRole = $data['role'];
        if ($user->role === $newRole) {
            return back();
        }

        if ($user->role === User::ROLE_DISPATCHER && $newRole !== User::ROLE_DISPATCHER) {
            $dispatchers = User::query()->where('role', User::ROLE_DISPATCHER)->count();
            if ($dispatchers <= 1) {
                return back()->withErrors(['role' => 'Нельзя снять роль у последнего диспетчера.']);
            }
        }

        $user->update(['role' => $newRole]);

        return back()->with('success', 'Роль обновлена.');
    }
}
