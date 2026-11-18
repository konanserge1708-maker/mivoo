# Mivoo — Phase 1 (Fondations)

## Réalisé

- Initialisation Laravel 13 + Tailwind CSS 4 + Blade
- Design system inspiré de la maquette (lavande, cartes arrondies, nav flottante mobile)
- Authentification worker (inscription / connexion) sans Alpine / Livewire / Inertia
- Rôles : worker, partner, admin et rôles admin spécialisés
- Migrations : users, roles, profiles, VIP plans, wallets, settings, audit
- Layouts et composants UI réutilisables
- Tableaux de bord Worker / Partner / Admin
- Seeders (plans VIP, settings, comptes démo)

## Comptes démo

| Rôle | Email | Mot de passe |
|---|---|---|
| Worker | worker@mivoo.test | password |
| Partenaire | partner@mivoo.test | password |
| Admin | admin@mivoo.test | password |

## Lancer en local

```bash
composer install
npm install
cp .env.example .env   # si besoin
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

> PostgreSQL est la cible de production (cahier des charges). En local, SQLite est utilisé par défaut pour démarrer rapidement.

## Suite prévue

- Phase 2 : souscriptions VIP + paiements sandbox
- Phase 3 : campagnes partenaires + attribution de tâches
- Phase 4 : finances, parrainage, retraits
- Phase 5–6 : admin avancée, tests, antifraude, déploiement
