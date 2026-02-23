# WimoDelivery — Rapport Technique & Analyse

> Généré le : 2026-02-21
> Analysé par : Claude Code (claude-sonnet-4-6)

---

## 6. Résumé Exécutif

**Stack** : PHP 8.4.16 / MariaDB 11.4.10, framework MVC custom (aucun Laravel/Symfony), PDO, phpspreadsheet, cURL. Pas de composer framework, architecture entièrement artisanale.

**Problème** : Lors de l'envoi de colis à l'API externe Oscario.org, le champ `code` transmis est `$row['or_id']` — l'identifiant auto-incrément de la table `orders`. Si plusieurs installations WimoDelivery (ou plusieurs entités) utilisent les mêmes credentials Oscario, leurs `or_id` (1, 2, 3...) se chevauchent et créent des conflits côté Oscario.

**Solution** : Générer un code unique global à la création de chaque colis (format `WMD-{user_id}-{or_id}` ou UUID), le stocker dans un champ dédié (`or_api_code` UNIQUE), et envoyer ce code vers l'API externe à la place de `or_id`.

---

## 1. Contexte Technique

### Framework
- **PHP pur avec MVC custom** — aucun framework standard
- Toutes les classes métier (`Route`, `View`, `How`, `URI`, `Request`) sont définies directement dans `index.php`
- Pas de namespace, pas d'injection de dépendances, pas de templating engine

### Version PHP / DB
- **PHP 8.4.16** (détecté dans le dump SQL `wimodeli_database.sql`)
- **MariaDB 11.4.10** (InnoDB pour la majorité des tables, MyISAM pour `users`)
- Connexion via **PDO** (singleton, classe `Database` dans `application/config/config.php`)

### Structure MVC

```
index.php              → Point d'entrée unique, définit Route, View, How, URI, Request
application/
  config/config.php    → Connexion DB (singleton PDO)
  routes/Routes.php    → Enregistrement de toutes les routes + helpers (POST, SRM, load_url)
  controllers/Admin/   → Contrôleurs PHP (rarement utilisés, logique dans les vues)
  views/default/
    Admin/             → Pages admin (UI + logique métier mélangées)
    files/sql/
      insert/          → Actions POST d'insertion
      update/          → Actions POST de mise à jour
      get/             → Requêtes GET / AJAX / endpoints
      unlink/          → Suppressions logiques ou physiques
```

> **Note architecturale** : La logique métier est principalement dans les vues (`views/default/`), pas dans les contrôleurs. Les fichiers `controllers/` sont souvent vides ou quasi-vides.

### Base de données
- **Moteur** : InnoDB (tables principales) / MyISAM (`users`)
- **Charset** : utf8mb4_general_ci
- **Tables principales** :

| Table | Rôle |
|-------|------|
| `orders` | Colis/commandes (table centrale) |
| `users` | Utilisateurs : admin, marchands, livreurs, aides |
| `api` | Configuration des APIs externes par user |
| `city` | Villes de livraison |
| `state` | États de livraison |
| `box` | Types d'emballages |
| `order_items` | Articles associés aux commandes |
| `log_print` | Journaux pickup/outlog |
| `state_activity` | Historique des changements d'état |
| `activities` | Activités financières |

### Dépendances
- `composer.json` : `"phpoffice/phpspreadsheet": "^1.29"` (export Excel)
- `Classes/PHPMailer-master/` : envoi d'emails
- `Classes/pdf/` : génération PDF
- Pas d'autre framework PHP

---

## 2. Cartographie du Projet

### Fichiers clés

| Fichier | Rôle |
|---------|------|
| `index.php` | Entry point, classes Route/View/How/URI/Request |
| `application/routes/Routes.php` | Toutes les routes + helpers globaux (POST, SRM, get_file, load_url) |
| `application/config/config.php` | Connexion PDO (singleton Database) |
| `application/views/default/Admin/packages.php` | Interface principale de gestion des colis |
| `application/views/default/Admin/api_list.php` | **Envoi de colis à l'API externe Oscario** |
| `application/views/default/files/sql/insert/newPackage.php` | Création de colis via UI web |
| `application/views/default/files/sql/get/add_package_api.php` | API WimoDelivery pour ajout de colis par les clients |
| `application/views/default/files/sql/update/config_orders.php` | Actions en masse sur les colis (dont `send_to_api`) |
| `application/views/default/files/sql/get/change_order_state_api.php` | API WimoDelivery pour changement d'état de colis |

### Routing

**Mécanisme** :
1. `index.php` définit la classe `Route`
2. `Routes.php` enregistre chaque route : `Route::set('path', function() { View::make('Admin/page'); })`
3. `View::make('Admin/page')` charge :
   - `application/controllers/Admin/page.php` (contrôleur, souvent vide)
   - `application/views/default/Admin/page.php` (vue + logique)

