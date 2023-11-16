Пакетная обработка
--

Эта глава покажет как наиболее эффективно можно осуществлять массовую вставку, обновление и удаление данных в _Doctrine_. Основная проблема при работе с массовыми операциями это повышенное потребление памяти. Есть несколько стратегий, которые могут помочь вам в этом вопросе.

> ORM, вообще говоря, не предназначена для таких операций. Для этого в каждой СУБД есть свой собственный гораздо более эффективный механизм, так что если описанные стратегии вам не подойдут, мы рекомендуем воспользоваться инструментами конкретной СУБД, предназначенными для таких операций.

Содержание

* [12.1. Массовые вставки](batch-processing.md#121)
* [12.2. Массовые обновления](batch-processing.md#122)
    * [12.2.1. DQL UPDATE](batch-processing.md#1221_DQL_UPDATE)
    * [12.2.2. Итерация по результирующему набору](batch-processing.md#1222)
* [12.3. Массовое удаление](batch-processing.md#123)
    * [12.3.1. DQL DELETE](batch-processing.md#1231_DQL_DELETE)
    * [12.3.2. Итерация по результирующему набору](batch-processing.md#1232)
* [12.4. Iterating Large Results for Data-Processing](batch-processing.md#124_Iterating_Large_Results_for_Data-Processing)

12.1. Массовые вставки
======================

В _Doctrine_ массовые вставки лучше всего выполнять партиями, используя возможности транзакций _EntityManager_. Следующий пример показывает как вставлять 10000 объектов партиями по 20 штук. Для достижения оптимального результата имеет смысл поэкспериментировать с размером партии. Больший размер означает бо’льшие возможности для повторного использования внутренних операций, но и требует больше работы при операции **flush()**.
```php
<?php  
$batchSize = 20;  
for ($i = 1; $i <= 10000; ++$i) {  
    $user = new CmsUser;  
    $user->setStatus('user');  
    $user->setUsername('user' . $i);  
    $user->setName('Mr.Smith-' . $i);  
    $em->persist($user);  
    if (($i % $batchSize) == 0) {  
        $em->flush();  
        $em->clear(); // Detaches all objects from Doctrine!  
    }  
}
```
12.2. Массовые обновления
=========================

В _Doctrine_ есть два способа осуществления массовых обновлений.

12.2.1. DQL UPDATE
------------------

На сегодняшний день наиболее эффективным способом осуществления массовых обновлений является _DQL_ запрос _UPDATE:_
```php
<?php  
$q = $em->createQuery('update MyProject\\Model\\Manager m set m.salary = m.salary * 0.9');  
$numUpdated = $q->execute();
```
12.2.2. Итерация по результирующему набору
------------------------------------------

Другой подход заключается в использовании метода **Query#iterate()** для итерации по результирующему набору без необходимости загрузки полного набора данных в память. Следующий пример показывает как это можно сделать, комбинируя итерацию и пакетную обработку:
```php
<?php  
$batchSize = 20;  
$i = 0;  
$q = $em->createQuery('select u from MyProject\\Model\\User u');  
$iterableResult = $q->iterate();  
foreach($iterableResult AS $row) {  
    $user = $row\[0\];  
    $user->increaseCredit();  
    $user->calculateNewBonuses();  
    if (($i % $batchSize) == 0) {  
        $em->flush(); // Выполняет обновления  
        $em->clear(); // Отсоединяет все объекты от Doctrine  
    }  
    ++$i;  
}
```
> Нельзя осуществлять итерацию в запросах, которые подсоединяют связи-коллекции **(collection-valued association)**. Природа таких результируюзих наборов не подходит для инкрементой гидрации.
>
> Оригинал: **Iterating results is not possible with queries that fetch-join a collection-valued association. The nature of such SQL result sets is not suitable for incremental hydration.**

12.3. Массовое удаление
=======================

В _Doctrine_ есть два способа осуществления массовых удалений. Можно либо запустить одиночный _DQL_ запрос _DELETE_ или же проитерировать набор данных, удаляя каждый из элементов по отдельности.

12.3.1. DQL DELETE
------------------

На сегодняшний день наиболее эффективным способом для массовых удалений является _DQL _запрос _DELETE_:
```php
<?php  
$q = $em->createQuery('delete from MyProject\\Model\\Manager m where m.salary > 100000');  
$numDeleted = $q->execute();
```
12.3.2. Итерация по результирующему набору
------------------------------------------

Другой подход заключается в использовании метода **Query#iterate()** для итерации по результирующему набору без необходимости загрузки полного набора данных в память. Следующий пример показывает как это можно сделать, комбинируя итерацию и пакетную обработку:
```php
<?php  
$batchSize = 20;  
$i = 0;  
$q = $em->createQuery('select u from MyProject\\Model\\User u');  
$iterableResult = $q->iterate();  
while (($row = $iterableResult->next()) !== false) {  
    $em->remove($row\[0\]);  
    if (($i % $batchSize) == 0) {  
        $em->flush(); // Выполняет удаления  
        $em->clear(); // Отсоединяет все объекты от Doctrine  
    }  
    ++$i;  
}
```
> Нельзя осуществлять итерацию в запросах, которые подсоединяют связи-коллекции **(collection-valued association)**. Природа таких результируюзих наборов не подходит для инкрементой гидрации.
>
> Оригинал: **Iterating results is not possible with queries that fetch-join a collection-valued association. The nature of such SQL result sets is not suitable for incremental hydration.**

12.4. Iterating Large Results for Data-Processing
=================================================

Если не требуется делать _UPDATE_ или _DELETE_, то для итерации можно использовать метод **iterate()**. Экземпляр _IterableResult_, возвращаемый методом **$query->iterate()** определяет интерфейс _Iterator_, поэтому можно обрабатывать большой результирующий набор при минимальных затратах памяти:
```php
<?php  
$q = $this->_em->createQuery('select u from MyProject\\Model\\User u');  
$iterableResult = $q->iterate();  
foreach ($iterableResult AS $row) {  
    // Что-то делаем с данными в строке, $row\[0\] всегда является объектом  
  
    // Отсоединяем от Doctrine, так что сразу будет запущена сборка мусора  
    $this->_em->detach($row\[0\]);  
}
```
> Нельзя осуществлять итерацию в запросах, которые подсоединяют связи-коллекции **(collection-valued association)**. Природа таких результируюзих наборов не подходит для инкрементой гидрации.
>
> Оригинал: **Iterating results is not possible with queries that fetch-join a collection-valued association. The nature of such SQL result sets is not suitable for incremental hydration.**
