# Понимая Doctrine

Так вышло, что документация Doctrine в некоторых местах не является достаточно детальной, поэтому было решено
рассмотреть основные ситуации в файле [tests/Functional/AllTest.php](tests/Functional/AllTest.php)

Напоминаю про:
- https://ocramius.github.io/doctrine-best-practices/
- тесты в официальном репозитории https://github.com/doctrine/orm/tree/2.9.x/tests/Doctrine/Tests/ORM/Functional
- https://buildmedia.readthedocs.org/media/pdf/doctrine2/stable/doctrine2.pdf

По правде я разочарован большим количеством неочевидных моментов и простых багов (учитывая кол-во лет разработки ORM).

## Как запустить тесты или поправить их

Предисловие: в репозитории имеется файл composer.lock.dist, необходимый, чтобы понимать, когда и при каких версиях
зависимостей текущие тесты успешно проходят, но Вы можете запускать их на основании своего composer.lock файла, это 
позволит выявлять расхождения в версиях библиотеки Doctrine.

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

## Doctrine docs

[Примечания переводчика](docs/translator-note.md "Примечания переводчика")

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

Работа не завершена, аналогичная документация: https://github.com/kaurov/doctrine2-ru/wiki

Copyrights © YaPro.Ru
