#!/bin/sh

#
# This file is part of the Expense Tracker.
#
#  (c) SekjuRiczard <dawidosak32@gmail.com>
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#
set -e

mkdir -p /app/var/cache /app/var/log /app/config/jwt
chown -R www-data:www-data /app/var /app/config/jwt

if [ ! -f /app/config/jwt/private.pem ] || [ ! -f /app/config/jwt/public.pem ]; then
  php /app/bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction
fi

if [ "$RUN_MIGRATIONS" = "1" ]; then
  php /app/bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
fi

exec "$@"