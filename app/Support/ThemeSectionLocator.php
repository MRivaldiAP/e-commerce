<?php
namespace App\Support;

use Illuminate\Support\Facades\View;

class ThemeSectionLocator
{
    /**
     * Resolve the view name for a section key within a theme.
     *
     * @param string $theme Theme identifier (folder name).
     * @param string $viewNamespace Registered view namespace for the theme.
     * @param string $currentPage Page key currently being rendered.
     * @param string $sectionKey Section identifier.
     * @param array<int, string> $origins Ordered list of pages that define the section.
     */
    public static function resolve(string $theme, string $viewNamespace, string $currentPage, string $sectionKey, array $origins = []): ?string
    {
        $candidates = [];

        $candidates[] = sprintf('%s::components.%s.sections.%s', $viewNamespace, $currentPage, $sectionKey);

        foreach ($origins as $originPage) {
            if ($originPage === $currentPage) {
                continue;
            }

            $candidates[] = sprintf('%s::components.%s.sections.%s', $viewNamespace, $originPage, $sectionKey);
        }

        foreach ($origins as $originPage) {
            $candidates[] = sprintf('%s::components.%s.section.%s', $viewNamespace, $originPage, $sectionKey);
        }

        $candidates[] = sprintf('%s::components.sections.%s', $viewNamespace, $sectionKey);

        $themeSections = PageElements::themeSections($theme);
        if (isset($themeSections[$sectionKey]['origins'])) {
            foreach ($themeSections[$sectionKey]['origins'] as $originPage) {
                $candidates[] = sprintf('%s::components.%s.sections.%s', $viewNamespace, $originPage, $sectionKey);
            }
        }

        $uniqueCandidates = [];
        foreach ($candidates as $candidate) {
            if (! in_array($candidate, $uniqueCandidates, true)) {
                $uniqueCandidates[] = $candidate;
            }
        }

        foreach ($uniqueCandidates as $view) {
            if (View::exists($view)) {
                return $view;
            }
        }

        return null;
    }
}
