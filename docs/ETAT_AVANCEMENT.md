# État d'avancement — WimoDelivery
Dernière mise à jour : 27-02-2026

## ✅ Terminé

### or_code — Identifiant unique colis
- Colonne existante or_code varchar(255) réutilisée
- Format : WMD-{or_id} (ex: WMD-2443)
- Généré automatiquement après INSERT dans :
  - newPackage.php (UI web)
  - add_package_api.php (API externe)
  - new_stock_package.php (stock)
- Index UNIQUE ajouté en DB
- Fallback : or_code ?? 'WMD-' . or_id dans api_list.php
- SQL prod exécuté : UPDATE + ALTER INDEX

### Intégration Chamel Express — Envoi colis
- $do == 3 dans api_list.php
- 1 colis → POST store-commande (endpoint simple)
- 2+ colis → POST store-commandes-bulk (max 100)
- Authentification : Header Special-Token
- Mapping ville : JOIN city + strtoupper() (noms en minuscules/mixte en DB)
- code_suivi : or_code ?? WMD-{or_id}
- Log dédié : logs/api_chamel.log
- Retour API affiché dans la modale (succès, erreurs 422, 500)
- Testé prod : HTTP 200 ✓ — référence Chamel retournée (ex: CAS260226P22QRJ)
- Erreur 500 bulk : bug côté Chamel Express, signalé à leur support

### Webhook Chamel Express — Réception statuts
- Endpoint : /webhooks/chamelexpress.php
- URL prod : https://wimodelivery.com/webhooks/chamelexpress.php
- Authentification : Special-Token hardcodé dans le fichier
- Token : WIMO_WH_7a3f8k2m9x4q1n6p0r5s8t3w2v9z4b1c
- Mapping statuts :
  LIVRÉ/LIVRE   → or_state = 1
  ANNULÉ/ANNULE → or_state = 2
  REFUSÉ/REFUSE → or_state = 3
  EN COURS      → or_state = 51
  NOUVELLE      → or_state = 52
  Inconnu       → WARNING loggé, pas de UPDATE
- UPDATE orders + INSERT state_activity
- Log dédié : logs/webhook_chamel.log
- Simulateur : /webhooks/test_webhook.php
- Testé prod : HTTP 200 ✓
- À faire : communiquer URL + token au support Chamel Express

### Infrastructure logs
- logs/api_oscario.log  → envois Oscario ($do==2)
- logs/api_chamel.log   → envois Chamel ($do==3)
- logs/webhook_chamel.log → webhooks reçus Chamel
- Encodage UTF-8 : JSON_UNESCAPED_UNICODE partout

### .htaccess racine
- Exception ajoutée : RewriteCond %{REQUEST_URI} !^/webhooks/
- Permet accès direct aux fichiers PHP du dossier webhooks

---

## 🔜 Prochains prestataires (2 et 3)

### Pattern à suivre (identique pour chaque nouveau prestataire)

**api_list.php :**
- Ajouter bloc elseif ($do == 4) pour prestataire 2
- Ajouter bloc elseif ($do == 5) pour prestataire 3
- Vérifier méthode auth (Special-Token ? Bearer ? GET params ?)
- Vérifier format body (JSON POST ? GET query string comme Oscario ?)
- JOIN city pour nom ville si nécessaire (strtoupper si besoin)
- Log dédié : logs/api_{nom}.log

**packages.php :**
- Ajouter <option value="4">nom_prestataire2</option>
- Ajouter <option value="5">nom_prestataire3</option>

**Webhook (si supporté) :**
- Créer webhooks/{nom_prestataire}.php
- Même structure que chamelexpress.php
- Vérifier format payload reçu (champs peuvent différer)
- Vérifier méthode auth (header ? query param ?)
- Log dédié : logs/webhook_{nom}.log
- Ajouter option dans le dropdown si test local nécessaire

**DB :**
- INSERT INTO api (api_id, api_name, api_rank, api_user)
  VALUES ({id}, '{nom}', 'user', 0);

**Questions à poser au prestataire avant développement :**
1. Documentation API complète (endpoint, auth, format body)
2. Token d'authentification
3. Format du code_suivi accepté
4. Noms de villes exacts acceptés (liste ou format libre ?)
5. Webhook supporté ? Format payload ? Comment passer le secret ?
6. Environnement de test disponible ?

---

## 📋 Référence technique rapide

### Structure api_list.php
$do == 1 → API dummy (non implémentée)
$do == 2 → Oscario (GET query string, tk/sk hardcodés)
$do == 3 → Chamel Express (POST JSON, Special-Token header)
$do == 4 → [Prochain prestataire 2]
$do == 5 → [Prochain prestataire 3]

### Table api en DB
api_id | api_name      | api_rank | api_user
2      | oscario       | user     | 300704
3      | chamelexpress | user     | 0

### Statuts orders (or_state)
1  → Livré
2  → Annulé
3  → Refusé
51 → En cours de livraison
52 → Ramassé

### Villes
Table : city (city_id, city_name)
Noms en mixte (ex: "Casablanca") → strtoupper() appliqué dans api_list.php