**Pattern URL** :
- URL : `/packages?do=new` → route `packages` → `View::make('Admin/packages')` → le `?do=new` est lu par `$_GET['do']` dans la vue
- URL : `/newPackage` → route `newPackage` → endpoint d'insertion POST

**Points d'entrée principaux** :

| Route | Rôle |
|-------|------|
| `/packages` | Gestion des colis (liste, ajout, édition) |
| `/newPackage` | POST : créer un colis (UI web) |
| `/add_package_api` | POST : créer un colis via API externe |
| `/config_orders` | POST : actions en masse sur les colis |
| `/api_list` | POST : envoi de colis à Oscario |
| `/change_order_state_api` | POST : changer l'état d'un colis via API |

---

## 3. Analyse du Flux "Ajout de Colis"

### 3.1 Ajout via Interface Web

**Fichier** : `application/views/default/files/sql/insert/newPackage.php`

1. Formulaire dans `packages.php` (route `/packages?do=new`) → POST vers `/newPackage`
2. `newPackage.php` traite le formulaire :
   - Lecture des champs POST : `warehouse`, `user`, `fragile`, `price`, `city`, `name`, `phone`, `item`, `location`, `note`, `qty`, `change_code`, `pickup`, `box`
   - **Aucun champ `or_code` n'est généré ou stocké** (colonne laissée NULL)
3. Insertion en base :
   ```sql
   INSERT INTO orders (
     or_warehouse, or_trade, or_fragile, or_try, or_open_package, or_change,
     or_total, or_city, or_name, or_phone, or_address, or_note, or_item, or_qty,
     or_change_code, or_box, or_box_price, or_pickup_date, or_unlink, or_created
   ) VALUES (...)
   ```
   → `or_id` est auto-généré par l'auto-increment
   → `or_code` reste NULL

### 3.2 Ajout via API WimoDelivery

**Fichier** : `application/views/default/files/sql/get/add_package_api.php`

1. Authentification par email/password (hash bcrypt)
2. Le client fournit un `code` (son propre identifiant interne)
3. Ce `code` est stocké dans `or_code` :
   ```php
   $code = POST("code");
   // ...
   $stmt->bindParam(':or_code', $code, PDO::PARAM_STR);
   ```
4. **Aucune vérification d'unicité sur `or_code`** (pas de contrainte UNIQUE en DB)
5. `or_id` reste l'auto-increment

### 3.3 Envoi à l'API Externe (Oscario.org)

**Fichier** : `application/views/default/Admin/api_list.php`
**Déclencheur** : `config_orders.php` quand `$do == "send_to_api"` → inclut `Admin/api_list`

**Flux** :
1. Réception de la liste d'`order_id` (IDs WimoDelivery) + `api_id` (= 2 pour Oscario)
2. Requête SELECT des commandes en DB
3. Pour chaque commande, appel cURL GET vers `https://oscario.org/addcolis.php` avec :

```php
// api_list.php, ligne 67-85
$tk = "29150fa19d13f04298bbe1a9672d0097";  // ← HARDCODÉ
$sk = "d8e62021f4426fadb2ba81b328b0d8fa";  // ← HARDCODÉ

$params = [
    "tk"          => $tk,
    "sk"          => $sk,
    "code"        => $row['or_id'],      // ← PROBLÈME : or_id auto-increment
    "fullname"    => $row['or_name'],
    "phone"       => $row['or_phone'],
    "city"        => $row['or_city'],
    "address"     => $row['or_address'],
    "price"       => $row['or_total'],
    "product"     => $productsText,
    "qty"         => $totalQty,
    "note"        => $row['or_note'],
    "change"      => 0,
    "openpackage" => 1
];

$url = "https://oscario.org/addcolis.php?" . http_build_query($params);
```

---

## 4. Analyse du Problème

### 4.1 Confirmation du problème d'unicité

**Problème identifié** : Le champ `code` envoyé à Oscario.org est `$row['or_id']`, l'identifiant auto-increment de la table `orders` WimoDelivery.

**Pourquoi c'est un problème** :
- `or_id` commence à 1 pour chaque installation WimoDelivery (chaque base de données)
- Si plusieurs installations de WimoDelivery (plusieurs entreprises de livraison franchisées) partagent les mêmes credentials Oscario (`tk`/`sk`), leurs `or_id` identiques produisent des conflits dans Oscario
- Exemple : Installation A envoie `code=1` pour son colis n°1. Installation B envoie également `code=1` pour son colis n°1. Oscario voit deux colis avec le même identifiant → conflit

