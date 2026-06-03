@extends('layouts.app')

@push('styles')
    <style>
        .teacher-hero {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #f7f7f8;
            padding: 16px 18px;
            margin-bottom: 14px;
        }

        .teacher-hero-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: #0f172a;
        }

        .teacher-hero-meta {
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 0;
        }

        .teacher-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .teacher-stat {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 14px;
            background: #fff;
        }

        .teacher-stat-label {
            color: #6b7280;
            font-size: 0.82rem;
            margin-bottom: 2px;
        }

        .teacher-stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .teacher-table-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
        }

        .teacher-table-card .table {
            margin-bottom: 0;
        }

        .teacher-table-card thead th {
            background: #f1f5f9;
            color: #6b7280;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom-color: #e5e7eb;
        }

        .teacher-table-card tbody td {
            border-bottom-color: #f1f5f9;
            vertical-align: middle;
        }

        .lesson-no {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(127, 86, 217, 0.1);
            color: #6941c6;
            font-weight: 700;
        }

        .lesson-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .lesson-badge.num {
            color: #6941c6;
            border-color: rgba(127, 86, 217, 0.25);
            background: rgba(127, 86, 217, 0.08);
        }

        .lesson-badge.den {
            color: #35571f;
            border-color: #c8e3b4;
            background: #edf8e5;
        }

        .lesson-badge.all {
            color: #6b7280;
            border-color: #e5e7eb;
            background: #f7f7f8;
        }

        @media (max-width: 992px) {
            .teacher-stats {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .teacher-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $totalLessons = count($lessons);
        $groupsCount = count(array_unique(array_map(static fn($item) => $item['group_name'] ?? '', $lessons)));
        $firstLesson = $totalLessons ? min(array_map(static fn($item) => (int) ($item['lesson_number'] ?? 0), $lessons)) : null;
    @endphp

    <div class="teacher-hero">
        <div class="teacher-hero-title">Пары на сегодня</div>
        <p class="teacher-hero-meta">
            {{ $today->format('d.m.Y') }}
            @if($studyDay)
                • {{ $studyDay }}
            @endif
            @if($teacherName)
                • {{ $teacherName }}
            @endif
        </p>
    </div>

    @if($teacherLinked && $studyDay && !empty($lessons))
        <div class="teacher-stats">
            <div class="teacher-stat">
                <div class="teacher-stat-label">Всего пар</div>
                <div class="teacher-stat-value">{{ $totalLessons }}</div>
            </div>
            <div class="teacher-stat">
                <div class="teacher-stat-label">Групп сегодня</div>
                <div class="teacher-stat-value">{{ $groupsCount }}</div>
            </div>
            <div class="teacher-stat">
                <div class="teacher-stat-label">Первая пара</div>
                <div class="teacher-stat-value">{{ $firstLesson ?: '—' }}</div>
            </div>
        </div>
    @endif

    @if(!$teacherLinked)
        <div class="alert alert-warning mb-0">
            Аккаунт не привязан к преподавателю. Укажите фамилию при регистрации или попросите диспетчера обновить профиль.
        </div>
    @elseif(!$studyDay)
        <div class="alert alert-info mb-0">
            Сегодня нет учебных пар.
        </div>
    @elseif(empty($lessons))
        <div class="alert alert-info mb-0">
            На сегодня у вас пар нет.
        </div>
    @else
        <div class="teacher-table-card card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Пара</th>
                            <th>Курс</th>
                            <th>Группа</th>
                            <th>Подгруппа</th>
                            <th>Дисциплина</th>
                            <th>Неделя</th>
                            <th>Аудитория</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lessons as $lesson)
                            @php
                                $weekLabel = $lesson['week_mode_label'] ?? 'Обе недели';
                                $weekClass = $weekLabel === 'Числитель' ? 'num' : ($weekLabel === 'Знаменатель' ? 'den' : 'all');
                            @endphp
                            <tr>
                                <td><span class="lesson-no">{{ $lesson['lesson_number'] }}</span></td>
                                <td>{{ $lesson['course'] }}</td>
                                <td>{{ $lesson['group_name'] }}</td>
                                <td>{{ $lesson['subgroup'] }}</td>
                                <td>{{ $lesson['subject_name'] }}</td>
                                <td><span class="lesson-badge {{ $weekClass }}">{{ $weekLabel }}</span></td>
                                <td>{{ $lesson['room'] ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
