<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorSetupController extends Controller
{
    public function show(Request $request, TwoFactorService $twoFactor): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled() && ! $request->session()->has('two_factor_recovery_codes_plain')) {
            return redirect()->route('home');
        }

        $recoveryCodes = $request->session()->get('two_factor_recovery_codes_plain');

        if ($recoveryCodes) {
            return view('auth.two-factor-setup', [
                'secret' => null,
                'qrSvg' => null,
                'fromRegistration' => (bool) $request->session()->get('two_factor_onboarding'),
                'recoveryCodes' => $recoveryCodes,
            ]);
        }

        $secret = $request->session()->get('two_factor_setup_secret');

        if (! $secret) {
            $secret = $twoFactor->generateSecret();
            $request->session()->put('two_factor_setup_secret', $secret);
            cache()->put('two_factor_setup:'.$user->id, $secret, now()->addMinutes(15));
        }

        return view('auth.two-factor-setup', [
            'secret' => $secret,
            'qrSvg' => $twoFactor->qrCodeSvg($user, $secret),
            'fromRegistration' => (bool) $request->session()->get('two_factor_onboarding'),
            'recoveryCodes' => null,
        ]);
    }

    public function confirm(Request $request, TwoFactorService $twoFactor): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();
        $secret = $request->session()->get('two_factor_setup_secret')
            ?? cache()->get('two_factor_setup:'.$user->id);

        if (! $secret) {
            return redirect()
                ->route('two-factor.setup')
                ->withErrors(['code' => 'Kurulum oturumu bulunamadı. Lütfen tekrar deneyin.']);
        }

        if (! $twoFactor->verify($secret, (string) $request->input('code'))) {
            return back()->withErrors(['code' => 'Doğrulama kodu geçersiz.']);
        }

        $plainCodes = $twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($plainCodes),
        ])->save();

        $request->session()->forget('two_factor_setup_secret');
        $request->session()->forget('two_factor_onboarding');
        cache()->forget('two_factor_setup:'.$user->id);
        $request->session()->flash('two_factor_recovery_codes_plain', $plainCodes);
        $request->session()->flash('status', 'Çift aşamalı doğrulama etkinleştirildi. Kurtarma kodlarını kaydet.');

        return redirect()->route('two-factor.setup');
    }

    public function finish(Request $request): RedirectResponse
    {
        $request->session()->forget('two_factor_recovery_codes_plain');
        $request->session()->forget('two_factor_onboarding');

        return redirect('/')->with('success', 'Hesabın hazır. İyi okumalar!');
    }

    public function skip(Request $request): RedirectResponse
    {
        $request->session()->forget('two_factor_setup_secret');
        $request->session()->forget('two_factor_onboarding');
        cache()->forget('two_factor_setup:'.$request->user()->id);

        return redirect('/')->with('status', '2FA’yı daha sonra Hesap Ayarları’ndan açabilirsin.');
    }
}
