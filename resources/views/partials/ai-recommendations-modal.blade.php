@auth
    <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">{{ __('ui.feed.ai') }}</p>
        <button
            type="button"
            class="bv-btn inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-[11px] font-bold uppercase tracking-wider text-white"
            data-ai-open
        >
            🤖 {{ __('ui.feed.ai_button') }}
        </button>
    </div>

    <div
        id="ai-recommend-modal"
        class="fixed inset-0 z-[250] hidden items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('ui.ai.title') }}"
    >
        <div class="bv-surface-matte flex max-h-[min(92dvh,880px)] w-full max-w-3xl flex-col overflow-hidden rounded-t-2xl shadow-xl sm:rounded-2xl">
            <div class="flex shrink-0 items-center justify-between border-b border-[#e8e4de] px-4 py-3 sm:px-5 sm:py-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">{{ __('ui.ai.title') }}</p>
                    <h3 class="mt-1 text-base font-bold text-[#1c1c1c] sm:text-lg">{{ __('ui.ai.subtitle') }}</h3>
                </div>
                <button type="button" class="flex h-10 w-10 items-center justify-center text-[#9a948d] hover:text-[#1c1c1c]" data-ai-close aria-label="{{ __('ui.ai.close') }}">
                    ✕
                </button>
            </div>

            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain p-4 sm:p-5" style="padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));">
                <form id="ai-recommend-form" class="space-y-4" data-ai-endpoint="{{ route('ai.recommend') }}">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="ai-mood">{{ __('ui.ai.mood') }}</label>
                        <select id="ai-mood" name="mood" class="bv-input w-full rounded-xl border border-[#e8e4de] bg-white px-4 py-2.5 text-sm">
                            <option value="">{{ __('ui.ai.mood_select') }}</option>
                            <option value="Sürükleyici">{{ __('ui.ai.mood_gripping') }}</option>
                            <option value="Karanlık">{{ __('ui.ai.mood_dark') }}</option>
                            <option value="İlham Verici">{{ __('ui.ai.mood_inspiring') }}</option>
                            <option value="Melankolik">{{ __('ui.ai.mood_melancholic') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="ai-genre">{{ __('ui.ai.genre') }}</label>
                        <select id="ai-genre" name="genre_id" class="bv-input w-full rounded-xl border border-[#e8e4de] bg-white px-4 py-2.5 text-sm">
                            <option value="">{{ __('ui.ai.all') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="ai-free-text">{{ __('ui.ai.free_text') }}</label>
                        <textarea
                            id="ai-free-text"
                            name="free_text"
                            rows="3"
                            placeholder="{{ __('ui.ai.free_text_placeholder') }}"
                            class="bv-input w-full rounded-xl border border-[#e8e4de] bg-white px-4 py-2.5 text-sm resize-none"
                        ></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="ai-shelf-status">{{ __('ui.ai.shelf_status') }}</label>
                        <select id="ai-shelf-status" name="status" class="bv-input w-full rounded-xl border border-[#e8e4de] bg-white px-4 py-2.5 text-sm">
                            <option value="okuyacagim">{{ __('ui.book.status_will_read') }}</option>
                            <option value="okuyorum">{{ __('ui.book.status_reading') }}</option>
                            <option value="okundu">{{ __('ui.book.status_read') }}</option>
                        </select>
                    </div>

                    <p id="ai-message" class="hidden text-xs font-semibold text-bv-accent"></p>

                    <div class="flex items-center justify-between gap-3">
                        <button type="button" class="bv-btn-outline rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-[#9a948d] hover:bg-[#f3f0eb]" data-ai-clear>
                            {{ __('ui.ai.clear') }}
                        </button>

                        <button type="submit" id="ai-submit-btn" class="bv-btn rounded-xl px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white disabled:cursor-wait disabled:opacity-70">
                            {{ __('ui.ai.get') }}
                        </button>
                    </div>
                </form>

                <div class="space-y-3 border-t border-[#e8e4de] pt-5">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#9a948d]">{{ __('ui.ai.results') }}</p>
                        <div id="ai-loading" class="hidden text-xs font-semibold text-bv-accent">{{ __('ui.ai.loading') }}</div>
                    </div>
                    <div id="ai-results" class="space-y-3">
                        <div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">
                            {{ __('ui.ai.hint') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('ai-recommend-modal');
            if (!modal) return;

            // Sidebar animasyon/sticky, fixed modalı dar sütuna sıkıştırır — body'ye taşı.
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            const openBtn = document.querySelector('[data-ai-open]');
            const closeBtn = modal.querySelector('[data-ai-close]');
            const form = document.getElementById('ai-recommend-form');
            const resultsEl = document.getElementById('ai-results');
            const loadingEl = document.getElementById('ai-loading');
            const messageEl = document.getElementById('ai-message');
            const submitBtn = document.getElementById('ai-submit-btn');
            const endpoint = form?.dataset.aiEndpoint;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const submitDefaultLabel = submitBtn?.textContent?.trim() || @json(__('ui.ai.get'));
            const loadingLabel = @json(__('ui.ai.loading'));
            const preparingLabel = @json(__('ui.ai.preparing'));
            const hintLabel = @json(__('ui.ai.hint'));
            const noResultsLabel = @json(__('ui.ai.no_results'));
            const errorLabel = @json(__('ui.ai.error'));
            const connectionErrorLabel = @json(__('ui.ai.connection_error'));
            const addLibraryLabel = @json(__('ui.ai.add_library'));
            const seeDetailsLabel = @json(__('ui.ai.see_details'));

            function setLoading(loading) {
                loadingEl.classList.toggle('hidden', !loading);
                if (submitBtn) {
                    submitBtn.disabled = loading;
                    submitBtn.textContent = loading ? loadingLabel : submitDefaultLabel;
                }
            }

            function showMessage(msg, type = 'accent') {
                messageEl.textContent = msg;
                messageEl.classList.remove('hidden');
                messageEl.classList.toggle('text-bv-accent', type === 'accent');
                messageEl.classList.toggle('text-red-600', type === 'error');
            }

            function hideMessage() {
                messageEl.classList.add('hidden');
                messageEl.textContent = '';
            }

            function resetUI() {
                resultsEl.innerHTML = `<div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">${hintLabel}</div>`;
                hideMessage();
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function renderCards(items) {
                if (!items || !items.length) {
                    resultsEl.innerHTML = `<div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">${noResultsLabel}</div>`;
                    return;
                }

                resultsEl.innerHTML = items.map((item) => {
                    const title = escapeHtml(item.title);
                    const author = escapeHtml(item.author);
                    const genre = escapeHtml(item.genre);
                    const reason = escapeHtml(item.reason || '');
                    const bookUrl = escapeHtml(item.book_url);
                    const imageUrl = escapeHtml(item.image_url || '');
                    const bookId = escapeHtml(item.book_id);

                    const cover = item.image_url
                        ? `<img src="${imageUrl}" alt="${title}" class="h-full w-full object-cover" />`
                        : '<div class="flex h-full items-center justify-center text-sm text-white">📖</div>';

                    const score = Math.max(0, Math.min(100, parseInt(item.matchScore || 0, 10)));

                    return `
                        <article class="bv-card p-4">
                            <div class="flex items-start gap-4">
                                <a href="${bookUrl}" class="h-20 w-14 shrink-0 overflow-hidden rounded-lg border border-[#e8e4de] bg-white">
                                    ${cover}
                                </a>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="inline-flex items-center rounded-full bg-[#f9f8f6] px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider text-bv-accent ring-1 ring-[#e8e4de]">${genre}</span>
                                        <span class="text-[10px] font-bold text-[#9a948d]">%${score}</span>
                                    </div>
                                    <h4 class="mt-2 text-sm font-extrabold leading-snug text-[#1c1c1c]">${title}</h4>
                                    <p class="mt-0.5 text-xs font-semibold text-[#9a948d]">${author}</p>
                                    <p class="mt-2 text-xs leading-relaxed text-[#6b6560]">${reason}</p>
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <button type="button" class="bv-btn px-4 py-2 text-[11px] font-bold uppercase tracking-wider" data-ai-add data-book-id="${bookId}">
                                            ${addLibraryLabel}
                                        </button>
                                        <a href="${bookUrl}" class="bv-btn-outline px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-[#9a948d] hover:bg-[#f3f0eb]">${seeDetailsLabel}</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    `;
                }).join('');

                resultsEl.querySelectorAll('[data-ai-add]').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const bookId = btn.dataset.bookId;
                        const status = document.getElementById('ai-shelf-status')?.value || 'okuyacagim';
                        const url = `/books/${bookId}/status`;

                        btn.disabled = true;
                        btn.classList.add('opacity-60');

                        try {
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ status }),
                            });

                            if (!res.ok && ![302, 303].includes(res.status)) {
                                throw new Error('add_failed');
                            }

                            if (typeof showToast === 'function') showToast('Kitap rafına eklendi.', 'success');
                            btn.textContent = 'Eklendi';
                        } catch (e) {
                            if (typeof showToast === 'function') showToast('Rafa eklenemedi.', 'error');
                            btn.disabled = false;
                            btn.classList.remove('opacity-60');
                        }
                    });
                });

                resultsEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function collectPayload() {
                const mood = document.getElementById('ai-mood')?.value || '';
                const genre_id = document.getElementById('ai-genre')?.value || '';
                const free_text = document.getElementById('ai-free-text')?.value || '';

                return {
                    mood: mood.trim() || null,
                    genre_id: genre_id ? parseInt(genre_id, 10) : null,
                    free_text: free_text.trim() || null,
                    limit: 5,
                };
            }

            async function submitRecommendation() {
                if (!endpoint) {
                    showMessage('Öneri adresi bulunamadı. Sayfayı yenile.', 'error');
                    return;
                }

                const payload = collectPayload();

                setLoading(true);
                hideMessage();
                resultsEl.innerHTML = `<div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">${preparingLabel}</div>`;

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(data.message || 'request_failed');
                    }

                    const items = data.recommendations || [];
                    renderCards(items);

                    if (data.message) {
                        showMessage(data.message, items.length ? 'accent' : 'error');
                    }
                } catch (e) {
                    showMessage(errorLabel, 'error');
                    resultsEl.innerHTML = `<div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">${connectionErrorLabel}</div>`;
                } finally {
                    setLoading(false);
                }
            }

            openBtn?.addEventListener('click', () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                resetUI();
            });

            closeBtn?.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });

            form?.addEventListener('submit', (e) => {
                e.preventDefault();
                submitRecommendation();
            });

            modal.querySelector('[data-ai-clear]')?.addEventListener('click', () => {
                document.getElementById('ai-mood').value = '';
                document.getElementById('ai-genre').value = '';
                document.getElementById('ai-free-text').value = '';
                document.getElementById('ai-shelf-status').value = 'okuyacagim';
                resetUI();
            });
        })();
    </script>
@endauth
