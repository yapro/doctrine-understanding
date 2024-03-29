В _Doctrine_ связи между сущностями выглядят так же как PHP выглядят ссылки на другие объекты и их коллекции. Но когда подобные структуры хранятся в базе данных нужно понимать следующие три вещи:

*   [Концепция прямой и обратной сторон связи]( association-mapping#61 "Отображение связей") в двусторонних отношениях
*   Если сущность удаляется из коллекции, это означает лишь удаление связи, но никак не удаление самой сущности. Связь представлена коллекцией входящих в нее сущностей, но не самими сущностями.
*   Поля сущности, являющиеся коллекциями должны реализовывать интерфейс **Doctrine\\Common\\Collections\\Collection**.

Все изменения, внесенные в связь в течение работы приложения не будут моментально синхронизироваться, это произойдет только после вызова метода **EntityManager#flush()**.

Чтобы описать все возможные варианты работы со связями мы подготовим специальный набор тестовых сущностей, на примере которых будут продемонстрированы разные способы работы со связями в _Doctrine_.

Содержание

*   [9.1. Тестовые сущности](working-with-associations.md#91)
*   [9.2. Создание связи](working-with-associations.md#92)
*   [9.3. Удаление связей](working-with-associations.md#93)
*   [9.4. Способы управления связями](working-with-associations.md#94)
*   [9.5. Синхронизация двусторонних коллекций](working-with-associations.md#95)
*   [9.6. Transitive persistence / Каскадные операции](working-with-associations.md#96_Transitive_persistence)
    *   [9.6.1. Persistence by Reachability: Cascade Persist](working-with-associations.md#961_Persistence_by_Reachability_Cascade_Persist)
*   [9.7. Паттерн “Orphan Removal”](working-with-associations.md#97_8220Orphan_Removal8221)

9.1. Тестовые сущности
======================

В качестве примеров мы будем использовать простую систему, в которой есть Пользователи **(Users)** и Комментарии**(Comments)**. Посмотрите на нижеприведенный код, из него будет понятно что к чему:

```php
<?php
/** @Entity */
class User
{
    /** @Id @GeneratedValue @Column(type="string") */
    private $id;

    /**
     * Двусторонняя связь - множество пользователей имеют множество избранных комментариев (это сторона владельца)
     *
     * @ManyToMany(targetEntity="Comment", inversedBy="userFavorites")
     * @JoinTable(name="user_favorite_comments",
     *   joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *   inverseJoinColumns={@JoinColumn(name="favorite_comment_id", referencedColumnName="id")}
     * )
     */
    private $favorites;

    /**
     * Односторонняя связь - множество пользователей могут пометить множество комментариев как прочтенные
     *
     * @ManyToMany(targetEntity="Comment")
     * @JoinTable(name="user_read_comments",
     *   joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *   inverseJoinColumns={@JoinColumn(name="comment_id", referencedColumnName="id")}
     * )
     */
    private $commentsRead;

    /**
     * Двусторонняя связь - один ко многим (обратная сторона)
     *
     * @OneToMany(targetEntity="Comment", mappedBy="author")
     */
    private $commentsAuthored;

    /**
     * Односторонняя связь - многие к одному
     *
     * @ManyToOne(targetEntity="Comment")
     */
    private $firstComment;
}

/** @Entity */
class Comment
{
    /** @Id @GeneratedValue @Column(type="string") */
    private $id;

    /**
     * Двусторонняя связь - множество комментариев добавлены в избранное множеством пользователей (обратная сторона)
     *
     * @ManyToMany(targetEntity="User", mappedBy="favorites")
     */
    private $userFavorites;

    /**
     * Двусторонняя связь - множество комментариев написано одним пользователем (сторона владельца)
     *
     * @ManyToOne(targetEntity="User", inversedBy="authoredComments")
     */
     private $author;
}
```

Для этих двух сущностей будет сгенерирована следующая схема в _MySQL_ (определение внешних ключей опущено) 
```sql
CREATE TABLE USER (
    id VARCHAR(255) NOT NULL,
    firstComment_id VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY(id)
) ENGINE = InnoDB;

CREATE TABLE Comment (
    id VARCHAR(255) NOT NULL,
    author_id VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY(id)
) ENGINE = InnoDB;

CREATE TABLE user_favorite_comments (
    user_id VARCHAR(255) NOT NULL,
    favorite_comment_id VARCHAR(255) NOT NULL,
    PRIMARY KEY(user_id, favorite_comment_id)
) ENGINE = InnoDB;

CREATE TABLE user_read_comments (
    user_id VARCHAR(255) NOT NULL,
    comment_id VARCHAR(255) NOT NULL,
    PRIMARY KEY(user_id, comment_id)
) ENGINE = InnoDB;
```

9.2. Создание связи
===================

Устанавливается связь между двумя сущностями просто. Вот примеры для односторонних отношений:
```php
<?php
class User
{
    // ...
    public function getReadComments() {
         return $this->commentsRead;
    }

    public function setFirstComment(Comment $c) {
        $this->firstComment = $c;
    }
}
```

Рабочий код будет выглядеть как показано ниже (**$em** здесь это EntityManager):
```php
<?php
$user = $em->find('User', $userId);

// односторонняя связь "многие ко многим"
$comment = $em->find('Comment', $readCommentId);
$user->getReadComments()->add($comment);

$em->flush();

// односторонняя связь "многие к одному"
$myFirstComment = new Comment();
$user->setFirstComment($myFirstComment);

$em->persist($myFirstComment);
$em->flush();
```

В случае двусторонних связей следует изменять поля у обоих сущностей:
```php
<?php
class User
{
    // ..

    public function getAuthoredComments() {
        return $this->commentsAuthored;
    }

    public function getFavoriteComments() {
        return $this->favorites;
    }
}

class Comment
{
    // ...

    public function getUserFavorites() {
        return $this->userFavorites;
    }

    public function setAuthor(User $author = null) {
        $this->author = $author;
    }
}

// Многие ко многим
$user->getFavorites()->add($favoriteComment);
$favoriteComment->getUserFavorites()->add($user);

$em->flush();

// Многие к одному / один ко многим, двусторонняя связь
$newComment = new Comment();
$user->getAuthoredComments()->add($newComment);
$newComment->setAuthor($user);

$em->persist($newComment);
$em->flush();
```
Обратите внимание на внесенные изменения — у двусторонней связи обновляются обе стороны. Предыдущий пример с односторонними отношениями был проще.

9.3. Удаление связей
====================

Удаление связи между двумя сущностями осуществляется аналогично. Сделать это можно двумя способами: по элементу или ключу. Примеры:
```php
<?php
// Удаление по элементам
$user->getComments()->removeElement($comment);
$comment->setAuthor(null);

$user->getFavorites()->removeElement($comment);
$comment->getUserFavorites()->removeElement($user);

// Удаление по ключу
$user->getComments()->remove($ithComment);
$comment->setAuthor(null);
```
Для внесения этих изменений в базу данных нужно будет вызвать метод **$em->flush()**.

Заметьте, что в двусторонней связи всегда обновляются обе стороны. Односторонние связи в этом отношении проще. Также имейте ввиду, что если вы явно укажете тип параметра в методах_, например_ **setAddress(Address $address)**, то PHP разрешит передачу этому методу значения **NULL** только если оно явно задано в качестве значения по умолчанию. В противном случае при удалении связи метод **setAddress(null)** потерпит неудачу. Если вам все-таки необходимо явно задать тип параметра **(type-hinting)**, то лучше создать специальный метод вроде **removeAddress()**. Такой подход улучшит инкапсуляцию класса, скрыв то, как этот класс будет обрабатывать ситуацию с отсутствующим адресом.

При работе с коллекциями имейте ввиду, что коллекция это, по сути, упорядоченная карта (подобно обычному массиву в PHP). Вот почему при удалении нужно указать индекс или ключ. Метод **removeElement** имеет сложность **O(n)**, т.к. работает через функцию **array\_search**, где **n** — это размер карты.

> Doctrine на предмет обновлений всегда просматривает только сторону владельца связи, поэтому нет необходимости писать код, который будет обновлять коллекцию с обратной стороны. Это даст вам пару очков в производительности, т.к. не нужно будет лишний загружать эту коллекцию.

Очистить содержимое коллекции можно с помощью метода **Collections::clear()**. Следует помнить, что его использование в последующей операции **flush()** может привести к вызовам DELETE и UPDATE, которые не знают о сущностях, добавленных в коллекцию до этого. (Оригинал: **You should be aware that using this method can lead to a straight and optimized database delete or update call during the flush operation that is not aware of entities that have been re-added to the collection**).

Скажем, вы очистили коллекцию тегов с помощью **$post->getTags()->clear()**, а затем вызывали **$post->getTags()->add($tag)**, добавив новый тег. В этом случае движок ORM не сможет распознать тег, который был там до этого, и как следствие, произведет два различных вызова к базе данных.

9.4. Способы управления связями
===============================

На самом деле было бы отлично, если весь механизм работы со связями был спрятан внутри сущностей. Это привнесет целостность в архитектуру вашего приложения, ведь все детали касательно связей будут инкапсулированы в классе.

Нижеприведенный код демонстрирует соответствующие изменения в сущностях **User** и **Comment**:
```php
<?php
class User
{
    //...
    public function markCommentRead(Comment $comment) {
        // Collections определяет интерфейс ArrayAccess
        $this->commentsRead[] = $comment;
    }

    public function addComment(Comment $comment) {
        if (count($this->commentsAuthored) == 0) {
            $this->setFirstComment($comment);
        }
        $this->comments[] = $comment;
        $comment->setAuthor($this);
    }

    private function setFirstComment(Comment $c) {
        $this->firstComment = $c;
    }

    public function addFavorite(Comment $comment) {
        $this->favorites->add($comment);
        $comment->addUserFavorite($this);
    }

    public function removeFavorite(Comment $comment) {
        $this->favorites->removeElement($comment);
        $comment->removeUserFavorite($this);
    }
}

class Comment
{
    // ..

    public function addUserFavorite(User $user) {
        $this->userFavorites[] = $user;
    }

    public function removeUserFavorite(User $user) {
        $this->userFavorites->removeElement($user);
    }
}
```
Как вы могли заметить, методы **addUserFavorite** и **removeUserFavorite** не вызывают соответствующих методов **addFavorite** и**removeFavorite**, таким образом, двустороннее отношение, старого говоря, является незаконченным. Однако, если вы, наивно полагая, добавите вызов **addFavorite** в метод **addUserFavorite**, то получите бесконечный цикл. Как видите, работа с двусторонними связями в ООП не тривиальная задача, а инкапсуляция деталей работы с ними внутрь классов иногда бывает сложна.

> Чтобы добиться идеальной инкапсуляции коллекций, не следует возвращать их напрямую из метода**getCollectionName()**, вместо него используйте **$collection->toArray()**. Таким образом, пользователь сущности не сможет обойти определенную вами логику обработки связей. Пример:
```php
<?php
class User {
    public function getReadComments() {
        return $this->commentsRead->toArray();
    }
}
```
Этот подход, однако, всегда приводит к предварительной инициализации коллекции, со всеми вытекающими отсюда проблемами с производительностью, хотя все зависит от размера коллекции. При работе с коллекциями больших размеров хорошей идеей будет полностью скрыть весь механизм считывания в методах репозитория (EntityRepository).

Не существует единственно верного способа работы с коллекциями. Все зависит, с одной стороны, от требований к вашим моделями, а с другой — от ваших предпочтений.

9.5. Синхронизация двусторонних коллекций
=========================================

При работе со связями типа “многие ко многим” вы как разработчик, возможно, захотите, чтобы при редактировании коллекций они всегда оставались синхронизированы с обоих сторон связи. Но Doctrine гарантирует согласование лишь при гидрации (при сохранении в БД), но не для вашего клиентского кода.

Давайте посмотрим с чем вы можете столкнуться на примере уже известной связки **User-Comment**:
```php
<?php
$user->getFavorites()->add($favoriteComment);
// Здесь не происходит вызова $favoriteComment->getUserFavorites()->add($user);

$user->getFavorites()->contains($favoriteComment); // TRUE
$favoriteComment->getUserFavorites()->contains($user); // FALSE
```
Существует два способа решения этой проблемы:  
Игнорировать обновление обратной стороны связи в двусторонних отношениях, но никогда ничего не считывать оттуда при запросах, которые изменяют их состояние. При следующем запросе Doctrine соответствующим образом подготовит и согласует состояние связи.  
Осуществлять синхронизацию двусторонних коллекций при помощи соответствующих методов. **Reads of the Collections directly after changes are consistent then.**

9.6. Transitive persistence / Каскадные операции
================================================

Когда мы имеем дело с навороченным графом объектов, в котором переплетено множество связей, то операции сохранения, удаления, отсоединения и слияния отдельных сущностей могут оказаться весьма громоздкими. Поэтому Doctrine 2 обеспечивает механизм **transitive persistence** путем каскадного выполнения соответствующих операций. По умолчанию каскадность отключена.

Существуют следующие опции каскадности:  
**persist** : Операции сохранения каскадно применяются к связанным сущностям.  
**remove** : Аналогично для удаления.  
**merge** : Аналогично для слияния.  
**detach** : Аналогично для отсоединения.  
**all** : Все вышеприведенные операции будут каскадно применены к связанным сущностям.

> Все каскадные операции производятся в оперативной памяти. Это значит, что непосредственно перед началом операции коллекции и связанные с ними сущности загружаются в память, даже если у них настроена “ленивая загрузка”. При этом для каждой операции будут соответствующим образом запущенны обработчики событий жизненного цикла сущностей (коллбеки: **beforPersist**, **afterPersists** и т.д) если они есть.  
> Однако, при больших размерах коллекций, размещение графа объектов в памяти может привести к проблемам с производительностью. Поэтому взвесьте все “за” и “против”, определите преимущества и узкие места каждой каскадной операции.  
> Если вместо этого при удалении нужно использовать механизмы каскадности, предоставляемые базой данных, то каждую **join column** можно сконфигурировать с опцией **onDelete**. Читайте об этом в соответствующих главах, касающихся драйверов метаданных.

Следующий пример показывает расширение уже известной нам связки **User-Comment**. Предположим, что пользователь должен быть создан, когда он напишет свой первый комментарий:
```php
<?php
$user = new User();
$myFirstComment = new Comment();
$user->addComment($myFirstComment);

$em->persist($user);
$em->persist($myFirstComment);
$em->flush();
```
Есть в этом примере удалить вызов **EntityManager#persist($myFirstComment)**, то код потерпит неудачу, даже если вы сохраните вашего нового Юзера, добавив к нему новый Комментарий. Все дело в том, что Doctrine 2 не обрабатывает каскадно сущности, которые были только что созданы и не прикреплены к менеджеру сущностей.

Вот аналогичный, но более сложный пример, в нем при удалении пользователя из системы удаляются все его комментарии:
```php
$user = $em->find('User', $deleteUserId);

foreach ($user->getAuthoredComments() AS $comment) {
    $em->remove($comment);
}
$em->remove($user);
$em->flush();
```
Если не выполнить цикл и не пройтись по всем комментариям пользователя, Doctrine выполнит запрос **UPDATE** только для того, чтобы установить значения внешних ключей в **NULL**, таким образом после операции **flush()** из базы будет удален только сам пользователь.

Чтобы Doctrine корректно обрабатывала оба случая можно изменить свойство **User#commentsAuthored**, добавив к нему опции каскадности **“persist”** и **“remove”**:
```php
<?php
class User
{
    //...
    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     *
     * @OneToMany(targetEntity="Comment", mappedBy="author", cascade={"persist", "remove"})
     */
    private $commentsAuthored;
    //...
}
```
Хотя автоматическая каскадность весьма удобна, использовать ее нужно с осторожностью. Не присваивать каждой связи опцию**cascade=all**, это приведет лишь к снижению производительности. При активации каждую каскадную операцию Doctrine применит и к связи, будь она одиночной или коллекцией.

9.6.1. Persistence by Reachability: Cascade Persist
---------------------------------------------------

У операций каскадного сохранения существует дополнительная семантика. Если при вызове **flush()** Doctrine обнаружит в какой-нибудь из коллекций свежесозданные сущности **(NEW)**, то далее события будут развиваться по одному из трех сценариев:

*   Новые сущности в коллекции, помещенные cascade=persist будут сохранены напрямую Doctrine
*   Новые сущности в коллекции, не имеющие такой опции выдадут исключение в результате чего откат операции **flush()**.
*   Коллекции, не имеющие новых сущностей будут пропущены.

Этот подход называется **Persistence by Reachability**: когда связь настроена на каскадное сохранение, то все новые сущности, найденные в коллекциях у уже существующих сущностей, будут автоматически сохранены.

9.7. Паттерн “Orphan Removal”
=============================

Есть еще один подобный механизм, он задействуется только при удалении сущностей из коллекций. Если сущность типа **A**содержит внутренние ссылки на сущности типа **B**, то когда ссылка **A**\->**B** удаляется (а, следовательно, связь разрывается), то и сущность **B** также будет удалена, потому что с этого момента она нигде не используется.

Паттерн **OrphanRemoval** (“удаление объектов-сирот”) работает как со связями “один к одному” так и “один ко многим”.

> При использовании параметра **orphanRemoval=true** Doctrine делает предположение, что сущности являются закрытыми и **не будут** повторно использоваться другими сущностями. Если пренебречь этим допущением, сущности будут удалены даже если вы присвоите такую осиротевшую сущность какой-либо другой.

Для примера рассмотрим приложение **Addressbook** (адресная книга), в котором есть сущности **Contacts**, **Addresses** и**StandingData**:
```php
<?php

namespace Addressbook;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 */
class Contact
{
    /** @Id @Column(type="integer") @GeneratedValue */
    private $id;

    /** @OneToOne(targetEntity="StandingData", orphanRemoval=true) */
    private $standingData;

    /** @OneToMany(targetEntity="Address", mappedBy="contact", orphanRemoval=true) */
    private $addresses;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    public function newStandingData(StandingData $sd)
    {
        $this->standingData = $sd;
    }

    public function removeAddress($pos)
    {
        unset($this->addresses[$pos]);
    }
}
```
Следующий пример показывает, что произойдет, когда вы удалите ссылки:
```php
<?php

$contact = $em->find("Addressbook\Contact", $contactId);
$contact->newStandingData(new StandingData("Firstname", "Lastname", "Street"));
$contact->removeAddress(1);

$em->flush();
```
Здесь вы не только изменили саму сущность **Contact**, вы таже удалили ссылку на контактные данные **(standing data)** и ссылку на один контактный адрес. Когда будет вызван метод **flush()** из базы данных будут удалены не только ссылки, но и  старая сущность с контактными данными **(staging data)**, а также сущность Address. Они будут удалены потому что они ни с кем не связаны.
