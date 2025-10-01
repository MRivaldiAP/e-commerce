<?php

namespace App\Services\AI;

use App\Models\AiSetting;
use Illuminate\Support\Str;
use RuntimeException;

class ArticleGenerator
{
    /**
     * Generate article content based on supplied keywords.
     */
    public function generate(string $keywords): array
    {
        $keywords = trim($keywords);

        if ($keywords === '') {
            throw new RuntimeException('Kata kunci tidak boleh kosong.');
        }

        $setting = AiSetting::forSection(AiSetting::SECTION_ARTICLE_GENERATION);

        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            throw new RuntimeException('Kunci API OpenAI belum dikonfigurasi.');
        }

        $clientOptions = array_filter([
            'organization' => config('services.openai.organization'),
        ]);

        $client = \OpenAI::client($apiKey, $clientOptions);

        $messages = [
            [
                'role' => 'system',
                'content' => 'Anda adalah asisten penulisan konten SEO berbahasa Indonesia untuk brand e-commerce. Fokus pada kualitas, orisinalitas, dan kepatuhan terhadap praktik SEO terbaru.',
            ],
            [
                'role' => 'user',
                'content' => $this->buildPrompt($keywords),
            ],
        ];

        $payload = array_filter([
            'model' => $setting->model ?: 'gpt-4o-mini',
            'messages' => $messages,
            'temperature' => $setting->temperature,
            'max_tokens' => $setting->max_tokens,
            'top_p' => $setting->top_p,
            'frequency_penalty' => $setting->frequency_penalty,
            'presence_penalty' => $setting->presence_penalty,
        ], fn ($value) => $value !== null && $value !== '');

        $response = $client->chat()->create($payload);

        $content = trim($response->choices[0]->message->content ?? '');

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Respons AI tidak dapat dipahami. Pastikan parameter yang digunakan sudah benar.');
        }

        $title = trim((string) ($decoded['title'] ?? ''));

        if ($title === '') {
            throw new RuntimeException('AI tidak menghasilkan judul artikel.');
        }

        $slugSource = trim((string) ($decoded['slug'] ?? $title));
        $slug = Str::slug($slugSource !== '' ? $slugSource : $title);

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => trim((string) ($decoded['excerpt'] ?? '')),
            'content' => trim((string) ($decoded['content'] ?? '')),
            'meta_title' => trim((string) ($decoded['meta_title'] ?? Str::limit($title, 60, ''))),
            'meta_description' => trim((string) ($decoded['meta_description'] ?? '')),
        ];
    }

    /**
     * Build the detailed prompt for the AI model.
     */
    protected function buildPrompt(string $keywords): string
    {
        $keywords = trim($keywords);

        return <<<PROMPT
Anda diminta membuat satu artikel blog berbahasa Indonesia untuk toko online. Terapkan praktik terbaik SEO terkini dan optimasi pengalaman pembaca.

Target kata kunci utama dan variasinya: {$keywords}.

Persyaratan konten:
1. Jumlah kata minimal 1.200 kata, dengan pembuka yang langsung menyinggung kata kunci utama.
2. Gunakan struktur heading h2 dan h3 yang jelas, sertakan list bernomor atau bullet, dan tabel ringkasan bila relevan.
3. Sisipkan kata kunci utama dan sinonimnya secara alami pada judul, subjudul penting, paragraf pertama, dan meta description tanpa keyword stuffing.
4. Jelaskan topik secara mendalam dengan gaya profesional yang ramah, sertakan bukti sosial atau data pendukung jika memungkinkan, dan akhiri dengan ajakan bertindak yang kuat.
5. Penuhi pedoman E-E-A-T (expertise, experience, authoritativeness, trustworthiness) serta hindari klaim berlebihan.
6. Format konten menggunakan HTML sederhana (h2, h3, p, ul, ol, li, strong, em, table, thead, tbody, tr, th, td) agar siap ditempel ke CMS.
7. Buat excerpt 30-40 kata yang merangkum artikel dan mengandung kata kunci utama.
8. Pastikan meta title maksimal 60 karakter, meta description 150-160 karakter yang menggugah klik.
9. Buat slug SEO-friendly seluruhnya huruf kecil, pisahkan kata dengan tanda hubung dan hindari karakter khusus.

Berikan hasil akhir dalam JSON valid tanpa penjelasan tambahan dengan format persis berikut:
{
  "title": "...",
  "slug": "...",
  "excerpt": "...",
  "content": "...",
  "meta_title": "...",
  "meta_description": "..."
}
PROMPT;
    }
}
