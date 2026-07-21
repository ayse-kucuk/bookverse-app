<div id="bv-toast-container" aria-live="polite" aria-atomic="false"></div>

<script>
// ── Toast sistemi (global) ──────────────────────────────────────────────────
window.showToast = function (message, type = 'success', duration = 3500) {
    const container = document.getElementById('bv-toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `bv-toast bv-toast-${type}`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <span class="bv-toast-bar"></span>
        <span class="bv-toast-msg">${message}</span>
        <button class="bv-toast-close" aria-label="Kapat">✕</button>
    `;

    const dismiss = () => {
        toast.classList.add('bv-toast-out');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    };

    toast.querySelector('.bv-toast-close').addEventListener('click', dismiss);
    container.appendChild(toast);
    setTimeout(dismiss, duration);
};

// Flash mesajlarını (session) otomatik toast'a çevir
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bv-flash]').forEach(el => {
        const type = el.dataset.bvFlash || 'success';
        const msg  = el.textContent.trim();
        if (msg) window.showToast(msg, type);
        el.remove();
    });
});
</script>

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    async function postJson(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });

        if (!response.ok) {
            throw new Error('request_failed');
        }

        return response.json();
    }

    function setLikeButtonState(btn, liked, count) {
        const icon = btn.querySelector('.bv-like-icon');
        const countEl = btn.querySelector('.bv-like-count');

        btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
        btn.classList.toggle('text-bv-accent', liked);
        btn.classList.toggle('text-slate-400', !liked);
        btn.classList.toggle('hover:bg-\[#f3f0eb\]', !liked);
        btn.classList.toggle('hover:text-bv-accent', !liked);

        if (icon) icon.textContent = liked ? '❤️' : '🤍';
        if (countEl) {
            countEl.textContent = count;
            countEl.classList.toggle('hidden', count < 1);
        }
    }

    function setStarButtons(widget, rating) {
        widget.dataset.current = rating;
        widget.querySelectorAll('[data-rating-star]').forEach((star) => {
            const value = parseInt(star.dataset.value, 10);
            star.classList.toggle('text-amber-400', value <= rating);
            star.classList.toggle('text-slate-200', value > rating);
            star.classList.toggle('hover:text-amber-300', value > rating);
        });

        const label = widget.querySelector('[data-rating-label]');
        if (label) {
            label.innerHTML = rating
                ? `Senin puanın: <span class="text-amber-600">${rating}/5</span>`
                : '<span class="text-slate-400">Puanlamak için yıldıza tıkla</span>';
        }
    }

    function renderAverageStars(value) {
        let html = '';
        for (let i = 1; i <= 5; i++) {
            html += i <= Math.round(value)
                ? '<span class="text-amber-400">★</span>'
                : '<span class="text-slate-200">★</span>';
        }
        return html;
    }

    function updateBookRatingSummary(data) {
        const summary = document.querySelector('[data-book-rating-summary]');
        if (!summary || data.average_rating === null) return;

        summary.innerHTML = `
            <span class="inline-flex items-center gap-0.5 text-lg">${renderAverageStars(data.average_rating)}</span>
            <span class="text-sm font-bold text-slate-700">${data.average_rating.toFixed(1)}</span>
            <span class="text-xs text-slate-400">(${data.ratings_count} puan)</span>
        `;
    }

    function updateNotificationBadge(count) {
        const bell = document.querySelector('[data-notification-bell]');
        let badge = document.querySelector('[data-notification-badge]');
        const readAllBtn = document.querySelector('[data-notification-read-all]');

        if (bell) {
            bell.dataset.unreadCount = String(count);
        }

        if (count < 1) {
            if (badge) badge.classList.add('hidden');
            if (readAllBtn) readAllBtn.remove();
            return;
        }

        if (!badge && bell) {
            badge = document.createElement('span');
            badge.dataset.notificationBadge = '';
            badge.className = 'absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center bg-[#1c1c1c] px-1 text-[9px] font-bold text-white';
            bell.appendChild(badge);
        }

        if (badge) {
            badge.classList.remove('hidden');
            badge.textContent = count > 9 ? '9+' : String(count);
        }
    }

    function showEmptyNotificationList() {
        const list = document.querySelector('[data-notification-list]');
        if (!list) return;

        if (!list.querySelector('[data-notification-read]')) {
            list.innerHTML = '<p class="px-4 py-6 text-center text-xs text-slate-400">Bildirim yok</p>';
        }
    }

    function removeNotificationItem(item) {
        if (!item) return;

        item.remove();
        const remaining = document.querySelectorAll('[data-notification-read]').length;
        updateNotificationBadge(remaining);
        showEmptyNotificationList();
    }

    async function markNotificationRead(url) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: '{}',
        });

        if (!response.ok) throw new Error('read_failed');

        return response.json();
    }

    async function markAllNotificationsRead(url) {
        document.querySelectorAll('[data-notification-read]').forEach((item) => item.remove());
        updateNotificationBadge(0);
        showEmptyNotificationList();

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: '{}',
        });

        if (!response.ok) throw new Error('read_all_failed');

        const data = await response.json();
        updateNotificationBadge(data.unread_count ?? 0);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const bell = document.querySelector('[data-notification-bell]');
        if (bell) {
            updateNotificationBadge(parseInt(bell.dataset.unreadCount || '0', 10));
        }
    });

    document.addEventListener('click', async (event) => {
        const readAllBtn = event.target.closest('[data-notification-read-all]');
        if (readAllBtn) {
            event.preventDefault();
            try {
                await markAllNotificationsRead(readAllBtn.dataset.notificationReadAll);
            } catch {
                // ignore
            }
            return;
        }

        const seeAllLink = event.target.closest('[data-notification-see-all]');
        if (seeAllLink) {
            event.preventDefault();
            const target = seeAllLink.getAttribute('href');
            try {
                await markAllNotificationsRead(seeAllLink.dataset.notificationSeeAll);
            } catch {
                // continue navigation
            }
            window.location.href = target;
            return;
        }

        const notificationLink = event.target.closest('[data-notification-read]');
        if (notificationLink) {
            event.preventDefault();

            const readUrl = notificationLink.dataset.notificationRead;
            const fallbackUrl = notificationLink.getAttribute('href');

            removeNotificationItem(notificationLink);

            try {
                const data = await markNotificationRead(readUrl);
                updateNotificationBadge(data.unread_count ?? 0);
                window.location.href = data.redirect;
            } catch {
                window.location.href = fallbackUrl;
            }
            return;
        }

        const likeBtn = event.target.closest('[data-like-toggle]');
        if (likeBtn) {
            event.preventDefault();
            if (likeBtn.disabled) return;

            likeBtn.disabled = true;
            likeBtn.classList.add('opacity-60');

            try {
                const data = await postJson(likeBtn.dataset.url, {});
                setLikeButtonState(likeBtn, data.liked, data.likes_count);
                if (typeof showToast === 'function') {
                    showToast(data.liked ? 'Paylaşım beğenildi' : 'Beğeni kaldırıldı', 'info', 2000);
                }
            } catch {
                likeBtn.classList.add('shake');
                if (typeof showToast === 'function') showToast('Bir hata oluştu', 'error');
            } finally {
                likeBtn.disabled = false;
                likeBtn.classList.remove('opacity-60');
            }
            return;
        }

        const starBtn = event.target.closest('[data-rating-star]');
        if (starBtn) {
            event.preventDefault();
            const widget = starBtn.closest('[data-rating-widget]');
            if (!widget || starBtn.disabled) return;

            const rating = parseInt(starBtn.dataset.value, 10);
            widget.querySelectorAll('[data-rating-star]').forEach((b) => b.disabled = true);
            setStarButtons(widget, rating);

            try {
                const data = await postJson(widget.dataset.url, { rating });
                setStarButtons(widget, data.user_rating);
                updateBookRatingSummary(data);
                if (typeof showToast === 'function') showToast(`${data.user_rating}/5 puan verildi`, 'success', 2500);
            } catch {
                const previous = parseInt(widget.dataset.current, 10) || 0;
                setStarButtons(widget, previous);
                if (typeof showToast === 'function') showToast('Puanlama kaydedilemedi', 'error');
            } finally {
                widget.querySelectorAll('[data-rating-star]').forEach((b) => b.disabled = false);
            }
        }
    });

    // Canlı arama önerileri
    (function initLiveSearch() {
        const input = document.querySelector('[data-live-search-input]');
        const dropdown = document.querySelector('[data-live-search-dropdown]');
        const wrap = document.querySelector('[data-live-search-wrap]');
        if (!input || !dropdown || !wrap) return;

        const suggestUrl = input.dataset.suggestUrl;
        let debounceTimer = null;
        let activeController = null;

        function hideDropdown() {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
        }

        function showDropdown() {
            dropdown.classList.remove('hidden');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function renderSuggestions(data) {
            const total = data.books.length + data.users.length + data.posts.length;

            if (total === 0) {
                dropdown.innerHTML = '<p class="px-4 py-3 text-xs font-medium text-slate-500">Sonuç bulunamadı</p>';
                showDropdown();
                return;
            }

            let html = '';

            if (data.books.length) {
                html += '<p class="px-4 pb-1 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Kitaplar</p>';
                data.books.forEach((book) => {
                    const cover = book.image_url
                        ? `<img src="${escapeHtml(book.image_url)}" alt="" class="h-full w-full object-cover">`
                        : '<span class="text-xs text-white">📖</span>';
                    html += `
                        <a href="${escapeHtml(book.url)}" class="flex items-center gap-3 px-3 py-2.5 transition hover:bg-\[#f3f0eb\]" role="option">
                            <div class="flex h-12 w-8 shrink-0 items-center justify-center overflow-hidden rounded-md bg-slate-800 ring-1 ring-slate-200">${cover}</div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900">${escapeHtml(book.title)}</p>
                                <p class="truncate text-xs font-medium text-slate-500">${escapeHtml(book.author)}</p>
                            </div>
                        </a>`;
                });
            }

            if (data.users.length) {
                html += '<p class="px-4 pb-1 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Kullanıcılar</p>';
                data.users.forEach((user) => {
                    html += `
                        <a href="${escapeHtml(user.url)}" class="flex items-center gap-3 px-3 py-2.5 transition hover:bg-\[#f3f0eb\]" role="option">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-100 text-sm">👤</span>
                            <p class="truncate text-sm font-bold text-slate-900">${escapeHtml(user.name)}</p>
                        </a>`;
                });
            }

            if (data.posts.length) {
                html += '<p class="px-4 pb-1 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Paylaşımlar</p>';
                data.posts.forEach((post) => {
                    html += `
                        <a href="${escapeHtml(post.url)}" class="block px-3 py-2.5 transition hover:bg-\[#f3f0eb\]" role="option">
                            <p class="line-clamp-2 text-xs font-medium text-slate-700">${escapeHtml(post.excerpt)}</p>
                            <p class="mt-0.5 text-[10px] font-semibold text-slate-500">${escapeHtml(post.author)}</p>
                        </a>`;
                });
            }

            if (data.search_url) {
                html += `
                    <a href="${escapeHtml(data.search_url)}" class="mt-1 block border-t border-slate-200 bg-slate-50 px-4 py-2.5 text-center text-xs font-bold text-bv-accent transition hover:bg-\[#f3f0eb\]">
                        Tüm sonuçları gör →
                    </a>`;
            }

            dropdown.innerHTML = html;
            showDropdown();
        }

        async function fetchSuggestions(query) {
            if (activeController) activeController.abort();
            activeController = new AbortController();

            const url = new URL(suggestUrl, window.location.origin);
            url.searchParams.set('q', query);

            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeController.signal,
            });

            if (!response.ok) throw new Error('suggest_failed');
            return response.json();
        }

        input.addEventListener('input', () => {
            const query = input.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 1) {
                hideDropdown();
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const data = await fetchSuggestions(query);
                    renderSuggestions(data);
                } catch (err) {
                    if (err.name !== 'AbortError') hideDropdown();
                }
            }, 200);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 1 && dropdown.innerHTML) {
                showDropdown();
            }
        });

        document.addEventListener('click', (event) => {
            if (!wrap.contains(event.target)) hideDropdown();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') hideDropdown();
        });
    })();
})();
</script>

@include('partials.flash')
