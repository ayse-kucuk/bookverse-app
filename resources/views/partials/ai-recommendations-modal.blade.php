@auth
    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">AI</p>
        <button
            type="button"
            class="bv-btn inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-[11px] font-bold uppercase tracking-wider text-white"
            data-ai-open
        >
            🤖 AI ile öneriler
        </button>
    </div>

    <div
        id="ai-recommend-modal"
        class="fixed inset-0 z-[250] hidden items-center justify-center bg-black/40 p-4"
        role="dialog"
        aria-modal="true"
        aria-label="AI Kitap Önerileri"
    >
        <div class="bv-surface-matte flex max-h-[min(92vh,880px)] w-full max-w-3xl flex-col overflow-hidden rounded-2xl shadow-xl">
            <div class="flex shrink-0 items-center justify-between border-b border-[#e8e4de] px-5 py-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Akıllı Kitap Asistanı</p>
                    <h3 class="mt-1 text-lg font-bold text-[#1c1c1c]">Kişiselleştirilmiş öneriler</h3>
                </div>
                <button type="button" class="text-[#9a948d] hover:text-[#1c1c1c]" data-ai-close aria-label="Kapat">
                    ✕
                </button>
            </div>

            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain p-5">
                <form id="ai-recommend-form" class="space-y-4" data-ai-endpoint="{{ route('ai.recommend') }}">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="ai-mood">Ruh hali</label>
                        <select id="ai-mood" name="mood" class="bv-input w-full rounded-xl border border-[#e8e4de] bg-white px-4 py-2.5 text-sm">
                            <option value="">Seç...</option>
                            <option value="Sürükleyici">Sürükleyici</option>
                            <option value="Karanlık">Karanlık</option>
                            <option value="İlham Verici">İlham Verici</option>
                            <option value="Melankolik">Melankolik</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="ai-genre">Tür</label>
                        <select id="ai-genre" name="genre_id" class="bv-input w-full rounded-xl border border-[#e8e4de] bg-white px-4 py-2.5 text-sm">
                            <option value="">Tümü</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="ai-free-text">Serbest istek</label>
                        <textarea
                            id="ai-free-text"
                            name="free_text"
                            rows="3"
                            placeholder="Örn: Uzayda geçen aksiyon dolu bilimkurgu arıyorum"
                            class="bv-input w-full rounded-xl border border-[#e8e4de] bg-white px-4 py-2.5 text-sm resize-none"
                        ></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="ai-shelf-status">Rafa eklerken</label>
                        <select id="ai-shelf-status" name="status" class="bv-input w-full rounded-xl border border-[#e8e4de] bg-white px-4 py-2.5 text-sm">
                            <option value="okuyacagim">Okuyacağım</option>
                            <option value="okuyorum">Okuyorum</option>
                            <option value="okundu">Okundu</option>
                        </select>
                    </div>

                    <p id="ai-message" class="hidden text-xs font-semibold text-bv-accent"></p>

                    <div class="flex items-center justify-between gap-3">
                        <button type="button" class="bv-btn-outline rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-[#9a948d] hover:bg-[#f3f0eb]" data-ai-clear>
                            Temizle
                        </button>

                        <button type="submit" id="ai-submit-btn" class="bv-btn rounded-xl px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white disabled:cursor-wait disabled:opacity-70">
                            Önerileri Getir
                        </button>
                    </div>
                </form>

                <div class="space-y-3 border-t border-[#e8e4de] pt-5">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#9a948d]">Sonuçlar</p>
                        <div id="ai-loading" class="hidden text-xs font-semibold text-bv-accent">İşleniyor...</div>
                    </div>
                    <div id="ai-results" class="space-y-3">
                        <div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">
                            Formu doldurup “Önerileri Getir”e bas. Boş bırakmak da olur.
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
            const submitDefaultLabel = submitBtn?.textContent?.trim() || 'Önerileri Getir';

            function setLoading(loading) {
                loadingEl.classList.toggle('hidden', !loading);
                if (submitBtn) {
                    submitBtn.disabled = loading;
                    submitBtn.textContent = loading ? 'İşleniyor...' : submitDefaultLabel;
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
                resultsEl.innerHTML = '<div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">Formu doldurup “Önerileri Getir”e bas. Boş bırakmak da olur.</div>';
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
                    resultsEl.innerHTML = '<div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">Sonuç yok. Türü değiştirip tekrar dene.</div>';
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
                                            Kütüphaneme Ekle
                                        </button>
                                        <a href="${bookUrl}" class="bv-btn-outline px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-[#9a948d] hover:bg-[#f3f0eb]">Detayları Gör</a>
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
                resultsEl.innerHTML = '<div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">Öneriler hazırlanıyor...</div>';

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
                    showMessage('AI önerisi alınamadı. Tekrar dene.', 'error');
                    resultsEl.innerHTML = '<div class="rounded-2xl border border-dashed border-[#e8e4de] p-5 text-center text-xs font-semibold text-[#9a948d]">Bağlantı hatası. Sayfayı yenileyip tekrar deneyin.</div>';
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
