<?php

namespace App\Http\Controllers;

use App\Models\EducationArticle;
use App\Models\EducationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EducationController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category', 'all');
        $categories = EducationCategory::where('is_active', true)
            ->ordered()
            ->pluck('name', 'slug')
            ->all();

        $query = EducationArticle::published()
            ->with('categoryRecord')
            ->withCount('steps')
            ->latest('published_at');

        if ($category !== 'all' && array_key_exists($category, $categories)) {
            $query->where('category', $category);
        }

        return view('dashboard.education-index', [
            'articles' => $query->paginate(12)->withQueryString(),
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    public function show(EducationArticle $educationArticle): View
    {
        if (! $educationArticle->isPublished() && Auth::user()->role !== 'admin') {
            abort(404);
        }

        $educationArticle->load(['steps', 'categoryRecord']);

        $related = EducationArticle::published()
            ->with('categoryRecord')
            ->whereKeyNot($educationArticle->id)
            ->where('category', $educationArticle->category)
            ->withCount('steps')
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('dashboard.education-show', [
            'article' => $educationArticle,
            'related' => $related,
        ]);
    }
}
