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
    @php
        $groupTableCols = 3 + ($hasGroupType ? 1 : 0) + (($hasSubgroupsColumn ?? false) ? 1 : 0);
    @endphp
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
            <form method="POST" action="{{ route('groups.finish_year') }}" onsubmit="return confirm('Завершить учебный год для этого курса?')">
                @csrf
                <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                <button type="submit" class="btn-pill danger">Завершить учебный год</button>
            </form>
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
                @if($hasSubgroupsColumn ?? false)
                    <div class="form-field">
                        <label for="groupHasSubgroups">Подвоение</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="groupHasSubgroups" name="has_subgroups" value="1" @checked(old('has_subgroups'))>
                            <label class="form-check-label" for="groupHasSubgroups">Есть подгруппа 2</label>
                        </div>
                    </div>
                @endif
                <div class="form-field form-field--actions">
                    <button type="submit" class="btn-pill primary">Добавить</button>
                </div>
            </div>
        </form>
        <div class="mt-3">
            <input type="search" id="groupSearch" class="search-input w-100" placeholder="Поиск по группе">
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-title">Список групп</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="groupsTable">
                <thead>
                    <tr>
                        <th>Группа</th>
                        <th>Номер</th>
                        @if($hasSubgroupsColumn ?? false)
                            <th>Подвоение</th>
                        @endif
                        @if($hasGroupType)
                            <th>Тип</th>
                        @endif
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                        <tr data-name="{{ mb_strtolower($group->group_name ?? '') }}">
                            <td>{{ $group->group_name }}</td>
                            <td>{{ $group->group_number }}</td>
                            @if($hasSubgroupsColumn ?? false)
                                <td>{{ !empty($group->has_subgroups) ? 'Да' : '—' }}</td>
                            @endif
                            @if($hasGroupType)
                                <td>{{ $group->group_type ?? '—' }}</td>
                            @endif
                            <td class="text-end">
                                <div class="table-actions">
                                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#edit-{{ $group->id }}">Редактировать</button>
                                    <form method="POST" action="{{ route('groups.destroy', ['id' => $group->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" type="submit" onclick="return confirm('Удалить группу?')">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-{{ $group->id }}">
                            <td colspan="{{ $groupTableCols }}">
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
                                    @if($hasSubgroupsColumn ?? false)
                                        <div class="form-field">
                                            <label>Подвоение</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="groupHasSubgroups-{{ $group->id }}" name="has_subgroups" value="1" @checked(old('has_subgroups', $group->has_subgroups))>
                                                <label class="form-check-label" for="groupHasSubgroups-{{ $group->id }}">Есть подгруппа 2</label>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="form-field form-field--actions">
                                        <button class="btn btn-outline-primary btn-sm">Сохранить</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $groupTableCols }}" class="empty-note">Группы не найдены.</td>
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

    const groupSearch = document.getElementById('groupSearch');
    const groupRows = Array.from(document.querySelectorAll('#groupsTable tbody tr'))
        .filter(row => row.dataset.name !== undefined);

    groupSearch?.addEventListener('input', () => {
        const query = groupSearch.value.trim().toLowerCase();
        groupRows.forEach(row => {
            row.style.display = row.dataset.name.includes(query) ? '' : 'none';
        });
    });
</script>
@endpush
