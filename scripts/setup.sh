#!/bin/sh
# One-time installer: WordPress + WooCommerce + Kitab theme + sample books.
# Runs inside the `wpcli` container (see docker-compose.yml):
#   docker compose run --rm wpcli sh /project/scripts/setup.sh
set -eu

SITE_URL="${SITE_URL:-http://localhost:8080}"
SITE_TITLE="${SITE_TITLE:-Hamza Book Store}"
SITE_TAGLINE="${SITE_TAGLINE:-Urdu, English & Islamic books delivered across Pakistan}"
ADMIN_USER="${ADMIN_USER:-admin}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@example.com}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-}"
IMPORT_SAMPLE_BOOKS="${IMPORT_SAMPLE_BOOKS:-yes}"
PROJECT_DIR="${PROJECT_DIR:-/project}"

echo "→ Waiting for WordPress files and database…"
i=0
until [ -f wp-config.php ] && wp db check >/dev/null 2>&1; do
	i=$((i + 1))
	[ "$i" -gt 60 ] && { echo "Database not reachable after 3 minutes" >&2; exit 1; }
	sleep 3
done

if ! wp core is-installed 2>/dev/null; then
	if [ -z "$ADMIN_PASSWORD" ]; then
		ADMIN_PASSWORD="$(head -c 18 /dev/urandom | base64 | tr -dc 'A-Za-z0-9' | head -c 20)"
		GENERATED_PASSWORD=1
	fi
	echo "→ Installing WordPress…"
	wp core install --url="$SITE_URL" --title="$SITE_TITLE" --admin_user="$ADMIN_USER" \
		--admin_password="$ADMIN_PASSWORD" --admin_email="$ADMIN_EMAIL" --skip-email
else
	echo "→ WordPress already installed, continuing."
fi

wp option update blogdescription "$SITE_TAGLINE"
wp option update timezone_string "Asia/Karachi"
wp rewrite structure '/%postname%/'

echo "→ Installing WooCommerce…"
wp plugin is-installed woocommerce || wp plugin install woocommerce
wp plugin activate woocommerce
wp plugin activate kitab-book-details
wp theme activate kitab
wp plugin delete hello akismet 2>/dev/null || true

if [ "$IMPORT_SAMPLE_BOOKS" = "yes" ]; then
	echo "→ Importing sample books…"
	wp eval-file "$PROJECT_DIR/scripts/import-books.php" "$PROJECT_DIR/sample-data/books.json"
fi

echo "→ Configuring store for Pakistan…"
wp eval-file "$PROJECT_DIR/scripts/configure-store.php"
wp rewrite flush
wp cache flush >/dev/null 2>&1 || true

echo ""
echo "✔ Done! Store:  $SITE_URL"
echo "         Admin:  $SITE_URL/wp-admin   (user: $ADMIN_USER)"
if [ "${GENERATED_PASSWORD:-0}" = "1" ]; then
	echo "      Password:  $ADMIN_PASSWORD   ← save this now, it won't be shown again"
fi
