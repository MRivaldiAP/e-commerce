<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiSettingController extends Controller
{
    /**
     * Display AI configuration dashboard.
     */
    public function index(): View
    {
        $sections = AiSetting::sections();

        $settings = $sections->mapWithKeys(function ($meta, $section) {
            return [$section => AiSetting::forSection($section)];
        });

        return view('admin.ai.settings', [
            'sections' => $sections,
            'settings' => $settings,
        ]);
    }

    /**
     * Update AI settings for a specific section.
     */
    public function update(Request $request, string $section): RedirectResponse
    {
        abort_unless(AiSetting::isValidSection($section), 404);

        $setting = AiSetting::forSection($section);

        $validated = $request->validate([
            'model' => ['required', 'string', 'max:255'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'top_p' => ['nullable', 'numeric', 'between:0,1'],
            'frequency_penalty' => ['nullable', 'numeric', 'between:-2,2'],
            'presence_penalty' => ['nullable', 'numeric', 'between:-2,2'],
        ]);

        $cleaned = collect($validated)
            ->map(fn ($value) => ($value === '' ? null : $value))
            ->all();

        $setting->fill($cleaned);
        $setting->save();

        return redirect()
            ->route('admin.ai-settings.index')
            ->with('success', 'Pengaturan AI berhasil diperbarui untuk "' . ($request->input('section_label') ?? $section) . '".');
    }
}
