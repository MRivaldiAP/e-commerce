<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\PageSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $viewPath = base_path("themes/{$activeTheme}/views/article.blade.php");

        if (! File::exists($viewPath)) {
            abort(404);
        }

        $settings = PageSetting::forPage('article');

        $articlesQuery = Article::published();

        if ($search = trim($request->input('search', ''))) {
            $articlesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('meta_title', 'like', "%{$search}%")
                    ->orWhere('meta_description', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($year = $request->input('year')) {
            $articlesQuery->whereYear('published_at', $year);
        }

        if ($month = $request->input('month')) {
            $articlesQuery->whereMonth('published_at', $month);
        }

        $articles = $articlesQuery
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Article $article) => $this->transformArticle($article));

        $timeline = Article::published()
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->get()
            ->groupBy(fn (Article $article) => $article->published_at->format('Y'))
            ->map(function ($yearGroup) {
                return $yearGroup->groupBy(fn (Article $article) => $article->published_at->format('m'))
                    ->sortKeysDesc()
                    ->map(function ($monthGroup) {
                        $first = $monthGroup->first();
                        $monthName = optional($first->published_at)->locale(app()->getLocale())->isoFormat('MMMM');

                        return [
                            'name' => $monthName,
                            'articles' => $monthGroup->map(fn (Article $article) => $this->transformArticle($article))->values(),
                        ];
                    });
            })
            ->sortKeysDesc();

        $firstArticle = $articles->first();
        $metaTitle = $settings['seo.meta_title'] ?? ($settings['hero.heading'] ?? 'Artikel');
        $metaDescription = $settings['seo.meta_description'] ?? Str::limit($firstArticle['excerpt'] ?? ($firstArticle['meta_description'] ?? ''), 160);

        return view()->file($viewPath, [
            'theme' => $activeTheme,
            'settings' => $settings,
            'articles' => $articles,
            'timeline' => $timeline,
            'filters' => [
                'search' => $request->input('search'),
                'year' => $request->input('year'),
                'month' => $request->input('month'),
            ],
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
            ],
        ]);
    }

    public function show(string $slug)
    {
        $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $viewPath = base_path("themes/{$activeTheme}/views/article-detail.blade.php");

        if (! File::exists($viewPath)) {
            abort(404);
        }

        $article = Article::published()->where('slug', $slug)->firstOrFail();

        $settings = PageSetting::forPage('article-detail');
        $listSettings = PageSetting::forPage('article');

        $recommended = Article::published()
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(fn (Article $item) => $this->transformArticle($item));

        $metaTitle = $article->meta_title ?: $article->title;
        $metaDescription = $article->meta_description ?: Str::limit($article->excerpt ?? strip_tags($article->content), 160);

        return view()->file($viewPath, [
            'theme' => $activeTheme,
            'settings' => $settings,
            'listSettings' => $listSettings,
            'article' => $this->transformArticle($article),
            'recommended' => $recommended,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
            ],
        ]);
    }

    protected function transformArticle(Article $article): array
    {
        $publishedAt = $article->published_at;

        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'meta_title' => $article->meta_title,
            'meta_description' => $article->meta_description,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'image' => $article->featured_image,
            'author' => null,
            'date_object' => $publishedAt,
            'date_formatted' => $publishedAt ? $publishedAt->locale(app()->getLocale())->isoFormat('D MMMM Y') : null,
            'year' => $publishedAt?->format('Y'),
            'month' => $publishedAt?->format('m'),
        ];
    }
}
