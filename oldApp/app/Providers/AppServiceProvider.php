<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with(config('app.url', ''), 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme("https");
        }

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // Логин: 5 попыток в минуту с одного IP, потом 429
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(fn() => back()
                    ->withInput($request->only('email', 'role'))
                    ->withErrors(['email' => 'Слишком много попыток входа. Подождите минуту.'])
                );
        });

        // Регистрация: максимум 5 аккаунтов в час с одного IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->ip())
                ->response(fn() => back()
                    ->withInput()
                    ->withErrors(['email' => 'Слишком много регистраций с вашего IP. Попробуйте позже.'])
                );
        });

        // Студенты: не более 60 запросов в минуту на страницы расписания
        RateLimiter::for('student-view', function (Request $request) {
            $user = $request->user();
            if ($user && method_exists($user, 'isStudent') && $user->isStudent()) {
                return Limit::perMinute(60)->by('student:' . $user->id);
            }
            // Для диспетчеров/учителей лимит выше
            return Limit::perMinute(200)->by('user:' . ($user?->id ?? $request->ip()));
        });
    }
}

