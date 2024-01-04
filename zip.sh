
composer install --optimize-autoloader --no-dev --ignore-platform-reqs
rm miniorange-keto-hook.zip
zip -r miniorange-keto-hook.zip vendor/* src/* *.php readme.md
