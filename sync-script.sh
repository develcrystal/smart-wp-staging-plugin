#!/bin/sh
# Sync Live → Staging
set -e

# 1. Dateien kopieren (rsync)
rsync -av --delete /live/ /staging/

# 2. Datenbank exportieren & importieren
mysqldump -h db_live -u "$WORDPRESS_DB_USER" -p"$DB_PASS" wp_live > /tmp/dump.sql
mysql -h db_staging -u "$WORDPRESS_DB_USER" -p"$DB_PASS" wp_staging < /tmp/dump.sql

# 3. wp-config anpassen (Staging-Modus)
sed -i "s/'WP_DEBUG', false/'WP_DEBUG', true/g" /staging/wp-config.php

# 4. Git-Commit (optional)
cd /staging && git add . && git commit -m "Auto-Sync $(date '+%Y-%m-%d %H:%M')" || true
