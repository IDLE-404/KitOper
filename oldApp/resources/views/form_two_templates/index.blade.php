@extends('layouts.app')

@section('content')
@php
    $itemsByTemplate = $itemsByTemplate ?? [];
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title">Шаблоны Формы 2</h1>
        <p class="page-subtitle">Управление предметами и подвоением по профессиям</p>
    </div>
    <a href="{{ route('first.schedule.form_two', ['course' => $course ?? 1]) }}" class="btn btn-secondary">К Форме 2</a>
</div>

@if($errors->any())
    <div class="app-alert app-alert-danger">
        <i class="bi bi-exclamation-circle"></i>
        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
@endif

<div class="surface surface-p" style="margin-bottom:16px">
    <form method="GET" class="form-row">
        <div class="form-field" style="flex:0 0 auto;min-width:0">
            <div class="field-group">
                <label class="field-label">Курс</label>
                <select name="course" class="field-input" style="width:auto">
                    @for($c = 1; $c <= 4; $c++)
                        <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="form-field-auto" style="align-self:flex-end">
            <button class="btn btn-primary">Показать</button>
        </div>
    </form>
</div>

<div class="surface surface-p" style="margin-bottom:16px">
    <h2 class="section-title">Новый шаблон</h2>
    <form method="POST" action="{{ route('form_two_templates.store') }}" class="form-row">
        @csrf
        <input type="hidden" name="course" value="{{ $course ?? 1 }}">
        <div class="form-field">
            <div class="field-group">
                <label class="field-label">Название</label>
                <input type="text" name="name" class="field-input" required placeholder="ПО, БКЕ, М">
            </div>
        </div>
        <div class="form-field" style="flex:3 1 300px">
            <div class="field-group">
                <label class="field-label">Токены групп</label>
                <input type="text" name="group_tokens" class="field-input" required placeholder="ПО, БКЕ, БҚЕ, M">
                <div style="font-size:11px;color:var(--c-text-3);margin-top:2px">Через запятую: при совпадении с названием группы шаблон применяется</div>
            </div>
        </div>
        <div class="form-field-auto" style="display:flex;align-items:center;gap:8px;align-self:center;margin-top:18px">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="is_active" id="newTemplateActive" value="1" checked>
                <label class="form-check-label" for="newTemplateActive" style="font-size:13px">Активен</label>
            </div>
        </div>
        <div class="form-field-auto" style="align-self:flex-end">
            <button class="btn btn-primary">Создать</button>
        </div>
    </form>
</div>

@forelse($templates as $template)
    <div class="surface surface-p" style="margin-bottom:16px">
        <form method="POST" action="{{ route('form_two_templates.update', $template->id) }}" class="form-row" style="margin-bottom:12px">
            @csrf
            @method('PUT')
            <input type="hidden" name="course" value="{{ $course ?? 1 }}">
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Название</label>
                    <input type="text" name="name" class="field-input" value="{{ $template->name }}" required>
                </div>
            </div>
            <div class="form-field" style="flex:3 1 300px">
                <div class="field-group">
                    <label class="field-label">Токены групп</label>
                    <input type="text" name="group_tokens" class="field-input" value="{{ $template->group_tokens }}" required>
                </div>
            </div>
            <div class="form-field-auto" style="display:flex;align-items:center;gap:8px;align-self:center;margin-top:18px">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="active{{ $template->id }}" value="1" @checked($template->is_active)>
                    <label class="form-check-label" for="active{{ $template->id }}" style="font-size:13px">Активен</label>
                </div>
            </div>
            <div class="form-field-auto" style="align-self:flex-end;display:flex;gap:6px">
                <button class="btn btn-primary btn-sm">Сохранить</button>
                <form method="POST" action="{{ route('form_two_templates.destroy', $template->id) }}" onsubmit="return confirm('Удалить шаблон?');" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                    <button class="btn btn-danger btn-sm" type="submit">Удалить</button>
                </form>
            </div>
        </form>

        <div style="overflow-x:auto;margin-bottom:12px">
            <table class="app-table">
                <thead>
                    <tr>
                        <th style="width:100px">Порядок</th>
                        <th>Предмет</th>
                        <th style="width:160px">Подгруппа 2</th>
                        <th style="width:120px;text-align:right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemsByTemplate[$template->id] ?? [] as $item)
                        <tr>
                            <td class="td-muted">{{ $item->sort_order }}</td>
                            <td>{{ $item->subject_name }}</td>
                            <td>{{ $item->include_subgroup_two ? 'Да' : 'Нет' }}</td>
                            <td style="text-align:right">
                                <form method="POST" action="{{ route('form_two_templates.items.destroy', $item->id) }}" onsubmit="return confirm('Удалить строку?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state" style="padding:24px">
                                    <div class="empty-state-title">Пока нет предметов в шаблоне</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <datalist id="subjectsList{{ $template->id }}">
            @foreach($subjects as $subject)
                <option value="{{ $subject->title }}">{{ $subject->title }}</option>
            @endforeach
        </datalist>

        <div style="border-top:1px solid var(--c-border);padding-top:12px">
            <h3 style="font-size:13px;font-weight:600;color:var(--c-text-2);margin-bottom:10px">Добавить предмет</h3>
            <form method="POST" action="{{ route('form_two_templates.items.store', $template->id) }}" class="form-row">
                @csrf
                <div class="form-field" style="flex:0 0 100px;min-width:80px">
                    <div class="field-group">
                        <label class="field-label">Порядок</label>
                        <input type="number" name="sort_order" class="field-input" min="0" max="1000" value="0">
                    </div>
                </div>
                <div class="form-field" style="flex:3 1 260px">
                    <div class="field-group">
                        <label class="field-label">Предмет</label>
                        <input type="text" name="subject_name" class="field-input" list="subjectsList{{ $template->id }}" required placeholder="Выберите или введите">
                    </div>
                </div>
                <div class="form-field-auto" style="display:flex;align-items:center;gap:8px;margin-top:18px">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="include_subgroup_two" id="sub2{{ $template->id }}" value="1">
                        <label class="form-check-label" for="sub2{{ $template->id }}" style="font-size:13px">В подвоение</label>
                    </div>
                </div>
                <div class="form-field-auto" style="align-self:flex-end">
                    <button class="btn btn-primary btn-sm">Добавить</button>
                </div>
            </form>
        </div>
    </div>
@empty
    <div class="surface surface-p">
        <div class="empty-state">
            <i class="bi bi-list-check"></i>
            <div class="empty-state-title">Для этого курса пока нет шаблонов</div>
        </div>
    </div>
@endforelse
@endsection

@push('scripts')
<script src="{{ asset('js/tours/form-two-templates.js') }}"></script>
@endpush
