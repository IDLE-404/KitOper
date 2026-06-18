@extends('layouts.app')

@section('content')
@php
    $groupTableCols = 3 + ($hasGroupType ? 1 : 0) + (($hasSubgroupsColumn ?? false) ? 1 : 0);
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title">Группы — {{ $course ?? 1 }} курс</h1>
        <p class="page-subtitle">Справочник учебных групп</p>
        <div style="margin-top:8px;display:flex;align-items:center;gap:8px">
            <span class="field-label">Курс:</span>
            <select id="courseSelect" class="field-input" style="width:auto">
                @for($c = 1; $c <= 4; $c++)
                    <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start">
        <a href="{{ route('first.schedule.index', ['course' => $course ?? 1]) }}" class="btn btn-secondary">К расписанию</a>
        <a href="{{ route('first.schedule.form_two', ['course' => $course ?? 1]) }}" class="btn btn-secondary">Форма 2</a>
    </div>
</div>

@if($errors->any())
    <div class="app-alert app-alert-danger">
        <i class="bi bi-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="surface surface-p" style="margin-bottom:16px">
    <h2 class="section-title">Добавить группу</h2>
    <form method="POST" action="{{ route('groups.store') }}">
        @csrf
        <input type="hidden" name="course" value="{{ $course ?? 1 }}">
        <div class="form-row">
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label" for="groupName">Название группы</label>
                    <input id="groupName" name="group_name" class="field-input" required placeholder="ПО-115" value="{{ old('group_name') }}">
                </div>
            </div>
            @if($hasGroupType)
                <div class="form-field">
                    <div class="field-group">
                        <label class="field-label" for="groupType">Тип</label>
                        <select id="groupType" name="group_type" class="field-input">
                            <option value="kz" @selected(old('group_type') === 'kz')>Казахский</option>
                            <option value="ru" @selected(old('group_type') === 'ru')>Русский</option>
                        </select>
                    </div>
                </div>
            @endif
            @if($hasSubgroupsColumn ?? false)
                <div class="form-field">
                    <div class="field-group">
                        <label class="field-label">Подвоение</label>
                        <div class="form-check" style="margin-top:4px">
                            <input class="form-check-input" type="checkbox" id="groupHasSubgroups" name="has_subgroups" value="1" @checked(old('has_subgroups'))>
                            <label class="form-check-label" for="groupHasSubgroups" style="font-size:13px">Есть подгруппа 2</label>
                        </div>
                    </div>
                </div>
            @endif
            <div class="form-field-auto" style="align-self:flex-end">
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </div>
    </form>
    <div style="margin-top:12px">
        <input type="search" id="groupSearch" class="field-input" placeholder="Поиск по группе">
    </div>
</div>

<div class="surface">
    <div class="surface-p" style="padding-bottom:12px">
        <h2 class="section-title" style="margin-bottom:0">Список групп</h2>
    </div>
    <div style="overflow-x:auto">
        <table class="app-table" id="groupsTable">
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
                    <th style="text-align:right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                    <tr data-name="{{ mb_strtolower($group->group_name ?? '') }}">
                        <td>{{ $group->group_name }}</td>
                        <td class="td-muted">{{ $group->group_number }}</td>
                        @if($hasSubgroupsColumn ?? false)
                            <td>{{ !empty($group->has_subgroups) ? 'Да' : '—' }}</td>
                        @endif
                        @if($hasGroupType)
                            <td>{{ $group->group_type ?? '—' }}</td>
                        @endif
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#edit-{{ $group->id }}">Изменить</button>
                                <form method="POST" action="{{ route('groups.destroy', ['id' => $group->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Удалить группу?')">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr class="collapse" id="edit-{{ $group->id }}">
                        <td colspan="{{ $groupTableCols }}">
                            <form method="POST" action="{{ route('groups.update', ['id' => $group->id]) }}" class="form-row" style="padding:8px 0">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                                <div class="form-field">
                                    <div class="field-group">
                                        <label class="field-label">Название группы</label>
                                        <input name="group_name" class="field-input" required value="{{ old('group_name', $group->group_name) }}">
                                    </div>
                                </div>
                                @if($hasGroupType)
                                    <div class="form-field">
                                        <div class="field-group">
                                            <label class="field-label">Тип</label>
                                            <select name="group_type" class="field-input">
                                                <option value="kz" @selected(old('group_type', $group->group_type) === 'kz')>Казахский</option>
                                                <option value="ru" @selected(old('group_type', $group->group_type) === 'ru')>Русский</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                @if($hasSubgroupsColumn ?? false)
                                    <div class="form-field">
                                        <div class="field-group">
                                            <label class="field-label">Подвоение</label>
                                            <div class="form-check" style="margin-top:4px">
                                                <input class="form-check-input" type="checkbox" id="groupHasSubgroups-{{ $group->id }}" name="has_subgroups" value="1" @checked(old('has_subgroups', $group->has_subgroups))>
                                                <label class="form-check-label" for="groupHasSubgroups-{{ $group->id }}" style="font-size:13px">Есть подгруппа 2</label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-field-auto" style="align-self:flex-end">
                                    <button class="btn btn-primary btn-sm">Сохранить</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $groupTableCols }}">
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                <div class="empty-state-title">Группы не найдены</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('courseSelect')?.addEventListener('change', function () {
        const params = new URLSearchParams(window.location.search);
        params.set('course', this.value);
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

@push('scripts')
<script src="{{ asset('js/tours/groups.js') }}"></script>
@endpush
