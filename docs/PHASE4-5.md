# Mivoo — Phases 3 partielle + 4 + 5

## Décision produit

Les **campagnes sont créées et financées par l’admin**. L’espace partenaire (création autonome) est reporté.

## Livré

### Campagnes & tâches (admin-driven)
- CRUD campagnes admin + financement / activation / suspension
- Attribution worker (max 10/jour, VIP, budget, anti-doublon)
- Soumission de preuve, validation / rejet / contestation
- Expiration automatique des missions

### Finances (Phase 4)
- Portefeuille + mouvements
- Ledger double entrée
- Gains après validation (plafond journalier VIP)
- Parrainage 10 %
- Retraits (min 1 000 F), approbation admin, sandbox auto-complete

### Administration (Phase 5)
- Users (recherche, suspension)
- Validations missions
- Retraits
- Support tickets
- Dashboard consolidé
- Plans VIP + paramètres (déjà Phase 2)

## Parcours de test rapide

1. Admin : `/admin/campaigns` (campagne démo déjà seedée)
2. Worker : Missions → **Prendre une mission** → démarrer → soumettre
3. Admin : Validations → Approuver
4. Worker : Portefeuille crédité → demander retrait (≥ 1000 F)
5. Admin : Retraits → Approuver

## Non livré (plus tard)
- Espace partenaire autonome
- Pay-out Jeko live (retraits)
- ApexCharts / simulateur rentabilité
- Antifraude avancé, 2FA, reset password
- PostgreSQL prod / Docker
