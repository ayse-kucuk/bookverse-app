<div
    class="bv-card rounded-2xl border border-[#e8e4de] bg-[#f9f8f6]/60 p-4"
    data-google-books-search
    data-search-url="{{ route('admin.books.google-search') }}"
>
    <div class="mb-3 flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Google Books</p>
            <p class="mt-0.5 text-sm text-slate-600">Kitap ara, seç — form otomatik dolsun.</p>
        </div>
        <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-slate-400 ring-1 ring-[#e8e4de]">API</span>
    </div>

    <div class="relative">
        <label for="google-books-query" class="sr-only">Google Books'ta ara</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/></svg>
            </span>
            <input
                id="google-books-query"
                type="search"
                placeholder="Kitap adı veya yazar ara..."
                autocomplete="off"
                data-google-books-input
                class="bv-input w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm"
            >
        </div>

        <div
            data-google-books-results
            class="absolute left-0 right-0 top-[calc(100%+0.5rem)] z-20 hidden max-h-80 overflow-y-auto rounded-2xl border border-[#e8e4de] bg-white py-2 shadow-lg"
            role="listbox"
            aria-label="Google Books sonuçları"
        ></div>
    </div>

    <p data-google-books-status class="mt-2 hidden text-xs font-medium text-slate-400"></p>
</div>

<script>
(function () {
    function initGoogleBooksSearch() {
        const wrap = document.querySelector('[data-google-books-search]');
        if (!wrap || wrap.dataset.googleBooksReady === '1') return;
        wrap.dataset.googleBooksReady = '1';

        const input = wrap.querySelector('[data-google-books-input]');
        const results = wrap.querySelector('[data-google-books-results]');
        const status = wrap.querySelector('[data-google-books-status]');
        const searchUrl = wrap.dataset.searchUrl;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        function formFields() {
            const form = wrap.closest('form');
            if (!form) {
                return {};
            }

            return {
                title: form.querySelector('[name="title"]'),
                author: form.querySelector('[name="author"]'),
                description: form.querySelector('[name="description"]'),
                pageCount: form.querySelector('[name="page_count"]'),
                imageUrl: form.querySelector('[name="image_url"]'),
                categoryId: form.querySelector('[name="category_id"]'),
            };
        }

        let debounceTimer = null;
        let activeController = null;
        let latestResults = [];

        function setStatus(message, isError = false) {
            if (!status) return;
            status.textContent = message;
            status.classList.toggle('hidden', !message);
            status.classList.toggle('text-bv-accent', isError);
            status.classList.toggle('text-slate-400', !isError);
        }

        function hideResults() {
            results.classList.add('hidden');
            results.innerHTML = '';
            latestResults = [];
        }

        function showResults() {
            results.classList.remove('hidden');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function renderResults(items) {
            latestResults = items;

            if (!items.length) {
                results.innerHTML = '<p class="px-4 py-3 text-xs font-medium text-slate-500">Sonuç bulunamadı.</p>';
                showResults();
                return;
            }

            results.innerHTML = items.map((book, index) => {
                const cover = book.image_url
                    ? `<img src="${escapeHtml(book.image_url)}" alt="" class="h-full w-full object-cover">`
                    : '<span class="text-xs text-slate-400">📖</span>';

                const year = book.published_year ? `<span class="text-slate-400"> · ${book.published_year}</span>` : '';
                const category = book.category
                    ? `<span class="mt-0.5 inline-block rounded-full bg-[#f3f0eb] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-bv-accent">${escapeHtml(book.category)}</span>`
                    : '';

                return `
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 px-3 py-2.5 text-left transition hover:bg-[#f3f0eb]"
                        role="option"
                        data-google-books-pick="${index}"
                    >
                        <div class="flex h-14 w-10 shrink-0 items-center justify-center overflow-hidden rounded-md bg-slate-100 ring-1 ring-slate-200">${cover}</div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-800">${escapeHtml(book.title)}</p>
                            <p class="truncate text-xs text-slate-500">${escapeHtml(book.author)}${year}</p>
                            ${category}
                        </div>
                    </button>
                `;
            }).join('');

            showResults();
        }

        function applyBook(book) {
            const fields = formFields();

            if (fields.title) fields.title.value = book.title ?? '';
            if (fields.author) fields.author.value = book.author ?? '';
            if (fields.description) fields.description.value = book.description ?? '';
            if (fields.pageCount) fields.pageCount.value = book.page_count ?? '';
            if (fields.imageUrl) fields.imageUrl.value = book.image_url ?? '';

            let categoryStatus = '';
            if (fields.categoryId) {
                if (book.category_id) {
                    fields.categoryId.value = String(book.category_id);
                    categoryStatus = ` Kategori: ${book.category}.`;
                } else {
                    fields.categoryId.value = '';
                    const hint = Array.isArray(book.category_labels) && book.category_labels.length
                        ? ` (kaynak: ${book.category_labels[0]})`
                        : '';
                    categoryStatus = ` Kategori eşleşmedi${hint} — lütfen seç.`;
                }
            }

            hideResults();
            input.value = book.title ?? '';
            setStatus(`Form dolduruldu.${categoryStatus}`);

            if (typeof showToast === 'function') {
                if (book.image_url) {
                    showToast(book.category_id ? 'Kitap ve kategori alındı' : 'Kitap bilgileri alındı', 'success');
                } else {
                    showToast('Kapak bulunamadı — lütfen kapak URL\'si girin', 'info', 4500);
                }
            }

            fields.title?.focus();
        }

        results.addEventListener('click', (event) => {
            const button = event.target.closest('[data-google-books-pick]');
            if (!button) return;

            event.preventDefault();
            const index = parseInt(button.dataset.googleBooksPick, 10);
            const book = latestResults[index];
            if (book) applyBook(book);
        });

        async function fetchResults(query) {
            if (activeController) activeController.abort();
            activeController = new AbortController();

            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('q', query);

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                signal: activeController.signal,
            });

            if (!response.ok) {
                if (response.status === 422) {
                    throw new Error('validation');
                }
                throw new Error('request_failed');
            }

            return response.json();
        }

        input.addEventListener('input', () => {
            const query = input.value.trim();
            clearTimeout(debounceTimer);
            setStatus('');

            if (query.length < 2) {
                hideResults();
                return;
            }

            debounceTimer = setTimeout(async () => {
                setStatus('Aranıyor...');

                try {
                    const data = await fetchResults(query);
                    const items = data.results ?? [];

                    if (items.length) {
                        setStatus(data.message ?? (data.source === 'open_library' ? 'Open Library sonuçları' : ''), false);
                    } else {
                        setStatus(data.message ?? 'Sonuç bulunamadı.', true);
                    }

                    renderResults(items);
                } catch (err) {
                    if (err.name === 'AbortError') return;
                    hideResults();
                    setStatus(err.message === 'validation' ? 'En az 2 karakter gir.' : 'Arama başarısız. Tekrar dene.', true);
                }
            }, 350);
        });

        document.addEventListener('click', (event) => {
            if (!wrap.contains(event.target)) {
                hideResults();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') hideResults();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGoogleBooksSearch);
    } else {
        initGoogleBooksSearch();
    }
})();
</script>
