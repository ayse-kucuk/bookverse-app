{{-- Bu partial sadece görünmez veri elementleri üretir; JS bunları okuyup toast gösterir --}}
@if(session('success'))
    <span data-bv-flash="success" class="hidden">{{ session('success') }}</span>
@endif
@if(session('error'))
    <span data-bv-flash="error" class="hidden">{{ session('error') }}</span>
@endif
@if(session('status') === 'profile-updated')
    <span data-bv-flash="success" class="hidden">Profil bilgilerin güncellendi.</span>
@endif
@if(session('status') === 'password-updated')
    <span data-bv-flash="success" class="hidden">Şifren güncellendi.</span>
@endif
