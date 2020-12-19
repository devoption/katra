<?php

namespace Katra\Katra\Providers;

use Illuminate\Http\Request;
use Katra\Katra\Models\User;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Katra\Katra\Actions\Fortify\CreateNewUser;
use Katra\Katra\Actions\Fortify\ResetUserPassword;
use Katra\Katra\Actions\Fortify\UpdateUserPassword;
use Katra\Katra\Actions\Fortify\UpdateUserProfileInformation;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        
        Fortify::loginView(function() {
            return view('katra::pages.auth.login');
        });

        Fortify::registerView(function() {
            return view('katra::pages.auth.register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('katra::pages.auth.forgot');
        });
    }
}
