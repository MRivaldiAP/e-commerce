<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AIController extends Controller
{
    public function index(): View
    {
        return view('admin.ai.index', [
            'settings' => [
                'openai_api_key' => Setting::getValue('ai.openai.api_key', ''),
                'article' => [
                    'model' => Setting::getValue('ai.article.model', 'gpt-4o-mini'),
                    'temperature' => Setting::getValue('ai.article.temperature', '0.7'),
                    'max_tokens' => Setting::getValue('ai.article.max_tokens', '2000'),
                    'top_p' => Setting::getValue('ai.article.top_p', '1'),
                    'presence_penalty' => Setting::getValue('ai.article.presence_penalty', '0'),
                    'frequency_penalty' => Setting::getValue('ai.article.frequency_penalty', '0'),
                ],
                'report' => [
                    'model' => Setting::getValue('ai.report.model', 'gpt-4o-mini'),
                    'temperature' => Setting::getValue('ai.report.temperature', '0.2'),
                    'max_tokens' => Setting::getValue('ai.report.max_tokens', '1200'),
                    'top_p' => Setting::getValue('ai.report.top_p', '1'),
                    'presence_penalty' => Setting::getValue('ai.report.presence_penalty', '0'),
                    'frequency_penalty' => Setting::getValue('ai.report.frequency_penalty', '0'),
                ],
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'openai_api_key' => ['nullable', 'string'],
            'article_model' => ['required', 'string', 'max:255'],
            'article_temperature' => ['required', 'numeric', 'between:0,2'],
            'article_max_tokens' => ['nullable', 'integer', 'min:1'],
            'article_top_p' => ['required', 'numeric', 'between:0,1'],
            'article_presence_penalty' => ['required', 'numeric', 'between:-2,2'],
            'article_frequency_penalty' => ['required', 'numeric', 'between:-2,2'],
            'report_model' => ['required', 'string', 'max:255'],
            'report_temperature' => ['required', 'numeric', 'between:0,2'],
            'report_max_tokens' => ['nullable', 'integer', 'min:1'],
            'report_top_p' => ['required', 'numeric', 'between:0,1'],
            'report_presence_penalty' => ['required', 'numeric', 'between:-2,2'],
            'report_frequency_penalty' => ['required', 'numeric', 'between:-2,2'],
        ]);

        $this->storeSetting('ai.openai.api_key', trim($validated['openai_api_key'] ?? ''));

        $this->storeSetting('ai.article.model', trim($validated['article_model']));
        $this->storeSetting('ai.article.temperature', (string) $validated['article_temperature']);
        $this->storeSetting('ai.article.max_tokens', isset($validated['article_max_tokens']) ? (string) $validated['article_max_tokens'] : '');
        $this->storeSetting('ai.article.top_p', (string) $validated['article_top_p']);
        $this->storeSetting('ai.article.presence_penalty', (string) $validated['article_presence_penalty']);
        $this->storeSetting('ai.article.frequency_penalty', (string) $validated['article_frequency_penalty']);

        $this->storeSetting('ai.report.model', trim($validated['report_model']));
        $this->storeSetting('ai.report.temperature', (string) $validated['report_temperature']);
        $this->storeSetting('ai.report.max_tokens', isset($validated['report_max_tokens']) ? (string) $validated['report_max_tokens'] : '');
        $this->storeSetting('ai.report.top_p', (string) $validated['report_top_p']);
        $this->storeSetting('ai.report.presence_penalty', (string) $validated['report_presence_penalty']);
        $this->storeSetting('ai.report.frequency_penalty', (string) $validated['report_frequency_penalty']);

        return redirect()
            ->route('admin.ai.index')
            ->with('success', 'Pengaturan AI berhasil diperbarui.');
    }

    public function generateArticle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keywords' => ['required', 'string', 'max:500'],
        ]);

        $apiKey = trim((string) Setting::getValue('ai.openai.api_key'));

        if (empty($apiKey)) {
            return response()->json([
                'message' => 'OpenAI API key belum disetel. Silakan atur pada menu AI.',
            ], 422);
        }

        $client = \OpenAI::client($apiKey);

        $model = Setting::getValue('ai.article.model', 'gpt-4o-mini');
        $temperature = (float) Setting::getValue('ai.article.temperature', 0.7);
        $maxTokens = (int) Setting::getValue('ai.article.max_tokens', 2000);
        $topP = (float) Setting::getValue('ai.article.top_p', 1.0);
        $presencePenalty = (float) Setting::getValue('ai.article.presence_penalty', 0.0);
        $frequencyPenalty = (float) Setting::getValue('ai.article.frequency_penalty', 0.0);

        $payload = [
            'model' => $model,
            'temperature' => $temperature,
            'top_p' => $topP,
            'presence_penalty' => $presencePenalty,
            'frequency_penalty' => $frequencyPenalty,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an Indonesian SEO strategist and senior copywriter who writes persuasive long-form articles for ecommerce brands. Always reply in Indonesian.',
                ],
                [
                    'role' => 'user',
                    'content' => $this->buildArticlePrompt($validated['keywords']),
                ],
            ],
        ];

        if ($maxTokens > 0) {
            $payload['max_tokens'] = $maxTokens;
        }

        try {
            $response = $client->chat()->create($payload);
            $content = trim($response->choices[0]->message->content ?? '');
            $data = $this->extractJson($content);

            if (! is_array($data)) {
                return response()->json([
                    'message' => 'Model tidak mengembalikan format data yang diharapkan.',
                    'raw' => $content,
                ], 422);
            }

            $data = array_merge([
                'title' => '',
                'slug' => '',
                'excerpt' => '',
                'content' => '',
                'meta_title' => '',
                'meta_description' => '',
            ], $data);

            $data['slug'] = Str::slug($data['slug'] ?: $data['title']);

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Gagal menghasilkan artikel: ' . $exception->getMessage(),
            ], 422);
        }
    }

    protected function buildArticlePrompt(string $keywords): string
    {
        $keywordsList = trim($keywords);

        return <<<PROMPT
Tulis satu artikel blog berbahasa Indonesia yang dioptimalkan SEO untuk merek e-commerce kami.

Target kata kunci utama dan turunan:
{$keywordsList}

Gunakan praktik SEO terbaik berikut:
- Minimal 1.200 kata.
- Pastikan kata kunci utama muncul di judul, paragraf pembuka, dan beberapa subjudul.
- Sertakan struktur heading yang jelas (H2 dan H3), bullet list, dan ajakan bertindak.
- Gunakan gaya bahasa profesional namun persuasif yang mudah dipahami.
- Meta title maksimal 60 karakter, meta description 150-160 karakter, dan ringkasan 40-60 kata.
- Gunakan HTML semantik untuk konten utama (misal <h2>, <h3>, <p>, <ul>, <ol>, <strong>).

Kembalikan jawaban dalam format JSON tanpa pembungkus Markdown dengan kunci berikut:
{
  "title": "",
  "slug": "",
  "excerpt": "",
  "content": "",
  "meta_title": "",
  "meta_description": ""
}

Pastikan nilai "content" menggunakan HTML yang rapi dan mudah dibaca, serta slug berupa lowercase dengan tanda hubung.
PROMPT;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function extractJson(string $content): ?array
    {
        if ($content === '') {
            return null;
        }

        $clean = trim($content);
        $clean = preg_replace('/^```json|^```|```$/m', '', $clean ?? '');
        $clean = trim($clean ?? '');

        $decoded = json_decode($clean, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    protected function storeSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
