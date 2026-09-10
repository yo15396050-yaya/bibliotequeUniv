<?php

namespace Database\Factories;

use App\Models\Livre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Livre>
 */
class LivreFactory extends Factory
{
    protected $model = Livre::class;

    private const CATEGORIES = [
        'Informatique', 'Mathématiques', 'Droit', 'Économie', 'Gestion',
        'Médecine', 'Sciences', 'Lettres', 'Philosophie', 'Ingénierie',
    ];

    private const PREFIXES = [
        'Introduction à', 'Précis de', 'Manuel de', 'Fondements de', 'Traité de',
        'Pratique de', 'Initiation à', 'Approche moderne de', 'Cours de', 'Méthodes de',
    ];

    private const SUJETS = [
        'l\'algorithmique', 'la programmation orientée objet', 'l\'analyse numérique',
        'la statistique appliquée', 'le droit des affaires', 'la comptabilité générale',
        'la microéconomie', 'l\'anatomie humaine', 'la physique quantique',
        'la littérature comparée', 'la philosophie politique', 'la résistance des matériaux',
        'les bases de données', 'les réseaux informatiques', 'la gestion de projet',
        'l\'intelligence artificielle', 'la cryptographie', 'le génie logiciel',
    ];

    public function definition(): array
    {
        $exemplaires = fake()->numberBetween(1, 6);
        $categorie = fake()->randomElement(self::CATEGORIES);

        return [
            'isbn' => fake()->unique()->isbn13(),
            'titre' => fake()->randomElement(self::PREFIXES).' '.fake()->randomElement(self::SUJETS),
            'sous_titre' => fake()->boolean(35) ? fake()->sentence(5) : null,
            'auteur' => fake()->name(),
            'editeur' => fake()->randomElement([
                'Presses Universitaires', 'Dunod', 'Eyrolles', 'Hachette Éducation',
                'Éditions du Seuil', 'De Boeck Supérieur', 'Ellipses',
            ]),
            'annee_publication' => fake()->numberBetween(1998, (int) date('Y')),
            'edition' => fake()->randomElement(['1re édition', '2e édition', '3e édition', '4e édition']),
            'categorie' => $categorie,
            'type_document' => fake()->randomElement(array_keys(Livre::TYPES_DOCUMENT)),
            'niveau_academique' => fake()->randomElement(array_keys(Livre::NIVEAUX_ACADEMIQUES)),
            'domaine' => $categorie,
            'langue' => fake()->randomElement(['Français', 'Français', 'Français', 'Anglais']),
            'nombre_pages' => fake()->numberBetween(80, 900),
            'resume' => fake()->paragraph(3),
            'description' => fake()->paragraphs(2, true),
            'mots_cles' => implode(', ', fake()->words(4)),
            'emplacement_rayon' => strtoupper(substr($categorie, 0, 3)).'-'.fake()->numberBetween(1, 20),
            'exemplaires_totaux' => $exemplaires,
            'exemplaires_disponibles' => $exemplaires,
            'statut' => 'disponible',
            'disponible_numerique' => false,
            'autoriser_telechargement' => true,
        ];
    }

    public function indisponible(): static
    {
        return $this->state(fn () => [
            'exemplaires_disponibles' => 0,
            'statut' => 'emprunté',
        ]);
    }
}
