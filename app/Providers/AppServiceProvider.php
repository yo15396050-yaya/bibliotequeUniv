<?php

namespace App\Providers;

use App\Models\DocumentNumerique;
use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\Penalite;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\DocumentNumeriquePolicy;
use App\Policies\EmpruntPolicy;
use App\Policies\ExemplairePolicy;
use App\Policies\LivrePolicy;
use App\Policies\PenalitePolicy;
use App\Policies\ReservationPolicy;
use App\Policies\UserPolicy;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    private array $policies = [
        Livre::class => LivrePolicy::class,
        Exemplaire::class => ExemplairePolicy::class,
        Emprunt::class => EmpruntPolicy::class,
        Reservation::class => ReservationPolicy::class,
        Penalite::class => PenalitePolicy::class,
        User::class => UserPolicy::class,
        DocumentNumerique::class => DocumentNumeriquePolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'fr'));
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        // Garde-fou anti N+1 : hors production, toute relation chargée
        // paresseusement est signalée dans les logs (sans casser la page).
        Model::preventLazyLoading(! app()->isProduction());
        Model::handleLazyLoadingViolationUsing(function (Model $modele, string $relation) {
            Log::warning('Chargement paresseux détecté', [
                'modele' => $modele::class,
                'relation' => $relation,
            ]);
        });

        if (config('app.env') === 'production' && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        foreach ($this->policies as $modele => $policy) {
            Gate::policy($modele, $policy);
        }

        // Une Gate par permission déclarée : @can('livres.creer') dans les vues.
        foreach (Permissions::toutes() as $permission) {
            Gate::define($permission, fn (User $user) => $user->peut($permission));
        }

        // L'administrateur passe toutes les portes.
        Gate::before(fn (User $user) => $user->estAdministrateur() ? true : null);
    }
}
