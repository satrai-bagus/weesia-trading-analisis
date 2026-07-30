import { setupLandingMotion } from './landing-motion';

const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
        return;
    }

    callback();
};

const liveSignalCharts = new Set();
const liveSignalTimeframe = '15M';

ready(() => {
    setupStickyHeader();
    setupMobileMenu();
    setupLandingMarketTicker();
    setupLandingFeaturedSignal();
    setupSignalCarousels();
    setupSignalFilters();
    setupReveal();
    setupLandingMotion();
    setupHeroCanvas();
    setupCounters();
    setupTp2Field();
    setupFilePickers();
    setupAlertBell();
    setupAlertDigest();
    setupTokenPicker();
    setupModals();
    setupUnlockConfirm();
    setupLivePriceCards();
    setupSignalLiveRoi();
    setupUserEntryTracker();
    setupLiveSignalCharts();
    setupFullscreenChart();
});

function setupStickyHeader() {
    const header = document.querySelector('[data-site-header]');
    const panel = document.querySelector('[data-site-header-panel]');

    if (!header || !panel) {
        return;
    }

    let lastState = null;
    let scheduled = false;

    const update = () => {
        const scrolled = window.scrollY > 24;

        if (scrolled === lastState) {
            scheduled = false;
            return;
        }

        lastState = scrolled;
        header.classList.toggle('py-3', scrolled);
        header.classList.toggle('py-6', !scrolled);
        panel.classList.toggle('glass', scrolled);
        panel.classList.toggle('shadow-[0_8px_32px_-12px_rgba(0,0,0,0.6)]', scrolled);
        panel.classList.toggle('px-4', scrolled);
        panel.classList.toggle('py-2', scrolled);
        panel.classList.toggle('px-2', !scrolled);
        panel.classList.toggle('py-1', !scrolled);
        scheduled = false;
    };

    const requestUpdate = () => {
        if (scheduled) {
            return;
        }

        scheduled = true;
        requestAnimationFrame(update);
    };

    update();
    window.addEventListener('scroll', requestUpdate, { passive: true });
}

function setupMobileMenu() {
    document.querySelectorAll('[data-mobile-menu-toggle]').forEach((button) => {
        const menu = document.querySelector(button.dataset.mobileMenuToggle);
        const openIcon = button.querySelector('[data-menu-open-icon]');
        const closeIcon = button.querySelector('[data-menu-close-icon]');

        if (!menu) {
            return;
        }

        const setOpen = (open) => {
            menu.classList.toggle('hidden', !open);
            button.setAttribute('aria-expanded', String(open));
            openIcon?.classList.toggle('hidden', open);
            closeIcon?.classList.toggle('hidden', !open);
        };

        setOpen(false);
        button.addEventListener('click', () => {
            setOpen(menu.classList.contains('hidden'));
        });

        menu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setOpen(false));
        });
    });
}

