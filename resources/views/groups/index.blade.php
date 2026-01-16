@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/schedule-modern.css') }}">
<style>
    .panel-card {
        background: var(--panel);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid #ecf0f6;
        padding: 18px 20px;
    }
    .panel-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 12px;
    }
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }
    .form-field {
        flex: 1 1 200px;
        min-width: 180px;
    }
    .form-field label {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 6px;
        display: inline-block;
    }
    .form-field--actions {
        flex: 0 0 auto;
    }
    .table-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }
    .table thead th {
        color: var(--muted);
        font-weight: 600;
        font-size: 13px;
        border-bottom: 1px solid #e6ebf2;
    }
    .table td {
        vertical-align: middle;
    }
    .empty-note {
        color: var(--muted);
        font-size: 14px;
        padding: 16px 0;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Группы — {{ $course ?? 1 }} курс</h1>
            <p class="page-subtitle">Справочник учебных групп</p>
            <div class="mt-2 d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Курс:</label>
                <select id="courseSelect" class="search-input" style="width:auto;">
                    @for($c = 1; $c <= 4; $c++)
                        <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="action-buttons">
            <a href="{{ route('first.schedule.index', ['course' => $course ?? 1]) }}" class="btn-pill ghost">К расписанию</a>
            <a href="{{ route('first.schedule.form_two', ['course' => $course ?? 1]) }}" class="btn-pill ghost">Форма 2</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel-card mb-4">
        <div class="panel-title">Добавить группу</div>
        <form method="POST" action="{{ route('groups.store') }}">
            @csrf
            <input type="hidden" name="course" value="{{ $course ?? 1 }}">
            <div class="form-row">
                <div class="form-field">
                    <label for="groupName">Название группы</label>
                    <input id="groupName" name="group_name" class="search-input w-100" required placeholder="ПО-115" value="{{ old('group_name') }}">
                </div>
                @if($hasGroupType)
                    <div class="form-field">
                        <label for="groupType">Тип</label>
                        <select id="groupType" name="group_type" class="search-input w-100">
                            <option value="kz" @selected(old('group_type') === 'kz')>Каз</option>
                            <option value="ru" @selected(old('group_type') === 'ru')>Рус</option>
                        </select>
                    </div>
                @endif
                <div class="form-field form-field--actions">
                    <button type="submit" class="btn-pill primary">Добавить</button>
                </div>
            </div>
        </form>
    </div>

    <div class="panel-card">
        <div class="panel-title">Список групп</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Группа</th>
                        <th>Номер</th>
                        @if($hasGroupType)
                            <th>Тип</th>
                        @endif
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                        <tr>
                            <td>{{ $group->group_name }}</td>
                            <td>{{ $group->group_number }}</td>
                            @if($hasGroupType)
                                <td>{{ $group->group_type ?? '—' }}</td>
                            @endif
                            <td class="text-end">
                                <div class="table-actions">
                                    <button class="btn-pill ghost" type="button" data-bs-toggle="collapse" data-bs-target="#edit-{{ $group->id }}">Редактировать</button>
                                    <form method="POST" action="{{ route('groups.destroy', ['id' => $group->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-pill danger" type="submit" onclick="return confirm('Удалить группу?')">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-{{ $group->id }}">
                            <td colspan="{{ $hasGroupType ? 6 : 5 }}">
                                <form method="POST" action="{{ route('groups.update', ['id' => $group->id]) }}" class="form-row">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                                    <div class="form-field">
                                        <label>Название группы</label>
                                        <input name="group_name" class="search-input w-100" required value="{{ old('group_name', $group->group_name) }}">
                                    </div>
                                    @if($hasGroupType)
                                        <div class="form-field">
                                            <label>Тип</label>
                                            <select name="group_type" class="search-input w-100">
                                                <option value="kz" @selected(old('group_type', $group->group_type) === 'kz')>Каз</option>
                                                <option value="ru" @selected(old('group_type', $group->group_type) === 'ru')>Рус</option>
                                            </select>
                                        </div>
                                    @endif
                                    <div class="form-field form-field--actions">
                                        <button class="btn-pill primary">Сохранить</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $hasGroupType ? 6 : 5 }}" class="empty-note">Группы не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const courseSelect = document.getElementById('courseSelect');
    courseSelect?.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('course', courseSelect.value);
        window.location.search = params.toString();
    });
</script>
@endpush
