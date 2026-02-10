<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'KitOper') }} — Вход</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@500;600&display=swap">
    <style>
        body {
            font-family: 'Manrope', sans-serif;
            background: radial-gradient(circle at 20% 20%, #e6f3ff 0, #f7f8ff 40%, #fff5f0 100%);
            min-height: 100vh;
        }
        .auth-card {
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(20, 33, 70, 0.12);
        }
        .brand {
            font-family: 'Unbounded', sans-serif;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-5">
                <div class="text-center mb-4">
                    <div class="brand h3 mb-1">KitOper</div>
                    <div class="text-muted">Система расписания колледжа</div>
                </div>
                <div class="card auth-card p-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
