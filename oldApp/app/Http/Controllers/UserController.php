<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    private function getAllGroups(): array
    {
        $tables = [
            1 => 'first_course_group',
            2 => 'second_course_group',
            3 => 'third_course_group',
            4 => 'fourth_course_group',
        ];
        $result = [];
        foreach ($tables as $course => $table) {
            if (!Schema::hasTable($table)) continue;
            $rows = DB::table($table)->orderBy('group_name')->get(['id', 'group_name']);
            if ($rows->isNotEmpty()) $result[$course] = $rows;
        }
        return $result;
    }

    private function groupTable(int $course): ?string
    {
        return match ($course) {
            1 => 'first_course_group',
            2 => 'second_course_group',
            3 => 'third_course_group',
            4 => 'fourth_course_group',
            default => null,
        };
    }

    private function groupNameById(int $course, int $id): ?string
    {
        $table = $this->groupTable($course);
        if (!$table) return null;
        return DB::table($table)->where('id', $id)->value('group_name');
    }

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

        $users  = $query->paginate(30)->withQueryString();
        $roles  = [
            User::ROLE_STUDENT    => 'Ученик',
            User::ROLE_TEACHER    => 'Преподаватель',
            User::ROLE_DISPATCHER => 'Диспетчер',
        ];
        $groups = $this->getAllGroups();

        return view('users.index', [
            'users'   => $users,
            'roles'   => $roles,
            'groups'  => $groups,
            'filters' => $request->only(['q', 'role']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => ['required', 'confirmed', Password::min(8)],
            'role'      => 'required|in:' . implode(',', [User::ROLE_STUDENT, User::ROLE_TEACHER, User::ROLE_DISPATCHER]),
            'group_key' => ['nullable', 'string', 'regex:/^\d+:\d+$/'],
        ]);

        $groupId = null; $groupCourse = null;
        if ($data['role'] === User::ROLE_STUDENT && !empty($data['group_key'])) {
            [$groupCourse, $groupId] = array_map('intval', explode(':', $data['group_key'], 2));
        }

        User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'role'         => $data['role'],
            'group_id'     => $groupId,
            'group_course' => $groupCourse,
        ]);

        return back()->with('success', 'Пользователь создан.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role'      => 'required|in:' . implode(',', [User::ROLE_STUDENT, User::ROLE_TEACHER, User::ROLE_DISPATCHER]),
            'group_key' => ['nullable', 'string', 'regex:/^\d+:\d+$/'],
        ]);

        $newRole = $data['role'];

        if ($user->role === User::ROLE_DISPATCHER && $newRole !== User::ROLE_DISPATCHER) {
            $dispatchers = User::query()->where('role', User::ROLE_DISPATCHER)->count();
            if ($dispatchers <= 1) {
                return back()->withErrors(['role' => 'Нельзя снять роль у последнего диспетчера.']);
            }
        }

        $groupId = null; $groupCourse = null;
        if ($newRole === User::ROLE_STUDENT && !empty($data['group_key'])) {
            [$groupCourse, $groupId] = array_map('intval', explode(':', $data['group_key'], 2));
        }

        $user->update([
            'role'         => $newRole,
            'group_id'     => $groupId,
            'group_course' => $groupCourse,
        ]);

        return back()->with('success', 'Сохранено.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['delete' => 'Нельзя удалить свой аккаунт.']);
        }

        if ($user->role === User::ROLE_DISPATCHER) {
            $dispatchers = User::query()->where('role', User::ROLE_DISPATCHER)->count();
            if ($dispatchers <= 1) {
                return back()->withErrors(['delete' => 'Нельзя удалить последнего диспетчера.']);
            }
        }

        $user->delete();

        return back()->with('success', 'Пользователь удалён.');
    }
}
