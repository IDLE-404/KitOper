<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'nullable|in:' . implode(',', [User::ROLE_STUDENT, User::ROLE_TEACHER, User::ROLE_DISPATCHER]),
        ]);

        $selectedRole = $credentials['role'] ?? null;

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            /** @var User|null $user */
            $user = $request->user();
            if ($selectedRole && $user && $user->role !== $selectedRole) {
                Auth::logout();
                return back()->withErrors([
                    'role' => 'Вы выбрали не тот тип аккаунта для этого пользователя.',
                ])->onlyInput(['email', 'role']);
            }

            $routeName = $this->routeForRole($user?->role ?? User::ROLE_STUDENT);
            return redirect()->intended(route($routeName));
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль.',
        ])->onlyInput(['email', 'role']);
    }

    public function showRegister(): View
    {
        $groups = $this->getAllGroups();
        return view('auth.register', compact('groups'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => ['required', 'confirmed', Password::min(8)],
            'group_key'  => ['required', 'string', 'regex:/^\d+:\d+$/'],
        ]);

        [$course, $groupId] = explode(':', $data['group_key'], 2);
        $course   = (int) $course;
        $groupId  = (int) $groupId;

        $table = $this->groupTable($course);
        if (!$table || !DB::table($table)->where('id', $groupId)->exists()) {
            return back()->withErrors(['group_key' => 'Выбранная группа не найдена.'])->withInput();
        }

        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'role'         => User::ROLE_STUDENT,
            'group_id'     => $groupId,
            'group_course' => $course,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    /** Все группы всех курсов, сгруппированные по курсу. */
    private function getAllGroups(): array
    {
        $result = [];
        for ($course = 1; $course <= 4; $course++) {
            $table = $this->groupTable($course);
            if (!$table || !Schema::hasTable($table)) {
                continue;
            }
            $rows = DB::table($table)->orderBy('group_name')->get(['id', 'group_name']);
            if ($rows->isNotEmpty()) {
                $result[$course] = $rows;
            }
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

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function routeForRole(string $role): string
    {
        return $role === User::ROLE_TEACHER ? 'teacher.today' : 'home';
    }

    private function resolveTeacherIdBySurname(string $surname): ?int
    {
        if (!Schema::hasTable('teachers')) {
            return null;
        }

        $needle = mb_strtolower(trim($surname), 'UTF-8');
        if ($needle === '') {
            return null;
        }

        $teachers = DB::table('teachers')
            ->select('id', 'teacher_name', 'initials')
            ->get();

        $bestId = null;
        $bestScore = 0;

        foreach ($teachers as $teacher) {
            $teacherName = mb_strtolower(trim((string) ($teacher->teacher_name ?? '')), 'UTF-8');
            $initials = mb_strtolower(trim((string) ($teacher->initials ?? '')), 'UTF-8');
            if ($teacherName === '' && $initials === '') {
                continue;
            }

            $score = 0;
            if ($teacherName === $needle || str_starts_with($teacherName, $needle . ' ')) {
                $score = 300;
            } elseif ($initials !== '' && str_starts_with($initials, $needle)) {
                $score = 250;
            } else {
                $tokens = preg_split('/[\\s\\.\\-]+/u', $teacherName . ' ' . $initials, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                foreach ($tokens as $token) {
                    if ($token === $needle) {
                        $score = max($score, 200);
                    } elseif (str_starts_with($token, $needle)) {
                        $score = max($score, 120);
                    }
                }
                if ($score === 0 && str_contains($teacherName, $needle)) {
                    $score = 60;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestId = (int) $teacher->id;
            }
        }

        return $bestScore > 0 ? $bestId : null;
    }
}
