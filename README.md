# Mivoo

Plateforme de micro-tâches rémunérées avec abonnements VIP (Côte d’Ivoire · Franc CFA).

Stack : **Laravel + Blade + Tailwind CSS + JavaScript natif + PostgreSQL (cible)**.

## Phases

- Phase 1 : fondations, auth, design system → `docs/PHASE1.md`
- Phase 2 : VIP + paiements Jeko (sandbox/live) → `docs/PHASE2.md`

Cahier des charges : `cahier.txt`.

## Démarrage local

```bash
composer install
npm install
php artisan migrate --seed
npm run build
php artisan serve
```

Ouvrir [http://127.0.0.1:8000](http://127.0.0.1:8000).

### Comptes démo

- Worker : `worker@mivoo.test` / `password`
- Partenaire : `partner@mivoo.test` / `password`
- Admin : `admin@mivoo.test` / `password`

## Contraintes UI/UX

Interface mobile-first inspirée de la maquette fournie :

- fond lavande / dégradés doux
- cartes très arrondies
- navigation flottante (accueil, missions, +, progrès, profil)
- typographie Plus Jakarta Sans
- composants Blade `x-ui.*` et layouts `x-layouts.*`
