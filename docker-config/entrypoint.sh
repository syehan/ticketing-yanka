#!/bin/sh
set -e

echo "* * * * * php /var/www/html/artisan schedule:run >> /dev/null 2>&1" >> /etc/crontab

php /var/www/html/artisan migrate --force
service nginx start
supervisord -c /etc/supervisord.conf

exec "$@"