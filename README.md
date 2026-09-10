# BiblioteqUniv — Gestion de bibliothèque universitaire

Application Laravel complète de gestion d'une bibliothèque universitaire :
catalogue, exemplaires physiques, circulation (emprunts / retours /
renouvellements), réservations avec file d'attente, pénalités et paiements,
documents numériques sécurisés, notifications, statistiques, rapports
exportables et journal d'audit.

---

## 1. Architecture du projet

**Stack** — Laravel 12 · PHP 8.2+ · MySQL (phpMyAdmin) · Blade · Bootstrap 5
(compilé par Vite depuis les sources SCSS) · Chart.js · SweetAlert2 ·
dompdf · simple-qrcode.

```
app/
├── Console/Commands/      Tâches planifiées (retards, réservations, compteurs)
├── Exceptions/            RegleMetierException : messages métier lisibles
├── Http/
│   ├── Controllers/       22 contrôleurs, un par module fonctionnel
│   ├── Middleware/        CheckRole, VerifierCompteActif
│   └── Requests/          15 Form Requests (validation + autorisation)
├── Models/                21 modèles Eloquent
├── Notifications/         9 notifications (interne + email)
├── Policies/              7 policies (autorisation par objet)
├── Providers/             Gates, policies, garde-fou anti N+1
├── Services/              Cœur métier (voir ci-dessous)
└── Support/               Parametres, Permissions, Export, CodeBarre

database/
├── factories/             6 factories réalistes
├── migrations/            31 migrations (socle + extensions)
└── seeders/               6 seeders orchestrés par DatabaseSeeder

resources/
├── sass/                  Charte graphique → variables Bootstrap
├── js/                    Bundle unique (Bootstrap, Chart.js, SweetAlert2)
└── views/                 93 vues Blade + composants réutilisables
```

**Couche services** — toute règle métier vit dans un service, jamais dans un
contrôleur ni dans une vue :

| Service | Responsabilité |
|---|---|
| `EmpruntService` | Éligibilité, prêt, retour, renouvellement, traitement des retards |
| `ReservationService` | File d'attente, mise de côté, notification, expiration |
| `PenaliteService` | Calcul, création, paiements (partiels inclus), annulation |
| `DocumentService` | Stockage privé, contrôle des formats, téléchargement tracé |
| `NotificationService` | Canaux actifs, envoi non bloquant |
| `StatistiqueService` | Agrégats des tableaux de bord et des rapports |
| `AuditService` | Journalisation des opérations sensibles |

**Principes appliqués** — MVC strict, Form Requests pour toute entrée
utilisateur, Policies pour toute autorisation, transactions SQL sur chaque
opération de circulation, verrous `lockForUpdate` contre les doubles emprunts,
eager loading systématique, pagination partout, cache sur les paramètres et
les permissions.

---

## 2. Liste des modules

| Module | Contenu |
|---|---|
| **Utilisateurs** | Comptes, profils, rôles, statuts, réinitialisation de mot de passe |
| **Catalogue** | Ouvrages, auteurs, éditeurs, catégories et sous-catégories |
| **Exemplaires** | Copies physiques, codes-barres, états, étiquettes imprimables |
| **Localisations** | Bibliothèque → Salle → Rayon → Étagère |
| **Emprunts** | Prêt guidé, guichet de retour par scan, reçus PDF |
| **Retours** | Contrôle d'état, calcul du retard, remise en circulation |
| **Réservations** | File d'attente, mise de côté, délai de retrait, expiration |
| **Renouvellements** | Demande, validation optionnelle, historique |
| **Pénalités** | Retard, perte, dégradation ; paiements partiels ; reçus |
| **Documents numériques** | Stockage privé, visibilité par profil, liseuse intégrée |
| **Notifications** | Centre interne + email, cloche temps réel |
| **Rapports** | 7 rapports exportables en PDF, Excel et CSV |
| **Administration** | Paramètres métier, matrice RBAC, années académiques |
| **Audit** | Journal horodaté de toutes les opérations sensibles |

