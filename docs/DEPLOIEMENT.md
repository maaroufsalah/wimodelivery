# Déploiement — Intégration Chamel Express
Date : 26-02-2026

## Fichiers à uploader sur le VPS

### Fichiers MODIFIÉS :
- application/views/default/Admin/api_list.php
- application/views/default/Admin/packages.php
- application/views/default/files/sql/insert/newPackage.php
- application/views/default/files/sql/get/add_package_api.php
- application/views/default/files/sql/insert/new_stock_package.php
- .htaccess

### Fichiers CRÉÉS :
- webhooks/chamelexpress.php
- webhooks/test_webhook.php
- webhooks/.htaccess

## SQL à exécuter sur la DB production

```sql
-- 1. Remplir or_code existants
UPDATE orders SET or_code = CONCAT('WMD-', or_id) WHERE or_code IS NULL OR or_code = '';

-- 2. Index unique
ALTER TABLE orders ADD UNIQUE INDEX idx_or_code (or_code);

-- 3. Ajouter Chamel Express dans table api
INSERT INTO api (api_id, api_name, api_rank, api_user)
VALUES (3, 'chamelexpress', 'user', 0);
```

## Token webhook
URL : https://wimodelivery.com/webhooks/chamelexpress.php
Header : Special-Token: WIMO_WH_7a3f8k2m9x4q1n6p0r5s8t3w2v9z4b1c
