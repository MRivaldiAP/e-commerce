<?php

namespace App\Support;

class PageElements
{
    /**
     * @var array<string, mixed>
     */
    protected static array $definitionCache = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected static array $availabilityCache = [];

    /**
     * Get the master element definitions shared across themes.
     */
    public static function definitions(): array
    {
        if (! array_key_exists('__master__', self::$definitionCache)) {
            $path = base_path('themes/element.php');
            self::$definitionCache['__master__'] = file_exists($path) ? include $path : [];
        }

        return self::$definitionCache['__master__'];
    }

    /**
     * Get the element availability map for a specific theme.
     */
    public static function availability(string $theme): array
    {
        if (! array_key_exists($theme, self::$availabilityCache)) {
            $path = base_path('themes/' . $theme . '/element.php');
            self::$availabilityCache[$theme] = file_exists($path) ? include $path : [];
        }

        return self::$availabilityCache[$theme];
    }

    /**
     * Resolve the sections for a given page and theme.
     *
     * @return array<string, array{label: string, elements: array<int, array<string, mixed>>}>
     */
    public static function sections(string $page, string $theme): array
    {
        $definitions = self::definitions();
        $pageDefinition = $definitions[$page]['sections'] ?? [];

        if ($pageDefinition === []) {
            return [];
        }

        $availability = self::availability($theme);

        if (array_key_exists($page, $availability)) {
            $pageAvailability = $availability[$page];
        } else {
            $pageAvailability = [];
            foreach ($pageDefinition as $sectionKey => $sectionDefinition) {
                $pageAvailability[$sectionKey] = array_keys($sectionDefinition['elements'] ?? []);
            }
        }

        $resolved = [];

        foreach ($pageDefinition as $sectionKey => $sectionDefinition) {
            if (! array_key_exists($sectionKey, $pageAvailability)) {
                continue;
            }

            $allowedElements = $pageAvailability[$sectionKey];

            if ($allowedElements === ['*']) {
                $allowedElements = array_keys($sectionDefinition['elements'] ?? []);
            }

            $elements = [];
            foreach ($allowedElements as $elementId) {
                if (! isset($sectionDefinition['elements'][$elementId])) {
                    continue;
                }

                $config = $sectionDefinition['elements'][$elementId];
                $elements[] = array_merge(['id' => $elementId], $config);
            }

            if ($elements === []) {
                continue;
            }

            $resolved[$sectionKey] = [
                'label' => $sectionDefinition['label'] ?? ucfirst($sectionKey),
                'elements' => $elements,
            ];
        }

        return $resolved;
    }

    /**
     * Flush cached definitions or availability maps.
     */
    public static function flush(?string $theme = null): void
    {
        if ($theme === null) {
            self::$definitionCache = [];
            self::$availabilityCache = [];

            return;
        }

        unset(self::$availabilityCache[$theme]);
    }

    /**
     * Resolve the active section keys for a page based on stored settings.
     *
     * @param array<string, mixed> $pageSettings
     * @return array<int, string>
     */
    public static function activeSectionKeys(string $page, string $theme, array $pageSettings = []): array
    {
        $allSections = self::sections($page, $theme);

        if ($allSections === []) {
            return [];
        }

        $defaultOrder = array_keys($allSections);

        $rawComposition = $pageSettings['__sections'] ?? null;
        $composition = [];
        $hasCustomComposition = false;

        if (is_string($rawComposition) && $rawComposition !== '') {
            $decoded = json_decode($rawComposition, true);
            if (is_array($decoded)) {
                $hasCustomComposition = true;

                foreach ($decoded as $key) {
                    if (
                        is_string($key)
                        && array_key_exists($key, $allSections)
                        && ! in_array($key, $composition, true)
                    ) {
                        $composition[] = $key;
                    }
                }

                if ($decoded !== [] && $composition === []) {
                    $hasCustomComposition = false;
                }
            }
        }

        if ($composition === []) {
            $composition = $hasCustomComposition ? [] : $defaultOrder;
        }

        return $composition;
    }
}
