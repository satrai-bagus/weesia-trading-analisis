<div id="publish-signal" class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                <x-icon name="bot" class="h-5 w-5" />
            </span>
            <div>
                <h2 class="font-display text-2xl leading-none text-ink-50">Publish Signal Baru</h2>
                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Real-time</div>
            </div>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 px-3 py-2 text-xs text-ink-200">
            <x-icon name="target" class="h-4 w-4" />
            Admin
        </span>
    </div>

    @if (session('status'))
        <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            <x-icon name="target" class="mt-0.5 h-4 w-4 shrink-0" />
            {{ session('status') }}
        </div>
    @endif
    @if (isset($errors) && $errors->any())
        <div class="mb-5 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('signals.store') }}" class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-2">
        @csrf
        <label>
            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Ticker Crypto</span>
            <input name="ticker" value="{{ old('ticker') }}" list="crypto-ticker-options" placeholder="BTC/USDT" required class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
            <datalist id="crypto-ticker-options">
                @foreach ($marketTickers as $ticker)
                    <option value="{{ $ticker['ticker'] }}">{{ $ticker['price'] ? '$'.$fmt($ticker['price']) : 'Harga live belum tersedia' }}</option>
                @endforeach
            </datalist>
            <span class="mt-2 block text-xs leading-relaxed text-ink-400">Harga live dipakai otomatis sebagai entry estimasi saat signal dipublish.</span>
        </label>
        <label>
            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Arah Posisi</span>
            <select name="position_side" required class="min-h-11 w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                <option value="{{ \App\Models\TradeSignal::SIDE_LONG }}" @selected(old('position_side', \App\Models\TradeSignal::SIDE_LONG) === \App\Models\TradeSignal::SIDE_LONG)>Long</option>
                <option value="{{ \App\Models\TradeSignal::SIDE_SHORT }}" @selected(old('position_side') === \App\Models\TradeSignal::SIDE_SHORT)>Short</option>
            </select>
        </label>
        <label>
            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Jangka Signal</span>
            <select name="signal_term" required class="min-h-11 w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                <option value="{{ \App\Models\TradeSignal::TERM_SHORT }}" @selected(old('signal_term', \App\Models\TradeSignal::TERM_SHORT) === \App\Models\TradeSignal::TERM_SHORT)>Short Term</option>
                <option value="{{ \App\Models\TradeSignal::TERM_LONG }}" @selected(old('signal_term') === \App\Models\TradeSignal::TERM_LONG)>Long Term</option>
            </select>
        </label>
        <label>
            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Bar Waktu</span>
            <input type="datetime-local" name="info_at" value="{{ old('info_at') }}" class="min-h-11 w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
            <span class="mt-2 block text-xs leading-relaxed text-ink-400">Opsional, hanya tampil di halaman admin.</span>
        </label>
        <x-form-field label="Take Profit 1" name="take_profit" placeholder="71420" />
        <x-form-field label="Stop Loss" name="stop_loss" placeholder="66930" />
        <label>
            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Leverage</span>
            <input name="leverage" value="{{ old('leverage', 75) }}" inputmode="numeric" required class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
        </label>
        <label class="flex items-center justify-between gap-4 rounded-2xl border border-ink-700/70 bg-ink-800/50 px-4 py-3">
            <span>
                <span class="block text-sm text-ink-100">Ada Take Profit 2?</span>
                <span class="mt-1 block font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">Aktifkan kalau setup punya target lanjutan</span>
            </span>
            <input data-tp2-toggle="#tp2-field" type="checkbox" name="has_take_profit_2" value="1" class="h-5 w-5 accent-gold-400" @checked(old('has_take_profit_2'))>
        </label>
        <div id="tp2-field" class="{{ old('has_take_profit_2') ? '' : 'hidden' }}">
            <x-form-field label="Take Profit 2" name="take_profit_2" placeholder="73200" :required="false" />
        </div>
        <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(212,167,44,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(212,167,44,1)] lg:col-span-2">
            <x-icon name="send" class="h-4 w-4" />
            Publish ke User Dashboard
        </button>
    </form>
</div>
