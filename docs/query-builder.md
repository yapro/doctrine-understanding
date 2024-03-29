QueryBuilder
--

QueryBuilder — это API, который позволяет в несколько шагов создать практически любой DQL запрос.

В составе QueryBuilder входит набор классов и методов для программного построения запросов и весьма гибкий API. Использовать ли построитель или писать запросы вручную решать вам.

Содержание

* [14.1. Создание объекта QueryBuilder](query-builder.md#141__QueryBuilder)
* [14.2. Работа с QueryBuilder](query-builder.md#142__QueryBuilder)
    * [14.2.1. Параметры](query-builder.md#1421)
    * [14.2.2. Ограничения](query-builder.md#1422)
    * [14.2.3. Выполнение запроса](query-builder.md#1423)
    * [14.2.4. Классы Expr*](query-builder.md#1424_Expr)
    * [14.2.5. Класс Expr](query-builder.md#1425_Expr)
    * [14.2.6. Вспомогательные методы](query-builder.md#1426)

14.1. Создание объекта QueryBuilder
===================================

Точно так же как вы создавали обычный запрос, создается и объект QueryBuilder. Сразу пример:
```php
<?php  
// $em — экземпляр EntityManager  
  
// Пример 1: создание экземпляра QueryBuilder  
$qb = $em->createQueryBuilder();
```
Экземпляр QueryBuilder имеет набор функций, названия которых говорят сами за себя. Так, например, можно узнать тип объекта QueryBuilder:
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Пример 2: retrieving type of QueryBuilder  
echo $qb->getType(); // Выведет: 0

Объект может иметь один из трех возможных типов:

* **QueryBuilder::SELECT**, which returns value 0
* **QueryBuilder::DELETE**, returning value 1
* **QueryBuilder::UPDATE**, which returns value 2
```
После того как запрос построен можно получить экземпляр менеджера сущностей, текст DQL запроса и сам объект этого запроса:
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Пример 3: получение экземпляра EntityManager  
$em = $qb->getEntityManager();  
  
// Пример 4: получение текста DQL запроса, представленного экземпляром QueryBuilder  
$dql = $qb->getDql();  
  
// Пример 5: получение экземпляра объекта Query  
$q = $qb->getQuery();
```
Для увеличения производительности в QueryBuilder используется кеш DQL. Любое вносимое в объект запроса изменение, которое может повлиять на текст результирующего запроса переводит QueryBuilder в состояние, которое мы называем**STATE_DIRTY**. Итого, любой экземпляр QueryBuilder может находиться в одном из двух состояний:

* **QueryBuilder::STATE_CLEAN** означает, что DQL запрос находится в актуальном состоянии, объект принимает это состояние сразу после создания
* **QueryBuilder::STATE_DIRTY** означает, что DQL запрос был изменен и должен быть перестроен

14.2. Работа с QueryBuilder
===========================

Все вспомогательные методы объекта QueryBuilder являются сокращениями для метода **add()**, который и отвечает за построение DQL запроса. Он принимает три параметра: **$dqlPartName**, **$dqlPart** и **$append** (по умолчанию равен _false_)

* **$dqlPartName**: Определяет место, в котором будет размещен **$dqlPart**. Возможные значения: select, from, where, groupBy, having, orderBy
* **$dqlPart**: Что должно быть размещено в **$dqlPartName**. Принимает строку или любой экземпляр _Doctrine\\ORM\\Query\\Expr\\*_
* **$append**: Необязательный аргумент (по умолчанию равен _false_) должен ли **$dqlPart** переопределять все заданные до этого элементы в **$dqlPartName**
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Пример 6: создание запроса "SELECT u FROM User u WHERE u.id = ? ORDER BY u.name ASC" с помощью QueryBuilder  
$qb->add('select', 'u')  
   ->add('from', 'User u')  
   ->add('where', 'u.id = ?1')  
   ->add('orderBy', 'u.name ASC');
```
14.2.1. Параметры
-----------------

В Doctrine есть возможность привязки параметров к объекту построителя, подобно тому как это делалось при ручном написании запросов (см. предыдущую главу). Можно использовать как строковые так и цифровые метки, хотя синтаксис их немного отличается. Так или иначе, нужно выбрать что-то одно: не допускается смешивать оба стиля. Привязать параметры можно следующим образом:
```php
<?php  
// $qb - экземпляр QueryBuilder  
  
// Пример 6: определяем запрос: "SELECT u FROM User u WHERE u.id = ? ORDER BY u.name ASC" с помощью QueryBuilder  
$qb->add('select', 'u')  
   ->add('from', 'User u')  
   ->add('where', 'u.id = ?1')  
   ->add('orderBy', 'u.name ASC');  
   ->setParameter(1, 100); // Устанавливает параметр ?1 в 100, т.о. будет запрошен пользователь, имеющий u.id = 100
```
Не обязательно использовать именно цифровые параметры, есть и другой способ:
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Пример 6: определяем запрос: "SELECT u FROM User u WHERE u.id = ? ORDER BY u.name ASC" с помощью QueryBuilder  
$qb->add('select', 'u')  
   ->add('from', 'User u')  
   ->add('where', 'u.id = :identifier')  
   ->add('orderBy', 'u.name ASC');  
   ->setParameter('identifier', 100); // Устанавливает параметр :identifier в 100, т.о. будет запрошен пользователь с u.id = 100
```
Обратите внимание на то, что имена цифровых параметров начинаются со знака `**?**`, за которым следует число, а именованных со знака `**:**`, за которым следует имя.

Для привязки сразу нескольких параметров можно использовать метод **setParameters()**:
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Query here...  
$qb->setParameters(array(1 => 'value for ?1', 2 => 'value for ?2'));
```
Привязанные ранее параметры можно получить с помощью методов “**getParameter()**” или “**getParameters()**”:
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Первый способ  
$params = $qb->getParameters(array(1, 2));  
// Второй способ  
$param  = array($qb->getParameter(1), $qb->getParameter(2));
```
При попытка обращения к несуществующему параметру метод **getParameter()** вернет ~хуй~ NULL.

14.2.2. Ограничения
-------------------

Для наложения ограничений на результат запроса можно использовать методы, аналогичные используемым в объекте Query, который можно получит с помощью метода **EntityManager#createQuery()**.
```php
<?php  
// $qb - экземпляр QueryBuilder  
$offset = (int)$_GET\['offset'\];  
$limit = (int)$_GET\['limit'\];  
  
$qb->add('select', 'u')  
   ->add('from', 'User u')  
   ->add('orderBy', 'u.name ASC')  
   ->setFirstResult( $offset )  
   ->setMaxResults( $limit );
```
14.2.3. Выполнение запроса
--------------------------

QueryBuilder всего лишь строит объект, это вовсе не означает выполнение получившегося запроса. Кроме того, такие вещи как, например, подсказки нельзя задать непосредственно в самом билдере, для этого сначала нужно получить объект Query:
```php
<?php  
// $qb — экземпляр QueryBuilder  
$query = $qb->getQuery();  
  
// Установка дополнительных параметров  
$query->setQueryHint('foo', 'bar');  
$query->useResultCache('my\_cache\_id');  
  
// Выполнение запроса  
$result = $query->getResult();  
$single = $query->getSingleResult();  
$array = $query->getArrayResult();  
$scalar = $query->getScalarResult();  
$singleScalar = $query->getSingleScalarResult();
```
14.2.4. Классы Expr*
--------------------

Когда вы вызываете метод add(), передавая ему строковой параметр, в действительность происходит создание экземпляра класса **Doctrine\\ORM\\Query\\Expr\\Expr\\***. Следующий пример демонстрирует это:
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Приер 7: задаем запрос: "SELECT u FROM User u WHERE u.id = ? ORDER BY u.name ASC" с помощью QueryBuilder, используя экземпляры Expr\\*  
$qb->add('select', new Expr\\Select(array('u')))  
   ->add('from', new Expr\\From('User', 'u'))  
   ->add('where', new Expr\\Comparison('u.id', '=', '?1'))  
   ->add('orderBy', new Expr\\OrderBy('u.name', 'ASC'));
```
Конечно, нифига не удобно строить запросы таким хитржопым способом. Поэтому для упрощения есть специальный вспомогательный класс Expr.

14.2.5. Класс Expr
------------------

Чтобы обойти различного рода проблемы при использовании метода **add()** в Doctrine есть вспомогательный класс для построения выражений — Expr, для этих целей он содержит ряд полезных методов:
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Пример 8: QueryBuilder-реализация запроса "SELECT u FROM User u WHERE u.id = ? OR u.nickname LIKE ? ORDER BY u.surname DESC" с использованием класса Expr  
$qb->add('select', new Expr\\Select(array('u')))  
   ->add('from', new Expr\\From('User', 'u'))  
   ->add('where', $qb->expr()->orX(  
       $qb->expr()->eq('u.id', '?1'),  
       $qb->expr()->like('u.nickname', '?2')  
   ))  
   ->add('orderBy', new Expr\\OrderBy('u.name', 'ASC'));
```
Да, выглядит это все еще громоздко, но в этом и есть основная фича класса Expr — дать возможность программно создавать условные выражения. Полный список методов этого класса приведен ниже:
```php
<?php  
class Expr  
{  
    /\*\* Условия **/  
  
    // Пример — $qb->expr()->andX($cond1 \[, $condN\])->add(...)->...  
    public function andX($x = null); // Возвращает экземпляр Expr\\AndX instance  
  
    // Пример — $qb->expr()->orX($cond1 \[, $condN\])->add(...)->...  
    public function orX($x = null); // Возвращает экземпляр Expr\\OrX instance  
  
    /\*\* Сравнение **/  
  
    // Пример — $qb->expr()->eq('u.id', '?1') => u.id = ?1  
    public function eq($x, $y); // Возвращает экземпляр Expr\\Comparison  
  
    // Пример — $qb->expr()->neq('u.id', '?1') => u.id <> ?1  
    public function neq($x, $y); // Возвращает экземпляр Expr\\Comparison  
  
    // Пример — $qb->expr()->lt('u.id', '?1') => u.id < ?1  
    public function lt($x, $y); // Возвращает экземпляр Expr\\Comparison  
  
    // Пример — $qb->expr()->lte('u.id', '?1') => u.id <= ?1  
    public function lte($x, $y); // Возвращает экземпляр Expr\\Comparison  
  
    // Пример — $qb->expr()->gt('u.id', '?1') => u.id > ?1  
    public function gt($x, $y); // Возвращает экземпляр Expr\\Comparison  
  
    // Пример — $qb->expr()->gte('u.id', '?1') => u.id >= ?1  
    public function gte($x, $y); // Возвращает экземпляр Expr\\Comparison  
  
    // Пример — $qb->expr()->isNull('u.id') => u.id IS NULL  
    public function isNull($x); // Возвращает строку  
  
    // Пример — $qb->expr()->isNotNull('u.id') => u.id IS NOT NULL  
    public function isNotNull($x); // Возвращает строку  
  
    /\*\* Арифметика **/  
  
    // Пример — $qb->expr()->prod('u.id', '2') => u.id * 2  
    public function prod($x, $y); // Возвращает экземпляр Expr\\Math  
  
    // Пример — $qb->expr()->diff('u.id', '2') => u.id - 2  
    public function diff($x, $y); // Возвращает экземпляр Expr\\Math  
  
    // Пример — $qb->expr()->sum('u.id', '2') => u.id + 2  
    public function sum($x, $y); // Возвращает экземпляр Expr\\Math  
  
    // Пример — $qb->expr()->quot('u.id', '2') => u.id / 2  
    public function quot($x, $y); // Возвращает экземпляр Expr\\Math  
  
    /\*\* Псевдо-функции **/  
  
    // Пример — $qb->expr()->exists($qb2->getDql())  
    public function exists($subquery); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->all($qb2->getDql())  
    public function all($subquery); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->some($qb2->getDql())  
    public function some($subquery); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->any($qb2->getDql())  
    public function any($subquery); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->not($qb->expr()->eq('u.id', '?1'))  
    public function not($restriction); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->in('u.id', array(1, 2, 3))  
    // Нельзя вставлять значения напрямую: $qb->expr()->in('value', array('stringvalue')), это приведет к выбросу исключения.  
    // Вместо этого используйте параметризацию: $qb->expr()->in('value', array('?1')), затем назначьте параметр ?1 (см. предыдущий раздел)  
    public function in($x, $y); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->notIn('u.id', '2')  
    public function notIn($x, $y); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->like('u.firstname', $qb->expr()->literal('Gui%'))  
    public function like($x, $y); // Возвращает экземпляр Expr\\Comparison  
  
    // Пример — $qb->expr()->between('u.id', '1', '10')  
    public function between($val, $x, $y); // Возвращает экземпляр Expr\\Func  
  
    /\*\* Функции **/  
  
    // Пример — $qb->expr()->trim('u.firstname')  
    public function trim($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->concat('u.firstname', $qb->expr()->concat(' ', 'u.lastname'))  
    public function concat($x, $y); // Возвращает экземпляр Expr\\Func  
  
    // Пример\- $qb->expr()->substr('u.firstname', 0, 1)  
    public function substr($x, $from, $len); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->lower('u.firstname')  
    public function lower($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->upper('u.firstname')  
    public function upper($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->length('u.firstname')  
    public function length($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->avg('u.age')  
    public function avg($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->max('u.age')  
    public function max($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->min('u.age')  
    public function min($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->abs('u.currentBalance')  
    public function abs($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->sqrt('u.currentBalance')  
    public function sqrt($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->count('u.firstname')  
    public function count($x); // Возвращает экземпляр Expr\\Func  
  
    // Пример — $qb->expr()->countDistinct('u.surname')  
    public function countDistinct($x); // Возвращает экземпляр Expr\\Func  
}
```
14.2.6. Вспомогательные методы
------------------------------

До текущего момента запросы создавались самым сложным низкоуровневым способом. Безусловно, когда дело касается оптимизации это полезно, но в большинстве случаев рекомендуется использовать готовые абстракции. Итак, чтобы сделать построение запросов еще проще можно воспользоваться преимуществом Helper-методов. Давайте рассмотрим их, ниже приведен Пример 6, переписанный с использованием хелперов класса QueryBuilder:
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Пример 9: создание запроса "SELECT u FROM User u WHERE u.id = ?1 ORDER BY u.name ASC" с использованием псмомогательных методов  
$qb->select('u')  
   ->from('User', 'u')  
   ->where('u.id = ?1')  
   ->orderBy('u.name', 'ASC');
```
Хелперы — это стандартный способ создания запросов. Вообще говоря, старайтесь избегать ручного написания запросов с помощью строк и не слишком вдохновляйтесь методами **$qb->expr()->***. Ниже приведен рефакторинг Примера 8 с использованием описанной парадигмы:
```php
<?php  
// $qb — экземпляр QueryBuilder  
  
// Пример 8: QueryBuilder-аналог запроса "SELECT u FROM User u WHERE u.id = ?1 OR u.nickname LIKE ?2 ORDER BY u.surname DESC" с использованием хелперов  
$qb->select(array('u')) // строка 'u' будет самостоятельно конвертирована в массив  
   ->from('User', 'u')  
   ->where($qb->expr()->orX(  
       $qb->expr()->eq('u.id', '?1'),  
       $qb->expr()->like('u.nickname', '?2')  
   ))  
   ->orderBy('u.surname', 'ASC'));
```
Полный список полезных вспомогательных методов класса QueryBuilder:
```php
<?php  
class QueryBuilder  
{  
    // Пример — $qb->select('u')  
    // Пример — $qb->select(array('u', 'p'))  
    // Пример — $qb->select($qb->expr()->select('u', 'p'))  
    public function select($select = null);  
  
    // Пример — $qb->delete('User', 'u')  
    public function delete($delete = null, $alias = null);  
  
    // Пример — $qb->update('Group', 'g')  
    public function update($update = null, $alias = null);  
  
    // Пример — $qb->set('u.firstName', $qb->expr()->literal('Arnold'))  
    // Пример — $qb->set('u.numChilds', 'u.numChilds + ?1')  
    // Пример — $qb->set('u.numChilds', $qb->expr()->sum('u.numChilds', '?1'))  
    public function set($key, $value);  
  
    // Пример — $qb->from('Phonenumber', 'p')  
    public function from($from, $alias = null);  
  
    // Пример — $qb->innerJoin('u.Group', 'g', Expr\\Join::WITH, $qb->expr()->eq('u.status_id', '?1'))  
    // Пример — $qb->innerJoin('u.Group', 'g', 'WITH', 'u.status = ?1')  
    public function innerJoin($join, $alias = null, $conditionType = null, $condition = null);  
  
    // Пример — $qb->leftJoin('u.Phonenumbers', 'p', Expr\\Join::WITH, $qb->expr()->eq('p.area_code', 55))  
    // Пример — $qb->leftJoin('u.Phonenumbers', 'p', 'WITH', 'p.area_code = 55')  
    public function leftJoin($join, $alias = null, $conditionType = null, $condition = null);  
  
    // Важно: ->where() затирает заданные ранее условия  
    //  
    // Пример — $qb->where('u.firstName = ?1', $qb->expr()->eq('u.surname', '?2'))  
    // Пример — $qb->where($qb->expr()->andX($qb->expr()->eq('u.firstName', '?1'), $qb->expr()->eq('u.surname', '?2')))  
    // Пример — $qb->where('u.firstName = ?1 AND u.surname = ?2')  
    public function where($where);  
  
    // Пример — $qb->andWhere($qb->expr()->orX($qb->expr()->lte('u.age', 40), 'u.numChild = 0'))  
    public function andWhere($where);  
  
    // Пример — $qb->orWhere($qb->expr()->between('u.id', 1, 10));  
    public function orWhere($where);  
  
    // Важно: -\> groupBy() затирает заданные ранее схемы группировки  
    //  
    // Пример — $qb->groupBy('u.id')  
    public function groupBy($groupBy);  
  
    // Пример — $qb->addGroupBy('g.name')  
    public function addGroupBy($groupBy);  
  
    // Важно: -\> having() также затирает все ранее заданные им условия  
    //  
    // Пример — $qb->having('u.salary >= ?1')  
    // Пример — $qb->having($qb->expr()->gte('u.salary', '?1'))  
    public function having($having);  
  
    // Пример\- $qb->andHaving($qb->expr()->gt($qb->expr()->count('u.numChild'), 0))  
    public function andHaving($having);  
  
    // Пример — $qb->orHaving($qb->expr()->lte('g.managerLevel', '100'))  
    public function orHaving($having);  
  
    // Важно: -\> orderBy() затирает ранее заданные правила сортировки  
    //  
    // Пример\- $qb->orderBy('u.surname', 'DESC')  
    public function orderBy($sort, $order = null);  
  
    // Пример\- $qb->addOrderBy('u.firstName')  
    public function addOrderBy($sort, $order = null); // По умолчанию $order = 'ASC'  
}
```
