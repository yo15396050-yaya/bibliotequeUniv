<?php

namespace Database\Seeders;

use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\Penalite;
use App\Models\Reservation;
use App\Models\User;
use App\Support\Parametres;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Historique de circulation réaliste : emprunts en cours, retours,
 * retards, pénalités (payées, partielles, impayées) et files de réservation.
 * Les données permettent de tester réellement les tableaux de bord.
 */
class CirculationSeeder extends Seeder
{
    public function run(): void
    {
        $emprunteurs = User::whereIn('role', [User::ROLE_ETUDIANT, User::ROLE_ENSEIGNANT])
            ->where('actif', true)->get();
        $bibliothecaires = User::where('role', User::ROLE_BIBLIOTHECAIRE)->get();

        if ($emprunteurs->isEmpty() || Exemplaire::count() === 0) {
            $this->command?->warn('Aucun usager ou exemplaire : lancez d\'abord les seeders précédents.');

            return;
        }

        $montantParJour = Parametres::decimal('penalite.montant_par_jour', 100);

        // 1. Historique clos : 260 emprunts déjà retournés (dont des retours en retard).
        $this->command?->info('Génération de l\'historique des emprunts...');
        $exemplaires = Exemplaire::where('statut', Exemplaire::STATUT_DISPONIBLE)
            ->inRandomOrder()->limit(400)->get();

        $index = 0;
        foreach ($exemplaires->take(260) as $exemplaire) {
            $usager = $emprunteurs->random();
            $dateEmprunt = now()->subDays(random_int(40, 330));
            $duree = $usager->dureeEmprunt();
            $echeance = $dateEmprunt->copy()->addDays($duree);

            // Un retour sur cinq est en retard.
            $retard = $index % 5 === 0 ? random_int(1, 25) : 0;
            $retour = $echeance->copy()->addDays($retard - ($retard ? 0 : random_int(0, $duree - 1)));

            $emprunt = Emprunt::create([
                'user_id' => $usager->id,
                'bibliothecaire_id' => $bibliothecaires->random()->id ?? null,
                'livre_id' => $exemplaire->livre_id,
                'exemplaire_id' => $exemplaire->id,
                'date_emprunt' => $dateEmprunt->toDateString(),
                'date_retour_prevue' => $echeance->toDateString(),
                'date_retour_effective' => $retour->toDateString(),
                'receptionne_par' => $bibliothecaires->random()->id ?? null,
                'statut' => Emprunt::STATUT_RETOURNE,
                'etat_retour' => 'bon',
                'nombre_renouvellements' => random_int(0, 1),
            ]);

            if ($retard > 0) {
                $montant = $retard * $montantParJour;
                $paye = match ($index % 3) {
                    0 => $montant,                       // soldée
                    1 => round($montant / 2, 2),         // partielle
                    default => 0,                        // impayée
                };

                Penalite::create([
                    'user_id' => $usager->id,
                    'emprunt_id' => $emprunt->id,
                    'type' => Penalite::TYPE_RETARD,
                    'montant' => $montant,
                    'montant_paye' => $paye,
                    'jours_retard' => $retard,
                    'statut' => $paye >= $montant
                        ? Penalite::STATUT_PAYEE
                        : ($paye > 0 ? Penalite::STATUT_PARTIELLE : Penalite::STATUT_IMPAYEE),
                    'motif' => "Retard de {$retard} jour(s).",
                    'created_at' => $retour,
                    'updated_at' => $retour,
                ]);

                $emprunt->forceFill(['amende' => $paye >= $montant ? 0 : $montant])->save();
            }

            $index++;
        }

        // 2. Emprunts en cours (dont des retards actifs et des échéances proches).
        $enCours = $exemplaires->slice(260, 110);
        $compteurRetards = 0;

        foreach ($enCours as $position => $exemplaire) {
            $usager = $emprunteurs->random();

            // On respecte le quota : on saute l'usager s'il est déjà au maximum.
            if ($usager->emprunts()->whereIn('statut', ['en cours', 'en retard'])->count() >= $usager->quotaEmprunts()) {
                continue;
            }

            $duree = $usager->dureeEmprunt();

            // 1 sur 4 en retard, 1 sur 4 à échéance proche, le reste normal.
            $dateEmprunt = match ($position % 4) {
                0 => now()->subDays($duree + random_int(1, 20)),
                1 => now()->subDays($duree - random_int(1, 2)),
                default => now()->subDays(random_int(1, max(1, $duree - 4))),
            };

            $echeance = $dateEmprunt->copy()->addDays($duree);
            $estEnRetard = $echeance->isPast();

            $emprunt = Emprunt::create([
                'user_id' => $usager->id,
                'bibliothecaire_id' => $bibliothecaires->random()->id ?? null,
                'livre_id' => $exemplaire->livre_id,
                'exemplaire_id' => $exemplaire->id,
                'date_emprunt' => $dateEmprunt->toDateString(),
                'date_retour_prevue' => $echeance->toDateString(),
                'statut' => $estEnRetard ? Emprunt::STATUT_EN_RETARD : Emprunt::STATUT_EN_COURS,
            ]);

            $exemplaire->update(['statut' => Exemplaire::STATUT_EMPRUNTE]);
            $usager->increment('nombre_emprunts');

            if ($estEnRetard) {
                $jours = $emprunt->joursRetard();
                Penalite::create([
                    'user_id' => $usager->id,
                    'emprunt_id' => $emprunt->id,
                    'type' => Penalite::TYPE_RETARD,
                    'montant' => $jours * $montantParJour,
                    'jours_retard' => $jours,
                    'statut' => Penalite::STATUT_IMPAYEE,
                    'motif' => "Retard de {$jours} jour(s).",
                ]);
                $emprunt->forceFill(['amende' => $jours * $montantParJour])->save();
                $compteurRetards++;
            }
        }

        // Les compteurs doivent refléter les emprunts avant de constituer
        // les files d'attente : une réservation vise un ouvrage indisponible.
        Livre::whereIn('id', DB::table('emprunts')->distinct()->pluck('livre_id'))
            ->chunkById(200, fn ($livres) => $livres->each->synchroniserCompteurs());

        // 3. Pénalités forfaitaires (perte / dégradation).
        foreach (Exemplaire::where('statut', Exemplaire::STATUT_PERDU)->take(5)->get() as $exemplaire) {
            Penalite::create([
                'user_id' => $emprunteurs->random()->id,
                'type' => Penalite::TYPE_PERTE,
                'montant' => Parametres::decimal('penalite.montant_perte', 25000),
                'statut' => Penalite::STATUT_IMPAYEE,
                'motif' => "Perte de l'exemplaire {$exemplaire->code_barre}.",
            ]);
        }

        // 4. Files de réservation sur des ouvrages indisponibles.
        $livresIndisponibles = Livre::where('exemplaires_disponibles', 0)->inRandomOrder()->take(15)->get();

        foreach ($livresIndisponibles as $livre) {
            $candidats = $emprunteurs->random(min(3, $emprunteurs->count()));

            foreach ($candidats->values() as $position => $usager) {
                Reservation::create([
                    'user_id' => $usager->id,
                    'livre_id' => $livre->id,
                    'date_reservation' => now()->subDays(random_int(0, 5))->toDateString(),
                    'date_expiration' => now()->addDays(Parametres::entier('reservation.duree_validite', 7))->toDateString(),
                    'statut' => Reservation::STATUT_ACTIVE,
                    'position_file_attente' => $position + 1,
                ]);
            }
        }

        $this->command?->info(sprintf(
            'Circulation générée : %d emprunts (%d en cours, %d en retard), %d pénalités, %d réservations.',
            Emprunt::count(),
            Emprunt::where('statut', Emprunt::STATUT_EN_COURS)->count(),
            Emprunt::where('statut', Emprunt::STATUT_EN_RETARD)->count(),
            Penalite::count(),
            Reservation::count()
        ));
    }
}
