Нативный SQL
--

Итак, Доктрина умеет выполнять самый настоящий SQL, а затем создавать из результата такого запроса объектную модель. Это очень круто. С помощью класса NativeQuery можно выполнять низкоуровневые запросы SELECT, и отображать их результат на необходимые объекты. Описание того, как именно будет произведено отображение представлено классом ResultSetMapping. Она описывает соответствие полей базы данных полям объекта. Что это дает: можно использовать супер-оптимизированные запросы или хранимые процедуры и при этом не терять преимуществ ORM. Одна из красивейших возможностей Doctrine.

> Запросы DELETE, UPDATE or INSERT использовать с помощью NativeSQL API не получится. Для этого подойдет метод**EntityManager#getConnection()**, он вернет ссылку на объект подключение к базе данных, с помощью нее можно вызвать метод executeUpdate().

Содержание

* [15.1. Класс NativeQuery](native-sql.md#151_NativeQuery)
* [15.2. The ResultSetMapping](native-sql.md#152_The_ResultSetMapping)
    * [15.2.1. Entity results](native-sql.md#1521_Entity_results)
    * [15.2.2. Joined entity results](native-sql.md#1522_Joined_entity_results)
    * [15.2.3. Field results](native-sql.md#1523_Field_results)
    * [15.2.4. Scalar results](native-sql.md#1524_Scalar_results)
    * [15.2.6. Столбец дискриминатора](native-sql.md#1526)
    * [15.2.7. Примеры](native-sql.md#1527)
* [15.3. ResultSetMappingBuilder](native-sql.md#153_ResultSetMappingBuilder)

15.1. Класс NativeQuery
=======================

Экземпляр NativeQuery создается с помощью метода **EntityManager#createNativeQuery($sql, $resultSetMapping)**. Он принимает параметра: текста запроса и объект **ResultSetMapping**, который описывает правила отображения результатов.

После создания экземпляра **NativeQuery**, к нему можно привязать параметры, а затем выполнить.

15.2. The ResultSetMapping
==========================

Понимание класса ResultSetMapping является ключ к успешной работе с NativeQuery. Результат запроса в Doctrine может содержать следующие компоненты:

* **Entity results**. Корневые элементы в запросе.
* **Joined entity results**. Сущности из связей от вышеприведенных.
* **Field results**. Колонка, представляющая собой поле сущности. Она всегда связана или с **entity result** или **joined entity result**.
* **Scalar results**. Обычные скалярные значения, они будут находится в каждой строке результата. Если результирующий набор содержит сущности, то после добавления к нему скалярных значений он станет смешанным **(mixed)**.
* **Meta results**. Колонки, содержащие мета-информацию, такую как внешние ключи или столбцы дискриминатора. При запросе объектов (getResult()) все мета-столбцы корневых и связанных сущностей должны быть представлены в SQL запросе и соответственно отображены с помощью **ResultSetMapping#addMetaResult**.

**(Далее по тексту эти термины приведены как есть без перевода – прим пер.)**

> Не будет сюрпризом, что при создании DQL запросов Doctrine внутри себя как раз и использует **ResultSetMapping**. Когда запрос будет разобран синтаксическим анализаторов и преобразован в SQL, Doctrine заполнит ResultSetMapping информацией о том, как запрос должен быть обработан при гидрации.

Ниже будет подробно рассмотрен каждый из описанных выше типов.

15.2.1. Entity results
----------------------

**Entity result** описывает тип сущности, которая будет корневым элементом в результате запроса. Entity result можно добавить с помощью **ResultSetMapping#addEntityResult()**. Сигнатура метода выглядит следующим образом:
```php
<?php  
/**  
 \* Добавляет entity result к текущему ResultSetMapping.  
 *  
 \* @param string $class Имя класса сущности.  
 \* @param string $alias Синоним для класса. Должен быть уникальным среди всех entity  
 \*                      results или joined entity results в данном ResultSetMapping.  
 */  
public function addEntityResult($class, $alias)
```
Первый параметр это полное имя класса. Второй — синоним, который должен быть уникальным в рамках ResultSetMapping. Этот синоним используется для прикрепления **field results** к соответствующему **entity result**. Это аналоги идентификационной переменной, используемой в DQL в качестве алиаса для классов или связей.

Но одного entity result недостаточно для формирования корректного ResultSetMapping. Как **entity result** так и **joined entity result**всегда требуют дополнительного набор **field results**, который мы вскоре рассмотрим.

15.2.2. Joined entity results
-----------------------------

Этот тип результата описывает связь, в результате запроса она будет связана собственно с элементом **entity result**. Этот типа можно добавить с помощью метода **ResultSetMapping#addJoinedEntityResult()**. Сигнатура методы выглядит так:
```php
<?php  
/**  
 \* Добавляет joined entity result.  
 *  
 \* @param string $class Имя класса данной joined entity.  
 \* @param string $alias Алиас.  
 \* @param string $parentAlias Алиас для родительской entity result.  
 \* @param object $relation Имя связи, соединяющей родительский entity result с joined entity result.  
 */  
public function addJoinedEntityResult($class, $alias, $parentAlias, $relation)
```
Первый параметр это просто имя класса связанной сущности. Второй — уникальный синоним, он будет нужен для подключения**field results**. Третий параметр это алиас родительской **entity result** для данной связи. Последним параметром задается имя поля родительского **entity result**, которое и будет являться ссылкой на связь.

15.2.3. Field results
---------------------

Тут все просто: **field result** описывает какому полю сущности соответствует тот или иной столбец результата SQL запроса. Таким образом, этот тип связан с **entity results**. Добавляется он с помощью метода **ResultSetMapping#addFieldResult()**.  Сигнатурам метода:
```php
<?php  
/**  
 \* Добавляет field result к entity result или joined entity result.  
 *  
 \* @param string $alias Алиас entity result или joined entity result, к которому будет добавлено поле.  
 \* @param string $columnName Имя колонки в результирующем набора SQL запроса.  
 \* @param string $fieldName Имя поле сущности.  
 */  
public function addFieldResult($alias, $columnName, $fieldName)
```
Первый параметр это алиаса для **entity result**, к которому будет привязано данный **field result**. Второй параметр это имя столбца из SQL запроса. Имейте ввиду, что имя столбца зависит от регистра. Последний параметр это имя поля, которому и будет назначено соответствие.

15.2.4. Scalar results
----------------------

Тип scalar result описывает соответствие колонки из SQL запроса скалярному значению в результате Doctrine. Обычно скалярные результаты используются для хранения результатов агрегатных функций, тем не менее сюда можно назначить абсолютно любой столбец. Добавляется с помощью метода **ResultSetMapping#addScalarResult()**:
```php
<?php  
/**  
 \* Добавляет соответствие для scalar result.  
 *  
 \* @param string $columnName Имя колонки в результирующем наборе SQL запроса.  
 \* @param string $alias Синоним под которым значение этой колонки будет хранится в итоговом результирующем наборе.  
 */  
public function addScalarResult($columnName, $alias)
```
15.2.5. Meta results

Meta result описывает единственный столбец в результирующем наборе SQL запроса, который может быть внешним ключом или столбцом дискриминатора. Эти колонки имеют фундаментальное значение для Doctrine, т.к. с их помощью происходит создание объектов из результатов SQL запроса (гидрация, мать ее так.). Для добавления используется метод**ResultSetMapping#addMetaResult()**, имеющий следующую сигнатуру:
```php
<?php  
/**  
 \* Добавляет внешний ключ или столбец дискриминатора.  
 *  
 \* @param string $alias  
 \* @param string $columnAlias  
 \* @param string $columnName  
 */  
public function addMetaResult($alias, $columnAlias, $columnName)
```
Первый параметр это алиас **entity result**, которой этот столбец соответствует. Столбец с мета-информацией (внешний ключ или столбец дискриминатора) всегда указывает на entity result. Второй параметр это имя или алиас столбца из SQL запроса. Третий параметр это имя используемой для отображения колонки.

15.2.6. Столбец дискриминатора
------------------------------

При подключении дерева наследования Doctrine нужно дать подсказку по поводу того, какая meta-column в этом дереве является столбцом дискриминатора.
```php
<?php  
  
/**  
 \* Sets a discriminator column for an entity result or joined entity result.  
 \* The discriminator column will be used to determine the concrete class name to  
 \* instantiate.  
 *  
 \* @param string $alias The alias of the entity result or joined entity result the discriminator  
 \*                      column should be used for.  
 \* @param string $discrColumn The name of the discriminator column in the SQL result set.  
 */  
public function setDiscriminatorColumn($alias, $discrColumn)
```
15.2.7. Примеры
---------------

Чтобы лучше понять как работает **ResultSetMapping** давайте разберем несколько примеров.

Первый пример описывает отображение для одной сущности.
```php
<?php  
// Эквивалентный DQL запрос: "select u from User u where u.name=?1"  
// User не имеет связей.  
$rsm = new ResultSetMapping;  
$rsm->addEntityResult('User', 'u');  
$rsm->addFieldResult('u', 'id', 'id');  
$rsm->addFieldResult('u', 'name', 'name');  
  
$query = $this->_em->createNativeQuery('SELECT id, name FROM users WHERE name = ?', $rsm);  
$query->setParameter(1, 'romanb');  
  
$users = $query->getResult();
```
Результат запроса будет выглядеть следующим образом:
```
array(  
    \[0\] => User (Object)  
)
```
Обратите внимание, если сущность имеет больше полей, чем представлено в примере выше, то сформированный объект будет являться неполным. Что такое неполные объекты будет описано в 17 главе. Строка, передаваемая методу **createNativeQuery**есть не что иное как нативный SQL запрос, он будет выполнен как есть без каких-либо преобразований со стороны Doctrine.

В предыдущем примере User не имел никаких связей, поэтому таблица была задействована без внешний ключей. В следующем примере предполагается, что User имеет одностороннюю или двустороннюю связь “один к одному” с сущностью CmsAddress, где User является владеющей стороной с внешним ключом.
```php
<?php  
// Эквивалентный DQL Запрос: "select u from User u where u.name=?1"  
// User владеет связью Address, но Address не будет загружен запросом.  
$rsm = new ResultSetMapping;  
$rsm->addEntityResult('User', 'u');  
$rsm->addFieldResult('u', 'id', 'id');  
$rsm->addFieldResult('u', 'name', 'name');  
$rsm->addMetaResult('u', 'address\_id', 'address\_id');  
  
$query = $this->\_em->createNativeQuery('SELECT id, name, address\_id FROM users WHERE name = ?', $rsm);  
$query->setParameter(1, 'romanb');  
  
$users = $query->getResult();
```
Внешние ключи используются Доктриной для ленивой загрузки. П вышеприведенном примере у каждого объекта User в результирующем набор будет свой прокси-объект представляющий Address (ключ address_id). И при запросе этого прокси произойдет фактическая загрузка объекта, представленного этим ключом.

Следовательно, для fetch-joined связи не обязательно иметь внешний ключи в SQL запросе, это нужно только для lazy-loading связей.
```php
<?php  
// Эквивалентный DQL запрос: "select u from User u join u.address a WHERE u.name = ?1"  
// User владеет связью Address, которая будет загружена запросом.  
$rsm = new ResultSetMapping;  
$rsm->addEntityResult('User', 'u');  
$rsm->addFieldResult('u', 'id', 'id');  
$rsm->addFieldResult('u', 'name', 'name');  
$rsm->addJoinedEntityResult('Address' , 'a', 'u', 'address');  
$rsm->addFieldResult('a', 'address_id', 'id');  
$rsm->addFieldResult('a', 'street', 'street');  
$rsm->addFieldResult('a', 'city', 'city');  
  
$sql = 'SELECT u.id, u.name, a.id AS address_id, a.street, a.city FROM users u ' .  
       'INNER JOIN address a ON u.address_id = a.id WHERE u.name = ?';  
$query = $this->_em->createNativeQuery($sql, $rsm);  
$query->setParameter(1, 'romanb');  
  
$users = $query->getResult();
```
В этом примере вложенная сущность Address регистрируется с помощью метода **ResultSetMapping#addJoinedEntityResult**, который уведомляет Doctrine о том, что эта сущность не будет находится на нижнем уровне в наборе, а будет представлена как joined-сущность и располагаться где-то внутри графа объектов. В этом случае третьим параметром мы указываем алиас  ‘u’ и “address” четвертым параметром, это означает, что Address будет соответствовать полю User::$address property.

Если связанная сущность является частью иерархии наследования, которой нужен столбец дискриминатора, то этот столбец должен присутствовать в результирующем наборе в виде мета-столбца. Эта ситуация представлена в следующем примере, здесь предполагается, что у User есть один или несколько подклассов и для отображения иерархии используется Class Table Inheritance или Single Table Inheritance (в обоих случаях используется столбец дискриминатора).
```php
<?php  
// Эквивалентный DQL запрос: "select u from User u where u.name=?1"  
// User является базовым классом. У User нет связей.  
$rsm = new ResultSetMapping;  
$rsm->addEntityResult('User', 'u');  
$rsm->addFieldResult('u', 'id', 'id');  
$rsm->addFieldResult('u', 'name', 'name');  
$rsm->addMetaResult('u', 'discr', 'discr'); // discriminator column  
$rsm->setDiscriminatorColumn('u', 'discr');  
  
$query = $this->_em->createNativeQuery('SELECT id, name, discr FROM users WHERE name = ?', $rsm);  
$query->setParameter(1, 'romanb');  
  
$users = $query->getResult();
```
В случае Class Table Inheritance пример выше породит неполные объекты в случае если любые объекты в результирующем набор являются подтипами User. При использовании DQL, Doctrine автоматически подключает необходимые связи, при использовании нативного SQL это ваша ответственность.

15.3. ResultSetMappingBuilder
=============================

У запросов, создаваемый с помощью нативного SQL есть минусы. Основной минус заключается в том, что при изменении имен колонок нужно будет править правила отображения. В DQL это происходит автоматически.

Чтобы избежать этих проблем есть специальный класс **ResultSetMappingBuilder**. Он позволяет добавить все колонки заданной сущности в правила отображения. Чтобы избежать конфликтов можно переименовать необходимые колонки как в примере ниже:
```php
<?php  
  
$sql = "SELECT u.id, u.name, a.id AS address_id, a.street, a.city " .  
       "FROM users u INNER JOIN address a ON u.address_id = a.id";  
  
$rsm = new ResultSetMappingBuilder($em);  
$rsm->addRootEntityFromClassMetadata('MyProject\\User', 'u');  
$rsm->addJoinedEntityFromClassMetadata('MyProject\\Address', 'a', 'u', 'address', array('id' => 'address_id'));
```
Для сущностей с множеством полей использование билдера весьма удобно. Он наследует класс **ResultSetMapping** и имеет ту же функциональностьl. В настоящий момент **ResultSetMappingBuilder** не умеет работать с наследованием классов сущностей.
