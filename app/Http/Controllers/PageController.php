<?php

namespace App\Http\Controllers;

use App\Models\Livre;
use App\Support\Parametres;

/**
 * Pages institutionnelles et d'aide, accessibles sans authentification.
 */
class PageController extends Controller
{
    /** Page d'accueil publique : présentation et parcours de découverte. */
    public function accueil()
    {
        return view('welcome', [
            'statistiques' => [
                'ouvrages' => Livre::count(),
                'disponibles' => Livre::disponibles()->count(),
                'numeriques' => Livre::where('disponible_numerique', true)->count(),
            ],
            'nomBibliotheque' => Parametres::chaine('general.nom_bibliotheque', 'Bibliothèque Universitaire'),
        ]);
    }

    public function aide()
    {
        return view('pages.aide', [
            'email' => Parametres::chaine('general.email_contact', ''),
            'telephone' => Parametres::chaine('general.telephone', ''),
        ]);
    }

    public function faq()
    {
        return view('pages.faq', ['questions' => $this->questionsFrequentes()]);
    }

    public function guide()
    {
        return view('pages.guide');
    }

    public function conditions()
    {
        return view('pages.conditions', [
            'nomBibliotheque' => Parametres::chaine('general.nom_bibliotheque', 'Bibliothèque Universitaire'),
        ]);
    }

    /** @return array<int, array{question: string, reponse: string}> */
    private function questionsFrequentes(): array
    {
        return [
            [
                'question' => 'Comment emprunter un ouvrage ?',
                'reponse' => 'Repérez l\'ouvrage dans le catalogue, vérifiez sa disponibilité, puis présentez-vous '
                    .'au guichet avec votre carte d\'étudiant. Le bibliothécaire scanne le code-barres de l\'exemplaire '
                    .'et enregistre l\'emprunt : vous le retrouvez immédiatement dans « Mes emprunts ».',
            ],
            [
                'question' => 'Combien de temps dure un prêt ?',
                'reponse' => 'La durée est de '.Parametres::entier('emprunt.duree_etudiant', 14).' jours pour les étudiants et de '
                    .Parametres::entier('emprunt.duree_enseignant', 30).' jours pour les enseignants. '
                    .'Ces durées sont paramétrables par la bibliothèque.',
            ],
            [
                'question' => 'Combien d\'ouvrages puis-je emprunter en même temps ?',
                'reponse' => 'Un étudiant peut détenir '.Parametres::entier('emprunt.max_etudiant', 3).' ouvrage(s) simultanément, '
                    .'un enseignant '.Parametres::entier('emprunt.max_enseignant', 10).'.',
            ],
            [
                'question' => 'Puis-je prolonger un emprunt ?',
                'reponse' => 'Oui, tant que l\'ouvrage n\'est pas en retard, qu\'aucune réservation ne porte dessus et que '
                    .'le nombre maximal de renouvellements n\'est pas atteint. Le bouton « Renouveler » apparaît '
                    .'directement sur la fiche de votre emprunt.',
            ],
            [
                'question' => 'Comment réserver un ouvrage indisponible ?',
                'reponse' => 'Depuis la fiche de l\'ouvrage, cliquez sur « Réserver ». Vous entrez dans une file d\'attente : '
                    .'dès qu\'un exemplaire revient, le premier de la file reçoit une notification et dispose de '
                    .Parametres::entier('reservation.delai_retrait', 2).' jour(s) pour venir le retirer.',
            ],
            [
                'question' => 'Que se passe-t-il en cas de retard ?',
                'reponse' => 'Une pénalité de '.Parametres::formaterMontant(Parametres::decimal('penalite.montant_par_jour', 100))
                    .' par jour de retard est appliquée. Au-delà de '
                    .Parametres::formaterMontant(Parametres::decimal('penalite.seuil_blocage', 1000))
                    .' de dette, les nouveaux emprunts sont bloqués jusqu\'à régularisation.',
            ],
            [
                'question' => 'Comment accéder aux documents numériques ?',
                'reponse' => 'Les ouvrages disposant d\'une version numérique affichent un bouton « Lire en ligne ». '
                    .'L\'accès dépend de vos droits : les fichiers sont stockés dans un espace privé et ne sont jamais '
                    .'accessibles par une URL devinée.',
            ],
            [
                'question' => 'J\'ai oublié mon mot de passe, que faire ?',
                'reponse' => 'Utilisez le lien « Mot de passe oublié » sur la page de connexion : un lien de réinitialisation '
                    .'vous est envoyé par email. Vous pouvez aussi demander une réinitialisation au guichet.',
            ],
        ];
    }
}
