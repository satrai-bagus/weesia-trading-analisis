<x-layouts.app title="Admin - Edukasi">
    <x-dashboard-shell active="admin-education">
        @php
            $statusOptions = [
                'all' => 'Semua',
                'published' => 'Published',
                'draft' => 'Draft',
            ];
            $createSteps = old('steps', [
                ['title' => '', 'body' => '', 'youtube_url' => ''],
                ['title' => '', 'body' => '', 'youtube_url' => ''],
            ]);
            $defaultCategory = old('category', array_key_first($categories));
        @endphp

        <section class="relative mx-auto max-w-7xl px-4 pb-12 pt-24 sm:px-6 sm:pt-28">
            @if (session('status'))
                <div class="reveal mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="reveal mb-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <div class="reveal flex flex-col items-start justify-between gap-4 border-b border-ink-700/60 pb-6 sm:flex-row sm:items-end">
                <div>
                    <div class="inline-flex items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Admin &middot; Edukasi</span>
                    </div>
                    <h1 class="mt-3 font-display text-3xl text-ink-50 sm:text-4xl">Modul Edukasi</h1>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-ink-300">Kelola tutorial step-by-step untuk chart, exchange, dan basic trading.</p>
                </div>
                <a href="#new-education" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-4 py-2 font-mono text-[10px] font-semibold uppercase tracking-[0.22em] text-ink-900 shadow-[0_10px_30px_-10px_rgba(212,167,44,0.7)] transition-all hover:shadow-[0_14px_40px_-10px_rgba(212,167,44,0.95)]">
                    <x-icon name="plus" class="h-3.5 w-3.5" />
                    Buat Edukasi
                </a>
            </div>

            <div class="reveal mt-6 grid grid-cols-3 gap-px overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-700/40">
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Total</div>
                    <div class="mt-2 font-display text-3xl text-ink-50">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Published</div>
                    <div class="mt-2 font-display text-3xl text-emerald-200">{{ $stats['published'] }}</div>
                </div>
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Draft</div>
                    <div class="mt-2 font-display text-3xl text-gold-200">{{ $stats['draft'] }}</div>
                </div>
            </div>

            <div class="reveal mt-6 rounded-3xl border border-ink-700/60 bg-ink-900/75 p-6 backdrop-blur-xl sm:p-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Kategori</div>
                        <h2 class="mt-2 font-display text-2xl text-ink-50">Kelola Kategori Edukasi</h2>
                        <p class="mt-1 max-w-xl text-xs leading-relaxed text-ink-300">Nama kategori bisa diedit kapan saja. Slug dijaga stabil supaya artikel lama tetap aman.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.education-categories.store') }}" class="flex w-full max-w-md flex-col gap-2 sm:flex-row">
                        @csrf
                        <input type="text" name="name" required maxlength="80" placeholder="Kategori baru"
                               class="min-w-0 flex-1 rounded-xl border border-ink-700/70 bg-ink-800/60 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                        <button class="inline-flex items-center justify-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-100 transition-all hover:border-gold-400/70">
                            <x-icon name="plus" class="h-3 w-3" />
                            Tambah
                        </button>
                    </form>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-3">
                    @foreach ($categoryModels as $categoryModel)
                        <div class="rounded-2xl border border-ink-700/60 bg-ink-800/35 p-4">
                            <form method="POST" action="{{ route('admin.education-categories.update', $categoryModel) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <div class="grid grid-cols-[minmax(0,1fr)_72px] gap-2">
                                    <input type="text" name="name" value="{{ $categoryModel->name }}" required maxlength="80"
                                           class="min-w-0 rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    <input type="number" name="sort_order" value="{{ $categoryModel->sort_order }}" min="0" max="999"
                                           class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300">{{ $categoryModel->slug }} &middot; {{ $categoryModel->articles_count }} artikel</div>
                                    <label class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-200">
                                        <input type="checkbox" name="is_active" value="1" @checked($categoryModel->is_active) class="h-3.5 w-3.5 rounded border-ink-600 bg-ink-800 text-gold-400 focus:ring-gold-400">
                                        Aktif
                                    </label>
                                </div>
                                <div class="flex items-center justify-end">
                                    <button class="rounded-full border border-ink-600/70 bg-ink-900/70 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-200 transition-all hover:border-gold-500/40 hover:text-gold-100">Update</button>
                                </div>
                            </form>

                            @if ($categoryModel->articles_count === 0)
                                <form method="POST" action="{{ route('admin.education-categories.destroy', $categoryModel) }}" onsubmit="return confirm('Hapus kategori ini?')" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button class="font-mono text-[10px] uppercase tracking-[0.2em] text-rose-300 transition-colors hover:text-rose-200">Hapus kategori</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="new-education" class="reveal mt-6 rounded-3xl border border-gold-500/25 bg-ink-900/75 p-6 backdrop-blur-xl sm:p-8">
                <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Artikel Baru</div>
                <h2 class="mt-2 font-display text-2xl text-ink-50">Buat Tutorial Step-by-Step</h2>

                <form method="POST" action="{{ route('admin.education.store') }}" enctype="multipart/form-data" class="mt-5 space-y-5" data-education-form>
                    @csrf

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Judul <span class="text-rose-300">*</span></span>
                            <input type="text" name="title" value="{{ old('title') }}" required maxlength="160" placeholder="Cara Membaca Area Entry, TP, dan SL"
                                   class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                        </label>

                        <label class="block">
                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Kategori <span class="text-rose-300">*</span></span>
                            <select name="category" required class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                @foreach ($categories as $value => $label)
                                    <option value="{{ $value }}" @selected($defaultCategory === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Video YouTube Utama</span>
                            <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" maxlength="500" placeholder="https://youtu.be/..."
                                   class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-xs text-ink-100 outline-none focus:border-gold-500/50">
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Intro / Ringkasan</span>
                            <textarea name="summary" rows="3" maxlength="2000" placeholder="Ringkasan singkat yang muncul di halaman list dan pembuka detail."
                                      class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">{{ old('summary') }}</textarea>
                        </label>

                        <label class="block">
                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Cover Image</span>
                            <span class="mt-1 block text-[11px] text-ink-400">Rasio 16:9 disarankan. JPG/PNG max 4MB.</span>
                            <input type="file" name="cover_image" accept="image/*"
                                   class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-3 py-2 text-xs text-ink-200 file:mr-3 file:rounded-xl file:border-0 file:bg-gold-500/15 file:px-3 file:py-1.5 file:text-xs file:text-gold-200">
                        </label>

                        <label class="inline-flex items-center gap-3 self-end rounded-2xl border border-ink-700/70 bg-ink-800/40 px-4 py-3 font-mono text-[10px] uppercase tracking-[0.22em] text-gold-200">
                            <input type="checkbox" name="is_published" value="1" @checked(old('is_published')) class="h-4 w-4 rounded border-ink-600 bg-ink-800 text-gold-400 focus:ring-gold-400">
                            Publish langsung
                        </label>
                    </div>

                    <div class="rounded-2xl border border-ink-700/60 bg-ink-800/30 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Steps</div>
                                <p class="mt-1 text-xs text-ink-300">Isi judul, penjelasan, gambar, atau video pada tiap langkah.</p>
                            </div>
                            <button type="button" data-add-education-step class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 bg-ink-900/70 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200 transition-all hover:border-gold-500/40 hover:text-gold-100">
                                <x-icon name="plus" class="h-3 w-3" />
                                Tambah Step
                            </button>
                        </div>

                        <div class="mt-4 space-y-3" data-education-step-list>
                            @foreach ($createSteps as $i => $step)
                                <div class="rounded-2xl border border-ink-700/60 bg-ink-900/70 p-4" data-education-step-row>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Step {{ $i + 1 }}</div>
                                        <button type="button" data-remove-education-step class="font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300 transition-colors hover:text-rose-200">Hapus</button>
                                    </div>
                                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <input type="text" name="steps[{{ $i }}][title]" value="{{ $step['title'] ?? '' }}" maxlength="160" placeholder="Judul step"
                                               class="rounded-xl border border-ink-700 bg-ink-800/70 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                        <input type="url" name="steps[{{ $i }}][youtube_url]" value="{{ $step['youtube_url'] ?? '' }}" maxlength="500" placeholder="YouTube step (opsional)"
                                               class="rounded-xl border border-ink-700 bg-ink-800/70 px-3 py-2 font-mono text-xs text-ink-100 outline-none focus:border-gold-500/50">
                                        <textarea name="steps[{{ $i }}][body]" rows="3" placeholder="Penjelasan step" class="sm:col-span-2 rounded-xl border border-ink-700 bg-ink-800/70 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">{{ $step['body'] ?? '' }}</textarea>
                                        <input type="file" name="steps[{{ $i }}][image]" accept="image/*"
                                               class="sm:col-span-2 block w-full rounded-xl border border-ink-700 bg-ink-800/70 px-3 py-2 text-xs text-ink-200 file:mr-3 file:rounded-lg file:border-0 file:bg-gold-500/15 file:px-2 file:py-1 file:text-[11px] file:text-gold-200">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <button class="inline-flex items-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(212,167,44,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(212,167,44,1)]">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            Simpan Edukasi
                        </button>
                    </div>
                </form>
            </div>

            <div class="reveal mt-8 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.education', ['category' => 'all', 'status' => $status]) }}" class="rounded-full border px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] transition-all {{ $category === 'all' ? 'border-gold-500/50 bg-gold-500/15 text-gold-100' : 'border-ink-700/70 bg-ink-800/50 text-ink-300 hover:border-gold-500/40 hover:text-gold-100' }}">Semua Kategori</a>
                    @foreach ($categoryModels as $categoryModel)
                        <a href="{{ route('admin.education', ['category' => $categoryModel->slug, 'status' => $status]) }}" class="rounded-full border px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] transition-all {{ $category === $categoryModel->slug ? 'border-gold-500/50 bg-gold-500/15 text-gold-100' : 'border-ink-700/70 bg-ink-800/50 text-ink-300 hover:border-gold-500/40 hover:text-gold-100' }}">{{ $categoryModel->name }}</a>
                    @endforeach
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($statusOptions as $value => $label)
                        <a href="{{ route('admin.education', ['category' => $category, 'status' => $value]) }}" class="rounded-full border px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] transition-all {{ $status === $value ? 'border-gold-500/50 bg-gold-500/15 text-gold-100' : 'border-ink-700/70 bg-ink-800/50 text-ink-300 hover:border-gold-500/40 hover:text-gold-100' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <div class="reveal mt-4 space-y-3">
                @forelse ($articles as $article)
                    <article class="rounded-2xl border border-ink-700/60 bg-ink-900/70 p-4 backdrop-blur-md sm:p-5">
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[13rem_minmax(0,1fr)_auto] lg:items-start">
                            <div class="overflow-hidden rounded-xl border border-ink-700/60 bg-ink-800/40" style="width: 100%; max-width: 13rem; aspect-ratio: 16 / 9;">
                                @if ($article->cover_image_path)
                                    <img src="{{ asset('storage/'.$article->cover_image_path) }}" alt="{{ $article->title }}" class="aspect-video w-full object-cover" style="display:block;width:100%;height:100%;object-fit:cover;">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-ink-500">
                                        <x-icon name="image-plus" class="h-6 w-6" />
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-gold-500/30 bg-gold-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200">{{ $article->categoryLabel() }}</span>
                                    <span class="rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] {{ $article->isPublished() ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : 'border-ink-700/70 bg-ink-800/60 text-ink-300' }}">{{ $article->isPublished() ? 'Published' : 'Draft' }}</span>
                                    <span class="rounded-full border border-ink-700/70 bg-ink-800/60 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $article->steps_count }} step</span>
                                </div>
                                <h3 class="mt-2 font-display text-xl text-ink-50">{{ $article->title }}</h3>
                                @if ($article->summary)
                                    <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-ink-300">{{ $article->summary }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">
                                    <span>{{ $article->updated_at->format('d M Y H:i') }}</span>
                                    @if ($article->createdBy)
                                        <span>{{ $article->createdBy->name }}</span>
                                    @endif
                                    @if ($article->isPublished())
                                        <a href="{{ route('education.show', $article) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-gold-300 transition-colors hover:text-gold-100">
                                            <x-icon name="arrow-up-right" class="h-3 w-3" />
                                            Preview
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-2 lg:justify-end">
                                <form method="POST" action="{{ route('admin.education.destroy', $article) }}" onsubmit="return confirm('Hapus artikel edukasi ini? File gambar step juga ikut dihapus.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex items-center gap-2 rounded-full border border-ink-700/70 bg-ink-800/60 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-all hover:border-rose-500/40 hover:text-rose-200">
                                        <x-icon name="trash" class="h-3 w-3" />
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        <details class="group mt-4 rounded-2xl border border-ink-700/60 bg-ink-800/30">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-colors hover:text-gold-200 [&::-webkit-details-marker]:hidden">
                                <span class="inline-flex items-center gap-2">
                                    <x-icon name="edit" class="h-3 w-3" />
                                    Edit Artikel
                                </span>
                                <span class="font-mono text-[9px] text-ink-500 group-open:hidden">Klik untuk buka</span>
                            </summary>

                            <form method="POST" action="{{ route('admin.education.update', $article) }}" enctype="multipart/form-data" class="space-y-4 border-t border-ink-700/60 p-4" data-education-form>
                                @csrf
                                @method('PATCH')

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="block sm:col-span-2">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Judul</span>
                                        <input type="text" name="title" value="{{ $article->title }}" required maxlength="160"
                                               class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    </label>

                                    <label class="block">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Kategori</span>
                                        <select name="category" required class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                            @foreach ($categories as $value => $label)
                                                <option value="{{ $value }}" @selected($article->category === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Video YouTube Utama</span>
                                        <input type="url" name="youtube_url" value="{{ $article->youtube_url }}" maxlength="500"
                                               class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 font-mono text-xs text-ink-100 outline-none focus:border-gold-500/50">
                                    </label>

                                    <label class="block sm:col-span-2">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Intro / Ringkasan</span>
                                        <textarea name="summary" rows="2" maxlength="2000" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">{{ $article->summary }}</textarea>
                                    </label>

                                    <div class="sm:col-span-2 grid grid-cols-1 gap-3 sm:grid-cols-[160px_minmax(0,1fr)]">
                                        <div class="overflow-hidden rounded-xl border border-ink-700/60 bg-ink-800/40" style="width: 100%; max-width: 160px; aspect-ratio: 16 / 9;">
                                            @if ($article->cover_image_path)
                                                <img src="{{ asset('storage/'.$article->cover_image_path) }}" alt="Cover" class="aspect-video w-full object-cover" style="display:block;width:100%;height:100%;object-fit:cover;">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center font-mono text-[10px] uppercase tracking-[0.2em] text-ink-500">No cover</div>
                                            @endif
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block">
                                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Ganti Cover</span>
                                                <input type="file" name="cover_image" accept="image/*"
                                                       class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-1.5 text-xs text-ink-200 file:mr-3 file:rounded-lg file:border-0 file:bg-gold-500/15 file:px-2 file:py-1 file:text-[11px] file:text-gold-200">
                                            </label>
                                            <div class="flex flex-wrap gap-4">
                                                @if ($article->cover_image_path)
                                                    <label class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300">
                                                        <input type="checkbox" name="remove_cover_image" value="1" class="h-3.5 w-3.5 rounded border-ink-600 bg-ink-800 text-rose-400 focus:ring-rose-400">
                                                        Hapus cover
                                                    </label>
                                                @endif
                                                <label class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-gold-200">
                                                    <input type="checkbox" name="is_published" value="1" @checked($article->isPublished()) class="h-3.5 w-3.5 rounded border-ink-600 bg-ink-800 text-gold-400 focus:ring-gold-400">
                                                    Published
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-ink-700/60 bg-ink-900/50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Steps</div>
                                        <button type="button" data-add-education-step class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 bg-ink-800/50 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200 transition-all hover:border-gold-500/40 hover:text-gold-100">
                                            <x-icon name="plus" class="h-3 w-3" />
                                            Tambah Step
                                        </button>
                                    </div>

                                    <div class="mt-4 space-y-3" data-education-step-list>
                                        @foreach ($article->steps as $i => $step)
                                            <div class="rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4" data-education-step-row>
                                                <input type="hidden" name="steps[{{ $i }}][id]" value="{{ $step->id }}">
                                                <div class="flex flex-wrap items-center justify-between gap-3">
                                                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Step {{ $i + 1 }}</div>
                                                    <label class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300">
                                                        <input type="checkbox" name="steps[{{ $i }}][delete]" value="1" class="h-3.5 w-3.5 rounded border-ink-600 bg-ink-800 text-rose-400 focus:ring-rose-400">
                                                        Hapus step
                                                    </label>
                                                </div>
                                                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                    <input type="text" name="steps[{{ $i }}][title]" value="{{ $step->title }}" maxlength="160" placeholder="Judul step"
                                                           class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                                    <input type="url" name="steps[{{ $i }}][youtube_url]" value="{{ $step->youtube_url }}" maxlength="500" placeholder="YouTube step (opsional)"
                                                           class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 font-mono text-xs text-ink-100 outline-none focus:border-gold-500/50">
                                                    <textarea name="steps[{{ $i }}][body]" rows="3" placeholder="Penjelasan step" class="sm:col-span-2 rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">{{ $step->body }}</textarea>

                                                    <div class="sm:col-span-2 grid grid-cols-1 gap-3 sm:grid-cols-[140px_minmax(0,1fr)]">
                                                        <div class="overflow-hidden rounded-xl border border-ink-700/60 bg-ink-900/70" style="width: 100%; max-width: 140px; aspect-ratio: 16 / 9;">
                                                            @if ($step->image_path)
                                                                <img src="{{ asset('storage/'.$step->image_path) }}" alt="Step image" class="aspect-video w-full object-cover" style="display:block;width:100%;height:100%;object-fit:cover;">
                                                            @else
                                                                <div class="flex h-full w-full items-center justify-center font-mono text-[10px] uppercase tracking-[0.2em] text-ink-500">No image</div>
                                                            @endif
                                                        </div>
                                                        <div class="space-y-2">
                                                            <input type="file" name="steps[{{ $i }}][image]" accept="image/*"
                                                                   class="block w-full rounded-xl border border-ink-700 bg-ink-900 px-3 py-1.5 text-xs text-ink-200 file:mr-3 file:rounded-lg file:border-0 file:bg-gold-500/15 file:px-2 file:py-1 file:text-[11px] file:text-gold-200">
                                                            @if ($step->image_path)
                                                                <label class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300">
                                                                    <input type="checkbox" name="steps[{{ $i }}][remove_image]" value="1" class="h-3.5 w-3.5 rounded border-ink-600 bg-ink-800 text-rose-400 focus:ring-rose-400">
                                                                    Hapus gambar
                                                                </label>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex items-center justify-end">
                                    <button class="inline-flex items-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-100 transition-all hover:border-gold-400/70">
                                        <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                        Update Artikel
                                    </button>
                                </div>
                            </form>
                        </details>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-ink-700/60 bg-ink-800/30 px-6 py-12 text-center">
                        <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full border border-ink-700/70 bg-ink-800/60 text-ink-400">
                            <x-icon name="clipboard" class="h-5 w-5" />
                        </div>
                        <p class="mt-3 text-sm text-ink-300">Belum ada artikel edukasi.</p>
                    </div>
                @endforelse
            </div>

            <div class="reveal mt-6">
                {{ $articles->links() }}
            </div>
        </section>

        <script>
            document.addEventListener('click', (event) => {
                const addButton = event.target.closest('[data-add-education-step]');
                if (addButton) {
                    const form = addButton.closest('[data-education-form]');
                    const list = form?.querySelector('[data-education-step-list]');
                    if (!list) return;

                    const idx = Date.now() + list.children.length;
                    const displayNumber = list.children.length + 1;
                    const row = document.createElement('div');
                    row.className = 'rounded-2xl border border-ink-700/60 bg-ink-900/70 p-4';
                    row.setAttribute('data-education-step-row', '');
                    row.innerHTML = `
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Step ${displayNumber}</div>
                            <button type="button" data-remove-education-step class="font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300 transition-colors hover:text-rose-200">Hapus</button>
                        </div>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <input type="text" name="steps[${idx}][title]" maxlength="160" placeholder="Judul step" class="rounded-xl border border-ink-700 bg-ink-800/70 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            <input type="url" name="steps[${idx}][youtube_url]" maxlength="500" placeholder="YouTube step (opsional)" class="rounded-xl border border-ink-700 bg-ink-800/70 px-3 py-2 font-mono text-xs text-ink-100 outline-none focus:border-gold-500/50">
                            <textarea name="steps[${idx}][body]" rows="3" placeholder="Penjelasan step" class="sm:col-span-2 rounded-xl border border-ink-700 bg-ink-800/70 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50"></textarea>
                            <input type="file" name="steps[${idx}][image]" accept="image/*" class="sm:col-span-2 block w-full rounded-xl border border-ink-700 bg-ink-800/70 px-3 py-2 text-xs text-ink-200 file:mr-3 file:rounded-lg file:border-0 file:bg-gold-500/15 file:px-2 file:py-1 file:text-[11px] file:text-gold-200">
                        </div>
                    `;
                    list.appendChild(row);
                    return;
                }

                const removeButton = event.target.closest('[data-remove-education-step]');
                if (!removeButton) return;

                const row = removeButton.closest('[data-education-step-row]');
                row?.remove();
            });
        </script>
    </x-dashboard-shell>
</x-layouts.app>
