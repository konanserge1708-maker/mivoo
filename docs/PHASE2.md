# Mivoo — Phase 2 (Abonnements VIP + Jeko)

## Décisions validées

- Prestataire : **Jeko** (`https://api.jeko.africa`)
- Prix VIP initiaux : ceux du cahier (modifiables en admin)
- Minimum de retrait : **1 000 F CFA** (non configurable en dessous)
- Activation VIP : uniquement après confirmation paiement (webhook / simulation sandbox)

## Paiements

| Mode | Driver | Usage |
|---|---|---|
| Simulation | `PAYMENT_DRIVER=sandbox` | Dev local — page « Simuler succès/échec » |
| Production | `PAYMENT_DRIVER=jeko` | Clés `JEKO_*` dans `.env` |

Jeko n’offre pas de sandbox officiel. Le driver `sandbox` est clairement séparé et ne se présente pas comme un paiement réel.

Webhook : `POST /webhooks/jeko` (signature HMAC `Jeko-Signature`, CSRF exempté).

## Admin

- `/admin/subscription-plans` — édition prix, plafonds, durée, activation
- Historique des modifications (`subscription_plan_audits`)
- `/admin/settings` — min retrait ≥ 1000, frais, tâches/jour, parrainage %

## Worker

- Comparaison des VIP
- Checkout + choix Wave / Orange / MTN / Moov / Djamo
- Historique des commandes
- Expiration auto (scheduler horaire `mivoo:expire-subscriptions`)

## Variables `.env`

```env
PAYMENT_DRIVER=sandbox
JEKO_BASE_URL=https://api.jeko.africa
JEKO_API_KEY=
JEKO_API_KEY_ID=
JEKO_STORE_ID=
JEKO_WEBHOOK_SECRET=
```
