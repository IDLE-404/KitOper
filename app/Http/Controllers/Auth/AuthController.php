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
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:' . implode(',', [User::ROLE_STUDENT, User::ROLE_TEACHER, User::ROLE_DISPATCHER]),
            'teacher_surname' => 'nullable|string|max:255',
        ]);

        $role = $data['role'] ?? User::ROLE_STUDENT;
        $teacherId = null;
        if ($role === User::ROLE_TEACHER) {
            $surname = trim((string) ($data['teacher_surname'] ?? ''));
            if ($surname === '') {
                return back()->withErrors([
                    'teacher_surname' => 'Для роли преподавателя укажите фамилию.',
                ])->withInput();
            }
            $teacherId = $this->resolveTeacherIdBySurname($surname);
            if (!$teacherId) {
                return back()->withErrors([
                    'teacher_surname' => 'Преподаватель по этой фамилии не найден. Укажите так же, как в справочнике преподавателей.',
                ])->withInput();
            }
        }

        $createPayload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
        ];

        if (Schema::hasColumn('users', 'teacher_id')) {
            $createPayload['teacher_id'] = $teacherId;
        }

        $user = User::create($createPayload);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($this->routeForRole($user->role));
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
