Build
```sh
docker build -t yapro/doctrine-understanding:latest -f ./Dockerfile ./
```

Tests
```sh
docker run --rm --user=1000:1000 -v $(pwd):/app yapro/doctrine-understanding:latest bash -c "cd /app \
  && composer install --optimize-autoloader --no-scripts --no-interaction \
  && vendor/bin/phpunit --testsuite=Functional"
```

Dev
```sh
docker run -it --rm --user=1000:1000 -v $(pwd):/app -w /app yapro/doctrine-understanding:latest bash
composer install -o
```

Debug PHP:
```sh
docker run --rm --user=1000:1000 -v $(pwd):/app yapro/doctrine-understanding:latest bash -c "cd /app \
  && composer install --optimize-autoloader --no-scripts --no-interaction \
  && PHP_IDE_CONFIG=\"serverName=common\" \
     XDEBUG_SESSION=common \
     XDEBUG_MODE=debug \
     XDEBUG_CONFIG=\"max_nesting_level=200 client_port=9003 client_host=172.16.30.130\" \
     vendor/bin/phpunit --cache-result-file=/tmp/phpunit.cache --testsuite=Functional"
```
Если с xdebug что-то не получается, напишите: php -dxdebug.log='/tmp/xdebug.log' и смотрите в лог.

- https://xdebug.org/docs/upgrade_guide
- https://www.jetbrains.com/help/phpstorm/2021.1/debugging-a-php-cli-script.html

# Doctrine docs

## Предисловие

1.  [Примечания переводчика](docs/translator-note.md "Примечания переводчика")

### Содержание

1.  [Введение](docs/introduction.md "Введение")
2.  [Архитектура](docs/architecture.md "Архитектура")
3.  [Настройка](docs/configuration.md "Настройка")
4.  [Часто задаваемые вопросы](docs/faq.md "Часто задаваемые вопросы")
5.  [Отображения](docs/basic-mapping.md "Отображения")
6.  [Отображения связей](docs/association-mapping.md "Отображение связей")
7.  [Отображения и наследование](docs/inheritance-mapping.md "Отображения и наследование")
8.  [Работа с объектами](docs/working-with-objects.md "Работа с объектами")
9.  [Работа со связями](docs/working-with-associations.md "Работа со связями")
10. [Транзакции и параллелизм](docs/transactions-and-concurrency.md "Транзакции и параллелизм")
11.  [События](docs/events.md "События")
12.  [Пакетная обработка](docs/batch-processing.md "Пакетная обработка")
13.  [Язык DQL](docs/dql-doctrine-query-language.md "Язык DQL – Doctrine Query Language")
14.  [QueryBuilder](docs/query-builder.md "Создание запросов с помощью QueryBuilder")
15.  [Нативный SQL](docs/native-sql.md "Нативный SQL")
16.  [Отслеживание изменений](docs/change-tracking-policies.md "Отслеживание изменений")
17.  [Неполные объекты](docs/partial-objects.md "Неполные объекты")
18.  Отображения через XML
19.  Отображения через YAML
20.  Справочник по аннотациям
21.  PHP отображения
22.  Кеширование
23.  Производительность
24.  Инструменты
25.  Драйверы метаданных
26.  Полезные советы
27.  Ограничения и известные проблемы

Copyrights © YaPro.Ru
