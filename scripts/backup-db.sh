#!/bin/bash
# WellCore Fitness — Database Backup Script
# Usage: bash scripts/backup-db.sh
# Make executable: chmod +x scripts/backup-db.sh
# Recommended: add to cron for scheduled backups
#   0 2 * * * /var/www/html/scripts/backup-db.sh >> /var/log/wellcore-backup.log 2>&1
set -e

# Load environment
source .env

BACKUP_DIR="storage/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
FILENAME="wellcore_${TIMESTAMP}.sql.gz"

# Create backup directory
mkdir -p $BACKUP_DIR

# Dump and compress
mysqldump -h${DB_HOST} -P${DB_PORT} -u${DB_USERNAME} -p${DB_PASSWORD} \
    ${DB_DATABASE} --single-transaction --quick | gzip > "${BACKUP_DIR}/${FILENAME}"

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

echo "✅ Backup created: ${FILENAME}"
echo "📦 Size: $(du -h ${BACKUP_DIR}/${FILENAME} | cut -f1)"
