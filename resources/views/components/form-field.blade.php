@props(['label', 'name', 'placeholder' => '', 'required' => true])

<label>
    <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">{{ $label }}</span>
    <input
        name="{{ $name }}"
        value="{{ old($name) }}"
        placeholder="{{ $placeholder }}"
        inputmode="decimal"
        @required($required)
        class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50"
    >
</label>
