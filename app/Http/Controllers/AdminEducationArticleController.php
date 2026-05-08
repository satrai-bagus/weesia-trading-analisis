<?php

namespace App\Http\Controllers;

use App\Models\EducationArticle;
use App\Models\EducationCategory;
use App\Models\EducationStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminEducationArticleController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('user.dashboard');
        }

        $category = $request->query('category', 'all');
        $status = $request->query('status', 'all');

        $categories = EducationCategory::ordered()->get();
        $categoryOptions = $categories
            ->where('is_active', true)
            ->pluck('name', 'slug')
            ->all();

        $query = EducationArticle::with(['createdBy', 'steps', 'categoryRecord'])
            ->withCount('steps')
            ->latest('updated_at');

        if ($category !== 'all' && $categories->contains('slug', $category)) {
            $query->where('category', $category);
        }

        if ($status === 'published') {
            $query->published();
        } elseif ($status === 'draft') {
            $query->whereNull('published_at');
        }

        return view('dashboard.admin-education', [
            'articles' => $query->paginate(12)->withQueryString(),
            'categories' => $categoryOptions,
            'categoryModels' => $categories->loadCount('articles'),
            'category' => $category,
            'status' => $status,
            'stats' => [
                'total' => EducationArticle::count(),
                'published' => EducationArticle::published()->count(),
                'draft' => EducationArticle::whereNull('published_at')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $this->validatePayload($request);
        $steps = $this->contentfulStepRows($validated['steps'] ?? [], $request);

        if ($steps === []) {
            return back()->withErrors(['steps' => 'Isi minimal satu step edukasi.'])->withInput();
        }

        $article = EducationArticle::create([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'category' => $validated['category'],
            'summary' => $validated['summary'] ?? null,
            'youtube_url' => $validated['youtube_url'] ?? null,
            'cover_image_path' => $request->hasFile('cover_image')
                ? $request->file('cover_image')->store('education-covers', 'public')
                : null,
            'published_at' => $request->boolean('is_published') ? now() : null,
            'created_by_id' => Auth::id(),
        ]);

        $this->syncSteps($article, $steps, $request);

        return redirect()->route('admin.education')->with('status', 'Artikel edukasi dibuat.');
    }

    public function update(Request $request, EducationArticle $educationArticle): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $this->validatePayload($request, $educationArticle);
        $steps = $this->contentfulStepRows($validated['steps'] ?? [], $request, $educationArticle);

        if ($steps === []) {
            return back()->withErrors(['steps' => 'Artikel perlu minimal satu step edukasi.'])->withInput();
        }

        $payload = [
            'title' => $validated['title'],
            'category' => $validated['category'],
            'summary' => $validated['summary'] ?? null,
            'youtube_url' => $validated['youtube_url'] ?? null,
            'published_at' => $request->boolean('is_published')
                ? ($educationArticle->published_at ?? now())
                : null,
        ];

        if ($educationArticle->title !== $validated['title']) {
            $payload['slug'] = $this->uniqueSlug($validated['title'], $educationArticle);
        }

        if ($request->hasFile('cover_image')) {
            $this->deletePublicFile($educationArticle->cover_image_path);
            $payload['cover_image_path'] = $request->file('cover_image')->store('education-covers', 'public');
        } elseif ($request->boolean('remove_cover_image')) {
            $this->deletePublicFile($educationArticle->cover_image_path);
            $payload['cover_image_path'] = null;
        }

        $educationArticle->update($payload);
        $this->syncSteps($educationArticle, $steps, $request);

        return redirect()->route('admin.education')->with('status', 'Artikel edukasi diupdate.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $maxOrder = (int) EducationCategory::max('sort_order');

        EducationCategory::create([
            'name' => trim($validated['name']),
            'slug' => EducationCategory::uniqueSlug($validated['name']),
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return back()->with('status', 'Kategori edukasi ditambahkan.');
    }

    public function updateCategory(Request $request, EducationCategory $educationCategory): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $educationCategory->update([
            'name' => trim($validated['name']),
            'sort_order' => $validated['sort_order'] ?? $educationCategory->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Kategori edukasi diupdate.');
    }

    public function destroyCategory(EducationCategory $educationCategory): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($educationCategory->articles()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih dipakai artikel, jadi belum bisa dihapus.']);
        }

        $educationCategory->delete();

        return back()->with('status', 'Kategori edukasi dihapus.');
    }

    public function destroy(EducationArticle $educationArticle): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $this->deletePublicFile($educationArticle->cover_image_path);
        $educationArticle->steps->each(fn (EducationStep $step) => $this->deletePublicFile($step->image_path));
        $educationArticle->delete();

        return back()->with('status', 'Artikel edukasi dihapus.');
    }

    private function validatePayload(Request $request, ?EducationArticle $article = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', Rule::exists('education_categories', 'slug')->where('is_active', true)],
            'summary' => ['nullable', 'string', 'max:2000'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'steps' => ['nullable', 'array'],
            'steps.*.id' => ['nullable', 'integer'],
            'steps.*.title' => ['nullable', 'string', 'max:160'],
            'steps.*.body' => ['nullable', 'string', 'max:6000'],
            'steps.*.youtube_url' => ['nullable', 'url', 'max:500'],
            'steps.*.image' => ['nullable', 'image', 'max:4096'],
            'steps.*.remove_image' => ['nullable', 'boolean'],
            'steps.*.delete' => ['nullable', 'boolean'],
        ]);
    }

    private function contentfulStepRows(array $rows, Request $request, ?EducationArticle $article = null): array
    {
        $existingSteps = $article
            ? $article->steps()->get()->keyBy('id')
            : collect();

        return collect($rows)
            ->map(function (array $row, int|string $index) use ($request, $existingSteps) {
                $row['__index'] = (int) $index;
                $existing = isset($row['id']) ? $existingSteps->get((int) $row['id']) : null;

                if (! empty($row['delete'])) {
                    return $row;
                }

                $hasFile = $request->hasFile("steps.$index.image");
                $keepsExistingImage = $existing && $existing->image_path && empty($row['remove_image']);
                $hasText = filled($row['title'] ?? null)
                    || filled($row['body'] ?? null)
                    || filled($row['youtube_url'] ?? null);

                return ($hasText || $hasFile || $keepsExistingImage) ? $row : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function syncSteps(EducationArticle $article, array $rows, Request $request): void
    {
        $existingSteps = $article->steps()->get()->keyBy('id');
        $sortOrder = 1;

        foreach ($rows as $row) {
            $index = $row['__index'];
            $step = isset($row['id']) ? $existingSteps->get((int) $row['id']) : null;

            if (! empty($row['delete'])) {
                if ($step) {
                    $this->deletePublicFile($step->image_path);
                    $step->delete();
                }
                continue;
            }

            $payload = [
                'sort_order' => $sortOrder++,
                'title' => filled($row['title'] ?? null) ? trim($row['title']) : null,
                'body' => filled($row['body'] ?? null) ? trim($row['body']) : null,
                'youtube_url' => filled($row['youtube_url'] ?? null) ? trim($row['youtube_url']) : null,
            ];

            if ($request->hasFile("steps.$index.image")) {
                if ($step) {
                    $this->deletePublicFile($step->image_path);
                }
                $payload['image_path'] = $request->file("steps.$index.image")->store('education-steps', 'public');
            } elseif ($step && ! empty($row['remove_image'])) {
                $this->deletePublicFile($step->image_path);
                $payload['image_path'] = null;
            }

            if ($step) {
                $step->update($payload);
            } else {
                $article->steps()->create($payload);
            }
        }
    }

    private function uniqueSlug(string $title, ?EducationArticle $ignore = null): string
    {
        $base = Str::slug($title) ?: 'edukasi';
        $slug = $base;
        $suffix = 2;

        while (EducationArticle::where('slug', $slug)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