function setupLandingMarketTicker() {
    document.querySelectorAll('[data-landing-market-ticker]').forEach((tickerStrip) => {
        const endpoint = tickerStrip.dataset.marketEndpoint;
        const tickers = tickerStrip.dataset.marketTickers;

        if (!endpoint || !tickers) {
            return;
        }

        const setTone = (element, up) => {
            element.classList.remove('text-emerald-300', 'text-rose-300', 'text-ink-300');
            element.classList.add(up ? 'text-emerald-300' : 'text-rose-300');
        };

        const applyTicker = (item) => {
            tickerStrip.querySelectorAll('[data-market-symbol]').forEach((node) => {
                if (node.dataset.marketSymbol !== item.symbol) {
                    return;
                }

                const price = node.querySelector('[data-market-price]');
                const change = node.querySelector('[data-market-change]');

                if (price) {
                    price.textContent = item.price;
                }

                if (change) {
                    change.textContent = item.change;
                    setTone(change, Boolean(item.up));
                }
            });
        };

        const refresh = async () => {
            try {
                const response = await fetch(`${endpoint}?tickers=${encodeURIComponent(tickers)}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                (payload.tickers || []).forEach(applyTicker);
            } catch (error) {
                // Keep fallback ticker values when public market data is temporarily unavailable.
            }
        };

        refresh();
        window.setInterval(refresh, 30000);
    });
}

function setupLandingFeaturedSignal() {
    document.querySelectorAll('[data-landing-featured-analysis]').forEach((card) => {
        const endpoint = card.dataset.featuredEndpoint;

        if (!endpoint) {
            return;
        }

        const setText = (selector, value) => {
            const element = card.querySelector(selector);
            if (element && value !== null && value !== undefined) {
                element.textContent = value;
            }
        };

        const setSideTone = (side) => {
            const element = card.querySelector('[data-featured-side]');
            if (!element) {
                return;
            }

            element.textContent = side || '-';
            element.classList.remove('border-emerald-500/30', 'bg-emerald-500/10', 'text-emerald-300', 'border-rose-500/30', 'bg-rose-500/10', 'text-rose-300');
            const isShort = String(side || '').toLowerCase() === 'short';
            element.classList.add(
                isShort ? 'border-rose-500/30' : 'border-emerald-500/30',
                isShort ? 'bg-rose-500/10' : 'bg-emerald-500/10',
                isShort ? 'text-rose-300' : 'text-emerald-300',
            );
        };

        const apply = (analysis) => {
            if (!analysis) {
                return;
            }

            setText('[data-featured-symbol]', analysis.symbol);
            setText('[data-featured-timeframe]', analysis.timeframe);
            setText('[data-featured-status-meta]', analysis.status_meta);
            setText('[data-featured-title]', analysis.title);
            setText('[data-featured-entry]', analysis.entry);
            setText('[data-featured-tp1]', analysis.tp1);
            setText('[data-featured-sl]', analysis.sl);
            setText('[data-featured-rr]', analysis.rr);
            setSideTone(analysis.side);

            if (typeof analysis.is_live === 'boolean') {
                const liveDot = card.querySelector('[data-featured-live-dot]');
                const liveLabel = card.querySelector('[data-featured-live-label]');

                if (liveDot) {
                    liveDot.className = analysis.is_live
                        ? 'animate-pulse-soft h-2.5 w-2.5 rounded-full bg-emerald-400 shadow-[0_0_12px_2px_rgba(47,199,124,0.6)]'
                        : 'h-2.5 w-2.5 rounded-full bg-gold-400 shadow-[0_0_12px_2px_rgba(23,209,131,0.5)]';
                }

                if (liveLabel) {
                    setText('[data-featured-live-label]', analysis.live_label);
                    liveLabel.classList.toggle('text-emerald-300', analysis.is_live);
                    liveLabel.classList.toggle('text-gold-200', !analysis.is_live);
                }
            }
        };

        const refresh = async () => {
            try {
                const response = await fetch(endpoint, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                apply(payload.analysis);
            } catch (error) {
                // Keep the rendered sample analysis if the public endpoint is temporarily unavailable.
            }
        };

        refresh();
        window.setInterval(refresh, 30000);
    });
}

function setupReveal() {
    const items = document.querySelectorAll('.reveal');

    if (!items.length || !('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const pending = new Set(items);
    const revealBuffer = Math.max(window.innerHeight * 0.7, 420);
    let observer = null;
    let scheduled = false;

    const revealNow = (item) => {
        if (!pending.has(item)) {
            return;
        }

        pending.delete(item);
        item.classList.add('is-visible');
        observer?.unobserve(item);
    };

    const scanVisibleItems = () => {
        pending.forEach((item) => {
            const rect = item.getBoundingClientRect();

            if (rect.top < window.innerHeight + revealBuffer && rect.bottom > -revealBuffer) {
                revealNow(item);
            }
        });

        if (!pending.size) {
            window.removeEventListener('scroll', requestScan);
            window.removeEventListener('resize', requestScan);
        }
    };

    const requestScan = () => {
        if (scheduled) {
            return;
        }

        scheduled = true;
        window.requestAnimationFrame(() => {
            scheduled = false;
            scanVisibleItems();
        });
    };

    observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            revealNow(entry.target);
        });
    }, { rootMargin: '0px 0px 480px 0px', threshold: 0 });

    items.forEach((item) => {
        const rect = item.getBoundingClientRect();

        if (rect.top < window.innerHeight + revealBuffer && rect.bottom > -revealBuffer) {
            revealNow(item);
            return;
        }

        observer.observe(item);
    });

    window.addEventListener('scroll', requestScan, { passive: true });
    window.addEventListener('resize', requestScan, { passive: true });
    scanVisibleItems();
}

function setupCounters() {
    const counters = document.querySelectorAll('[data-counter]');

    if (!counters.length || !('IntersectionObserver' in window)) {
        counters.forEach((counter) => {
            counter.textContent = formatCounter(counter.dataset.counter, counter.dataset.decimals, counter.dataset.suffix);
        });
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            animateCounter(entry.target);
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -80px 0px', threshold: 0.3 });

    counters.forEach((counter) => observer.observe(counter));
}

function animateCounter(element) {
    const target = Number(element.dataset.counter || 0);
    const decimals = Number(element.dataset.decimals || 0);
    const suffix = element.dataset.suffix || '';
    const startedAt = performance.now();
    const duration = 1600;

    const frame = (now) => {
        const progress = Math.min((now - startedAt) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = target * eased;
        element.textContent = `${value.toFixed(decimals)}${suffix}`;

        if (progress < 1) {
            requestAnimationFrame(frame);
            return;
        }

        element.textContent = formatCounter(target, decimals, suffix);
    };

    requestAnimationFrame(frame);
}

function formatCounter(value, decimals = 0, suffix = '') {
    return `${Number(value || 0).toFixed(Number(decimals || 0))}${suffix || ''}`;
}

function setupSignalCarousels() {
    document.querySelectorAll('[data-signal-carousel]').forEach((carousel) => {
        const items = Array.from(carousel.querySelectorAll('[data-carousel-item]'));
        const pageSize = Math.max(Number(carousel.dataset.pageSize || 7), 1);
        const previous = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');
        const status = carousel.querySelector('[data-carousel-status]');
        let page = 0;

        if (!items.length) {
            status?.classList.add('hidden');
            previous?.classList.add('hidden');
            next?.classList.add('hidden');
            return;
        }

        const render = () => {
            // Baca ulang urutan dari DOM supaya pagination mengikuti hasil
            // sorting ROI live (sortItems menyusun ulang node di dalam list).
            const orderedItems = Array.from(carousel.querySelectorAll('[data-carousel-item]'));
            const visibleItems = orderedItems.filter((item) => item.dataset.filterHidden !== 'true');
            const pageCount = Math.max(Math.ceil(visibleItems.length / pageSize), 1);
            page = Math.min(page, pageCount - 1);

            const start = page * pageSize;
            const end = Math.min(start + pageSize, visibleItems.length);
            const pageItems = new Set(visibleItems.slice(start, end));

            orderedItems.forEach((item) => {
                item.classList.toggle('hidden', !pageItems.has(item));
            });

            if (status) {
                status.textContent = visibleItems.length
                    ? `${start + 1}-${end} dari ${visibleItems.length}`
                    : `0 dari ${items.length}`;
                status.classList.remove('hidden');
            }

            if (previous) {
                previous.disabled = page === 0;
                previous.classList.toggle('hidden', visibleItems.length <= pageSize);
            }

            if (next) {
                next.disabled = page === pageCount - 1;
                next.classList.toggle('hidden', visibleItems.length <= pageSize);
            }
        };

        previous?.addEventListener('click', () => {
            page = Math.max(page - 1, 0);
            render();
        });

        next?.addEventListener('click', () => {
            const orderedItems = Array.from(carousel.querySelectorAll('[data-carousel-item]'));
            const visibleItems = orderedItems.filter((item) => item.dataset.filterHidden !== 'true');
            const pageCount = Math.max(Math.ceil(visibleItems.length / pageSize), 1);
            page = Math.min(page + 1, pageCount - 1);
            render();
        });

        render();

        carousel.__signalCarousel = {
            render,
            reset() {
                page = 0;
                render();
            },
        };
    });
}

function setupSignalFilters() {
    document.querySelectorAll('[data-signal-filter-root]').forEach((root) => {
        const search = root.querySelector('[data-signal-search]');
        const side = root.querySelector('[data-signal-side-filter]');
        const status = root.querySelector('[data-signal-status-filter]');
        const tickerButtons = Array.from(root.querySelectorAll('[data-signal-ticker-filter]'));
        const clear = root.querySelector('[data-signal-filter-clear]');
        const count = root.querySelector('[data-signal-filter-count]');
        const empty = root.querySelector('[data-signal-filter-empty]');
        const list = root.querySelector('[data-carousel-list]');
        const items = Array.from(root.querySelectorAll('[data-carousel-item]'));
        let activeTicker = 'all';

        if (!items.length) {
            return;
        }

        const normalize = (value) => String(value || '').toLowerCase().replace(/[^a-z0-9]/g, '');

        const statusMatches = (value, wanted) => {
            if (!wanted || wanted === 'all') return true;
            if (wanted === 'closed') return value !== 'active';
            if (wanted === 'tp') return value === 'hit_tp' || value === 'hit_tp2';

            return value === wanted;
        };

        const setTickerButtonState = () => {
            tickerButtons.forEach((button) => {
                const selected = (button.dataset.signalTickerFilter || 'all') === activeTicker;
                button.setAttribute('aria-pressed', String(selected));
                button.classList.toggle('border-gold-500/50', selected);
                button.classList.toggle('bg-gold-500/15', selected);
                button.classList.toggle('text-gold-100', selected);
                button.classList.toggle('border-ink-700/70', !selected);
                button.classList.toggle('bg-ink-800/50', !selected);
                button.classList.toggle('text-ink-300', !selected);
            });
        };

        const apply = () => {
            const query = normalize(search?.value);
            const sideValue = side?.value || 'all';
            const statusValue = status?.value || 'all';
            let visible = 0;

            items.forEach((item) => {
                const ticker = item.dataset.filterTicker || '';
                const tickerKey = item.dataset.filterTickerKey || normalize(ticker);
                const itemSide = item.dataset.filterSide || '';
                const itemStatus = item.dataset.filterStatus || '';
                const matchesQuery = !query || tickerKey.includes(query) || normalize(ticker).includes(query);
                const matchesTicker = activeTicker === 'all' || tickerKey === activeTicker;
                const matchesSide = sideValue === 'all' || itemSide === sideValue;
                const matchesStatus = statusMatches(itemStatus, statusValue);
                const matched = matchesQuery && matchesTicker && matchesSide && matchesStatus;

                item.dataset.filterHidden = matched ? 'false' : 'true';
                if (matched) visible += 1;
            });

            setTickerButtonState();
            root.__signalCarousel?.reset();
            empty?.classList.toggle('hidden', visible !== 0);
            list?.classList.toggle('hidden', visible === 0);

            if (count) {
                count.textContent = `${visible} dari ${items.length} analisa`;
            }
        };

        search?.addEventListener('input', apply);
        side?.addEventListener('change', apply);
        status?.addEventListener('change', apply);

        tickerButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeTicker = button.dataset.signalTickerFilter || 'all';
                apply();
            });
        });

        clear?.addEventListener('click', () => {
            if (search) search.value = '';
            if (side) side.value = 'all';
            if (status) status.value = 'all';
            activeTicker = 'all';
            apply();
        });

        root.__applySignalFilters = apply;
        apply();
    });
}

function setupTp2Field() {
    document.querySelectorAll('[data-tp2-toggle]').forEach((checkbox) => {
        const field = document.querySelector(checkbox.dataset.tp2Toggle);

        if (!field) {
            return;
        }

        const update = () => {
            field.classList.toggle('hidden', !checkbox.checked);
            field.querySelectorAll('input').forEach((input) => {
                input.required = checkbox.checked;
            });
        };

        update();
        checkbox.addEventListener('change', update);
    });
}

function setupFilePickers() {
    document.querySelectorAll('[data-file-input]').forEach((input) => {
        const label = document.querySelector(input.dataset.fileInput);

        if (!label) {
            return;
        }

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            label.textContent = file ? file.name : '';
            label.classList.toggle('hidden', !file);
        });
    });
}

function setupLiveSignalCharts() {
    document.querySelectorAll('[data-live-signal-chart]').forEach((element) => {
        registerLiveSignalChart(element);
    });

    setupLivePricePolling();
}

function chartNumber(value, fallback = null) {
    const parsed = Number(value);

    return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
}

function readChartConfig(element) {
    const entry = chartNumber(element.dataset.chartEntry);
    const current = chartNumber(element.dataset.chartCurrent, entry);
    const tp1 = chartNumber(element.dataset.chartTp1, current || entry);
    const tp2 = chartNumber(element.dataset.chartTp2);
    const sl = chartNumber(element.dataset.chartSl, current || entry);
    const leverage = Math.max(Number(element.dataset.chartLeverage || 75), 1);
    const frozen = element.dataset.chartFrozen === 'true';
    const closePrice = chartNumber(element.dataset.chartClosePrice);
    const closeLabel = element.dataset.chartCloseLabel || '';

    return {
        minimal: element.dataset.chartMinimal === 'true',
        ticker: element.dataset.chartTicker || 'BTC/USDT',
        entry: entry || current || tp1 || sl || 1,
        current: current || entry || tp1 || sl || 1,
        tp1: tp1 || current || entry || 1,
        tp2,
        sl: sl || entry || current || 1,
        side: element.dataset.chartSide === 'short' ? 'short' : 'long',
        leverage,
        showEntry: element.dataset.chartShowEntry !== 'false',
        frozen,
        closePrice,
        closeLabel,
    };
}

function registerLiveSignalChart(element, config = null) {
    if (element.__liveSignalChart) {
        element.__liveSignalChart.destroy();
        liveSignalCharts.delete(element.__liveSignalChart);
    }

    const chart = createLiveSignalChart(element, config || readChartConfig(element));
    element.__liveSignalChart = chart;
    liveSignalCharts.add(chart);

    return chart;
}

function setupLivePricePolling() {
    if (!liveSignalCharts.size) {
        return;
    }

    const endpoint = '/market/prices';
    let timer = null;

    const refresh = async () => {
        const tickers = Array.from(new Set(Array.from(liveSignalCharts).map((chart) => chart.config.ticker))).filter(Boolean);

        if (!tickers.length) {
            return;
        }

        try {
            const response = await fetch(`${endpoint}?tickers=${encodeURIComponent(tickers.join(','))}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const prices = payload.prices || {};

            liveSignalCharts.forEach((chart) => {
                const price = prices[chart.config.ticker];
                if (Number.isFinite(Number(price))) {
                    chart.setMarketPrice(Number(price));
                }
            });

            window.dispatchEvent(new CustomEvent('weesia:prices', { detail: prices }));

            if (payload.alerts) {
                window.dispatchEvent(new CustomEvent('weesia:alerts', { detail: payload.alerts }));
            }
        } catch (error) {
            // Keep the local pulse running when the market endpoint is temporarily unavailable.
        }
    };

    window.setTimeout(refresh, 1200);
    timer = window.setInterval(refresh, 30000);

    window.addEventListener('beforeunload', () => window.clearInterval(timer), { once: true });
}

function setupModals() {
    const openModal = (modal) => {
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        const focusable = modal.querySelector('input, select, textarea, button:not([data-modal-close])');
        focusable?.focus({ preventScroll: true });
    };

    const closeModal = (modal) => {
        modal.setAttribute('hidden', '');
        if (!document.querySelector('[data-modal]:not([hidden])')) {
            document.body.style.overflow = '';
        }
    };

    document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const target = document.querySelector(`[data-modal="${trigger.dataset.modalOpen}"]`);
            if (target) openModal(target);
        });
    });

    document.querySelectorAll('[data-modal]').forEach((modal) => {
        modal.querySelectorAll('[data-modal-close]').forEach((btn) => {
            btn.addEventListener('click', () => closeModal(modal));
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal(modal);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        // Tutup modal paling atas (paling akhir di DOM) saat ada modal bertumpuk,
        // mis. fullscreen chart yang dibuka dari dalam modal detail signal.
        const openModals = document.querySelectorAll('[data-modal]:not([hidden])');
        const open = openModals[openModals.length - 1];
        if (open) closeModal(open);
    });

    if (document.querySelector('[data-modal]:not([hidden])')) {
        document.body.style.overflow = 'hidden';
    }
}

function setupUserEntryTracker() {
    const trackers = Array.from(document.querySelectorAll('[data-user-entry]'));
    if (!trackers.length) return;

    const lastPrices = new Map();
    const formatPercent = (value) => {
        const sign = value > 0 ? '+' : '';
        return `${sign}${value.toFixed(2)}%`;
    };
    const formatUsd = (value) => {
        if (!Number.isFinite(value)) return '-';
        const formatted = new Intl.NumberFormat('en-US', { maximumFractionDigits: 8 }).format(value);
        return `$${formatted}`;
    };

    const setNeutral = (tracker) => {
        const dir = tracker.querySelector('[data-entry-direction]');
        const pnl = tracker.querySelector('[data-entry-pnl]');
        const detail = tracker.querySelector('[data-entry-detail]');
        const result = tracker.querySelector('[data-entry-result]');
        const icon = tracker.querySelector('[data-entry-icon]');
        if (dir) { dir.textContent = 'Belum Diisi'; dir.className = 'font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300'; }
        if (pnl) { pnl.textContent = '-'; pnl.className = 'mt-1 font-display text-2xl leading-none text-ink-200'; }
        if (detail) detail.textContent = 'Masukkan harga entry untuk lihat estimasi P/L';
        if (result) result.className = 'rounded-2xl border border-ink-700 bg-ink-900 p-3';
        if (icon) icon.className = 'text-ink-300';
    };

    const render = (tracker) => {
        const input = tracker.querySelector('[data-entry-input]');
        const userEntry = Number(input?.value);
        const ticker = tracker.dataset.signalTicker;
        const livePrice = lastPrices.get(ticker);
        const side = tracker.dataset.signalSide === 'short' ? 'short' : 'long';
        const leverage = Math.max(Number(tracker.dataset.signalLeverage || 1), 1);

        if (!userEntry || userEntry <= 0 || !Number.isFinite(livePrice)) {
            setNeutral(tracker);
            return;
        }

        const movePct = side === 'short'
            ? ((userEntry - livePrice) / userEntry) * 100
            : ((livePrice - userEntry) / userEntry) * 100;
        const pnlPct = movePct * leverage;
        const isUp = pnlPct >= 0;

        const dir = tracker.querySelector('[data-entry-direction]');
        const pnlEl = tracker.querySelector('[data-entry-pnl]');
        const detail = tracker.querySelector('[data-entry-detail]');
        const result = tracker.querySelector('[data-entry-result]');
        const icon = tracker.querySelector('[data-entry-icon]');

        if (dir) {
            dir.textContent = isUp ? 'Posisi Profit' : 'Posisi Loss';
            dir.className = `font-mono text-[10px] uppercase tracking-[0.22em] ${isUp ? 'text-emerald-300' : 'text-rose-300'}`;
        }
        if (pnlEl) {
            pnlEl.textContent = formatPercent(pnlPct);
            pnlEl.className = `mt-1 font-display text-2xl leading-none ${isUp ? 'text-emerald-300' : 'text-rose-300'}`;
        }
        if (detail) {
            detail.textContent = `Spot ${formatPercent(movePct)} - Live ${formatUsd(livePrice)} - ${leverage}x leverage`;
        }
        if (result) {
            result.className = `rounded-2xl border p-3 ${isUp ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-rose-500/30 bg-rose-500/5'}`;
        }
        if (icon) {
            icon.className = isUp ? 'text-emerald-300' : 'text-rose-300';
            icon.innerHTML = isUp
                ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>'
                : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M17 7 7 17"/><path d="M17 17H7V7"/></svg>';
        }
    };

    trackers.forEach((tracker) => {
        const ticker = tracker.dataset.signalTicker;
        const initialLive = Number(tracker.dataset.signalLive);
        if (Number.isFinite(initialLive) && initialLive > 0) lastPrices.set(ticker, initialLive);

        const signalId = tracker.dataset.signalId;
        const storageKey = `weesia.entry.${signalId}`;
        const input = tracker.querySelector('[data-entry-input]');

        try {
            const stored = localStorage.getItem(storageKey);
            if (stored && input) input.value = stored;
        } catch { /* localStorage may be unavailable */ }

        input?.addEventListener('input', () => {
            try { localStorage.setItem(storageKey, input.value); } catch { /* ignore */ }
            render(tracker);
        });

        render(tracker);
    });

    window.addEventListener('weesia:prices', (event) => {
        const prices = event.detail || {};
        Object.entries(prices).forEach(([ticker, price]) => {
            const num = Number(price);
            if (Number.isFinite(num) && num > 0) lastPrices.set(ticker, num);
        });
        trackers.forEach(render);
    });
}

function setupLivePriceCards() {
    const cards = Array.from(document.querySelectorAll('[data-live-price-card]'));
    if (!cards.length) return;

    const formatUsd = (value) => {
        if (!Number.isFinite(value)) return '-';
        const formatted = new Intl.NumberFormat('en-US', { maximumFractionDigits: 8 }).format(value);
        return `$${formatted}`;
    };

    const applyPrices = (prices) => {
        cards.forEach((card) => {
            const ticker = card.dataset.livePriceTicker;
            const price = Number(prices?.[ticker]);
            const value = card.querySelector('[data-level-value]');

            if (!value || !Number.isFinite(price) || price <= 0) {
                return;
            }

            value.textContent = formatUsd(price);
        });
    };

    window.addEventListener('weesia:prices', (event) => applyPrices(event.detail || {}));
}

function setupSignalLiveRoi() {
    const formatUsd = (value) => {
        if (!Number.isFinite(value) || value <= 0) return '-';
        const formatted = new Intl.NumberFormat('en-US', { maximumFractionDigits: 8 }).format(value);
        return `$${formatted}`;
    };

    const formatPercent = (value) => {
        if (!Number.isFinite(value)) return '-';
        const sign = value > 0 ? '+' : '';
        const fixed = value.toFixed(2).replace(/\.?0+$/, '');
        return `${sign}${fixed}%`;
    };

    const computeRoi = (live, target, side, leverage) => {
        if (!Number.isFinite(live) || live <= 0 || !Number.isFinite(target) || target <= 0) return null;
        const move = side === 'short'
            ? ((live - target) / live) * 100
            : ((target - live) / live) * 100;
        return move * leverage;
    };

    const scopes = Array.from(new Set([
        ...document.querySelectorAll('[data-signal-carousel]'),
        ...document.querySelectorAll('[data-live-roi-scope]'),
    ]));

    scopes.forEach((scope) => {
        const list = scope.querySelector('[data-carousel-list]');
        const allItems = list ? Array.from(scope.querySelectorAll('[data-carousel-item]')) : [];
        const rows = Array.from(scope.querySelectorAll('[data-signal-row]'));
        if (!rows.length) return;

        const lastPrices = new Map();

        rows.forEach((row) => {
            const ticker = row.dataset.signalTicker;
            const initial = Number(row.dataset.signalLive);
            if (ticker && Number.isFinite(initial) && initial > 0) {
                lastPrices.set(ticker, initial);
            }
        });

        const updateRow = (row) => {
            const ticker = row.dataset.signalTicker;
            const live = lastPrices.get(ticker);

            // Tanpa harga live, pertahankan nilai fallback hasil render server
            // (dihitung dari entry price) daripada menimpanya dengan "-".
            if (!Number.isFinite(live) || live <= 0) {
                return;
            }

            const tp1 = Number(row.dataset.signalTp1);
            const tp2Raw = Number(row.dataset.signalTp2);
            const tp2 = Number.isFinite(tp2Raw) && tp2Raw > 0 ? tp2Raw : null;
            const sl = Number(row.dataset.signalSl);
            const side = row.dataset.signalSide === 'short' ? 'short' : 'long';
            const leverage = Math.max(Number(row.dataset.signalLeverage || 1), 1);

            const entryEl = row.querySelector('[data-entry-level] [data-level-value]');
            if (entryEl && Number.isFinite(live) && live > 0) {
                entryEl.textContent = formatUsd(live);
            }

            const roiTp1 = computeRoi(live, tp1, side, leverage);
            const roiTp2 = tp2 ? computeRoi(live, tp2, side, leverage) : null;
            const roiSl = computeRoi(live, sl, side, leverage);

            const tp1El = row.querySelector('[data-roi-tp1] [data-level-value]');
            const tp2El = row.querySelector('[data-roi-tp2] [data-level-value]');
            const slEl = row.querySelector('[data-roi-sl] [data-level-value]');

            if (tp1El) tp1El.textContent = formatPercent(roiTp1);
            if (tp2El) tp2El.textContent = tp2 ? formatPercent(roiTp2) : '-';
            if (slEl) slEl.textContent = formatPercent(roiSl);

            const candidates = [roiTp1, roiTp2].filter((value) => Number.isFinite(value));
            const sortKey = candidates.length ? Math.max(...candidates) : Number.NEGATIVE_INFINITY;
            row.dataset.sortRoi = String(sortKey);
        };

        const sortItems = () => {
            if (!list || !allItems.length) return;

            const sorted = [...allItems].sort((a, b) => {
                const av = a.hasAttribute('data-signal-row') ? Number(a.dataset.sortRoi) : Number.NEGATIVE_INFINITY;
                const bv = b.hasAttribute('data-signal-row') ? Number(b.dataset.sortRoi) : Number.NEGATIVE_INFINITY;
                const aSafe = Number.isFinite(av) ? av : Number.NEGATIVE_INFINITY;
                const bSafe = Number.isFinite(bv) ? bv : Number.NEGATIVE_INFINITY;
                return bSafe - aSafe;
            });

            const fragment = document.createDocumentFragment();
            sorted.forEach((node) => fragment.appendChild(node));
            list.appendChild(fragment);

            scope.__signalCarousel?.render?.();
        };

        rows.forEach(updateRow);
        sortItems();

        window.addEventListener('weesia:prices', (event) => {
            const prices = event.detail || {};
            let touched = false;
            Object.entries(prices).forEach(([ticker, price]) => {
                const num = Number(price);
                if (Number.isFinite(num) && num > 0) {
                    lastPrices.set(ticker, num);
                    touched = true;
                }
            });
            if (!touched) return;
            rows.forEach(updateRow);
            sortItems();
        });
    });
}

function setupUnlockConfirm() {
    const modal = document.querySelector('[data-modal="unlock-confirm"]');
    if (!modal) return;

    const tickerEl = modal.querySelector('[data-unlock-ticker]');
    const sideEl = modal.querySelector('[data-unlock-side]');
    const balanceEl = modal.querySelector('[data-unlock-balance]');
    const costEl = modal.querySelector('[data-unlock-cost]');
    const afterEl = modal.querySelector('[data-unlock-after]');
    const form = modal.querySelector('[data-unlock-form]');
    const initialBalance = Number(balanceEl?.textContent.replace(/[^\d-]/g, '') || 0);
    const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value);

    document.querySelectorAll('[data-unlock-trigger]').forEach((btn) => {
        if (btn.disabled) return;
        btn.addEventListener('click', () => {
            const cost = Number(btn.dataset.signalCost || 0);
            const ticker = btn.dataset.signalTicker || '-';
            const side = btn.dataset.signalSide || '';
            const leverage = btn.dataset.signalLeverage || '';
            const action = btn.dataset.signalAction || '';

            if (tickerEl) tickerEl.textContent = ticker;
            if (sideEl) sideEl.textContent = side && leverage ? `${side} ${leverage}x` : side;
            if (costEl) costEl.textContent = formatNumber(cost);
            if (balanceEl) balanceEl.textContent = formatNumber(initialBalance);
            if (afterEl) afterEl.textContent = formatNumber(Math.max(0, initialBalance - cost));
            if (form) form.action = action;

            modal.removeAttribute('hidden');
            document.body.style.overflow = 'hidden';
        });
    });
}

