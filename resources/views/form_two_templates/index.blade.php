@extends('layouts.app')

@section('content')
@php
    $itemsByTemplate = $itemsByTemplate ?? [];
@endphp
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">Шаблоны Формы 2</h1>
            <div class="text-muted">Управление предметами и подвоением по профессиям</div>
        </div>
        <a href="{{ route('first.schedule.form_two', ['course' => $course ?? 1]) }}" class="btn btn-outline-secondary">К Форме 2</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted">Курс</label>
                    <select name="course" class="form-select">
                        @for($c = 1; $c <= 4; $c++)
                            <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary">Показать</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="fw-semibold mb-2">Новый шаблон</div>
            <form method="POST" action="{{ route('form_two_templates.store') }}" class="row g-2 align-items-end">
                @csrf
                <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Название</label>
                    <input type="text" name="name" class="form-control" required placeholder="ПО, БКЕ, М">
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted">Токены групп</label>
                    <input type="text" name="group_tokens" class="form-control" required placeholder="ПО, БКЕ, БҚЕ, M">
                    <div class="form-text">Через запятую: при совпадении с названием группы шаблон применяется.</div>
                </div>
                <div class="col-md-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="newTemplateActive" value="1" checked>
                        <label class="form-check-label" for="newTemplateActive">Активен</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100">Создать</button>
                </div>
            </form>
        </div>
    </div>

    @forelse($templates as $template)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="POST" action="{{ route('form_two_templates.update', $template->id) }}" class="row g-2 align-items-end mb-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Название</label>
                        <input type="text" name="name" class="form-control" value="{{ $template->name }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small text-muted">Токены групп</label>
                        <input type="text" name="group_tokens" class="form-control" value="{{ $template->group_tokens }}" required>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="active{{ $template->id }}" value="1" @checked($template->is_active)>
                            <label class="form-check-label" for="active{{ $template->id }}">Активен</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill">Сохранить</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('form_two_templates.destroy', $template->id) }}" onsubmit="return confirm('Удалить шаблон?');" class="mb-3">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                    <button class="btn btn-outline-danger btn-sm" type="submit">Удалить шаблон</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th width="120">Порядок</th>
                                <th>Предмет</th>
                                <th width="180">Подгруппа 2</th>
                                <th width="140" class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($itemsByTemplate[$template->id] ?? [] as $item)
                                <tr>
                                    <td>{{ $item->sort_order }}</td>
                                    <td>{{ $item->subject_name }}</td>
                                    <td>{{ $item->include_subgroup_two ? 'Да' : 'Нет' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('form_two_templates.items.destroy', $item->id) }}" onsubmit="return confirm('Удалить строку?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted text-center">Пока нет предметов в шаблоне.</td>
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

                <form method="POST" action="{{ route('form_two_templates.items.store', $template->id) }}" class="row g-2 align-items-end mt-1">
                    @csrf
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Порядок</label>
                        <input type="number" name="sort_order" class="form-control form-control-sm" min="0" max="1000" value="0">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small text-muted">Предмет</label>
                        <input type="text" name="subject_name" class="form-control form-control-sm" list="subjectsList{{ $template->id }}" required placeholder="Выберите из списка или введите вручную">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="include_subgroup_two" id="sub2{{ $template->id }}" value="1">
                            <label class="form-check-label" for="sub2{{ $template->id }}">В подвоение</label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-success btn-sm w-100">+</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-muted">Для этого курса пока нет шаблонов.</div>
        </div>
    @endforelse
</div>
@endsection
