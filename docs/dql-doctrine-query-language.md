Doctrine Query Language (DQL) – данное семейство запросов производное от языка _Object Query Language_, который в свою очередь чем-то напоминает такие грамматики как _Hibernate Query Language (HQL)_ и _Java Persistence Query Language (JPQL)_.

С помощью DQL можно строить довольно мощные запросы к существующим объектным моделям. Представьте себе, что все объекты у вас хранятся в некотором хранилище (что-то вроде объектной базы данных), и с помощью DQL запросов вы обращаетесь к этому хранилищу с целью получить необходимое вам подмножество объектов.

> Типичная ошибка новичков состоит в том, что они думают о DQL как об очередной форме SQL, пытаясь вставлять в запросы имена таблиц и столбцов, или же JOIN’ить таблицы друг с другом. Так что имейте ввиду, DQL — это язык запросов только для объектной модели, для реляционных движняков он не подходит.

DQL не чувствителен к регистру символов за исключением пространств имен, названий классов и их полей.

Содержание

* [13.1. Типы DQL запросов](dql-doctrine-query-language.md#131_DQL)
* [13.2. Запросы SELECT](dql-doctrine-query-language.md#132_SELECT)
    * [13.2.1. DQL SELECT](dql-doctrine-query-language.md#1321_DQL_SELECT)
    * [13.2.2. JOIN](dql-doctrine-query-language.md#1322_JOIN)
    * [13.2.3. Именованные и позиционные параметры](dql-doctrine-query-language.md#1323)
    * [13.2.4. Примеры DQL SELECT](dql-doctrine-query-language.md#1324_DQL_SELECT)
        * [13.2.4.1. Синтаксис partial объектов](dql-doctrine-query-language.md#13241_partial)
    * [13.2.5. Использование INDEX BY](dql-doctrine-query-language.md#1325_INDEX_BY)
* [13.3. Запросы UPDATE](dql-doctrine-query-language.md#133_UPDATE)
* [13.4. Запросы DELETE](dql-doctrine-query-language.md#134_DELETE)
* [13.5. Функции, операторы и аггрегации](dql-doctrine-query-language.md#135)
    * [13.5.1. Функции в DQL](dql-doctrine-query-language.md#1351__DQL)
    * [13.5.2. Арифметические операторы](dql-doctrine-query-language.md#1352)
    * [13.5.3. Агрегатные функции](dql-doctrine-query-language.md#1353)
    * [13.5.4. Другие выражения](dql-doctrine-query-language.md#1354)
    * [13.5.5. Создание пользовательских функций](dql-doctrine-query-language.md#1355)
* [13.6. Запросы к унаследованным классам](dql-doctrine-query-language.md#136)
    * [13.6.1. Одиночная таблица](dql-doctrine-query-language.md#1361)
    * [13.6.2. Class Table Inheritance](dql-doctrine-query-language.md#1362_Class_Table_Inheritance)
* [13.7. Класс Query](dql-doctrine-query-language.md#137_Query)
* [13.7.1. Форматы результата запросов](dql-doctrine-query-language.md#1371)
    * [13.7.2. Простые (Pure) и смешанные (Mixed) результаты](dql-doctrine-query-language.md#1372_Pure__Mixed)
    * [13.7.3. Несколько сущностей в предложени FROM](dql-doctrine-query-language.md#1373___FROM)
    * [13.7.4. Методы гидрации](dql-doctrine-query-language.md#1374)
        * [13.7.4.1. Object Hydration](dql-doctrine-query-language.md#13741_Object_Hydration)
        * [13.7.4.2. Array Hydration](dql-doctrine-query-language.md#13742_Array_Hydration)
        * [13.7.4.3. Scalar Hydration](dql-doctrine-query-language.md#13743_Scalar_Hydration)
        * [13.7.4.4. Single Scalar Hydration](dql-doctrine-query-language.md#13744_Single_Scalar_Hydration)
        * [13.7.4.5. Пользовательские режимы гидрации](dql-doctrine-query-language.md#13745)
    * [13.7.5. Итерирование по огромным результирующим наборам](dql-doctrine-query-language.md#1375)
    * [13.7.6. Функции](dql-doctrine-query-language.md#1376)
        * [13.7.6.1. Параметры](dql-doctrine-query-language.md#13761)
        * [13.7.6.2. API для управление кешем](dql-doctrine-query-language.md#13762_API)
        * [13.7.6.3. Подсказки](dql-doctrine-query-language.md#13763)
        * [13.7.6.4. Кеш запросов (только для DQL запросов)](dql-doctrine-query-language.md#13764___DQL)
        * [13.7.6.5. Первый и максимальный элементы в результирующем наборе (только для DQL)](dql-doctrine-query-language.md#13765_____DQL)
        * [13.7.6.6. Временное изменение режима выборки в DQL](dql-doctrine-query-language.md#13766___DQL)
* [13.8. EBNF](dql-doctrine-query-language.md#138_EBNF)
    * [13.8.1. Document syntax:](dql-doctrine-query-language.md#1381_Document_syntax)
    * [13.8.2. Terminals](dql-doctrine-query-language.md#1382_Terminals)
    * [13.8.3. Query Language](dql-doctrine-query-language.md#1383_Query_Language)
    * [13.8.4. Statements](dql-doctrine-query-language.md#1384_Statements)
    * [13.8.5. Identifiers](dql-doctrine-query-language.md#1385_Identifiers)
    * [13.8.6. Path Expressions](dql-doctrine-query-language.md#1386_Path_Expressions)
    * [13.8.7. Clauses](dql-doctrine-query-language.md#1387_Clauses)
    * [13.8.8. Items](dql-doctrine-query-language.md#1388_Items)
    * [13.8.9. From, Join and Index by](dql-doctrine-query-language.md#1389_From_Join_and_Index_by)
    * [13.8.10. Select Expressions](dql-doctrine-query-language.md#13810_Select_Expressions)
    * [13.8.11. Conditional Expressions](dql-doctrine-query-language.md#13811_Conditional_Expressions)
    * [13.8.12. Collection Expressions](dql-doctrine-query-language.md#13812_Collection_Expressions)
    * [13.8.13. Literal Values](dql-doctrine-query-language.md#13813_Literal_Values)
    * [13.8.14. Input Parameter](dql-doctrine-query-language.md#13814_Input_Parameter)
    * [13.8.15. Arithmetic Expressions](dql-doctrine-query-language.md#13815_Arithmetic_Expressions)
    * [13.8.16. Scalar and Type Expressions](dql-doctrine-query-language.md#13816_Scalar_and_Type_Expressions)
    * [13.8.17. Aggregate Expressions](dql-doctrine-query-language.md#13817_Aggregate_Expressions)
    * [13.8.18. Условия](dql-doctrine-query-language.md#13818)
    * [13.8.19. Другие выражения](dql-doctrine-query-language.md#13819)
    * [13.8.20. Функции](dql-doctrine-query-language.md#13820)

13.1. Типы DQL запросов
=======================

В DQL присутствуют такие конструкции как SELECT, UPDATE и DELETE, они аналогичны своим собратьям из мира SQL. Операция INSERT отсутствуют, потому что для согласованности объектной модели все сущности и связи заводятся под управление ORM через вызов **EntityManager#persist()** и способа вставлять из в базу напрямую не предусмотрено, да он и не нужен.

Запрос SELECT умеет вытаскивать какие-то определенные куски из вашей доменной модели, к которым нельзя получить доступ при помощь связей. В дополнение к этому, такие запросы позволяют запрашивать сущности вместе с полным набором связей с помощью единственного SQL запроса, что не может не радовать.

С помощью запросов UPDATE и DELETE можно выполнять пакетные обновления и удаления сущностей из доменной модели. Это бывает весьма полезно, ведь не всегда есть возможность загрузить в память полный набор сущностей для их последующего изменения.

13.2. Запросы SELECT
====================

13.2.1. DQL SELECT
------------------

Выражение SELECT определяет какие данные появятся в результатах запроса (кто-бы мог подумать, епта). Композиция различных выражений в запросе SELECT также может влиять на природу результатов запроса.

Пример ниже производит выборку пользователей старше 20 лет:
```php
<?php  
$query = $em->createQuery('SELECT u FROM MyProject\Model\User u WHERE u.age > 20');  
$users = $query->getResult();
```
Давайте рассмотрим это запрос:

* **u** — это синоним, указывающий на класс _MyProject\Model\User_. Помещая этот алиас в выражение _SELECT_ мы тем самым указываем, что нам нужно получить все экземпляры именно класса User.
* За ключевым словом FROM всегда следует полное имя класса, за которым в свою очередь следует алиас для этого класса. Класс это своего рода корень запроса, от которого далее можно перемещаться с помощью JOIN’ов (будет описано позднее) и различных путевых выражений **(path expressions)**.
* Выражение **u.age** в блоке WHERE это и есть путевое выражение. Их легко найти по оператору ‘.’, используемого для формирования путей. Выражение **u.age** указывает на поле age класса User.

Результатом этого запроса будет список объектов User, все пользователи в котором старше 20 лет.

Внутри выражения SELECT можно указывать как ключевые поля класса для загрузки всей сущности, так и лишь некоторые из них с помощью синтаксиса **u.name**. Можно комбинировать эти способы, а также применять к ним функции агрегации DQL. Числовые поля также могут использоваться в математических операциях. Для дополнительной информации смотрите разделы[Функции, операторы и агрегации](dql-doctrine-query-language.md#135).

13.2.2. JOIN
------------

Запрос SELECT может содержать JOIN’ы двух типов: “Regular” и “Fetch”.

Regular Joins: используются с целью фильтрации результатов запросов, а также вычисления агрегированных значений.

Fetch Joins: Похожи на обычные JOIN’ы, но дополнительно вытаскивают из базы все связанные сущности и включают их в результат запроса.

Не существует какого-то ключевого слова, которое бы говорило о том какой из JOIN’ов использовать. JOIN, будь то INNER JOIN или OUTER JOIN, будет трактоваться как “Fetch JOIN” если поля подключаемой через JOIN сущности появятся в SELECT-части DQL запроса вне какой-либо агрегатной функции. В противном случае это будет обычный (regular) JOIN.

Пример:

Обычные JOIN по полю адреса:
```php
<?php  
$query = $em->createQuery("SELECT u FROM User u JOIN u.address a WHERE a.city = 'Berlin'");  
$users = $query->getResult();
```
Fetch-JOIN по адресу:
```php
<?php  
$query = $em->createQuery("SELECT u, a FROM User u JOIN u.address a WHERE a.city = 'Berlin'");  
$users = $query->getResult();
```
Когда Doctrine строит результат для запроса с Fetch-JOIN’ами, на первом уровне результирующего массива будет расположен класс из выражения FROM. В предыдущем примере будет возвращен массив экземпляров класса User, при этом в каждый экземпляр будет добавлена переменная **User#address**. Когда вы обратитесь к этой переменной Doctrine не нужно будет дополнительно подгружать всю связь с помощью другого запроса, нет необходимости использоваться здесь _Lazy loading_.

> Тем не менее Doctrine дает возможность ссылаться на любые доступные связи между объектами доменной модели. Объекты, которые не были загружены из БД заменяются на экземпляры прокси-классов, для их загрузки будет использоваться “ленивая загрузка”. С коллекциями все аналогично — они будут загружены с помощью lazy-loading при первом доступе к ним. Помните, использование lazy-loading ведет к том, что база данных будет бомбардироваться кучей мелких запросов, что безусловно может отрицательно сказаться на производительности приложения. И Fetch-JOIN’ы как раз таки и позволяют избежать этого — они загрузят всю нужную вам ветку сущностей с помощью единственного SELECT запроса.

13.2.3. Именованные и позиционные параметры
-------------------------------------------

DQL поддерживает два типа параметров: именованные и позиционные. Однако, в отличие от многих SQL-грамматик, здесь позиционные параметры задаются с помощью чисел, например ”?1”, ”?2” и т.д. Именованные параметры задаются в виде ”:name1”, ”:name2” и т.д.

Когда нужно сослаться на параметр в методе **Query#setParameter($param, $value)**, то оба типа параметров нужно указывать без префиксов.

13.2.4. Примеры DQL SELECT
--------------------------

Этот раздел содержит большой набор различных DQL запросов с комментариями. Фактический результат также зависит от режима **hydrations** (черт побьери, кто-нибудь знает адекватный аналог этого слова на русском).

Запросив все сущности класса **User**:
```php
 <?php  
$query = $em->createQuery('SELECT u FROM MyProject\Model\User u');  
$users = $query->getResult(); // массив объектов класса User
```
Получение первичных ключей (ID) всех CmsUsers:
```php
<?php  
$query = $em->createQuery('SELECT u.id FROM CmsUser u');  
$ids = $query->getResult(); // массив идентификаторов CmsUser
```
Получение идентификаторов всею юзеров, написавших хотя бы одну статью:
```php
 <?php  
$query = $em->createQuery('SELECT DISTINCT u.id FROM CmsArticle a JOIN a.user u');  
$ids = $query->getResult(); // массив идентификаторов CmsUser
```
Получение всех статей, отсортированных по имени автора:
```php
<?php  
$query = $em->createQuery('SELECT a FROM CmsArticle a JOIN a.user u ORDER BY u.name ASC');  
$articles = $query->getResult(); // массих объектов CmsArticle
```
Получение полей Username и Name класса CmsUser:
```php
<?php  
$query = $em->createQuery('SELECT u.username, u.name FROM CmsUser u');  
$users = $query->getResults(); // массив значение полей username и name класса CmsUser  
echo $users\[0\]\['username'\];
```
Получение объектов ForumUser и связанной с ними сущности:
```php
<?php  
$query = $em->createQuery('SELECT u, a FROM ForumUser u JOIN u.avatar a');  
$users = $query->getResult(); // массив объектов ForumUser с загруженной связью avatar  
echo get_class($users\[0\]->getAvatar());
```
Получение объекта CmsUser с полной загрузкой всех его телефонных номеров:
```php
<?php  
$query = $em->createQuery('SELECT u, p FROM CmsUser u JOIN u.phonenumbers p');  
$users = $query->getResult(); // массив объектов CmsUser с загруженной связью phonenumbers  
$phonenumbers = $users\[0\]->getPhonenumbers();
```
Сортировка по возрастанию:
```php
<?php  
$query = $em->createQuery('SELECT u FROM ForumUser u ORDER BY u.id ASC');  
$users = $query->getResult(); // массив объектов ForumUser
```
Сортировка по убыванию:
```php
 <?php  
$query = $em->createQuery('SELECT u FROM ForumUser u ORDER BY u.id DESC');  
$users = $query->getResult(); // массив объектов ForumUser
```
Аггрегатные функции:
```php
<?php  
$query = $em->createQuery('SELECT COUNT(u.id) FROM Entities\User u');  
$count = $query->getSingleScalarResult();  
  
$query = $em->createQuery('SELECT u, count(g.id) FROM Entities\User u JOIN u.groups g GROUP BY u.id');  
$result = $query->getResult();
```
Выражение WHERE и позиционные параметры:
```php
<?php  
$query = $em->createQuery('SELECT u FROM ForumUser u WHERE u.id = ?1');  
$query->setParameter(1, 321);  
$users = $query->getResult(); // массив объектов ForumUser
```
Предложение WHERE и именованные параметры:
```php
<?php  
$query = $em->createQuery('SELECT u FROM ForumUser u WHERE u.username = :name');  
$query->setParameter('name', 'Bob');  
$users = $query->getResult(); // array of ForumUser objects
```
Вложенные условия в предложении WHERE:
```php
<?php  
$query = $em->createQuery('SELECT u from ForumUser u WHERE (u.username = :name OR u.username = :name2) AND u.id = :id');  
$query->setParameters(array(  
    'name' => 'Bob',  
    'name2' => 'Alice',  
    'id' => 321,  
));  
$users = $query->getResult(); // массив объектов ForumUser
```
COUNT DISTINCT:
```php
<?php  
$query = $em->createQuery('SELECT COUNT(DISTINCT u.name) FROM CmsUser');  
$users = $query->getResult(); // массив объектов ForumUser
```
Арифметическое выражение:
```php
<?php  
$query = $em->createQuery('SELECT u FROM CmsUser u WHERE ((u.id + 5000) * u.id + 3) < 10000000');  
$users = $query->getResult(); // массив объектов ForumUser
```
Получение идентификаторов пользователей и статей, если они есть при помощи LEFT JOIN:
```php
<?php  
$query = $em->createQuery('SELECT u.id, a.id as article_id FROM CmsUser u LEFT JOIN u.articles a');  
$results = $query->getResult(); // array of user ids and every article_id for each user
```
Дополнительные условия в JOIN:
```php
<?php  
$query = $em->createQuery("SELECT u FROM CmsUser u LEFT JOIN u.articles a WITH a.topic LIKE '%foo%'");  
$users = $query->getResult();
```
Использование нескольких Fetch-JOIN’ов:
```php
<?php  
$query = $em->createQuery('SELECT u, a, p, c FROM CmsUser u JOIN u.articles a JOIN u.phonenumbers p JOIN a.comments c');  
$users = $query->getResult();
```
Выражение BETWEEN:
```php
<?php  
$query = $em->createQuery('SELECT u.name FROM CmsUser u WHERE u.id BETWEEN ?1 AND ?2');  
$query->setParameter(1, 123);  
$query->setParameter(2, 321);  
$usernames = $query->getResult();
```
Использование функций DQL в выражении WHERE:
```php
<?php  
$query = $em->createQuery("SELECT u.name FROM CmsUser u WHERE TRIM(u.name) = 'someone'");  
$usernames = $query->getResult();
```
Выражение IN():
```php
<?php  
$query = $em->createQuery('SELECT u.name FROM CmsUser u WHERE u.id IN(46)');  
$usernames = $query->getResult();  
  
$query = $em->createQuery('SELECT u FROM CmsUser u WHERE u.id IN (1, 2)');  
$users = $query->getResult();  
  
$query = $em->createQuery('SELECT u FROM CmsUser u WHERE u.id NOT IN (1)');  
$users = $query->getResult();
```
Функция CONCAT():
```php
<?php  
$query = $em->createQuery("SELECT u.id FROM CmsUser u WHERE CONCAT(u.name, 's') = ?1");  
$query->setParameter(1, 'Jess');  
$ids = $query->getResult();  
  
$query = $em->createQuery('SELECT CONCAT(u.id, u.name) FROM CmsUser u WHERE u.id = ?1');  
$query->setParameter(1, 321);  
$idUsernames = $query->getResult();
```
Ключевое слово EXISTS и связанный с ним подзапрос:
```php
<?php  
$query = $em->createQuery('SELECT u.id FROM CmsUser u WHERE EXISTS (SELECT p.phonenumber FROM CmsPhonenumber p WHERE p.user = u.id)');  
$ids = $query->getResult();
```
Получение всех пользователей, являющихся членами группы **$group**:
```php
<?php  
$query = $em->createQuery('SELECT u.id FROM CmsUser u WHERE :groupId MEMBER OF u.groups');  
$query->setParameter('groupId', $group);  
$ids = $query->getResult();
```
Получение всех пользователей, имеющих более одного телефонного номера:
```php
<?php  
$query = $em->createQuery('SELECT u FROM CmsUser u WHERE SIZE(u.phonenumbers) > 1');  
$users = $query->getResult();
```
Пользователи, не имеющие ни одного номера:
```php
<?php  
$query = $em->createQuery('SELECT u FROM CmsUser u WHERE u.phonenumbers IS EMPTY');  
$users = $query->getResult();
```
Выборка с учетом иерархии наследования, в примере ниже показано получение экземпляров заданного класса, являющихся потомками другого класса:
```php
<?php  
$query = $em->createQuery('SELECT u FROM Doctrine\Tests\Models\Company\CompanyPerson u WHERE u INSTANCE OF Doctrine\Tests\Models\Company\CompanyEmployee');  
$query = $em->createQuery('SELECT u FROM Doctrine\Tests\Models\Company\CompanyPerson u WHERE u INSTANCE OF ?1');  
$query = $em->createQuery('SELECT u FROM Doctrine\Tests\Models\Company\CompanyPerson u WHERE u NOT INSTANCE OF ?1');
```
### 13.2.4.1. СИНТАКСИС PARTIAL ОБЪЕКТОВ

Обычно когда вы запрашиваете не все, а только какие-то определенные поля, нет необходимости вытаскивать весь объект. Вместо этого, можно запросить только массив в виде обычного плоского набора, аналогично тому как если бы для получения данных вы использовали напрямую язык SQL вместе с JOIN.

Когда нужно получить **partial** объекты, нужно использовать одноименное ключевое слово:
```php
<?php  
$query = $em->createQuery('SELECT partial u.{id, username} FROM CmsUser u');  
$users = $query->getResult(); // массив неполных объектов CmsUser
```
Аналогично это работает и с JOIN’ами:
```php
<?php  
$query = $em->createQuery('SELECT partial u.{id, username}, partial a.{id, name} FROM CmsUser u JOIN u.articles a');  
$users = $query->getResult(); // массив неполных объектов CmsUser
```
13.2.5. Использование INDEX BY
------------------------------

Конструкция INDEX BY никак не транслируется в SQL, она затрагивает лишь объекты и генерацию результирующего набора. Что именно имеется ввиду? После предложение FROM и JOIN можно указать по какому полю будет индексироваться этот класс в результирующем наборе. По умолчанию в качестве ключей выступают целочисленные инкрементные значения начиная с нуля. Но с помощью INDEX BY можно назначить любую колонку в качестве ключа, хотя делать это имеет смысл только для первичных или уникальных ключей:
```sql
SELECT u.id, u.STATUS, UPPER(u.name) nameUpper FROM USER u INDEX BY u.id  
JOIN u.phonenumbers p INDEX BY p.phonenumber
````
Такой запрос возвратит набор, индексированный сразу по **user.id** и **phonenumber.id**:
```php
array  
  0 =>  
    array  
      1 =>  
        object(stdClass)\[299\]  
          public '\_\_CLASS\_\_' => string 'Doctrine\Tests\Models\CMS\CmsUser' (length=33)  
          public 'id' => int 1  
          ..  
      'nameUpper' => string 'ROMANB' (length=6)  
  1 =>  
    array  
      2 =>  
        object(stdClass)\[298\]  
          public '\_\_CLASS\_\_' => string 'Doctrine\Tests\Models\CMS\CmsUser' (length=33)  
          public 'id' => int 2  
          ...  
      'nameUpper' => string 'JWAGE' (length=5)
```
13.3. Запросы UPDATE
====================

DQL умеет не только получать данные, но и менять их (кто бы мог подумать). Работа оператора UPDATE полностью предсказуема и работает как показано в примере ниже:
```sql
UPDATE MyProject\Model\USER u SET u.password = 'new' WHERE u.id IN (1, 2, 3)
```
Ссылаться на связанные сущность можно только в предложении WHERE или используя под-запросы.

> DQL UPDATE транслируется напрямую в SQL UPDATE и, таким образом, обходит любые схемы блокировки, события и не увеличивает номер версии. Сущности, которые были ранее загружены из базы и являются PERSISTED-сущностями, не будут автоматически синхронизированы с актуальными данными в базе. Чтобы сделать это рекомендуется каждый раз вызывать метод **EntityManager#clear()** и заново получать экземпляры затронутой сущности.

13.4. Запросы DELETE
====================

Запросы DELETE имеют такой же простой синтаксис как и UPDATE:
```sql
DELETE MyProject\Model\USER u WHERE u.id = 4
```
На связанные сущности накладывается такие же ограничения.

> DQL запросы DELETE транслируются напрямую в одноименный SQL, исключая, т.о., реакцию на события и выполнение проверки для столбца с версией, если они не были явно добавлены в предложение WHERE. Кроме того, удаление не распространяется каскадно на связанные сущности, даже если это явно указано в метаданных.

13.5. Функции, операторы и аггрегации
=====================================

13.5.1. Функции в DQL
---------------------

В предложениях SELECT, WHERE и HAVING поддерживаются следующие функции:

* ABS(arithmetic_expression)
* CONCAT(str1, str2)
* CURRENT_DATE() – текущая дата
* CURRENT_TIME() – текущее время
* CURRENT_TIMESTAMP()
* LENGTH(str) – длина строки
* LOCATE(needle, haystack \[, offset\]) – позиция первого вхождения подстроки
* LOWER(str) – перевод в нижний регистр.
* MOD(a, b) – остаток от деления a на b.
* SIZE(collection) – количество элементов в коллекции
* SQRT(q) – квадратный корень.
* SUBSTRING(str, start \[, length\]) – подстрока.
* TRIM(\[LEADING | TRAILING | BOTH\] \[‘trchar’ FROM\] str) – Удалении оконечных пробелов.
* UPPER(str) – перевод в верхний регистр.
* DATE_ADD(date, days, unit) – добавляет к дате заданное количество дней (доступные единицы измерения: DAY, MONTH)
* DATE_SUB(date, days, unit) – вычитание дней из даты
* DATE_DIFF(date1, date2) – Разница в днях между двумя датами

13.5.2. Арифметические операторы
--------------------------------

В DQL допускает использование арифметических выражений:
```sql
SELECT person.salary * 1.5 FROM CompanyPerson person WHERE person.salary < 100000
```
13.5.3. Агрегатные функции
--------------------------

В предложениях SELECT и GROUP BY можно использовать следующие функции: AVG, COUNT, MIN, MAX, SUM

13.5.4. Другие выражения
------------------------

Помимо всего вышеперечисленного, в DQL есть довольно широкий набор различных выражений, пришедший из SQL, например:

* **ALL/ANY/SOME** – при использовании в выражении WHERE сразу после подзапроса работает как и его эквивалент в SQL.
* **BETWEEN a AND b** и **NOT BETWEEN a AND b** для проверки попадания значения в заданный интервал.
* **IN (x1, x2, …)** и **NOT IN (x1, x2, ..)** для проверки вхождения значения в заданный набор.
* **LIKE** .. и **NOT LIKE** .. сравнение строк.
* IS NULL и **IS NOT NULL** проверка на NULL
* **EXISTS** и **NOT EXISTS** в связке с подзапросами

13.5.5. Создание пользовательских функций
-----------------------------------------

По умолчанию DQL имеет в своем арсенале общий набор функций, поддерживаемых многими СУБД. Однако, чаще всего база выбирается раз и навсегда. В таком случае можно расширить синтаксис DQL функциями, ориентированными на конкретную платформу.

Регистрация пользовательских функций осуществляется через объект Configuration:
```php
<?php  
$config = new \Doctrine\ORM\Configuration();  
$config->addCustomStringFunction($name, $class);  
$config->addCustomNumericFunction($name, $class);  
$config->addCustomDatetimeFunction($name, $class);  
  
$em = EntityManager::create($dbParams, $config);
```
В зависимости от типа функции она может возвращать строку, число или дату и время. Давайте в качестве примера добавим специфичную для MySQL функцию FLOOR(). Все классы нужно наследовать от базового:
```php
<?php  
namespace MyProject\Query\AST;  
  
use \Doctrine\ORM\Query\AST\Functions\FunctionNode;  
use \Doctrine\ORM\Query\Lexer;  
  
class MysqlFloor extends FunctionNode  
{  
    public $simpleArithmeticExpression;  
  
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)  
    {  
        return 'FLOOR(' . $sqlWalker->walkSimpleArithmeticExpression(  
            $this->simpleArithmeticExpression  
        ) . ')';  
    }  
  
    public function parse(\Doctrine\ORM\Query\Parser $parser)  
    {  
        $lexer = $parser->getLexer();  
  
        $parser->match(Lexer::T_IDENTIFIER);  
        $parser->match(Lexer::T\_OPEN\_PARENTHESIS);  
  
        $this->simpleArithmeticExpression = $parser->SimpleArithmeticExpression();  
  
        $parser->match(Lexer::T\_CLOSE\_PARENTHESIS);  
    }  
}
```
Зарегистрируем функцию, после чего она станет доступна прямо в DQL запросе:
```php
<?php  
\Doctrine\ORM\Query\Parser::registerNumericFunction('FLOOR', 'MyProject\Query\MysqlFloor');  
$dql = "SELECT FLOOR(person.salary * 1.75) FROM CompanyPerson person";
```
13.6. Запросы к унаследованным классам
======================================

В этой главе рассказывается как строить запросы к унаследованным классам и какой результат при этом ожидать.

13.6.1. Одиночная таблица
-------------------------

Стратегия [Single Table Inheritance](http://martinfowler.com/eaaCatalog/singleTableInheritance.html) заключается в том, что все классы в иерархии соответствуют одной единственной таблице. И чтобы различать какая запись какому классу соответствует используется так называемый **столбец дискриминатора**.

Чтобы показать как это работает давайте для начала подготовим набор сущностей. Возьмем сущности Person и Employee:
```php
<?php  
namespace Entities;  
  
/**  
 \* @Entity  
 \* @InheritanceType("SINGLE_TABLE")  
 \* @DiscriminatorColumn(name="discr", type="string")  
 \* @DiscriminatorMap({"person" = "Person", "employee" = "Employee"})  
 */  
class Person  
{  
    /**  
     \* @Id @Column(type="integer")  
     \* @GeneratedValue  
     */  
    protected $id;  
  
    /**  
     \* @Column(type="string", length=50)  
     */  
    protected $name;  
  
    // ...  
}  
  
/**  
 \* @Entity  
 */  
class Employee extends Person  
{  
    /**  
     \* @Column(type="string", length=50)  
     */  
    private $department;  
  
    // ...  
}
```
Обратите внимание как будет выглядеть SQL запрос на создание таблиц для этих сущностей, а таблица-то всего одна:
```sql
CREATE TABLE Person (  
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,  
    name VARCHAR(50) NOT NULL,  
    discr VARCHAR(255) NOT NULL,  
    department VARCHAR(50) NOT NULL  
)
```
И теперь каждый раз когда будет сохраняться экземпляр сущности Employee, автоматом будет заполняться столбец дискриминатора:
```php
<?php  
$employee = new \Entities\Employee();  
$employee->setName('test');  
$employee->setDepartment('testing');  
$em->persist($employee);  
$em->flush();
```
Теперь напишем запрос, который достанет только что сохраненную сущность Employee из базы:
```sql
SELECT e FROM Entities\Employee e WHERE e.name = 'test'
```
Если посмотреть на нативный SQL запрос, можно заметить специальное условие, которое гарантирует, что из базы будет возвращена именно сущность типа Employee:
```sql
SELECT p0_.id AS id0, p0_.name AS name1, p0_.department AS department2,  
       p0_.discr AS discr3 FROM Person p0_  
WHERE (p0_.name = ?) AND p0_.discr IN ('employee')
```
13.6.2. Class Table Inheritance
-------------------------------

[Стратегия Class Table Inheritance](http://martinfowler.com/eaaCatalog/classTableInheritance.html) справедлива когда каждый класс в иерархии соответствует нескольким таблицам: его собственной и таблицам его родительских классов. Таким образом, таблица дочернего класса будет связана с таблицей родительского класса посредством внешнего ключа. В Doctrine 2 имплементирует эту стратегию посредством использования столбца дискриминатора у самой верхней таблицы в иерархии, потому что в контексте этой стратегии это наипростейший из способов для возможности работы полиморфных запросов.

Пример ниже аналогичен наследования от единственной таблицы, нужно только поменять тип наследования с **SINGLE_TABLE**на **JOINED**:
```php
<?php  
/**  
 \* @Entity  
 \* @InheritanceType("JOINED")  
 \* @DiscriminatorColumn(name="discr", type="string")  
 \* @DiscriminatorMap({"person" = "Person", "employee" = "Employee"})  
 */  
class Person  
{  
    // ...  
}
```
Посмотрите на SQL запрос, который создает таблицы, обратите внимание на различия с предыдущим примером:
```sql
CREATE TABLE Person (  
    id INT AUTO_INCREMENT NOT NULL,  
    name VARCHAR(50) NOT NULL,  
    discr VARCHAR(255) NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  
CREATE TABLE Employee (  
    id INT NOT NULL,  
    department VARCHAR(50) NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  
ALTER TABLE Employee ADD FOREIGN KEY (id) REFERENCES Person(id) ON DELETE CASCADE
```
* Данные бьются на две таблицы.
* Таблицы связаны внешним ключом

Теперь если попробовать сохранить ту же сущность Employee как мы делали для SINGLE_TABLE, а затем запросить ее из базы, то итоговый SQL запрос будет выглядеть иначе, в нем будет автоматически подсоединена таблица, несущая в себе информацию о типе Person:
```sql
SELECT p0_.id AS id0, p0_.name AS name1, e1_.department AS department2,  
       p0_.discr AS discr3  
FROM Employee e1_ INNER JOIN Person p0_ ON e1_.id = p0_.id  
WHERE p0_.name = ?
```
13.7. Класс Query
=================

Любой запрос DQL всегда будет представлен экземпляром класса _Doctrine\ORM\Query_. Этот экземпляр создается при вызове**EntityManager#createQuery($dql)** куда вы передаете строку с запросом. Либо можно создать пустой экземпляр Query, а затем вызвать метод **Query#setDql($dql)**. Пару примеров:
```php
<?php  
// $em это эклемпляр EntityManager  
  
// example1: Передача строки с DQL  
$q = $em->createQuery('select u from MyProject\Model\User u');  
  
// example2: то же при помощи setDql  
$q = $em->createQuery();  
$q->setDql('select u from MyProject\Model\User u');
```
13.7.1. Форматы результата запросов
===================================

Формат в котором будет возвращен результат DQL запроса SELECT может быть определен с помощью так называемого режима гидрации (**hidration mode**, кто-нибудь, мать его, знает как переводится это слово в контексте ORM?). Режим гидрации определяет каким способом будет подготовлен SQL запрос. Для каждого типа гидрации есть свой отдельный метода в классе Query. Вот они:

**Query#getResult()**: Возвращает коллекцию объектов. Результатом может быть либо коллекция объектов (простой) либо массив, в котором объекты вложены в строки результатов запроса (смешанный).

**Query#getSingleResult()**: Возвращает один объект. Если в результате запроса содержится более одного обхекте или объект отсутствует, будет выброшено исключение. Нет разницы простой это результат или смешанный.

**Query#getOneOrNullResult()**: Возвращает один объект Если объект отсутствует будет возвращено значение NULL.

**Query#getArrayResult()**: Возвращает массив графов (вложенный масив), который в значительной степени взаимозаменяем с графом объектов, возвращаемых методом **Query#getResult()**, но только для чтения.

> В некоторых случаях граф массивов может отличаться от соответствующего графа объектов из-за отличия в семантике между массивами и объектами.

**Query#getScalarResult()**: Возвращает плоский/прямоугольный результирующий набор, который может содержать повторяющиеся данные. Нет разницы простой это результат или смешанный.

**Query#getSingleScalarResult()**: Возвращает единственное скалярное значение из результата, возвращаемого СУБД. Если результат содержит более одного такого значения, будет выброшено исключение. Нет разницы простой это результат или смешанный.

Вместо этих методом можно воспользоваться универсальным методом** Query#execute(array $params = array(), $hydrationMode = Query::HYDRATE_OBJECT)**. В нем можно явно указать метод гидрации. Фактически, все вышеприведенные методы это лишь сокращения для метода **Query#execute**. Например, **Query#getResult()** внутри себя вызывает **Query#execute**, передавая Query::HYDRATE_OBJECT в качестве метода гидрации.

С целью удобства лучше применять вышеприведенные методы вместо execute.

13.7.2. Простые (Pure) и смешанные (Mixed) результаты
-----------------------------------------------------

DQL запрос SELECT, вызыванный с помощью методов **Query#getResult()** и **Query#getArrayResult()** может возвращать результат в двух формах: простой (pure) и смешанной (mixed). В предыдущих примерах вы уже видели простую форму — это просто массив объектов. По умолчанию, результат возвращается в простой форме, но если в предложении SELECT будут присутствовать скалярные значения, не относящиеся к сущности, такие как агрегации и т.д., результат будет представлен в смешанной форме. Смешанный результат имеет иную структуру, чтобы вмещать в себя скалярные значение.

Простой результат обычно выглядит так::
```php
$dql = "SELECT u FROM User u";  
  
array  
    \[0\] => Object  
    \[1\] => Object  
    \[2\] => Object  
    ...
```
Смешанный результат имеет структуру следующего формата:
```php
$dql = "SELECT u, 'some scalar string', count(u.groups) AS num FROM User u JOIN u.groups g GROUP BY u.id";  
  
array  
    \[0\]  
        \[0\] => Object  
        \[1\] => "some scalar string"  
        \['num'\] => 42  
        // ... здесь идут другие скалярные значение, индексируемые числовым способом или по имени  
    \[1\]  
        \[0\] => Object  
        \[1\] => "some scalar string"  
        \['num'\] => 42  
        // ... здесь идут другие скалярные значение, индексируемые числовым способом или по имени
```
Чтобы лучше понять суть смешанных результатов рассмотрим следующий DQL-запрос:
```sql
SELECT u, UPPER(u.name) nameUpper FROM MyProject\Model\USER u
```
В запросе используется функция UPPER, которая возвращает скалярное значение. Таким образом, в предложении SELECT будет присотствовать не только сущность но и скалярное знаение, поэтому результат будет смешанным.

Несколько нюансов о смешанных результатах:

* Объект, запрашиваемый в предложении FROM всегда будет доступен по ключу ’0′.
* Каждое скалярное значение без имени будет пронумеровано по порядку его нахождения в запросе, начиная с единицы.
* Скалярные значения, имеющие псевдоним будут доступны по ключу, значением которого является этот псевдоним. Регистр имени сохраняется.
* Если в предложении FROM указано несколько объектов они будут чередоваться в каждой строке.

Ниже показано как может выглядеть результат запроса:
```php
array  
    array  
        \[0\] => User (Object)  
        \['nameUpper'\] => "ROMAN"  
    array  
        \[0\] => User (Object)  
        \['nameUpper'\] => "JONATHAN"  
    ...
```
И как получить доступ к его элементам из PHP кода:
```php
<?php  
foreach ($results as $row) {  
    echo "Name: " . $row\[0\]->getName();  
    echo "Name UPPER: " . $row\['nameUpper'\];  
}
```
13.7.3. Несколько сущностей в предложени FROM
---------------------------------------------

В случае если вы делаете выборку сразу нескольких сущностей, указывая их в предложении FROM, в результирующий набор сущности будут чередоваться по строкам. Вот как это выглядит:
```php
$dql = "SELECT u, g FROM User u, Group g";  
  
array  
    \[0\] => Object (User)  
    \[1\] => Object (Group)  
    \[2\] => Object (User)  
    \[3\] => Object (Group)
```
13.7.4. Методы гидрации
-----------------------

Каждый из режимов гидрации делает предположение о том, в каком виде результат должен быть возвращен конечному пользователю. Чтобы умет ьвыбирать нужный формат для результата запроса следует понимать все детали гидрации:

Каждый режим представлен соответствующей константов:

* Query::HYDRATE_OBJECT
* Query::HYDRATE_ARRAY
* Query::HYDRATE_SCALAR
* Query::HYDRATE\_SINGLE\_SCALAR

### 13.7.4.1. OBJECT HYDRATION

Object hydration оформляет результирующий набор в виде графа объектов:
```php
<?php  
$query = $em->createQuery('SELECT u FROM CmsUser u');  
$users = $query->getResult(Query::HYDRATE_OBJECT);
```
### 13.7.4.2. ARRAY HYDRATION

Примерно тоже самое, результатом будет тот же граф объектов, представленный в виде массива:
```php
 <?php  
$query = $em->createQuery('SELECT u FROM CmsUser u');  
$users = $query->getResult(Query::HYDRATE_ARRAY);
```
Тоже самое можно сделать одним коротким вызовом метода getArrayResult():
```php
<?php  
$users = $query->getArrayResult();
```
### 13.7.4.3. SCALAR HYDRATION

Возвращает простой прямоугольный набор вместо графа:
```php
<?php  
$query = $em->createQuery('SELECT u FROM CmsUser u');  
$users = $query->getResult(Query::HYDRATE_SCALAR);  
echo $users\[0\]\['u_id'\];
```
при скалярной гидрации делаются следующие предположения относительно выбираемых полей:  
К полям классов  будет добавлен префикс в виде псевдонима DQL. Результирующий набор для запроса вида ‘SELECT u.name ..’ будет содержать ключ ‘u_name’.

### 13.7.4.4. SINGLE SCALAR HYDRATION

Для случая когда запрос возвращает единственное скалярное значение:
```php
<?php  
$query = $em->createQuery('SELECT COUNT(a.id) FROM CmsUser u LEFT JOIN u.articles a WHERE u.username = ?1 GROUP BY u.id');  
$query->setParameter(1, 'jwage');  
$numArticles = $query->getResult(Query::HYDRATE\_SINGLE\_SCALAR);
```
В качестве сокращения можно использовать метод getSingleScalarResult():
```php
<?php  
$numArticles = $query->getSingleScalarResult();
```
### 13.7.4.5. ПОЛЬЗОВАТЕЛЬСКИЕ РЕЖИМЫ ГИДРАЦИИ

Для создание своих методов гидрации нужно создать класс, унаследовав его от _AbstractHydrator_:
```php
<?php  
namespace MyProject\Hydrators;  
  
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;  
  
class CustomHydrator extends AbstractHydrator  
{  
    protected function _hydrateAll()  
    {  
        return $this->\_stmt->fetchAll(PDO::FETCH\_ASSOC);  
    }  
}
```
Затем нужно добавить этот класс в конфикурацию ORM:
```php
<?php  
$em->getConfiguration()->addCustomHydrationMode('CustomHydrator', 'MyProject\Hydrators\CustomHydrator');
```
Теперь гидратор доступен для использования в запросах:
```php
<?php  
$query = $em->createQuery('SELECT u FROM CmsUser u');  
$results = $query->getResult('CustomHydrator');
```
13.7.5. Итерирование по огромным результирующим наборам
-------------------------------------------------------

Иногда бывает, что запрос возвращает такой неебически большой объем данных, что его нельзя просто взять и обработать. Какой бы режим гидрации вы не использовали, все они загружают весь результирующий набор целиком в память, что может быть недопустимо при работе с большим объемом данных. В главе [Пакетная обработка](http://odiszapc.ru/doctrine/batch-processing "Пакетная обработка") описано как работать с большими массивами данных.

13.7.6. Функции
---------------

У класса AbstractQuery, от которого наследуются Query и NativeQuery есть следующие методы.

### 13.7.6.1. ПАРАМЕТРЫ

Выражения, использующие именованные wildcards, для выполнения требуют дополнительных параметров. Передать парамтеры в запрос можно с помощью следующих методов:

* AbstractQuery::setParameter($param, $value) – Устанавливает значение для численного или именованного wildcard.
* AbstractQuery::setParameters(array $params) – Устанавливает параметры, получая массив а виде пар ключ-значение.
* AbstractQuery::getParameter($param)
* AbstractQuery::getParameters()

Парадавать именованные и позиционные параметры в эти методы нужно без префиксов **?** или **:**.

### 13.7.6.2. API ДЛЯ УПРАВЛЕНИЕ КЕШЕМ

Кешировать результаты запросов можно как на основе переменных (SQL, режим гидрации, параметры, Hints) или пользовательских ключей. По умолчанию результаты запроса не кешируются. Включить кеш можно персонально для каждого запроса. В следующем примере показано как работать с Result Cache API:
```php
<?php  
$query = $em->createQuery('SELECT u FROM MyProject\Model\User u WHERE u.id = ?1');  
$query->setParameter(1, 12);  
  
$query->setResultCacheDriver(new ApcCache());  
  
$query->useResultCache(true)  
      ->setResultCacheLifeTime($seconds = 3600);  
  
$result = $query->getResult(); // промах  
  
$query->expireResultCache(true);  
$result = $query->getResult(); // игнорирование кеша, снова промах  
  
$query->setResultCacheId('my\_query\_result');  
$result = $query->getResult(); // результат сохранен под ключом 'my\_query\_result'  
  
// Либо можно вызвать useResultCache() со всем параметрами:  
$query->useResultCache(true, $seconds = 3600, 'my\_query\_result');  
$result = $query->getResult(); // cache hit!  
  
// Интроспекция  
$queryCacheProfile = $query->getQueryCacheProfile();  
$cacheDriver = $query->getResultCacheDriver();  
$lifetime = $query->getLifetime();  
$key = $query->getCacheKey();
```
> Установить драйвер кеша можно глобально в экземпляре класса Doctrine\ORM\Configuration, т.о. он будет передан во все экземпляры Query и NativeQuery.

### 13.7.6.3. ПОДСКАЗКИ

С помощью метода **AbstractQuery::setHint($name, $value)** в гидраторы и парсер запроса можно передавать подсказки (hints). Сейчас в основном существуют внтуренние подсказки, которые не используются пользователями библиотеки. Однако, следующие несколько можно применять:

* Query::HINT\_FORCE\_PARTIAL_LOAD – позволяет производить гидрацию даже если из базы данных были получены не все столбцы. Этот хинт помогает снизить потребление памяти для больших массивов данных. В Doctrine нет функционала для неявной перезагрузки таких данных. Чтобы заново подгрузить такие объекты из базы данных их нужно передать методу EntityManager::refresh().
* Query::HINT_REFRESH – этот хинт используется внутри метода EntityManager::refresh(), но также может быть использован и в обычном режиме. Как он работает: когда вы загружаете из БД данные сущности, которая уже находится под управлением UnitOfWork, то поля этой сущности будут обновлены. В обычном режиме предпочтение отдается данным существующей сущности, а результирующий набор отклоняется.
* Query::HINT\_CUSTOM\_TREE_WALKERS – Массив дополнительных эклемпляров Doctrine\ORM\Query\TreeWalker, подключаемых к процессу синтаксического разбора DQL запроса.

### 13.7.6.4. КЕШ ЗАПРОСОВ (ТОЛЬКО ДЛЯ DQL ЗАПРОСОВ)

Думаю всем очевидно, что разбор DQL запросов и последующая их трансформация в SQL несут в себе ряд издержек по сравнению с выполнением обычных SQL запросов. Поэтому мы существует специальный кеш, где хранится результат синтакцсического разбора каждого DQL запроса. Если использовать wildcards  в запросах, то синтаксический анализ можно вообще свести на нет – все будет браться из кеша.

Каждому экземпляру _Doctrine\ORM\Query_ драйвер кеша запросов передается из экземпляра _Doctrine\ORM\Configuration instance to each Doctrine\ORM\Query_, кроме того он включен по умолчанию. Так что обычно не стоит заморачиваться с опциями этого кеша, однако, если все же захотите, то следующие методы помогут вам:

* Query::setQueryCacheDriver($driver) – позволяет установить экземпляр драйвера кеша.
* Query::setQueryCacheLifeTime($seconds = 3600) – устанавливает время жизни кеша.
* Query::expireQueryCache($bool) – если установлен в TRUE принудительно инвалидирует кеш, отключая его.
* Query::getExpireQueryCache()
* Query::getQueryCacheDriver()
* Query::getQueryCacheLifeTime()

### 13.7.6.5. ПЕРВЫЙ И МАКСИМАЛЬНЫЙ ЭЛЕМЕНТЫ В РЕЗУЛЬТИРУЮЩЕМ НАБОРЕ (ТОЛЬКО ДЛЯ DQL)

Можно делать срез по результататм DQL запросов (limit и offset):

* Query::setMaxResults($maxResults)
* Query::setFirstResult($offset)

> Если в запросе присутствует подключаемая fetch-joined коллекция, вышеприведенные методы будут работать не так как ожидается. setMaxResults просто ограничивает число строк результата, в то время как при использовании fetch-joined коллекций одна и та же ведущая сущность может появляться в различных строках, финальный результат после гидрации будет меньше заданного числа строк.

### 13.7.6.6. ВРЕМЕННОЕ ИЗМЕНЕНИЕ РЕЖИМА ВЫБОРКИ В DQL

Обычно все связи помечены ка к lazy или extra lazy, однако в некоторых случаях из за высокой стоимость операции JOIN не нужно включать в результирующий набор остальные сущности через fetch join. Поэтому такие связи Many-To-One или One-To-One можно пометить соответствующим образом для пакетной обработки с помощью конструкции WHERE .. IN.
```php
<?php  
$query = $em->createQuery("SELECT u FROM MyProject\User u");  
$query->setFetchMode("MyProject\User", "address", "EAGER");  
$query->execute();
```
Допустим в БД лежат 10 пользователей и соответствующие им адреса, тогда запрос будет выглядеть так:
```sql
SELECT * FROM users;  
SELECT * FROM address WHERE id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
```
13.8. EBNF
==========

Следующая контекстно-свободная грамматика, представленная в форме EBNF описывает язык DQL. К ней можно обращаться если вам не очевидны те или иные стороны DQL или непонятен синтаксис того или иного запроса.

**Прим. пер.: Дальнейший материал является чисто справочным и я не вижу смысла переводить (на самом деле мне лень), тут и так все ясно.**

13.8.1. Document syntax:
------------------------

* non-terminals begin with an upper case character
* terminals begin with a lower case character
* parentheses (…) are used for grouping
* square brackets \[...\] are used for defining an optional part, e.g. zero or one time
* curly brackets {…} are used for repetition, e.g. zero or more times
* double quotation marks ”…” define a terminal string a vertical bar | represents an alternative

13.8.2. Terminals
-----------------

* identifier (name, email, …)
* string (‘foo’, ‘bar’’s house’, ‘%ninja%’, …)
* char (‘/’, ‘\’, ‘ ‘, …)
* integer (-1, 0, 1, 34, …)
* float (-0.23, 0.007, 1.245342E+8, …)
* boolean (false, true)

13.8.3. Query Language
----------------------
```
QueryLanguage ::= SelectStatement | UpdateStatement | DeleteStatement
```
13.8.4. Statements
------------------
```
SelectStatement ::= SelectClause FromClause \[WhereClause\] \[GroupByClause\] \[HavingClause\] \[OrderByClause\]  
UpdateStatement ::= UpdateClause \[WhereClause\]  
DeleteStatement ::= DeleteClause \[WhereClause\]
```
13.8.5. Identifiers
-------------------
```
/\* Alias Identification usage (the "u" of "u.name") */  
IdentificationVariable ::= identifier

/\* Alias Identification declaration (the "u" of "FROM User u") */  
AliasIdentificationVariable :: = identifier

/\* identifier that must be a class name (the "User" of "FROM User u") */  
AbstractSchemaName ::= identifier

/\* identifier that must be a field (the "name" of "u.name") */  
/\* This is responsible to know if the field exists in Object, no matter if it's a relation or a simple field */  
FieldIdentificationVariable ::= identifier

/\* identifier that must be a collection-valued association field (to-many) (the "Phonenumbers" of "u.Phonenumbers") */  
CollectionValuedAssociationField ::= FieldIdentificationVariable

/\* identifier that must be a single-valued association field (to-one) (the "Group" of "u.Group") */  
SingleValuedAssociationField ::= FieldIdentificationVariable

/\* identifier that must be an embedded class state field (for the future) */  
EmbeddedClassStateField ::= FieldIdentificationVariable

/\* identifier that must be a simple state field (name, email, ...) (the "name" of "u.name") */  
/\* The difference between this and FieldIdentificationVariable is only semantical, because it points to a single field (not mapping to a relation) */  
SimpleStateField ::= FieldIdentificationVariable

/\* Alias ResultVariable declaration (the "total" of "COUNT(*) AS total") */  
AliasResultVariable = identifier

/\* ResultVariable identifier usage of mapped field aliases (the "total" of "COUNT(*) AS total") */  
ResultVariable = identifier
```
13.8.6. Path Expressions
------------------------
```
/\* "u.Group" or "u.Phonenumbers" declarations */  
JoinAssociationPathExpression             ::= IdentificationVariable "." (CollectionValuedAssociationField | SingleValuedAssociationField)

/\* "u.Group" or "u.Phonenumbers" usages */  
AssociationPathExpression                 ::= CollectionValuedPathExpression | SingleValuedAssociationPathExpression

/\* "u.name" or "u.Group" */  
SingleValuedPathExpression                ::= StateFieldPathExpression | SingleValuedAssociationPathExpression

/\* "u.name" or "u.Group.name" */  
StateFieldPathExpression                  ::= IdentificationVariable "." StateField | SingleValuedAssociationPathExpression "." StateField

/\* "u.Group" */  
SingleValuedAssociationPathExpression     ::= IdentificationVariable "." SingleValuedAssociationField

/\* "u.Group.Permissions" */  
CollectionValuedPathExpression            ::= IdentificationVariable "." {SingleValuedAssociationField "."}* CollectionValuedAssociationField

/\* "name" */  
StateField                                ::= {EmbeddedClassStateField "."}* SimpleStateField

/\* "u.name" or "u.address.zip" (address = EmbeddedClassStateField) */  
SimpleStateFieldPathExpression            ::= IdentificationVariable "." StateField
```
13.8.7. Clauses
---------------
```
SelectClause        ::= "SELECT" \["DISTINCT"\] SelectExpression {"," SelectExpression}*  
SimpleSelectClause  ::= "SELECT" \["DISTINCT"\] SimpleSelectExpression  
UpdateClause        ::= "UPDATE" AbstractSchemaName \["AS"\] AliasIdentificationVariable "SET" UpdateItem {"," UpdateItem}*  
DeleteClause        ::= "DELETE" \["FROM"\] AbstractSchemaName \["AS"\] AliasIdentificationVariable  
FromClause          ::= "FROM" IdentificationVariableDeclaration {"," IdentificationVariableDeclaration}*  
SubselectFromClause ::= "FROM" SubselectIdentificationVariableDeclaration {"," SubselectIdentificationVariableDeclaration}*  
WhereClause         ::= "WHERE" ConditionalExpression  
HavingClause        ::= "HAVING" ConditionalExpression  
GroupByClause       ::= "GROUP" "BY" GroupByItem {"," GroupByItem}*  
OrderByClause       ::= "ORDER" "BY" OrderByItem {"," OrderByItem}*  
Subselect           ::= SimpleSelectClause SubselectFromClause \[WhereClause\] \[GroupByClause\] \[HavingClause\] \[OrderByClause\]
```
13.8.8. Items
-------------
```
UpdateItem  ::= IdentificationVariable "." (StateField | SingleValuedAssociationField) "=" NewValue  
OrderByItem ::= (ResultVariable | SingleValuedPathExpression) \["ASC" | "DESC"\]  
GroupByItem ::= IdentificationVariable | SingleValuedPathExpression  
NewValue    ::= ScalarExpression | SimpleEntityExpression | "NULL"
```
13.8.9. From, Join and Index by
-------------------------------
```
IdentificationVariableDeclaration          ::= RangeVariableDeclaration \[IndexBy\] {JoinVariableDeclaration}*  
SubselectIdentificationVariableDeclaration ::= IdentificationVariableDeclaration | (AssociationPathExpression \["AS"\] AliasIdentificationVariable)  
JoinVariableDeclaration                    ::= Join \[IndexBy\]  
RangeVariableDeclaration                   ::= AbstractSchemaName \["AS"\] AliasIdentificationVariable  
Join                                       ::= \["LEFT" \["OUTER"\] | "INNER"\] "JOIN" JoinAssociationPathExpression  
                                               \["AS"\] AliasIdentificationVariable \["WITH" ConditionalExpression\]  
IndexBy                                    ::= "INDEX" "BY" SimpleStateFieldPathExpression
```
13.8.10. Select Expressions
---------------------------
```
SelectExpression        ::= IdentificationVariable | PartialObjectExpression | (AggregateExpression | "(" Subselect ")"  | FunctionDeclaration | ScalarExpression) \[\["AS"\] AliasResultVariable\]  
SimpleSelectExpression  ::= ScalarExpression | IdentificationVariable |  
                            (AggregateExpression \[\["AS"\] AliasResultVariable\])  
PartialObjectExpression ::= "PARTIAL" IdentificationVariable "." PartialFieldSet  
PartialFieldSet         ::= "{" SimpleStateField {"," SimpleStateField}* "}"
```
13.8.11. Conditional Expressions
--------------------------------
```
ConditionalExpression       ::= ConditionalTerm {"OR" ConditionalTerm}*  
ConditionalTerm             ::= ConditionalFactor {"AND" ConditionalFactor}*  
ConditionalFactor           ::= \["NOT"\] ConditionalPrimary  
ConditionalPrimary          ::= SimpleConditionalExpression | "(" ConditionalExpression ")"  
SimpleConditionalExpression ::= ComparisonExpression | BetweenExpression | LikeExpression |  
                                InExpression | NullComparisonExpression | ExistsExpression |  
                                EmptyCollectionComparisonExpression | CollectionMemberExpression |  
                                InstanceOfExpression
```
13.8.12. Collection Expressions
-------------------------------
```
EmptyCollectionComparisonExpression ::= CollectionValuedPathExpression "IS" \["NOT"\] "EMPTY"  
CollectionMemberExpression          ::= EntityExpression \["NOT"\] "MEMBER" \["OF"\] CollectionValuedPathExpression
```
13.8.13. Literal Values
-----------------------
```
Literal     ::= string | char | integer | float | boolean  
InParameter ::= Literal | InputParameter
```
13.8.14. Input Parameter
------------------------
```
InputParameter      ::= PositionalParameter | NamedParameter  
PositionalParameter ::= "?" integer  
NamedParameter      ::= ":" string
```
13.8.15. Arithmetic Expressions
-------------------------------
```
ArithmeticExpression       ::= SimpleArithmeticExpression | "(" Subselect ")"  
SimpleArithmeticExpression ::= ArithmeticTerm {("+" | "-") ArithmeticTerm}*  
ArithmeticTerm             ::= ArithmeticFactor {("*" | "/") ArithmeticFactor}*  
ArithmeticFactor           ::= \[("+" | "-")\] ArithmeticPrimary  
ArithmeticPrimary          ::= SingleValuedPathExpression | Literal | "(" SimpleArithmeticExpression ")"  
                               | FunctionsReturningNumerics | AggregateExpression | FunctionsReturningStrings  
                               | FunctionsReturningDatetime | IdentificationVariable | InputParameter | CaseExpression
```
13.8.16. Scalar and Type Expressions
------------------------------------
```
ScalarExpression       ::= SimpleArithmeticExpression | StringPrimary | DateTimePrimary | StateFieldPathExpression  
                           BooleanPrimary | EntityTypeExpression | CaseExpression  
StringExpression       ::= StringPrimary | "(" Subselect ")"  
StringPrimary          ::= StateFieldPathExpression | string | InputParameter | FunctionsReturningStrings | AggregateExpression | CaseExpression  
BooleanExpression      ::= BooleanPrimary | "(" Subselect ")"  
BooleanPrimary         ::= StateFieldPathExpression | boolean | InputParameter  
EntityExpression       ::= SingleValuedAssociationPathExpression | SimpleEntityExpression  
SimpleEntityExpression ::= IdentificationVariable | InputParameter  
DatetimeExpression     ::= DatetimePrimary | "(" Subselect ")"  
DatetimePrimary        ::= StateFieldPathExpression | InputParameter | FunctionsReturningDatetime | AggregateExpression
```
> Parts of CASE expressions are not yet implemented.

13.8.17. Aggregate Expressions
------------------------------

AggregateExpression ::= ("AVG" | "MAX" | "MIN" | "SUM") "(" \["DISTINCT"\] StateFieldPathExpression ")" |  
                        "COUNT" "(" \["DISTINCT"\] (IdentificationVariable | SingleValuedPathExpression) ")"

13.8.18. Условия
----------------
```
CaseExpression        ::= GeneralCaseExpression | SimpleCaseExpression | CoalesceExpression | NullifExpression  
GeneralCaseExpression ::= "CASE" WhenClause {WhenClause}* "ELSE" ScalarExpression "END"  
WhenClause            ::= "WHEN" ConditionalExpression "THEN" ScalarExpression  
SimpleCaseExpression  ::= "CASE" CaseOperand SimpleWhenClause {SimpleWhenClause}* "ELSE" ScalarExpression "END"  
CaseOperand           ::= StateFieldPathExpression | TypeDiscriminator  
SimpleWhenClause      ::= "WHEN" ScalarExpression "THEN" ScalarExpression  
CoalesceExpression    ::= "COALESCE" "(" ScalarExpression {"," ScalarExpression}* ")"  
NullifExpression      ::= "NULLIF" "(" ScalarExpression "," ScalarExpression ")"
```
13.8.19. Другие выражения
-------------------------
```
QUANTIFIED/BETWEEN/COMPARISON/LIKE/NULL/EXISTS  
QuantifiedExpression     ::= ("ALL" | "ANY" | "SOME") "(" Subselect ")"  
BetweenExpression        ::= ArithmeticExpression \["NOT"\] "BETWEEN" ArithmeticExpression "AND" ArithmeticExpression  
ComparisonExpression     ::= ArithmeticExpression ComparisonOperator ( QuantifiedExpression | ArithmeticExpression )  
InExpression             ::= StateFieldPathExpression \["NOT"\] "IN" "(" (InParameter {"," InParameter}* | Subselect) ")"  
InstanceOfExpression     ::= IdentificationVariable \["NOT"\] "INSTANCE" \["OF"\] (InstanceOfParameter | "(" InstanceOfParameter {"," InstanceOfParameter}* ")")  
InstanceOfParameter      ::= AbstractSchemaName | InputParameter  
LikeExpression           ::= StringExpression \["NOT"\] "LIKE" string \["ESCAPE" char\]  
NullComparisonExpression ::= (SingleValuedPathExpression | InputParameter) "IS" \["NOT"\] "NULL"  
ExistsExpression         ::= \["NOT"\] "EXISTS" "(" Subselect ")"  
ComparisonOperator       ::= "=" | "&lt;" | "<=" | "<&gt;" | ">" | ">=" | "!="
```
13.8.20. Функции
----------------
```code
FunctionDeclaration ::= FunctionsReturningStrings | FunctionsReturningNumerics | FunctionsReturningDateTime  
  
FunctionsReturningNumerics ::=  
        "LENGTH" "(" StringPrimary ")" |  
        "LOCATE" "(" StringPrimary "," StringPrimary \["," SimpleArithmeticExpression\]")" |  
        "ABS" "(" SimpleArithmeticExpression ")" | "SQRT" "(" SimpleArithmeticExpression ")" |  
        "MOD" "(" SimpleArithmeticExpression "," SimpleArithmeticExpression ")" |  
        "SIZE" "(" CollectionValuedPathExpression ")"  
  
FunctionsReturningDateTime ::= "CURRENT\_DATE" | "CURRENT\_TIME" | "CURRENT_TIMESTAMP"  
  
FunctionsReturningStrings ::=  
        "CONCAT" "(" StringPrimary "," StringPrimary ")" |  
        "SUBSTRING" "(" StringPrimary "," SimpleArithmeticExpression "," SimpleArithmeticExpression ")" |  
        "TRIM" "(" \[\["LEADING" | "TRAILING" | "BOTH"\] \[char\] "FROM"\] StringPrimary ")" |  
        "LOWER" "(" StringPrimary ")" |  
        "UPPER" "(" StringPrimary ")"
```