function setupAlertBell() {
    const bell = document.querySelector('[data-alert-bell]');
    if (!bell) {
        return;
    }

    const toggle = bell.querySelector('[data-alert-toggle]');
    const panel = bell.querySelector('[data-alert-panel]');
    const badge = bell.querySelector('[data-alert-badge]');
    const list = bell.querySelector('[data-alert-list]');
    const clearBtn = bell.querySelector('[data-alert-clear]');
    const toastHost = document.querySelector('[data-alert-toast-host]');

    const seenIds = new Set(
        Array.from(list?.querySelectorAll('[data-alert-item]') || []).map((el) => el.getAttribute('data-alert-item')),
    );

    const toneClass = (tone) => {
        if (tone === 'rose') return 'border-rose-500/30 bg-rose-500/10 text-rose-300';
        if (tone === 'emerald') return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300';
        return 'border-gold-500/30 bg-gold-500/10 text-gold-200';
    };

    const dotClass = (tone) => {
        if (tone === 'rose') return 'bg-rose-400 shadow-[0_0_10px_rgba(244,63,94,0.7)]';
        if (tone === 'emerald') return 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.7)]';
        return 'bg-gold-400';
    };

    const setBadge = (count) => {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = String(count);
            badge.classList.remove('hidden');
        } else {
            badge.textContent = '0';
            badge.classList.add('hidden');
        }
    };

    const renderItems = (items) => {
        if (!list) return;
        list.innerHTML = '';
        if (!items.length) {
            list.innerHTML = '<div data-alert-empty class="px-4 py-6 text-center text-xs text-ink-300">Belum ada signal yang hit.</div>';
            return;
        }
        items.forEach((item) => {
            const node = document.createElement('div');
            node.setAttribute('data-alert-item', String(item.id));
            node.className = `flex items-start gap-3 border-b border-ink-700/50 px-4 py-3 last:border-0 ${item.is_unread ? 'bg-gold-500/5' : ''}`;
            node.innerHTML = `
                <span class="mt-1 h-2 w-2 shrink-0 rounded-full ${dotClass(item.tone)}"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <div data-ticker class="truncate font-display text-sm text-ink-50"></div>
                        <span data-status class="shrink-0 rounded-full border px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.18em] ${toneClass(item.tone)}"></span>
                    </div>
                    <div data-meta class="mt-1 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300"></div>
                </div>
            `;
            node.querySelector('[data-ticker]').textContent = item.ticker;
            node.querySelector('[data-status]').textContent = item.status_label;
            node.querySelector('[data-meta]').textContent = `${item.side} ${item.leverage}x - ${item.auto_hit_human}`;
            list.appendChild(node);
        });
    };

    const showToast = (item) => {
        if (!toastHost) return;
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto flex items-start gap-3 rounded-2xl border bg-ink-900/95 p-4 shadow-[0_30px_60px_-30px_rgba(0,0,0,0.9)] backdrop-blur-xl ${toneClass(item.tone)}`;
        toast.innerHTML = `
            <span class="mt-1 h-2 w-2 shrink-0 rounded-full ${dotClass(item.tone)}"></span>
            <div class="min-w-0 flex-1">
                <div data-toast-title class="font-display text-sm text-ink-50"></div>
                <div data-toast-meta class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300"></div>
            </div>
            <button type="button" aria-label="Tutup" class="shrink-0 text-ink-300 transition-colors hover:text-ink-50">×</button>
        `;
        toast.querySelector('[data-toast-title]').textContent = `${item.ticker} - ${item.status_label}`;
        toast.querySelector('[data-toast-meta]').textContent = `${item.side} ${item.leverage}x - ${item.auto_hit_human}`;
        toast.querySelector('button').addEventListener('click', () => toast.remove(), { once: true });
        toastHost.appendChild(toast);
        window.setTimeout(() => toast.remove(), 8000);
    };

    const applyPayload = (payload, { toast = false } = {}) => {
        if (!payload) return;
        const items = Array.isArray(payload.items) ? payload.items : [];
        setBadge(payload.unread_count || 0);
        renderItems(items);
        if (toast) {
            items
                .filter((item) => item.is_unread && !seenIds.has(String(item.id)))
                .forEach((item) => showToast(item));
        }
        items.forEach((item) => seenIds.add(String(item.id)));
    };

    toggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        panel?.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!panel || panel.classList.contains('hidden')) return;
        if (!bell.contains(event.target)) {
            panel.classList.add('hidden');
        }
    });

    clearBtn?.addEventListener('click', () => {
        markAlertsSeen();
    });

    window.addEventListener('weesia:alerts', (event) => {
        applyPayload(event.detail, { toast: true });
    });
}

// Marks every auto-hit alert as read, then broadcasts the fresh payload so the
// bell dropdown and the dashboard digest stay in sync from a single request.
async function markAlertsSeen() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const response = await fetch('/alerts/seen', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
        });
        if (!response.ok) return null;
        const payload = await response.json();
        if (payload.alerts) {
            window.dispatchEvent(new CustomEvent('weesia:alerts', { detail: payload.alerts }));
        }

        return payload.alerts || null;
    } catch (error) {
        return null;
    }
}

function setupAlertDigest() {
    const digest = document.querySelector('[data-alert-digest]');
    if (!digest) {
        return;
    }

    const list = digest.querySelector('[data-alert-digest-list]');
    const countEl = digest.querySelector('[data-alert-digest-count]');
    const headline = digest.querySelector('[data-alert-digest-headline]');
    const clearBtn = digest.querySelector('[data-alert-digest-clear]');
    const maxItems = 6;

    const toneClass = (tone) => {
        if (tone === 'rose') return 'border-rose-500/30 bg-rose-500/10 text-rose-200';
        if (tone === 'emerald') return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200';
        return 'border-gold-500/30 bg-gold-500/10 text-gold-200';
    };

    const dotClass = (tone) => {
        if (tone === 'rose') return 'bg-rose-400 shadow-[0_0_10px_rgba(244,63,94,0.7)]';
        if (tone === 'emerald') return 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.7)]';
        return 'bg-gold-400';
    };

    const render = (payload) => {
        const unread = (Array.isArray(payload?.items) ? payload.items : []).filter((item) => item.is_unread);
        const total = Math.max(Number(payload?.unread_count) || 0, unread.length);

        if (!total) {
            digest.classList.add('hidden');
            return;
        }

        const items = unread.slice(0, maxItems);
        const rest = total - items.length;

        if (countEl) countEl.textContent = `${total} baru`;
        if (headline) headline.textContent = `${total} analisa menyentuh levelnya dan belum kamu baca.`;

        if (list) {
            list.innerHTML = '';
            items.forEach((item) => {
                const node = document.createElement('li');
                node.setAttribute('data-alert-digest-item', String(item.id));
                node.className = 'flex items-center justify-between gap-3 rounded-xl border border-ink-700/60 bg-ink-900/60 px-3 py-2.5';
                node.innerHTML = `
                    <span class="flex min-w-0 items-center gap-2.5">
                        <span class="h-2 w-2 shrink-0 rounded-full ${dotClass(item.tone)}"></span>
                        <span class="min-w-0">
                            <span data-ticker class="block truncate font-display text-sm text-ink-50"></span>
                            <span data-meta class="mt-0.5 block truncate font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400"></span>
                        </span>
                    </span>
                    <span data-status class="shrink-0 rounded-full border px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.18em] ${toneClass(item.tone)}"></span>
                `;
                node.querySelector('[data-ticker]').textContent = item.ticker;
                node.querySelector('[data-meta]').textContent = `${item.side} ${item.leverage}x - ${item.auto_hit_human}`;
                node.querySelector('[data-status]').textContent = item.status_label;
                list.appendChild(node);
            });

            if (rest > 0) {
                const more = document.createElement('li');
                more.setAttribute('data-alert-digest-rest', '');
                more.className = 'flex items-center justify-center rounded-xl border border-dashed border-ink-700/60 px-3 py-2.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400';
                more.textContent = `+${rest} lainnya di lonceng`;
                list.appendChild(more);
            }
        }

        digest.classList.remove('hidden');
        digest.classList.add('is-visible');
    };

    clearBtn?.addEventListener('click', () => {
        markAlertsSeen();
    });

    window.addEventListener('weesia:alerts', (event) => {
        render(event.detail);
    });
}

function setupTokenPicker() {
    const picker = document.querySelector('[data-token-picker]');
    if (!picker) {
        return;
    }

    const radios = picker.querySelectorAll('input[type="radio"][name="package_key"]');
    if (!radios.length) {
        return;
    }

    const priceEl = picker.querySelector('[data-token-summary-price]');
    const amountEl = picker.querySelector('[data-token-summary-amount]');
    const unitEl = picker.querySelector('[data-token-summary-unit]');

    const sync = () => {
        const selected = Array.from(radios).find((radio) => radio.checked);
        if (!selected) return;

        if (priceEl) priceEl.textContent = selected.dataset.tokenPrice || '';
        if (amountEl) amountEl.textContent = `${selected.dataset.tokenAmount || 0} token`;
        if (unitEl) unitEl.textContent = `@ ${selected.dataset.tokenUnit || ''} / token`;
    };

    radios.forEach((radio) => radio.addEventListener('change', sync));
    sync();
}

function createLiveSignalChart(container, config) {
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const historyPointMs = 3000;
    const state = {
        width: 0,
        height: 0,
        dpr: 1,
        visible: true,
        pageVisible: !document.hidden,
        frameId: null,
        lastFrame: 0,
        lastPointAt: 0,
        price: config.current,
        marketPrice: config.current,
        phase: Math.random() * Math.PI * 2,
        history: [],
    };

    container.innerHTML = '';
    container.appendChild(canvas);

    const seedHistory = () => {
        const count = Math.max(72, Math.min(120, Math.floor(state.width / 7) || 101));
        const start = config.entry;
        const end = state.price || config.current;
        const move = end - start;
        const volatility = Math.max(Math.abs(config.tp1 - config.entry), Math.abs(config.entry - config.sl), config.entry * 0.02) * 0.065;
        let price = start - move * 0.18;

        state.history = Array.from({ length: count }, (_, index) => {
            const progress = index / Math.max(count - 1, 1);
            const trend = start + move * progress;
            const wave = Math.sin(progress * Math.PI * 5 + state.phase) * volatility * 0.72
                + Math.sin(progress * Math.PI * 13 + state.phase * 0.7) * volatility * 0.28
                + (Math.random() - 0.5) * volatility * 0.16;
            price += ((trend + wave) - price) * 0.48;

            return price;
        });
        state.history[state.history.length - 1] = state.price;
        state.lastPointAt = performance.now();
    };

    const resize = () => {
        const rect = container.getBoundingClientRect();
        state.width = Math.floor(rect.width);
        state.height = Math.floor(rect.height);

        if (state.width < 80 || state.height < 120) {
            canvas.width = 0;
            canvas.height = 0;
            return;
        }

        state.dpr = Math.min(window.devicePixelRatio || 1, state.width > 720 ? 2 : 1.5);
        canvas.width = Math.floor(state.width * state.dpr);
        canvas.height = Math.floor(state.height * state.dpr);
        context.setTransform(state.dpr, 0, 0, state.dpr, 0, 0);
        seedHistory();
        draw(performance.now(), false);
    };

    const money = (value) => {
        if (!Number.isFinite(value)) {
            return '-';
        }

        const decimals = value >= 1000 ? 2 : value >= 1 ? 4 : 8;

        return value.toLocaleString('en-US', {
            minimumFractionDigits: value >= 1000 ? 2 : 0,
            maximumFractionDigits: decimals,
        });
    };

    const percent = (target, reference = config.entry) => {
        if (!reference || !target) {
            return '-';
        }

        const raw = config.side === 'short'
            ? ((reference - target) / reference) * 100
            : ((target - reference) / reference) * 100;
        const value = raw * config.leverage;
        const prefix = value > 0 ? '+' : '';

        return `${prefix}${value.toFixed(2).replace(/\.?0+$/, '')}%`;
    };

    const roundedRect = (x, y, width, height, radius) => {
        const safeWidth = Math.max(width, 0);
        const safeHeight = Math.max(height, 0);

        context.beginPath();

        if (safeWidth <= 0 || safeHeight <= 0) {
            return;
        }

        const r = Math.max(0, Math.min(radius, safeWidth / 2, safeHeight / 2));
        context.moveTo(x + r, y);
        context.arcTo(x + safeWidth, y, x + safeWidth, y + safeHeight, r);
        context.arcTo(x + safeWidth, y + safeHeight, x, y + safeHeight, r);
        context.arcTo(x, y + safeHeight, x, y, r);
        context.arcTo(x, y, x + safeWidth, y, r);
        context.closePath();
    };

    const levels = () => [config.entry, state.price, config.tp1, config.tp2, config.sl, ...state.history]
        .filter((value) => Number.isFinite(value) && value > 0);

    const bounds = () => {
        const values = levels();
        let min = Math.min(...values);
        let max = Math.max(...values);
        const pad = Math.max((max - min) * 0.18, config.entry * 0.01, 0.00000001);
        min -= pad;
        max += pad;

        return { min, max, range: Math.max(max - min, 0.00000001) };
    };

    const updatePrice = (now) => {
        const range = Math.max(Math.abs(config.tp1 - config.entry), Math.abs(config.entry - config.sl), config.entry * 0.015);
        const amplitude = range * 0.026;
        const pulse = Math.sin(now / 3600 + state.phase) * amplitude
            + Math.cos(now / 8200 + state.phase * 0.7) * amplitude * 0.62
            + Math.sin(now / 1300 + state.phase * 1.9) * amplitude * 0.18;
        const target = (state.marketPrice || config.current || config.entry) + pulse;
        state.price += (target - state.price) * 0.045;

        if (state.history.length) {
            state.history[state.history.length - 1] = state.price;
        }

        if (now - state.lastPointAt > historyPointMs) {
            state.history.push(state.price);
            if (state.history.length > Math.max(72, Math.min(120, Math.floor(state.width / 7) || 101))) {
                state.history.shift();
            }
            state.lastPointAt = now;
        }
    };

    const drawText = (text, x, y, options = {}) => {
        context.save();
        context.font = `${options.weight || 400} ${options.size || 12}px ${options.family || 'JetBrains Mono, ui-monospace, monospace'}`;
        context.fillStyle = options.color || '#f5f3ee';
        context.textAlign = options.align || 'left';
        context.textBaseline = options.baseline || 'alphabetic';
        if (options.letterSpacing) {
            const chars = String(text).split('');
            let currentX = x;
            chars.forEach((char) => {
                context.fillText(char, currentX, y);
                currentX += context.measureText(char).width + options.letterSpacing;
            });
        } else {
            context.fillText(text, x, y);
        }
        context.restore();
    };

    const drawLevel = (label, value, y, color, x, right, compact, alignRight = true) => {
        context.save();
        context.setLineDash([7, 8]);
        context.strokeStyle = color;
        context.globalAlpha = label === 'LIVE' ? 0.72 : 0.45;
        context.lineWidth = label === 'LIVE' ? 1.5 : 1;
        context.beginPath();
        context.moveTo(x, y);
        context.lineTo(right, y);
        context.stroke();
        context.setLineDash([]);
        context.globalAlpha = 1;

        const text = `${label} ${money(value)}`;
        const textWidth = context.measureText(text).width + 22;
        const badgeW = Math.min(Math.max(textWidth, compact ? 94 : 124), compact ? 132 : 168);
        const badgeH = compact ? 25 : 32;
        const badgeX = alignRight ? right - badgeW - 8 : x + 8;
        const badgeY = Math.max(y - badgeH / 2, 8);
        roundedRect(badgeX, badgeY, badgeW, badgeH, badgeH / 2);
        context.fillStyle = 'rgba(5, 5, 5, 0.82)';
        context.fill();
        context.strokeStyle = color;
        context.globalAlpha = 0.42;
        context.stroke();
        context.globalAlpha = 1;
        drawText(text, badgeX + 12, badgeY + badgeH / 2 + 4, {
            color,
            size: compact ? 9 : 11,
            weight: 700,
        });
        context.restore();
    };

    const drawMetric = (x, y, width, height, label, value, color, subValue = '') => {
        const tight = height < 62;
        roundedRect(x, y, width, height, Math.min(18, height / 3));
        context.fillStyle = 'rgba(7, 7, 6, 0.72)';
        context.fill();
        context.strokeStyle = color;
        context.globalAlpha = 0.24;
        context.stroke();
        context.globalAlpha = 1;
        drawText(label, x + 14, y + (tight ? 17 : 27), {
            color: '#8a877e',
            size: tight ? 8 : 10,
            letterSpacing: tight ? 3 : 5,
        });
        drawText(value, x + 14, y + (tight ? 38 : height - 26), {
            color,
            size: tight ? (width < 100 ? 11 : 13) : (width < 140 ? 16 : 20),
            weight: 700,
        });
        if (subValue && !tight) {
            drawText(subValue, x + 14, y + height - 9, {
                color: '#8a877e',
                size: 9,
                letterSpacing: 1,
            });
        }
    };

    const draw = (now, animate = true) => {
        if (!context || state.width < 80 || state.height < 120) {
            return;
        }

        if (animate && !reducedMotion && !config.frozen) {
            updatePrice(now);
        }

        const compact = state.width < 520 || state.height < 380;
        const fullscreen = state.width > 800 && state.height > 520;
        const minimal = Boolean(config.minimal);
        const pad = minimal ? 12 : compact ? 18 : Math.min(Math.max(state.width * 0.055, 38), 74);
        const headerH = minimal ? 4 : compact ? 56 : fullscreen ? 116 : 82;
        const bottomH = minimal ? 4 : compact ? 60 : fullscreen ? 150 : 104;
        const chartX = pad;
        const chartY = headerH + (compact ? 8 : 20);
        const chartW = state.width - pad * 2;
        const chartH = Math.max(state.height - chartY - bottomH - pad * 0.45, 70);
        const chartRight = chartX + chartW;
        const chartBottom = chartY + chartH;
        const scale = bounds();
        const yFor = (price) => chartY + ((scale.max - price) / scale.range) * chartH;
        const entryY = yFor(config.entry);
        const tp1Y = yFor(config.tp1);
        const tp2Y = config.tp2 ? yFor(config.tp2) : null;
        const slY = yFor(config.sl);
        const liveY = yFor(state.price);
        const profitTarget = config.side === 'short'
            ? Math.min(config.tp1, config.tp2 || config.tp1)
            : Math.max(config.tp1, config.tp2 || config.tp1);
        const profitY = yFor(profitTarget);

        context.clearRect(0, 0, state.width, state.height);

        const bg = context.createLinearGradient(0, 0, state.width, state.height);
        bg.addColorStop(0, '#050505');
        bg.addColorStop(0.62, '#070706');
        bg.addColorStop(1, '#11100d');
        context.fillStyle = bg;
        context.fillRect(0, 0, state.width, state.height);

        const halo = context.createRadialGradient(state.width * 0.8, state.height * 0.05, 0, state.width * 0.8, state.height * 0.05, state.width * 0.55);
        halo.addColorStop(0, 'rgba(47, 199, 124, 0.13)');
        halo.addColorStop(0.42, 'rgba(23, 209, 131, 0.08)');
        halo.addColorStop(1, 'rgba(5, 5, 5, 0)');
        context.fillStyle = halo;
        context.fillRect(0, 0, state.width, state.height);

        if (!minimal) {
            drawText(compact ? config.ticker : 'LIVE MARKET PULSE', pad, compact ? 30 : 34, {
                color: compact ? '#f5f3ee' : '#8a877e',
                family: compact ? 'Fraunces, Georgia, serif' : 'JetBrains Mono, ui-monospace, monospace',
                size: compact ? 26 : 13,
                weight: compact ? 700 : 400,
                letterSpacing: compact ? 0 : 7,
            });
            if (!compact) {
                drawText('FibPath Live', pad, fullscreen ? 88 : 74, {
                    color: '#f5f3ee',
                    family: 'Fraunces, Georgia, serif',
                    size: fullscreen ? 58 : 36,
                    weight: 700,
                });
                drawText(`${config.side.toUpperCase()} ${config.leverage}X  /  ${liveSignalTimeframe} LIVE PULSE  /  ${config.ticker}`, pad, fullscreen ? 122 : 100, {
                    color: '#17d183',
                    size: 11,
                    letterSpacing: 4,
                });
            } else {
                drawText(`${config.side.toUpperCase()} ${config.leverage}X  /  ${liveSignalTimeframe}`, pad, 50, {
                    color: '#17d183',
                    size: 10,
                    letterSpacing: 3,
                });
            }

            if (!compact) {
                const pulseX = state.width - pad - 58;
                const pulseY = fullscreen ? 68 : 48;
                context.beginPath();
                context.arc(pulseX, pulseY, fullscreen ? 42 : 30, 0, Math.PI * 2);
                context.fillStyle = 'rgba(47, 199, 124, 0.12)';
                context.fill();
                context.strokeStyle = 'rgba(47, 199, 124, 0.32)';
                context.stroke();
                context.strokeStyle = '#8ef0b3';
                context.lineWidth = 3;
                context.beginPath();
                context.moveTo(pulseX - 22, pulseY + 4);
                context.lineTo(pulseX - 8, pulseY + 4);
                context.lineTo(pulseX - 2, pulseY - 18);
                context.lineTo(pulseX + 9, pulseY + 18);
                context.lineTo(pulseX + 15, pulseY + 2);
                context.lineTo(pulseX + 26, pulseY + 2);
                context.stroke();
            }

            context.strokeStyle = 'rgba(245, 243, 238, 0.08)';
            context.beginPath();
            context.moveTo(pad, headerH);
            context.lineTo(state.width - pad, headerH);
            context.stroke();
        }

        roundedRect(chartX, chartY, chartW, chartH, compact ? 18 : 28);
        context.fillStyle = 'rgba(0, 0, 0, 0.34)';
        context.fill();
        context.strokeStyle = 'rgba(245, 243, 238, 0.08)';
        context.stroke();

        context.save();
        roundedRect(chartX, chartY, chartW, chartH, compact ? 18 : 28);
        context.clip();

        const profitTop = Math.min(entryY, profitY);
        const profitHeight = Math.abs(entryY - profitY);
        const riskTop = Math.min(entryY, slY);
        const riskHeight = Math.abs(entryY - slY);
        context.fillStyle = 'rgba(47, 199, 124, 0.075)';
        context.fillRect(chartX, profitTop, chartW, profitHeight);
        context.fillStyle = 'rgba(244, 63, 94, 0.07)';
        context.fillRect(chartX, riskTop, chartW, riskHeight);

        for (let i = 1; i < 5; i += 1) {
            const y = chartY + (chartH / 5) * i;
            context.setLineDash([6, 10]);
            context.strokeStyle = 'rgba(23, 209, 131, 0.08)';
            context.beginPath();
            context.moveTo(chartX, y);
            context.lineTo(chartRight, y);
            context.stroke();
        }
        context.setLineDash([]);

        const points = state.history.map((price, index) => {
            const x = chartX + (index / Math.max(state.history.length - 1, 1)) * chartW;
            return [x, yFor(price)];
        });

        if (points.length > 1) {
            context.beginPath();
            points.forEach(([x, y], index) => {
                if (index === 0) {
                    context.moveTo(x, y);
                    return;
                }
                context.lineTo(x, y);
            });
            context.lineTo(chartRight, chartBottom);
            context.lineTo(chartX, chartBottom);
            context.closePath();
            const fill = context.createLinearGradient(0, chartY, 0, chartBottom);
            fill.addColorStop(0, 'rgba(23, 209, 131, 0.34)');
            fill.addColorStop(0.62, 'rgba(23, 209, 131, 0.09)');
            fill.addColorStop(1, 'rgba(23, 209, 131, 0)');
            context.fillStyle = fill;
            context.fill();

            context.beginPath();
            points.forEach(([x, y], index) => {
                if (index === 0) {
                    context.moveTo(x, y);
                    return;
                }
                context.lineTo(x, y);
            });
            context.strokeStyle = 'rgba(6, 48, 32, 0.45)';
            context.lineWidth = compact ? 5 : 8;
            context.lineJoin = 'round';
            context.lineCap = 'round';
            context.stroke();

            const line = context.createLinearGradient(chartX, 0, chartRight, 0);
            line.addColorStop(0, '#8df0c2');
            line.addColorStop(0.48, '#eafff5');
            line.addColorStop(1, '#17d183');
            context.strokeStyle = line;
            context.lineWidth = compact ? 2.2 : 3.6;
            context.shadowColor = 'rgba(201, 247, 224, 0.5)';
            context.shadowBlur = compact ? 8 : 14;
            context.stroke();
            context.shadowBlur = 0;
        }

        drawLevel('TP1', config.tp1, tp1Y, '#6fe0a8', chartX, chartRight, compact);
        if (tp2Y) {
            drawLevel('TP2', config.tp2, tp2Y, '#6fe0a8', chartX, chartRight, compact);
        }
        if (config.showEntry) {
            drawLevel('ENTRY', config.entry, entryY, '#17d183', chartX, chartRight, compact, false);
        }
        drawLevel('SL', config.sl, slY, '#fda4af', chartX, chartRight, compact);
        const liveLabel = config.frozen
            ? (config.closeLabel ? config.closeLabel.toUpperCase() : 'CLOSED')
            : 'LIVE';
        const liveColor = config.frozen
            ? (config.closeLabel && config.closeLabel.toLowerCase().includes('sl') ? '#fda4af' : '#6fe0a8')
            : (state.price >= config.entry ? '#c9f7e0' : '#fda4af');
        drawLevel(liveLabel, state.price, liveY, liveColor, chartX, chartRight, compact, false);

        context.beginPath();
        context.arc(chartRight, liveY, compact ? 5 : 7, 0, Math.PI * 2);
        const dotIsLoss = config.frozen
            ? (config.closeLabel && config.closeLabel.toLowerCase().includes('sl'))
            : state.price < config.entry;
        context.fillStyle = dotIsLoss ? '#fda4af' : '#6fe0a8';
        context.shadowColor = dotIsLoss ? 'rgba(244, 63, 94, 0.75)' : 'rgba(47, 199, 124, 0.75)';
        context.shadowBlur = 16;
        context.fill();
        context.shadowBlur = 0;
        context.restore();

        if (!minimal) {
            const bottomY = chartBottom + (compact ? 10 : 26);
            const gap = compact ? 8 : 18;
            const metricH = compact ? 48 : 88;
            const metricW = (state.width - pad * 2 - gap * 2) / 3;
            const bottomLabel = config.showEntry ? 'ENTRY' : (config.frozen ? 'CLOSED' : 'LIVE');
            drawMetric(pad, bottomY, metricW, metricH, bottomLabel, money(config.showEntry ? config.entry : state.price), '#f5f3ee', compact ? '' : config.ticker);
            drawMetric(pad + metricW + gap, bottomY, metricW, metricH, 'TP1', money(config.tp1), '#6fe0a8', compact ? '' : percent(config.tp1));
            drawMetric(pad + (metricW + gap) * 2, bottomY, metricW, metricH, 'SL', money(config.sl), '#fda4af', compact ? '' : percent(config.sl));

            if (!compact) {
                drawText(config.showEntry
                    ? `Live ${money(state.price)}  /  Entry ${money(config.entry)}  /  PnL ${percent(state.price)}`
                    : `Live ${money(state.price)}  /  TP1 ${percent(config.tp1)}  /  Risk ${percent(config.sl)}`, pad, state.height - 28, {
                    color: '#8a877e',
                    size: 11,
                    letterSpacing: 2,
                });
                drawText('WEESIA', state.width - pad, state.height - 28, {
                    color: '#17d183',
                    size: 10,
                    align: 'right',
                    letterSpacing: 4,
                });
            }
        }
    };

    const stop = () => {
        if (state.frameId) {
            cancelAnimationFrame(state.frameId);
            state.frameId = null;
        }
    };

    const tick = (now) => {
        if (!state.visible || !state.pageVisible) {
            stop();
            return;
        }

        if (config.frozen) {
            draw(now, false);
            state.lastFrame = now;
            stop();
            return;
        }

        if (now - state.lastFrame > 1000 / 30) {
            draw(now);
            state.lastFrame = now;
        }

        state.frameId = requestAnimationFrame(tick);
    };

    const start = () => {
        if (state.frameId || !state.visible || !state.pageVisible) {
            return;
        }

        state.frameId = requestAnimationFrame(tick);
    };

    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);

    const visibilityObserver = new IntersectionObserver((entries) => {
        state.visible = entries.some((entry) => entry.isIntersecting);
        if (state.visible) {
            start();
        } else {
            stop();
        }
    }, { rootMargin: '160px 0px', threshold: 0 });
    visibilityObserver.observe(container);

    const onVisibilityChange = () => {
        state.pageVisible = !document.hidden;
        if (state.pageVisible) {
            start();
        } else {
            stop();
        }
    };

    document.addEventListener('visibilitychange', onVisibilityChange);
    resize();
    start();

    return {
        config,
        setMarketPrice(price) {
            if (!Number.isFinite(price) || price <= 0 || config.frozen) {
                return;
            }

            state.marketPrice = price;
            config.current = price;
            if (!config.showEntry) {
                config.entry = price;
            }
        },
        resize() {
            resize();
        },
        destroy() {
            stop();
            resizeObserver.disconnect();
            visibilityObserver.disconnect();
            document.removeEventListener('visibilitychange', onVisibilityChange);
            if (container.__liveSignalChart === this) {
                container.__liveSignalChart = null;
            }
        },
    };
}

function setupFullscreenChart() {
    const modal = document.querySelector('[data-modal="fullscreen-chart"]');
    if (!modal) return;

    const host = modal.querySelector('[data-fullscreen-host]');
    const titleEl = modal.querySelector('[data-fullscreen-ticker]');
    let activeChart = null;

    const cleanup = () => {
        if (activeChart) {
            activeChart.destroy();
            liveSignalCharts.delete(activeChart);
            activeChart = null;
        }
        if (host) host.innerHTML = '';
    };

    const openChart = (trigger) => {
        if (!trigger) return;

        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';

        try {
            const config = readChartConfig(trigger);
            if (titleEl) titleEl.textContent = config.ticker;
            cleanup();
            if (host) {
                host.classList.add('live-trading-chart');
                host.style.minHeight = '100%';
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    activeChart = registerLiveSignalChart(host, config);
                    window.setTimeout(() => activeChart?.resize?.(), 80);
                }));
            }
        } catch (error) {
            console.error('Fullscreen chart failed to render:', error);
            if (titleEl) titleEl.textContent = trigger.dataset.chartTicker || 'Chart';
        }
    };

    document.querySelectorAll('[data-fullscreen-live-chart]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            openChart(trigger);
        });
    });

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented) return;
        const trigger = event.target.closest('[data-fullscreen-live-chart]');
        if (!trigger) return;

        event.preventDefault();
        event.stopPropagation();
        openChart(trigger);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const trigger = event.target.closest?.('[data-fullscreen-live-chart]');
        if (!trigger) return;
        event.preventDefault();
        openChart(trigger);
    });

    const observer = new MutationObserver(() => {
        if (modal.hasAttribute('hidden')) cleanup();
    });
    observer.observe(modal, { attributes: true, attributeFilter: ['hidden'] });
}

function setupHeroCanvas() {
    const canvas = document.querySelector('[data-hero-canvas]');

    if (!canvas) {
        return;
    }

    const context = canvas.getContext('2d', { alpha: true });

    if (!context) {
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const staticLayer = document.createElement('canvas');
    const staticContext = staticLayer.getContext('2d');
    const maxDpr = window.innerWidth < 768 ? 1 : 1.5;
    const dpr = Math.min(window.devicePixelRatio || 1, maxDpr);
    const pointCount = window.innerWidth < 768 ? 90 : 120;
    const particleCount = window.innerWidth < 768 ? 24 : 40;
    const particles = [];
    const points = [];
    const chartXs = [];
    const levels = [0.236, 0.382, 0.5, 0.618, 0.786];
    let areaGradient = null;
    let strokeGradient = null;
    let width = 0;
    let height = 0;
    let phase = 0;
    let frameId = null;
    let visible = true;
    let pageVisible = !document.hidden;
    let lastFrame = 0;
    const frameInterval = 1000 / 30;

    const seedPoints = () => {
        points.length = 0;
        for (let index = 0; index < pointCount; index += 1) {
            const time = index / pointCount;
            points.push(0.5 + Math.sin(time * Math.PI * 2) * 0.15 + (Math.random() - 0.5) * 0.05);
        }
    };

    const seedParticles = () => {
        particles.length = 0;
        for (let index = 0; index < particleCount; index += 1) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                r: Math.random() * 1.4 + 0.3,
                vx: (Math.random() - 0.5) * 0.15,
                vy: (Math.random() - 0.5) * 0.15,
                a: Math.random() * 0.5 + 0.2,
            });
        }
    };

    const drawStaticLayer = () => {
        if (!staticContext) {
            return;
        }

        staticContext.clearRect(0, 0, width, height);
        staticContext.save();
        staticContext.strokeStyle = 'rgba(23, 209, 131, 0.05)';
        staticContext.lineWidth = 1;

        for (let x = 0; x < width; x += 80) {
            staticContext.beginPath();
            staticContext.moveTo(x, 0);
            staticContext.lineTo(x, height);
            staticContext.stroke();
        }

        for (let y = 0; y < height; y += 80) {
            staticContext.beginPath();
            staticContext.moveTo(0, y);
            staticContext.lineTo(width, y);
            staticContext.stroke();
        }

        staticContext.restore();
    };

    const resize = () => {
        width = canvas.clientWidth;
        height = canvas.clientHeight;

        if (!width || !height) {
            return;
        }

        canvas.width = width * dpr;
        canvas.height = height * dpr;
        context.setTransform(dpr, 0, 0, dpr, 0, 0);

        staticLayer.width = width * dpr;
        staticLayer.height = height * dpr;
        staticContext?.setTransform(dpr, 0, 0, dpr, 0, 0);

        const padX = width * 0.04;
        const innerW = width - padX * 2;
        const padY = height * 0.18;
        const innerH = height - padY * 2;

        chartXs.length = 0;
        for (let index = 0; index < pointCount; index += 1) {
            chartXs.push(padX + (index / (pointCount - 1)) * innerW);
        }

        areaGradient = context.createLinearGradient(0, padY, 0, padY + innerH);
        areaGradient.addColorStop(0, 'rgba(23, 209, 131, 0.18)');
        areaGradient.addColorStop(1, 'rgba(23, 209, 131, 0)');

        strokeGradient = context.createLinearGradient(0, 0, width, 0);
        strokeGradient.addColorStop(0, 'rgba(23, 209, 131, 0.2)');
        strokeGradient.addColorStop(0.5, 'rgba(201, 247, 224, 0.85)');
        strokeGradient.addColorStop(1, 'rgba(23, 209, 131, 0.2)');

        drawStaticLayer();
        seedParticles();
    };

    seedPoints();
    resize();

    const observer = new ResizeObserver(resize);
    observer.observe(canvas);

    const drawParticles = () => {
        for (let index = 0; index < particles.length; index += 1) {
            const particle = particles[index];
            particle.x += particle.vx;
            particle.y += particle.vy;

            if (particle.x < 0) particle.x = width;
            if (particle.x > width) particle.x = 0;
            if (particle.y < 0) particle.y = height;
            if (particle.y > height) particle.y = 0;

            context.beginPath();
            context.arc(particle.x, particle.y, particle.r, 0, Math.PI * 2);
            context.fillStyle = `rgba(23, 209, 131, ${particle.a})`;
            context.fill();
        }
    };

    const updateChartPoints = () => {
        phase += 0.018;
        const last = points[points.length - 1];
        const target = 0.5 + Math.sin(phase) * 0.18 + Math.cos(phase * 0.7) * 0.08 + Math.sin(phase * 2.3) * 0.02;
        points.copyWithin(0, 1);
        points[points.length - 1] = last + (target - last) * 0.08;
    };

    const drawChart = () => {
        const padX = width * 0.04;
        const innerW = width - padX * 2;
        const padY = height * 0.18;
        const innerH = height - padY * 2;

        context.beginPath();
        for (let index = 0; index < points.length; index += 1) {
            const point = points[index];
            const x = chartXs[index];
            const y = padY + (1 - point) * innerH;
            if (index === 0) {
                context.moveTo(x, y);
            } else {
                context.lineTo(x, y);
            }
        }

        context.lineTo(padX + innerW, padY + innerH);
        context.lineTo(padX, padY + innerH);
        context.closePath();
        context.fillStyle = areaGradient || 'rgba(23, 209, 131, 0.12)';
        context.fill();

        context.beginPath();
        for (let index = 0; index < points.length; index += 1) {
            const point = points[index];
            const x = chartXs[index];
            const y = padY + (1 - point) * innerH;
            if (index === 0) {
                context.moveTo(x, y);
            } else {
                context.lineTo(x, y);
            }
        }

        context.strokeStyle = strokeGradient || 'rgba(201, 247, 224, 0.85)';
        context.lineWidth = 1.6;
        context.shadowColor = 'rgba(23, 209, 131, 0.6)';
        context.shadowBlur = 10;
        context.stroke();
        context.shadowBlur = 0;

        context.save();
        context.setLineDash([4, 6]);
        context.font = '10px ui-monospace, monospace';
        levels.forEach((level) => {
            const y = padY + level * innerH;
            context.beginPath();
            context.moveTo(padX, y);
            context.lineTo(padX + innerW, y);
            context.strokeStyle = 'rgba(20, 163, 94, 0.18)';
            context.lineWidth = 0.8;
            context.stroke();
            context.fillStyle = 'rgba(47, 199, 124, 0.5)';
            context.fillText(level.toFixed(3), padX + innerW + 6, y + 3);
        });
        context.restore();
    };

    const drawFrame = (animate = true) => {
        if (!width || !height) {
            return;
        }

        if (animate) {
            updateChartPoints();
        }

        context.clearRect(0, 0, width, height);
        if (staticContext) {
            context.drawImage(staticLayer, 0, 0, width, height);
        }
        drawParticles();
        drawChart();
    };

    const stop = () => {
        if (frameId) {
            cancelAnimationFrame(frameId);
            frameId = null;
        }
    };

    const tick = (now) => {
        if (!visible || !pageVisible || reducedMotion) {
            stop();
            return;
        }

        if (now - lastFrame >= frameInterval) {
            drawFrame();
            lastFrame = now;
        }

        frameId = requestAnimationFrame(tick);
    };

    const start = () => {
        if (frameId || !visible || !pageVisible || reducedMotion) {
            return;
        }

        frameId = requestAnimationFrame(tick);
    };

    const visibilityObserver = new IntersectionObserver((entries) => {
        visible = entries.some((entry) => entry.isIntersecting);
        if (visible) {
            start();
        } else {
            stop();
        }
    }, { rootMargin: '160px 0px', threshold: 0 });

    visibilityObserver.observe(canvas);

    document.addEventListener('visibilitychange', () => {
        pageVisible = !document.hidden;
        if (pageVisible) {
            start();
        } else {
            stop();
        }
    });

    drawFrame(false);
    start();
}