---

## 3. Schéma de la base de données

35 tables. Entités principales et relations :

```
users ──┬─< emprunts >── livres ──< exemplaires >── emplacements
        │       │                     │                  │
        │       ├─< renouvellements   │              rayons
        │       └─< penalites >──< paiements_penalites    │
        │                                              salles
        ├─< reservations >── livres                       │
        ├─< notifications                            bibliotheques
        ├─< journaux_activite
        └─< role_user >── roles >── permission_role >── permissions

livres ──< auteur_livre >── auteurs
livres ──> editeurs, categories (parent_id → sous-catégories)
livres ──< documents_numeriques
users  ──> annees_academiques
parametres  (règles métier configurables)
```

**Distinction fondamentale** — `livres` est la **notice bibliographique**
(« Algorithmique avancée ») ; `exemplaires` sont les **copies physiques**
(ALG-0001, ALG-0002, ALG-0003), chacune avec son code-barres, son état, son
statut et son emplacement. Les compteurs `exemplaires_totaux` /
`exemplaires_disponibles` sont dénormalisés et resynchronisés par
`Livre::synchroniserCompteurs()`.

**Index** — ISBN, code-barres, numéro d'inventaire, matricule, email, titre,
statuts, dates d'échéance, plus les index composés `(user_id, statut)` et
`(livre_id, statut)` pour les écrans de circulation.

**Intégrité** — clés étrangères sur toutes les relations, `cascadeOnDelete`
sur les dépendances fortes, `nullOnDelete` sur les références facultatives.

---

## 4. Rôles et permissions

RBAC à deux niveaux : le rôle principal (`users.role`) plus des rôles
additionnels attribuables. 30 permissions réparties en 7 modules, éditables
depuis **Paramètres → Rôles**.

| Rôle | Portée |
|---|---|
| **Administrateur** | Accès complet, y compris paramètres, rôles et journal d'audit |
| **Bibliothécaire** | Catalogue, exemplaires, usagers, circulation, pénalités, rapports. Ne peut ni modifier les paramètres système, ni gérer les permissions, ni supprimer un ouvrage |
| **Enseignant** | Consultation, emprunts et réservations avec quotas et durées étendus |
| **Étudiant** | Consultation, emprunts, réservations, suivi personnel |

Dans les vues : `@can('livres.creer')`. Dans les contrôleurs :
`$this->authorize('update', $livre)`. Un usager n'accède jamais aux données
d'un autre en modifiant un identifiant dans l'URL — c'est explicitement testé.

---

## 5. Fonctionnalités développées

- **Authentification** : connexion, déconnexion, mot de passe oublié,
  changement de mot de passe, vérification d'email, gestion du profil,
  activation/désactivation immédiate d'un compte.
- **Catalogue** : 18 champs bibliographiques, 9 types de documents,
  auteurs multiples, éditeurs, catégories hiérarchiques, couverture, QR code.
- **Exemplaires** : création unitaire ou en lot, codes-barres générés,
  7 statuts, 4 états, étiquettes imprimables (code-barres Code 128 + QR code
  générés sans dépendance externe).
- **Recherche** : une seule barre interroge titre, sous-titre, auteur, ISBN,
  éditeur, mots-clés, domaine et code-barres ; filtres par catégorie, langue,
  année, type, niveau, emplacement, disponibilité et version numérique ;
  suggestions instantanées dans la barre de navigation.
- **Emprunts** : sélection guidée usager → ouvrage → exemplaire (ou scan),
  contrôles automatiques (compte actif, quota, retards, pénalités, réservation
  prioritaire, exemplaire disponible), échéance calculée, reçu PDF.
- **Retours** : guichet dédié — scan, recherche, validation, résultat ;
  contrôle d'état, calcul du retard, pénalité automatique, remise en
  circulation et notification du premier réservataire.
