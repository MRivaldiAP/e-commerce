<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AiSetting extends Model
{
    use HasFactory;

    public const PROVIDER_OPENAI = 'openai';

    public const SECTION_ARTICLE_GENERATION = 'article_generation';
    public const SECTION_REPORT_READER = 'report_reader';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'section',
        'provider',
        'model',
        'temperature',
        'max_tokens',
        'top_p',
        'frequency_penalty',
        'presence_penalty',
        'extra_settings',
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'top_p' => 'float',
        'frequency_penalty' => 'float',
        'presence_penalty' => 'float',
        'extra_settings' => 'array',
    ];

    /**
     * Return known AI sections with their labels and descriptions.
     */
    public static function sections(): Collection
    {
        return collect([
            self::SECTION_ARTICLE_GENERATION => [
                'label' => 'Generator Artikel SEO',
                'description' => 'Atur parameter untuk pembuatan artikel otomatis yang mengoptimalkan SEO dan pengalaman pembaca.',
            ],
            self::SECTION_REPORT_READER => [
                'label' => 'Pembaca Laporan (Coming Soon)',
                'description' => 'Siapkan parameter AI yang kelak digunakan untuk menganalisis dan merangkum laporan bisnis.',
            ],
        ]);
    }

    /**
     * Determine whether the given section is supported.
     */
    public static function isValidSection(string $section): bool
    {
        return self::sections()->keys()->contains($section);
    }

    /**
     * Retrieve the AI setting for a section and ensure defaults are populated.
     */
    public static function forSection(string $section): self
    {
        if (! self::isValidSection($section)) {
            abort(404, 'AI section not found.');
        }

        $defaults = self::defaultAttributesForSection($section);

        return static::firstOrCreate(
            ['section' => $section],
            $defaults
        );
    }

    /**
     * Default attributes used when creating a section for the first time.
     */
    protected static function defaultAttributesForSection(string $section): array
    {
        return match ($section) {
            self::SECTION_REPORT_READER => [
                'provider' => self::PROVIDER_OPENAI,
                'model' => 'gpt-4o-mini',
                'temperature' => 0.3,
                'max_tokens' => 1500,
                'top_p' => 1.0,
                'frequency_penalty' => 0.0,
                'presence_penalty' => 0.0,
                'extra_settings' => [],
            ],
            default => [
                'provider' => self::PROVIDER_OPENAI,
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
                'max_tokens' => 2048,
                'top_p' => 1.0,
                'frequency_penalty' => 0.0,
                'presence_penalty' => 0.0,
                'extra_settings' => [],
            ],
        };
    }
}
