<?php

namespace App\Http\Controllers;

use App\Support\CourseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FormTwoTemplateController extends Controller
{
    public function index(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);

        $templates = DB::table('form_two_templates')
            ->where('course', $course)
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        $itemsByTemplate = [];
        $templateIds = $templates->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($templateIds) {
            $items = DB::table('form_two_template_items')
                ->whereIn('template_id', $templateIds)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            foreach ($items as $item) {
                $itemsByTemplate[(int) $item->template_id][] = $item;
            }
        }

        $tables = CourseContext::tables($course);
        $subjects = collect();
        if (Schema::hasTable($tables['subjects'])) {
            $subjects = DB::table($tables['subjects'])
                ->select('id', 'subject_name', 'name_ru', 'name_kz', 'module_title')
                ->orderBy('module_title')
                ->orderByRaw('COALESCE(name_ru, name_kz, subject_name)')
                ->get()
                ->map(function ($row) use ($course) {
                    $name = $row->name_ru ?: ($row->name_kz ?: $row->subject_name);
                    $module = trim((string) ($row->module_title ?? ''));
                    $row->title = ($course !== 1 && $module !== '') ? trim($module . ' ' . $name) : $name;
                    return $row;
                });
        }

        return view('form_two_templates.index', [
            'course' => $course,
            'templates' => $templates,
            'itemsByTemplate' => $itemsByTemplate,
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'group_tokens' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        DB::table('form_two_templates')->insert([
            'course' => $course,
            'name' => trim($data['name']),
            'group_tokens' => $this->normalizeTokens($data['group_tokens']),
            'is_active' => $request->boolean('is_active', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('form_two_templates.index', ['course' => $course])
            ->with('success', 'Шаблон Формы 2 создан.');
    }

    public function update(Request $request, int $id)
    {
        $template = DB::table('form_two_templates')->where('id', $id)->first();
        if (!$template) {
            return redirect()->back()->withErrors(['template' => 'Шаблон не найден.']);
        }

        $course = CourseContext::normalize($request->integer('course') ?? (int) $template->course);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'group_tokens' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        DB::table('form_two_templates')
            ->where('id', $id)
            ->update([
                'name' => trim($data['name']),
                'group_tokens' => $this->normalizeTokens($data['group_tokens']),
                'is_active' => $request->boolean('is_active'),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('form_two_templates.index', ['course' => $course])
            ->with('success', 'Шаблон обновлен.');
    }

    public function destroy(Request $request, int $id)
    {
        $template = DB::table('form_two_templates')->where('id', $id)->first();
        if (!$template) {
            return redirect()->back()->withErrors(['template' => 'Шаблон не найден.']);
        }

        DB::table('form_two_templates')->where('id', $id)->delete();

        return redirect()
            ->route('form_two_templates.index', ['course' => (int) $template->course])
            ->with('success', 'Шаблон удален.');
    }

    public function storeItem(Request $request, int $templateId)
    {
        $template = DB::table('form_two_templates')->where('id', $templateId)->first();
        if (!$template) {
            return redirect()->back()->withErrors(['item' => 'Шаблон не найден.']);
        }

        $data = $request->validate([
            'subject_name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:1000',
            'include_subgroup_two' => 'sometimes|boolean',
        ]);

        DB::table('form_two_template_items')->insert([
            'template_id' => $templateId,
            'subject_name' => trim($data['subject_name']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'include_subgroup_two' => $request->boolean('include_subgroup_two'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('form_two_templates.index', ['course' => (int) $template->course])
            ->with('success', 'Предмет добавлен в шаблон.');
    }

    public function updateItem(Request $request, int $itemId)
    {
        $item = DB::table('form_two_template_items')->where('id', $itemId)->first();
        if (!$item) {
            return redirect()->back()->withErrors(['item' => 'Строка шаблона не найдена.']);
        }
        $template = DB::table('form_two_templates')->where('id', $item->template_id)->first();
        if (!$template) {
            return redirect()->back()->withErrors(['item' => 'Шаблон не найден.']);
        }

        $data = $request->validate([
            'subject_name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:1000',
            'include_subgroup_two' => 'sometimes|boolean',
        ]);

        DB::table('form_two_template_items')
            ->where('id', $itemId)
            ->update([
                'subject_name' => trim($data['subject_name']),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'include_subgroup_two' => $request->boolean('include_subgroup_two'),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('form_two_templates.index', ['course' => (int) $template->course])
            ->with('success', 'Строка шаблона обновлена.');
    }

    public function destroyItem(Request $request, int $itemId)
    {
        $item = DB::table('form_two_template_items')->where('id', $itemId)->first();
        if (!$item) {
            return redirect()->back()->withErrors(['item' => 'Строка шаблона не найдена.']);
        }

        $template = DB::table('form_two_templates')->where('id', $item->template_id)->first();
        DB::table('form_two_template_items')->where('id', $itemId)->delete();

        return redirect()
            ->route('form_two_templates.index', ['course' => (int) ($template->course ?? 1)])
            ->with('success', 'Строка шаблона удалена.');
    }

    private function normalizeTokens(string $tokens): string
    {
        $parts = preg_split('/[,;\\s]+/u', mb_strtoupper($tokens, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts = array_values(array_unique(array_map('trim', $parts)));
        return implode(', ', $parts);
    }
}