- **Réservations** : file d'attente ordonnée, mise de côté d'un exemplaire à
  la restitution, délai de retrait, expiration automatique et passage au
  suivant, renumérotation de la file.
- **Renouvellements** : conditions configurables, validation par un
  bibliothécaire activable, historique complet.
- **Pénalités** : retard (par jour, avec jours de grâce et plafond), perte,
  dégradation ; paiements partiels multi-modes ; reçu PDF ; annulation motivée ;
  seuil de dette bloquant les emprunts.
- **Documents numériques** : disque privé, visibilité par profil, contrôle
  d'accès par policy, liseuse intégrée, téléchargements comptabilisés.
- **Notifications** : 8 événements, canaux interne et email, cloche avec
  compteur, centre de notifications.
- **Tableaux de bord** : indicateurs et graphiques pour le personnel ;
  emprunts actifs, échéances proches, retards, réservations, pénalités et
  recommandations personnalisées pour l'usager.
- **Rapports** : emprunts, retards, pénalités, catalogue, usagers, ouvrages
  perdus et endommagés — consultables à l'écran et exportables en PDF, Excel
  et CSV.
- **Audit** : chaque création, modification, suppression, emprunt, retour,
  réservation, paiement et changement de permission est tracé avec
  utilisateur, date, adresse IP et données de l'opération.

---

## 6. Comptes de démonstration

| Profil | Email | Mot de passe |
|---|---|---|
| Administrateur | `admin@bibliotheque.univ` | `password` |
| Bibliothécaire | `biblio@bibliotheque.univ` | `password` |
| Enseignant | `enseignant@bibliotheque.univ` | `password` |
| Étudiant | `etudiant@bibliotheque.univ` | `password` |

Jeu de données généré : 119 comptes (1 administrateur, 5 bibliothécaires,
12 enseignants, 100 étudiants), 500 notices, ~1 750 exemplaires, ~370 emprunts
dont des retards actifs, ~85 pénalités (payées, partielles, impayées) et des
files de réservation. Les tableaux de bord sont donc réellement testables.

> Changez ces mots de passe avant toute mise en production.

---

## 7. Procédure d'installation

```bash
git clone <dépôt> && cd bibliotequeUniv

composer install
cp .env.example .env
php artisan key:generate
```

Créez la base dans phpMyAdmin (`bibliotheque_univ`, interclassement
`utf8mb4_unicode_ci`), puis renseignez `.env` :

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bibliotheque_univ
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
```

---

## 8. Commandes Laravel nécessaires

```bash
php artisan key:generate            # clé d'application
php artisan migrate                 # création du schéma
php artisan migrate --seed          # schéma + jeu de démonstration
php artisan storage:link            # lien public des couvertures et photos
php artisan db:seed                 # peuplement seul
php artisan test                    # suite de tests
php artisan queue:work              # traitement des notifications en file
php artisan schedule:work           # planificateur en développement

# Commandes métier de l'application
php artisan bibliotheque:traiter-retards          # retards, pénalités, rappels
php artisan bibliotheque:expirer-reservations     # file d'attente
php artisan bibliotheque:synchroniser-compteurs   # cohérence des exemplaires
```

Planification en production — une seule ligne de cron :

```cron
* * * * * cd /chemin/du/projet && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Procédure de migration

Les migrations sont **additives et idempotentes** : chaque ajout de colonne est
protégé par `Schema::hasColumn`, chaque création de table par
`Schema::hasTable`. Une base existante se met donc à jour sans perte :

```bash
php artisan migrate                 # applique uniquement les nouveautés
php artisan bibliotheque:synchroniser-compteurs
```

Pour repartir d'une base vierge :

```bash
php artisan migrate:fresh --seed
```

Les migrations historiques du projet sont conservées ; les extensions portent
le préfixe `2026_09_10_*`.

---

## 10. Procédure de lancement

```bash
php artisan serve          # http://127.0.0.1:8000
npm run dev                # rechargement à chaud des assets (développement)
```

