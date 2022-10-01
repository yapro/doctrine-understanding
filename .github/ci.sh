#!/bin/sh -e

# чтобы печатались выполняемые команды:
set -o xtrace

docker run --rm --user=$(id -u):$(id -g) -v $(pwd):/app -w /app yapro/doctrine-understanding:latest ./phpmd.phar . text phpmd.xml --exclude .github/workflows,vendor --strict
docker run --rm --user=$(id -u):$(id -g) -v $(pwd):/app -w /app yapro/doctrine-understanding:latest ./php-cs-fixer.phar fix --config=.php-cs-fixer.dist.php -v --dry-run --stop-on-violation --using-cache=no --allow-risky=yes --diff
docker run --rm --user=$(id -u):$(id -g) -v $(pwd):/app -w /app yapro/doctrine-understanding:latest composer install --optimize-autoloader --no-scripts --no-interaction
docker run --rm --user=$(id -u):$(id -g) -v $(pwd):/app -w /app yapro/doctrine-understanding:latest vendor/bin/phpunit --testsuite=Functional
