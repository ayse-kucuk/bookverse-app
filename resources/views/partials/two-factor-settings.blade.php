{{-- 2FA section for account settings --}}
<section class="bv-card mt-6 p-7 sm:p-8" data-two-factor-section data-enabled="{{ $user->hasTwoFactorEnabled() ? '1' : '0' }}">
    <div class="mb-5 flex flex-wrap items-start justify-between gap-4 border-b border-[#f0ece6] pb-5">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Güvenlik</p>
            <h2 class="mt-1 text-xl font-semibold text-[#1c1c1c]">Çift Aşamalı Doğrulama (2FA)</h2>
            <p class="mt-1 text-sm text-[#6b6560]">Authenticator uygulaması ile hesabını ekstra koru.</p>
        </div>
        <span
            data-two-factor-badge
            class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider {{ $user->hasTwoFactorEnabled() ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-[#f3f0eb] text-[#9a948d] ring-1 ring-[#e8e4de]' }}"
        >
            {{ $user->hasTwoFactorEnabled() ? 'Aktif' : 'Kapalı' }}
        </span>
    </div>

    <div data-two-factor-enabled-panel class="{{ $user->hasTwoFactorEnabled() ? '' : 'hidden' }} space-y-4">
        <p class="text-sm text-[#6b6560]">2FA açık. Girişte e-posta/şifreden sonra 6 haneli kod istenir.</p>
        <button type="button" data-two-factor-open-disable class="bv-btn-outline px-5 py-2.5 text-xs font-bold uppercase tracking-wider">
            2FA’yı Kapat
        </button>
    </div>

    <div data-two-factor-disabled-panel class="{{ $user->hasTwoFactorEnabled() ? 'hidden' : '' }}">
        <button type="button" data-two-factor-open-setup class="bv-btn px-5 py-2.5 text-xs font-bold uppercase tracking-wider">
            2FA’yı Etkinleştir
        </button>
    </div>
</section>

{{-- Setup modal --}}
<div data-two-factor-setup-modal class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/40 p-4" role="dialog" aria-modal="true">
    <div class="bv-surface-matte w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between border-b border-[#e8e4de] px-5 py-4">
            <h3 class="text-sm font-bold text-[#1c1c1c]" data-two-factor-setup-title>2FA Kurulumu</h3>
            <button type="button" data-two-factor-close-setup class="text-[#9a948d] hover:text-[#1c1c1c]" aria-label="Kapat">✕</button>
        </div>

        <div class="space-y-4 px-5 py-5" data-two-factor-step="1">
            <p class="text-sm text-[#6b6560]">Google Authenticator veya benzeri uygulamada QR kodu tarayın. Alternatif olarak secret anahtarı elle girin.</p>
            <div class="flex justify-center rounded-xl bg-white p-4 ring-1 ring-[#e8e4de]" data-two-factor-qr></div>
            <p class="break-all text-center font-mono text-xs text-[#6b6560]" data-two-factor-secret></p>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="two-factor-confirm-code">6 haneli kod</label>
                <input id="two-factor-confirm-code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="8"
                    class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-center text-lg tracking-[0.3em]"
                    data-two-factor-confirm-input>
                <p class="mt-1 hidden text-xs text-bv-accent" data-two-factor-confirm-error></p>
            </div>
            <button type="button" data-two-factor-confirm-btn class="bv-btn w-full px-5 py-3 text-xs font-bold uppercase tracking-wider">
                Doğrula ve Aktifleştir
            </button>
        </div>

        <div class="hidden space-y-4 px-5 py-5" data-two-factor-step="2">
            <p class="text-sm font-semibold text-[#1c1c1c]">Kurtarma kodlarını güvenli bir yere kaydet.</p>
            <p class="text-xs text-[#6b6560]">Bu kodlar yalnızca bir kez gösterilir. Authenticator’a erişemezsen bunlarla giriş yapabilirsin.</p>
            <ul class="grid grid-cols-2 gap-2 font-mono text-xs" data-two-factor-recovery-list></ul>
            <button type="button" data-two-factor-finish-setup class="bv-btn w-full px-5 py-3 text-xs font-bold uppercase tracking-wider">
                Tamamladım
            </button>
        </div>
    </div>
</div>

{{-- Disable modal --}}
<div data-two-factor-disable-modal class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/40 p-4" role="dialog" aria-modal="true">
    <div class="bv-surface-matte w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between border-b border-[#e8e4de] px-5 py-4">
            <h3 class="text-sm font-bold text-[#1c1c1c]">2FA’yı Kapat</h3>
            <button type="button" data-two-factor-close-disable class="text-[#9a948d] hover:text-[#1c1c1c]" aria-label="Kapat">✕</button>
        </div>
        <form class="space-y-4 px-5 py-5" data-two-factor-disable-form>
            @csrf
            <p class="text-sm text-[#6b6560]">Kapatmak için şifreni ve geçerli bir 2FA / kurtarma kodunu gir.</p>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="two-factor-disable-password">Şifre</label>
                <input id="two-factor-disable-password" type="password" name="password" required
                    class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm" data-two-factor-disable-password>
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="two-factor-disable-code">Doğrulama kodu</label>
                <input id="two-factor-disable-code" type="text" name="code" required
                    class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm tracking-widest" data-two-factor-disable-code>
            </div>
            <p class="hidden text-xs text-bv-accent" data-two-factor-disable-error></p>
            <button type="submit" class="bv-btn w-full px-5 py-3 text-xs font-bold uppercase tracking-wider">Kapat</button>
        </form>
    </div>
</div>

<script>
(function () {
    const section = document.querySelector('[data-two-factor-section]');
    if (!section) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const setupModal = document.querySelector('[data-two-factor-setup-modal]');
    const disableModal = document.querySelector('[data-two-factor-disable-modal]');
    const badge = document.querySelector('[data-two-factor-badge]');
    const enabledPanel = document.querySelector('[data-two-factor-enabled-panel]');
    const disabledPanel = document.querySelector('[data-two-factor-disabled-panel]');

    function openModal(el) {
        el.classList.remove('hidden');
        el.classList.add('flex');
    }
    function closeModal(el) {
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    function setEnabledUI(enabled) {
        section.dataset.enabled = enabled ? '1' : '0';
        badge.textContent = enabled ? 'Aktif' : 'Kapalı';
        badge.className = enabled
            ? 'rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
            : 'rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider bg-[#f3f0eb] text-[#9a948d] ring-1 ring-[#e8e4de]';
        enabledPanel.classList.toggle('hidden', !enabled);
        disabledPanel.classList.toggle('hidden', enabled);
    }

    async function postJson(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body ?? {}),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = data.message
                || Object.values(data.errors || {}).flat()[0]
                || 'İşlem başarısız.';
            throw new Error(message);
        }
        return data;
    }

    document.querySelector('[data-two-factor-open-setup]')?.addEventListener('click', async () => {
        try {
            const data = await postJson(@json(route('two-factor.setup')));
            document.querySelector('[data-two-factor-qr]').innerHTML = data.qr_svg;
            document.querySelector('[data-two-factor-secret]').textContent = data.secret;
            document.querySelector('[data-two-factor-step="1"]').classList.remove('hidden');
            document.querySelector('[data-two-factor-step="2"]').classList.add('hidden');
            document.querySelector('[data-two-factor-confirm-input]').value = '';
            document.querySelector('[data-two-factor-confirm-error]').classList.add('hidden');
            openModal(setupModal);
        } catch (err) {
            if (typeof showToast === 'function') showToast(err.message, 'error');
        }
    });

    document.querySelector('[data-two-factor-confirm-btn]')?.addEventListener('click', async () => {
        const code = document.querySelector('[data-two-factor-confirm-input]').value.trim();
        const errorEl = document.querySelector('[data-two-factor-confirm-error]');
        errorEl.classList.add('hidden');
        try {
            const data = await postJson(@json(route('two-factor.confirm')), { code });
            const list = document.querySelector('[data-two-factor-recovery-list]');
            list.innerHTML = (data.recovery_codes || []).map(c => `<li class="rounded-lg bg-[#f9f8f6] px-2 py-2 text-center ring-1 ring-[#e8e4de]">${c}</li>`).join('');
            document.querySelector('[data-two-factor-step="1"]').classList.add('hidden');
            document.querySelector('[data-two-factor-step="2"]').classList.remove('hidden');
            setEnabledUI(true);
            if (typeof showToast === 'function') showToast('2FA etkinleştirildi', 'success');
        } catch (err) {
            errorEl.textContent = err.message;
            errorEl.classList.remove('hidden');
        }
    });

    document.querySelector('[data-two-factor-finish-setup]')?.addEventListener('click', () => closeModal(setupModal));
    document.querySelector('[data-two-factor-close-setup]')?.addEventListener('click', () => closeModal(setupModal));
    document.querySelector('[data-two-factor-open-disable]')?.addEventListener('click', () => openModal(disableModal));
    document.querySelector('[data-two-factor-close-disable]')?.addEventListener('click', () => closeModal(disableModal));

    document.querySelector('[data-two-factor-disable-form]')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const errorEl = document.querySelector('[data-two-factor-disable-error]');
        errorEl.classList.add('hidden');
        try {
            await postJson(@json(route('two-factor.disable')), {
                password: document.querySelector('[data-two-factor-disable-password]').value,
                code: document.querySelector('[data-two-factor-disable-code]').value,
            });
            setEnabledUI(false);
            closeModal(disableModal);
            if (typeof showToast === 'function') showToast('2FA kapatıldı', 'success');
        } catch (err) {
            errorEl.textContent = err.message;
            errorEl.classList.remove('hidden');
        }
    });

    setupModal?.addEventListener('click', (e) => { if (e.target === setupModal) closeModal(setupModal); });
    disableModal?.addEventListener('click', (e) => { if (e.target === disableModal) closeModal(disableModal); });
})();
</script>