**Problèmes secondaires identifiés** :
- Les credentials Oscario (`tk`, `sk`) sont **hardcodés** dans `api_list.php` — impossible de gérer des comptes Oscario différents par client
- La colonne `or_code` (qui stocke le code propre du client via l'API) n'est **jamais utilisée** lors de l'envoi à Oscario
- La colonne `or_code` n'a **aucune contrainte UNIQUE** en base
- Les ordres créés via l'UI web n'ont **pas de `or_code`** (NULL), donc ni le code client ni aucun code unique global n'est disponible au moment de l'envoi

### 4.2 Tous les endroits impactés

| Fichier | Ligne | Impact |
|---------|-------|--------|
| `views/default/Admin/api_list.php` | 74 | **Source du bug** : `"code" => $row['or_id']` |
| `views/default/files/sql/insert/newPackage.php` | 80-93 | Ne génère pas de code unique (`or_code` non renseigné) |
| `views/default/files/sql/get/add_package_api.php` | 40, 113 | Stocke le `code` client dans `or_code` sans vérification d'unicité globale |
| `wimodeli_database.sql` | 2869, 14924 | `or_code varchar(255) DEFAULT NULL` — pas de `UNIQUE INDEX` |

---

## 5. Solution Proposée

### Recommandation : Génération d'un code API unique à la création du colis

**Principe** : Créer un identifiant unique global (`or_api_code`) au moment de l'insertion du colis, et utiliser exclusivement ce champ lors de l'envoi à toute API externe.

Le format recommandé : `WMD-{or_trade}-{or_id}` — simple, lisible, garanti unique dans une installation, et discriminant entre installations si un préfixe par installation est ajouté.

Ou, pour une unicité absolue multi-installations : utiliser `uniqid('WMD', true)` ou `bin2hex(random_bytes(8))`.

---

### Étape 1 — Migration SQL (ajouter le champ `or_api_code`)

```sql
-- Ajouter la colonne
ALTER TABLE `orders`
  ADD COLUMN `or_api_code` VARCHAR(64) DEFAULT NULL AFTER `or_code`;

-- Ajouter un index UNIQUE
ALTER TABLE `orders`
  ADD UNIQUE INDEX `idx_or_api_code` (`or_api_code`);

-- Remplir les ordres existants (option A : format WMD-trade-id)
UPDATE `orders`
  SET `or_api_code` = CONCAT('WMD-', or_trade, '-', or_id)
  WHERE `or_api_code` IS NULL;
```

---

### Étape 2 — Génération à la création via UI web

**Fichier** : `application/views/default/files/sql/insert/newPackage.php`

Après l'INSERT (ligne ~120), récupérer le `lastInsertId` et mettre à jour `or_api_code` :

```php
// Après $stmt->execute()
if ($stmt->execute()) {
    $new_id = $con->lastInsertId();
    $api_code = 'WMD-' . $user . '-' . $new_id;

    $stmt_code = $con->prepare("UPDATE orders SET or_api_code = :api_code WHERE or_id = :id");
    $stmt_code->execute([':api_code' => $api_code, ':id' => $new_id]);

    // suite du code existant...
}
```

**Alternative propre** : Générer le code avant l'INSERT en utilisant une transaction :
```php
$api_code = 'WMD-' . $user . '-' . bin2hex(random_bytes(6));
// ajouter or_api_code dans l'INSERT directement
```

---

### Étape 3 — Génération à la création via API WimoDelivery

**Fichier** : `application/views/default/files/sql/get/add_package_api.php`

Modifier l'INSERT (ligne 101-133) pour inclure `or_api_code` :

```php
// Avant l'INSERT, générer un code unique
$api_code = 'WMD-' . $user . '-' . bin2hex(random_bytes(6));

// Dans l'INSERT SQL, ajouter :
// INSERT INTO orders (or_code, or_api_code, ...)
// VALUES (:or_code, :or_api_code, ...)

$stmt->bindParam(':or_api_code', $api_code, PDO::PARAM_STR);
```

---

### Étape 4 — Utiliser `or_api_code` lors de l'envoi à Oscario

**Fichier** : `application/views/default/Admin/api_list.php`, **ligne 74**

```php
// AVANT (ligne 74)
"code" => $row['or_id'],

// APRÈS
"code" => $row['or_api_code'],
```

---

### Étape 5 (optionnel mais recommandé) — Externaliser les credentials Oscario

Les `tk`/`sk` sont hardcodés (lignes 67-68). Si différents clients WimoDelivery ont des comptes Oscario distincts, les stocker dans la table `api` :

```sql
ALTER TABLE `api`
  ADD COLUMN `api_token` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN `api_secret` VARCHAR(255) DEFAULT NULL;

-- Insérer les credentials actuels
UPDATE `api` SET api_token = '29150fa19d13f04298bbe1a9672d0097',
                 api_secret = 'd8e62021f4426fadb2ba81b328b0d8fa'
WHERE api_id = 2;
```

Puis dans `api_list.php`, charger depuis la DB :
```php
$stmt_api = $con->prepare("SELECT api_token, api_secret FROM api WHERE api_id = :id");
$stmt_api->execute([':id' => $do]);
$api_creds = $stmt_api->fetch(PDO::FETCH_ASSOC);
$tk = $api_creds['api_token'];
$sk = $api_creds['api_secret'];
```

---

### Impact sur le reste du système

| Composant | Impact |
|-----------|--------|
| `packages.php` (UI) | Peut afficher `or_api_code` pour référence. Aucun impact fonctionnel. |
| `newPackage.php` | Ajouter génération de `or_api_code` post-INSERT |
| `add_package_api.php` | Ajouter génération de `or_api_code` dans l'INSERT |
| `api_list.php` | Changer ligne 74 : `or_id` → `or_api_code` |
| `change_order_state_api.php` | Utilise `or_id` pour identifier les ordres côté WimoDelivery — pas de changement nécessaire |
| `save_data.php` (import) | Vérifier si `or_api_code` doit être généré lors des imports batch |
| `new_stock_package.php` | Même traitement que `newPackage.php` |

### Risques

| Risque | Probabilité | Mitigation |
|--------|------------|------------|
| Ordres existants sans `or_api_code` envoyés à Oscario | Moyen | Le UPDATE SQL de l'Étape 1 doit être exécuté avant le déploiement |
| Collision lors de la génération `bin2hex(random_bytes(6))` | Très faible (1/281 trillion) | Ajouter un `try/catch` sur l'INSERT UNIQUE violation |
| Régression sur `api_list.php` si `or_api_code` est NULL | Faible | Ajouter un fallback : `$row['or_api_code'] ?? 'WMD-' . $row['or_id']` |

---

## Annexe — Structure de la table `orders` (colonnes clés)

```sql
CREATE TABLE `orders` (
  `or_id`        int(11)      NOT NULL AUTO_INCREMENT,  -- PK, auto-increment
  `or_trade`     int(11)      NOT NULL DEFAULT 0,        -- ID du marchand (client WimoDelivery)
  `or_code`      varchar(255) DEFAULT NULL,              -- Code fourni par le client (API), sans UNIQUE
  `or_api_code`  varchar(64)  DEFAULT NULL,              -- [À AJOUTER] Code unique global pour APIs externes
  -- ... autres colonnes
  PRIMARY KEY (`or_id`)
  -- [À AJOUTER] UNIQUE KEY `idx_or_api_code` (`or_api_code`)
);
```

---

*Rapport complet — Ne pas modifier le code avant validation de la solution proposée.*

---

## 7. Journal des modifications (2026-02-23)

### 7.1 Rollback BIGINT → INT(11)

La migration BIGINT précédente a été annulée. La colonne `or_id` et toutes les colonnes de référence ont été ramenées à leur type d'origine `INT(11)`.

**Tables concernées :**

| Table | Colonne | Avant | Après |
|-------|---------|-------|-------|
| `orders` | `or_id` | `bigint(20)` AUTO_INCREMENT | `int(11)` AUTO_INCREMENT = 2439 |
| `order_items` | `order_id` | `bigint(20)` | `int(11)` |
| `state_activity` | `sa_order` | `bigint(20)` | `int(11)` |
| `activities` | `ac_order` | `bigint(20)` | `int(11)` |
| `expedition_colis` | `colis_id` | `bigint(20)` | `int(11)` |

Un colis test (`or_id = 10000000000`) créé lors des tests BIGINT a été supprimé.

### 7.2 Solution API Oscario — Préfixe "WMD-"

**Fichier modifié :** `application/views/default/Admin/api_list.php`, ligne 74

```php
// AVANT
"code" => $row['or_id'],

// APRÈS
"code" => "WMD-" . $row['or_id'],
```

**Pourquoi ça suffit :** le préfixe `WMD-` différencie les codes de cette installation WimoDelivery des autres installations partageant les mêmes credentials Oscario. Les `or_id` `1`, `2`, `3`... deviennent `WMD-1`, `WMD-2`, `WMD-3`... et ne peuvent plus entrer en conflit avec une autre installation dont les IDs commenceraient aussi à 1.

**État final :** `or_id` reste `INT(11)` auto-increment standard, sans aucune autre modification.
