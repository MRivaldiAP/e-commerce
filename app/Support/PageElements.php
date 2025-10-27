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
     * @var array<string, array<string, array<string, mixed>>>
     */
    protected static array $themeSectionCache = [];

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
                'origins' => [$page],
            ];
        }

        return $resolved;
    }

    /**
     * Resolve the section catalogue available for a given theme.
     *
     * @return array<string, array{label: string, elements: array<int, array<string, mixed>>, origins: array<int, string>}>
     */
    public static function themeSections(string $theme): array
    {
        if (array_key_exists($theme, self::$themeSectionCache)) {
            return self::$themeSectionCache[$theme];
        }

        $definitions = self::definitions();

        if ($definitions === []) {
            self::$themeSectionCache[$theme] = [];

            return [];
        }

        $resolved = [];

        foreach (array_keys($definitions) as $pageKey) {
            $sections = self::sections($pageKey, $theme);

            foreach ($sections as $sectionKey => $definition) {
                if (! isset($resolved[$sectionKey])) {
                    $resolved[$sectionKey] = $definition;
                    continue;
                }

                $existing = $resolved[$sectionKey];

                $existingOrigins = $existing['origins'] ?? [];
                $existingOrigins[] = $pageKey;
                $resolved[$sectionKey]['origins'] = array_values(array_unique($existingOrigins));

                $indexed = [];
                foreach ($existing['elements'] ?? [] as $element) {
                    if (isset($element['id'])) {
                        $indexed[$element['id']] = $element;
                    }
                }

                foreach ($definition['elements'] ?? [] as $element) {
                    if (isset($element['id'])) {
                        $indexed[$element['id']] = $element;
                    }
                }

                $resolved[$sectionKey]['elements'] = array_values($indexed);

                $existingLabel = $resolved[$sectionKey]['label'] ?? null;
                $nextLabel = $definition['label'] ?? null;

                if (
                    (! is_string($existingLabel) || $existingLabel === '' || $existingLabel === ucfirst($sectionKey))
                    && is_string($nextLabel)
                    && $nextLabel !== ''
                ) {
                    $resolved[$sectionKey]['label'] = $nextLabel;
                }
            }
        }

        self::$themeSectionCache[$theme] = $resolved;

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
            self::$themeSectionCache = [];

            return;
        }

        unset(self::$availabilityCache[$theme]);
        unset(self::$themeSectionCache[$theme]);
    }

    /**
     * Resolve the active section keys for a page based on stored settings.
     *
     * @param array<string, mixed> $pageSettings
     * @return array<int, string>
     */
    public static function activeSectionKeys(string $page, string $theme, array $pageSettings = []): array
    {
        $defaultSections = self::sections($page, $theme);
        $themeSections = self::themeSections($theme);

        $allSections = $themeSections;

        foreach ($defaultSections as $key => $definition) {
            $definition['label'] = $definition['label'] ?? ($themeSections[$key]['label'] ?? ucfirst($key));
            $definition['elements'] = $definition['elements'] ?? ($themeSections[$key]['elements'] ?? []);
            $definition['origins'] = array_values(array_unique(array_merge(
                $themeSections[$key]['origins'] ?? [],
                $definition['origins'] ?? [$page]
            )));

            $allSections[$key] = $definition;
        }

        if ($allSections === []) {
            return [];
        }

        $defaultOrder = array_keys($defaultSections);
        if ($defaultOrder === []) {
            $defaultOrder = array_keys($allSections);
        }

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
