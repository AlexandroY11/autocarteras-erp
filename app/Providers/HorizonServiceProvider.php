<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Aquí puedes configurar notificaciones si Horizon falla
        // Horizon::routeMailNotificationsTo('notificaciones@autocarterascali.com');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            return in_array(optional($user)->email, [
                'notificaciones@autocarterascali.com', 
                'alexandro.yule@autocarterascali.com',          
                'jhon.yule@autocarterascali.com',          
            ]);
        });
    }
}
