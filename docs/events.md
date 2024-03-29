События
--

В _Doctrine 2_ имеется весьма удобная система событий, она идет как часть пакета _Common_.

Содержание

* [11.1. Система событий](events.md#111)
  * [11.1.1. Именование](events.md#1111)
* [11.2. События жизненного цикла](events.md#112)
* [11.3. Обратный вызов](events.md#113)
* [11.4. Обработка событий](events.md#114)
* [11.5. Реализация обработчиков событий](events.md#115)
  * [11.5.1. prePersist](events.md#1151_prePersist)
  * [11.5.2. preRemove](events.md#1152_preRemove)
  * [11.5.3. onFlush](events.md#1153_onFlush)
  * [11.5.4. preUpdate](events.md#1154_preUpdate)
  * [11.5.5. postUpdate, postRemove, postPersist](events.md#1155_postUpdate_postRemove_postPersist)
  * [11.5.6. postLoad](events.md#1156_postLoad)
* [11.6. Событие loadClassMetadata](events.md#116_loadClassMetadata)

11.1. Система событий
=====================

Любые события всегда находятся под контролем менеджера сущностей, именно он является центральным звеном в системе событий. На нем регистрируются слушатели событий, а вся обработка события также завязана на нем.
```php
<?php  
$evm = new EventManager();
```
После создания **$evm**, к нему можно добавлять слушателей. Давайте для примера создадим класс **EventTest**.
```php
<?php  
class EventTest  
{  
    const preFoo = 'preFoo';  
    const postFoo = 'postFoo';  
  
    private $_evm;  
  
    public $preFooInvoked = false;  
    public $postFooInvoked = false;  
  
    public function __construct($evm)  
    {  
        $evm->addEventListener(array(self::preFoo, self::postFoo), $this);  
    }  
  
    public function preFoo(EventArgs $e)  
    {  
        $this->preFooInvoked = true;  
    }  
  
    public function postFoo(EventArgs $e)  
    {  
        $this->postFooInvoked = true;  
    }  
}  
  
// Создадим экземпляр  
$test = new EventTest($evm);
```
Запустить обработчик можно с помощью метода **dispatchEvent()**.
```php
<?php  
$evm->dispatchEvent(EventTest::preFoo);  
$evm->dispatchEvent(EventTest::postFoo);
```
При помощи метода **removeEventListener()** можно отключить слушателя.
```php
<?php  
$evm->removeEventListener(array(self::preFoo, self::postFoo), $this);
```
Помимо слушателей в системе событий **Doctrine 2** существует понятие подписчиков. Мы можем создать простой класс**TestEventSubscriber**, который определяет интерфейс **\\Doctrine\\Common\\EventSubscriber** и имееь метод**getSubscribedEvents()**. Этот метод будет возвращать массив событий, на которые следует подписаться.
```php
<?php  
class TestEventSubscriber implements \\Doctrine\\Common\\EventSubscriber  
{  
    public $preFooInvoked = false;  
  
    public function preFoo()  
    {  
        $this->preFooInvoked = true;  
    }  
  
    public function getSubscribedEvents()  
    {  
        return array(TestEvent::preFoo);  
    }  
}  
  
$eventSubscriber = new TestEventSubscriber();  
$evm->addEventSubscriber($eventSubscriber);
```
При наступлении определенного события всем подписчикам будет отправлено соответствующее уведомление.
```php
<?php  
$evm->dispatchEvent(TestEvent::preFoo);
```
Можете проверить экземпляр **$eventSubscriber** и убедится, что был вызван метод **preFoo()**.
```php
<?php  
if ($eventSubscriber->preFooInvoked) {  
    echo 'pre foo invoked!';  
}
```
11.1.1. Именование
------------------

Назначать имена событиям лучше всего в стиле _CamelCase_, а значение соответствующей константы должно соответствовать ее имени, даже несмотря на орфографию. Этому есть следующие причины:

* Это легко читается.
* Простота.
* Каждый метод в _EventSubscriber _именуется после соответствующей константы. Если имя и значение константы отличаются, вам придется использовать новое значение, и, таким образом, менять сам код после изменения значения, что противоречит самой сути констант.

Пример правльной нотации был приведен выше в примере с **EventTest**.

11.2. События жизненного цикла
==============================

_EntityManager_ и _UnitOfWork_ могут вызывать множество различных событий в течение жизненного цикла подписанных на них сущностей.

* preRemove — возникает для заданной сущности перед тем как _EntityManager_ применит к ней операцию удаления. Это событие не вызывается при вызове _DQL_ запроса **DELETE**.
* postRemove — вызывается после того как сущность была удалена. Событие будет вызвано после выполнения операций удаления в базе даннах. Не вызывается для _DQL_ запросов **DELETE**.
* prePersist — возникает для заданной сущности перед тем как _EntityManager_ применит к ней операцию сохранения (персистирования).
* postPersist — возникает после того как сущность была сохранена. Событие вызывается посе операции вставки новой записи в базу данных. В этом событии будут доступны сгенерированные значения первичного ключа.
* preUpdate — возникает перед выполнением операций обновления в БД. Не вызывается для _DQL_ запросов **UPDATE**.
* postUpdate — возникает после выполнения операций обновления в БД. Не вызывается для _DQL_ запросов **UPDATE**.
* postLoad — возникает после того как сущность была загружена из БД в текущий _EntityManager_ или после того как к ней была применена операция **refresh**.
* loadClassMetadata — возникает после того как для класса были загружены метаданные из одного из возможных источников (аннотации, _XMLили YAML_).
* onFlush — возникает после того как для всех _MANAGED_-сущностей были вычислены наборы необходимых изменений**(change-sets)**. Это событие не относится к колбеку жизненного цикла.
* onClear — возникает при выполнении операции **EntityManager#clear()**, после того как из _UnitOfWork_ были удалены все ссылки на сущности.

> Заметьте, что событие postLoad возникает еще до того как были инициализированы связи сущности. Поэтому обращаться к связям из postLoad или обработчика события небезопасно.

Получить доступ к константам события можно из класса _Event_, относящемуся к пакету _ORM_.
```php
<?php  
use Doctrine\\ORM\\Events;  
echo Events::preUpdate;
```
Существует два различных типа слушателей, которые могут перехватывать события:

* **Lifecycle Callbacks** – это методы самих сущностей, они исполняются при возникновении того или иного события. Им не передаются аргументы, они созданы лишь для того, чтобы дать возможность вносить изменения изнутри контекста сущности.
* **Lifecycle Event Listeners** – это классы со своими специальными callback-методами, им передается соответствующий экземпляра класса EventArgs, который предоставляет доступ к сущности, EntityManager’у или иным данным.

> События, возникающие во время выполнения **EntityManager#flush()** накладывают специфические ограничения на перечень допустимых к использованию операций. В разделе “[Реализация обработчиков событий](events.md#115)” описано какие операции в каких событиях допустимы.

11.3. Обратный вызов
====================

Событие жизненного цикла представляет собой обычное событие, на которое можно повесить метод-коллбек внутри класса сущности, который будет вызван при его наступлении.
```php
<?php  
  
/\*\* @Entity @HasLifecycleCallbacks */  
class User  
{  
    // ...  
  
    /**  
     \* @Column(type="string", length=255)  
     */  
    public $value;  
  
    /\*\* @Column(name="created_at", type="string", length=255) */  
    private $createdAt;  
  
    /\*\* @PrePersist */  
    public function doStuffOnPrePersist()  
    {  
        $this->createdAt = date('Y-m-d H:m:s');  
    }  
  
    /\*\* @PrePersist */  
    public function doOtherStuffOnPrePersist()  
    {  
        $this->value = 'changed from prePersist callback!';  
    }  
  
    /\*\* @PostPersist */  
    public function doStuffOnPostPersist()  
    {  
        $this->value = 'changed from postPersist callback!';  
    }  
  
    /\*\* @PostLoad */  
    public function doStuffOnPostLoad()  
    {  
        $this->value = 'changed from postLoad callback!';  
    }  
  
    /\*\* @PreUpdate */  
    public function doStuffOnPreUpdate()  
    {  
        $this->value = 'changed from preUpdate callback!';  
    }  
}
```
Обратите внимание, при использовании коллбеков к классу сущности нужно добавить маркер **@HasLifecycleCallbacks**.

Регистрация событий при использовании _YAML_ или _XML_ осуществляется как показано ниже.
```yaml
User:  
  type: entity  
  fields:  
\# ...  
    name:  
      type: string(50)  
  lifecycleCallbacks:  
    prePersist: \[ doStuffOnPrePersist, doOtherStuffOnPrePersistToo \]  
    postPersist: \[ doStuffOnPostPersist \]
```
_XML_ будет выглядеть так:
```xml
<?xml version="1.0" encoding="UTF-8"?>  
  
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"  
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  
     xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping  
                         /Users/robo/dev/php/Doctrine/doctrine-mapping.xsd">  
  
    <entity name="User">  
  
        <lifecycle-callbacks>  
            <lifecycle-callback type="prePersist" method="doStuffOnPrePersist"/>  
            <lifecycle-callback type="postPersist" method="doStuffOnPostPersist"/>  
        </lifecycle-callbacks>  
  
    </entity>  
  
</doctrine-mapping>
```
Единственное, нужно убедится, что в модели **User** определены открытые методы **doStuffOnPrePersist()** и**doStuffOnPostPersist()**.
```php
<?php  
// ...  
  
class User  
{  
    // ...  
  
    public function doStuffOnPrePersist()  
    {  
        // ...  
    }  
  
    public function doStuffOnPostPersist()  
    {  
        // ...  
    }  
}
```
В теге **lifecycleCallbacks **ключ представляет собой тип события, а значение — имена методов. Допустимые типы событий были перечислены в предыдущем разделе.

11.4. Обработка событий
=======================

Обработчики событий **(event listeners)** гораздо интереснее методов-коллбеков, определяемых внутри классов. Их использование позволяет реализовать механизмы, которые можно повторно использовать в различных классах, правда это требует более глубокого понимания работы внутренних аспектов работы _EntityManager_ и _UnitOfWork_. Понять как написать свой собственный обработчик поможет глава [“Реализация обработчиков событий”](events.md#115).

Для регистрации обработчика нужно подключить его к _EventManager_, а затем передать последний в фабрику _EntityManager_:
```php
<?php  
$eventManager = new EventManager();  
$eventManager->addEventListener(array(Events::preUpdate), MyEventListener());  
$eventManager->addEventSubscriber(new MyEventSubscriber());  
  
$entityManager = EntityManager::create($dbOpts, $config, $eventManager);
```
Получить экзмепляр EventManager можно после создание EntityManager:
```php
<?php  
$entityManager->getEventManager()->addEventListener(array(Events::preUpdate), MyEventListener());  
$entityManager->getEventManager()->addEventSubscriber(new MyEventSubscriber());
```
11.5. Реализация обработчиков событий
=====================================

Эта секция объясняет какие действия можно, а какие нельзя выполнять в обработчиках событий _UnitOfWork_. Внимательно следуйте этим замечаниям, ведь хотя экземпляр _EntityManager_ и передается всем обработчикам, выполнение некорректной операции может повлечь за собой большое число ошибок вроде нарушения консистентности данных, либо потерь операций обновления, сохранения или удаления данных.

Для описываемых событий, если они являются также и событиями жизненного цикла, действует еще одно ограничение — внутри обработчиков у вас не будет доступа к _API EntityManager_ и _UnitOfWork_.

11.5.1. prePersist
------------------

Есть два способы вызвать событие _prePersist_. Первый очевиден — это когда вызывается **EntityManager#persist()**. Также, событие будет вызвано по иерархии для каскадных связей.

Другим способом событие может быть вызвано изнутри метода **flush()** после того как просчитаны все изменения, которые нужно будет внести в связи, и заданная связь была отмечена как **cascade persist**. Теперь любая новая сущность найденная при выполнении этой операции будет сохранена в базу, и для нее будет вызвано событие _prePersist_. Такой подход называется**“persistence by reachability”**.

В обоих случаях в обработчик передается экземпляр _LifecycleEventArgs_, который будет иметь доступ к самой сущности и ее_EntityManager_.

Для события _prePersist_ действуют следующие ограничения:

* Если используется какой-нибудь _PrePersist Identity Generator,_ например последовательность, то значение _ID_ не будет доступно в событии _PrePersist_.
* _Doctrine_ не умеет распознавать изменения, внесенные в связи в событии _PrePersist_, если оно было вызвано при каскадном сохранении (**by “reachability”**)  как было описано выше, пока вы не будете использовать для этого внутренний _API UnitOfWork_. Мы не рекомендуем использовать подобные операции в таком контексте, так что делайте это на свой страх и риск, и да поможет вам Бог (и unit-тесты).

11.5.2. preRemove
-----------------

Событие _preRemove_ выполняется для каждой сущности, переданной методу **EntityManager#remove()**. Событие каскадно распространяется на все связи, которые позволяют каскадное удаление.

Нет никаких ограничений на вызываемые методы внутри этого события, за исключением того когда метод **remove()** вызывается во время операции **flush()**.

11.5.3. onFlush
---------------

_OnFlush_ одно из самых навороченных. Оно вызывается внутри метода **EntityManager#flush()** после того как будут просчитаны все изменения в _MANAGED_ сущностях и их связях. Это означает, что _onFlush_ имее досутп к наборам:

* Сущностей, запланированных для вставки
* Сущностей, запланированных для обновления
* Сущностей, запланированных для удаления
* Коллекций, запланированных для обновления
* Коллекций, запланированных для удаления

Чтобы нормально работать с этим событием нужно разбираться во внутреннем _API UnitOfWork_, именно он предоставляет доступ к вышеприведенным наборам данных. Рассмотрим пример:
```php
<?php  
class FlushExampleListener  
{  
    public function onFlush(OnFlushEventArgs $eventArgs)  
    {  
        $em = $eventArgs->getEntityManager();  
        $uow = $em->getUnitOfWork();  
  
        foreach ($uow->getScheduledEntityInsertions() AS $entity) {  
  
        }  
  
        foreach ($uow->getScheduledEntityUpdates() AS $entity) {  
  
        }  
  
        foreach ($uow->getScheduledEntityDeletions() AS $entity) {  
  
        }  
  
        foreach ($uow->getScheduledCollectionDeletions() AS $col) {  
  
        }  
  
        foreach ($uow->getScheduledCollectionUpdates() AS $col) {  
  
        }  
    }  
}
```
Для _onFlush_ действуют следующие ограничения:

* Если в обработчике _onFlush_ созадется и сохраняется новую сущность, то одного вызова **EntityManager#persist()**недостаточно. Нужно сделать еще один — **$unitOfWork->computeChangeSet($classMetadata, $entity)**
* Изменение полей или связей требует явного пересчета набора изменений **(changeset)** затрагиваемой сущности. Делается это вызовом **$unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity)**.

11.5.4. preUpdate
-----------------

У этого события больше всего ограничений, т.к. вызывается оно внутри метода **EntityManager#flush()** непосредственно перед_SQL UPDATE_.

В этом событии нельзя вносить изменения в связи обновляемой сущности, т.к. на данном этапе операции **flush** _Doctrine_ не сумеет гарантированно обеспечить ссылочную целостность. Однако у этого события есть существенный плюс — оно вызывается с набором аргументов _PreUpdateEventArgs_, в котором содержится ссылка на просчитанный **change-set** для заданной сущности.

Это означает, что можно получить доступ ко всем затронутым при изменении полям, при этом будет доступно как старое, так и новое значения поля. У _PreUpdateEventArgs_ есть следующие методы:

* **getEntity()** возвращает саму сущность.
* **getEntityChangeSet()** возвращает копию массива с набором изменений. Изменения, внесенные в этот массив никак не повлияют на операцию обновления.
* **hasChangedField($fieldName)** проверяе изменилось ли заданное поле или нет.
* **getOldValue($fieldName)** и **getNewValue($fieldName)** возвращают значения поля до и после его изменения соответственно.
* **setNewValue($fieldName, $value)** позволяет изменить значение поля.

Типичный пример работы с _preUpdate_ выглядит примерно так:
```php
<?php  
class NeverAliceOnlyBobListener  
{  
    public function preUpdate(PreUpdateEventArgs $eventArgs)  
    {  
        if ($eventArgs->getEntity() instanceof User) {  
            if ($eventArgs->hasChangedField('name') && $eventArgs->getNewValue('name') == 'Alice') {  
                $eventArgs->setNewValue('name', 'Bob');  
            }  
        }  
    }  
}
```
На основе обработки этого события можно строить валидацию полей. Это гораздо эффективней использования **lifecycle callback**, в которых валидация дается недешево:
```php
<?php  
class ValidCreditCardListener  
{  
    public function preUpdate(PreUpdateEventArgs $eventArgs)  
    {  
        if ($eventArgs->getEntity() instanceof Account) {  
            if ($eventArgs->hasChangedField('creditCard')) {  
                $this->validateCreditCard($eventArgs->getNewValue('creditCard'));  
            }  
        }  
    }  
  
    private function validateCreditCard($no)  
    {  
        // throw an exception to interrupt flush event. Transaction will be rolled back.  
    }  
}
```
Для этого события существуют следующие ограничения:

* Изменения в связях переданных сущностей не будут восприниматься операцией **flush()**.
* Изменение полей также не будут восприняты операцией **flush()**, для этого нудно использовать сформированный **change-set**, которые передается событию.
* Настоятельно не рекомендуется вызывать **EntityManager#persist()** или **EntityManager#remove()** даже в комбинации с _API UnitOfWork_, т.к. вне операции **flush()** они не будут работать как ожидается.

11.5.5. postUpdate, postRemove, postPersist
-------------------------------------------

Эти три события вызываются внутри **EntityManager#flush()**. Изменения, выполненные в этих событиях не повлияют на базу данных, но можно использовать эти события для воздействия на несохраняемые элементы сущности, например простые поля класса, которые не отображены на БД, логирование или даже какие-то связанные классы, которые непосредственно обрабатываются Доктриной.

11.5.6. postLoad
----------------

Это событие возникает после того как сущность была сконструирована менеджером сущностей.

11.6. Событие loadClassMetadata
===============================

После считывания метаданных сущности они передаются в объект класса **ClassMetadataInfo**. Для манипуляции этим объектом нужно обработать событие **loadClassMetadata**.
```php
<?php  
$test = new EventTest();  
$metadataFactory = $em->getMetadataFactory();  
$evm = $em->getEventManager();  
$evm->addEventListener(Events::loadClassMetadata, $test);  
  
class EventTest  
{  
    public function loadClassMetadata(\\Doctrine\\ORM\\Event\\LoadClassMetadataEventArgs $eventArgs)  
    {  
        $classMetadata = $eventArgs->getClassMetadata();  
        $fieldMapping = array(  
            'fieldName' => 'about',  
            'type' => 'string',  
            'length' => 255  
        );  
        $classMetadata->mapField($fieldMapping);  
    }  
}
```
