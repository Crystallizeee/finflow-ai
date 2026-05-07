<?php

namespace App\Providers;

use App\Actions\Auth\CreateNewUser;
use App\Actions\Auth\ResetUserPassword;
use App\Actions\Auth\UpdateUserPassword;
use App\Actions\Auth\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Fortify + Inertia Views
        Fortify::loginView(fn () => inertia('auth/Login'));
        Fortify::registerView(fn () => inertia('auth/Register'));
        Fortify::verifyEmailView(fn () => inertia('auth/VerifyEmail'));
        Fortify::resetPasswordView(fn ($request) => inertia('auth/ResetPassword', ['token' => $request->route('token'), 'email' => $request->email]));
        Fortify::requestPasswordResetLinkView(fn () => inertia('auth/ForgotPassword'));
        Fortify::confirmPasswordView(fn () => inertia('auth/ConfirmPassword'));
        Fortify::twoFactorChallengeView(fn () => inertia('auth/TwoFactorChallenge'));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