Ou, en une commande (serveur, file d'attente, logs et Vite) :

```bash
composer dev
```

En production : `npm run build`, puis `php artisan config:cache route:cache
view:cache`, un cron pour le planificateur et un superviseur pour
`php artisan queue:work`.

---

## 11. Tests réalisés

`php artisan test` → **114 tests, 348 assertions, tous verts.**

| Suite | Couverture |
|---|---|
| `AuthentificationTest` | Connexion, identifiants invalides, compte désactivé, pages protégées, inscription fermée, hachage du mot de passe, déconnexion |
| `PermissionsTest` | Matrice RBAC, cloisonnement des données entre usagers, accès interdit par manipulation d'URL |
| `CatalogueTest` | CRUD d'un ouvrage, unicité de l'ISBN, validation, liaison auteurs/éditeurs/catégories, recherche multi-critères, QR code |
| `ExemplaireTest` | Création en lot, unicité du code-barres, suppression bloquée, scan, synchronisation des compteurs, étiquette |
| `CirculationTest` | Emprunt, quota, retard bloquant, pénalité bloquante, compte suspendu, retour, calcul du retard et de la pénalité (plafond, jours de grâce), perte, dégradation, double retour, guichet |
| `ReservationTest` | File d'attente, non-consommation du stock, notification, expiration et passage au suivant, réservation honorée, plafond, annulation |
| `RenouvellementTest` | Conditions, maximum atteint, retard, ouvrage réservé, validation et refus motivé |
| `PenaliteTest` | Montants forfaitaires, paiements partiels et intégral, dépassement refusé, annulation, reçu PDF |
| `DocumentNumeriqueTest` | Stockage privé, formats refusés, accès par visibilité, téléchargement désactivé, compte suspendu, suppression du fichier |
| `AuditTest` | Traçabilité création / emprunt / retour / changement de rôle, absence de mot de passe dans le journal |
| `ParametresTest` | Valeurs par défaut, surcharge, cache, typage, effet immédiat sur les règles métier |
| `SmokeTest` | Toutes les pages GET de l'application répondent sans erreur serveur |
| `CodeBarreTest`, `ExportTest` | Génération Code 128, exports CSV/Excel, échappement |

---

## 12. Améliorations possibles

- **Recherche** : indexation Meilisearch/Scout au-delà de quelques dizaines de
  milliers de notices (les `LIKE` restent efficaces à l'échelle actuelle grâce
  aux index, mais ne montent pas indéfiniment).
- **Authentification** : l'architecture accepte un fournisseur externe (Google,
  SSO universitaire, LDAP/CAS) ; le branchement reste à réaliser.
- **Notifications** : les canaux SMS et WhatsApp sont prévus comme extensions —
  `NotificationService::canaux()` est le seul point à étendre.
- **Scan mobile** : lecture du code-barres par la caméra du navigateur
  (l'interface de saisie et l'API de recherche existent déjà).
- **Import en masse** : chargement du catalogue depuis un fichier CSV/MARC.
- **API** : exposition Sanctum pour une application mobile native.
- **Amendes** : intégration d'un encaissement Mobile Money automatisé.
- **Statistiques** : export planifié des rapports par email à la direction.

---

## Sécurité

- CSRF sur tous les formulaires, échappement Blade contre le XSS, requêtes
  préparées Eloquent contre l'injection SQL.
- Validation côté serveur systématique via Form Requests.
- Autorisation par Policies et Gates ; l'identifiant dans l'URL ne donne
  jamais accès aux données d'autrui.
- Documents numériques sur disque privé, servis uniquement après contrôle.
- Limitation de débit sur les téléchargements et les suggestions de recherche.
- Compte désactivé déconnecté immédiatement par le middleware dédié.
- Mots de passe hachés (bcrypt), jamais journalisés.
- Journal d'audit horodaté avec adresse IP sur toutes les opérations sensibles.
- Aucune trace technique affichée en production (`APP_DEBUG=false`) : les
  erreurs métier remontent en message clair.
