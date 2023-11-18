Отображение связей
--

В этой главе приводится описание того как Doctrine работает со связанными сущностями, т.е. сущностями, между которыми существуют некоторые отношения. Для начала будет дано описание концепции прямой и обратной сторон связи. Это очень важный момент, он поможет понять принцип работы двусторонних связей. Главное, нужно усвоить, что связи могут быть одно- и дву- сторонними.

Содержание

*   [6.1. Прямая и обратная стороны связи](association-mapping.md#61)
*   [6.2. Коллекции](association-mapping.md#62)
*   [6.3. Параметры отображения по-умолчанию](association-mapping.md#63)
*   [6.4. Инициализация коллекций](association-mapping.md#64)
*   [6.5. Валидация отображений в различных средах (Runtime и Development)](association-mapping.md#65___Runtime_Development)
*   [6.6. Отношения “один к одному”, односторонние](association-mapping.md#66_8220_8221)
*   [6.7. Отношения “один к одному”, двусторонние](association-mapping.md#67_8220_8221)
*   [6.8. Отношения “один к одному” со ссылкой на себя же](association-mapping.md#68_8220_8221)
*   [6.9. Отношения “один ко многим”, односторонние, с использованием @JoinTable](association-mapping.md#69_8220_8221__JoinTable)
*   [6.10. Отношения “многие к одному”, односторонние](association-mapping.md#610_8220_8221)
*   [6.11. Отношения “один ко многим”, двусторонние](association-mapping.md#611_8220_8221)
*   [6.12. Отношения “один ко многим” со ссылкой на себя](association-mapping.md#612_8220_8221)
*   [6.13. Отношения “многие ко многим”, односторонние](association-mapping.md#613_8220_8221)
*   [6.14. Отношения “многие ко многим”, двусторонние](association-mapping.md#614_8220_8221)
    *   [6.14.1. Picking Owning and Inverse Side](association-mapping.md#6141_Picking_Owning_and_Inverse_Side)
*   [6.15.  Отношения “многие ко многим” со ссылкой на себя](association-mapping.md#615_nbsp_8220_8221)
*   [6.16. Сортировка коллекций в связях “To-Many”](association-mapping.md#616___8220To-Many8221)

# 6.1. Прямая и обратная стороны связи

При работе с двусторонними связями важно понимать суть прямой (owning) и обратной (inverse) сторон связи. Давайте начнем с простых правил:

*   Отношения межу сущностями могут быть двусторонними и односторонними.
*   У двустороннего отношения есть как прямая сторона (сторона владельца), так и обратная сторона.
*   У односторонних отношений есть только прямая сторона.
*   Именно прямая сторона отношения непосредственно влияет на все изменения, которые будут внесены в него в процессе работы приложения.

Для **двусторонних** связей справедливы следующие правила:

*   Обратная сторона отношения должна ссылаться на основную сторону с помощью атрибута _mappedBy_, который используется в аннотациях _OneToOne_, _OneToMany_ и _ManyToMany_. Этот атрибут указывает на поле сущности, которое является “владельцем” этого отношения (и это поле расположено на “противоположном” конце связи).
*   И наоборот, прямая сторона двустороннего отношения ссылается на обратную сторону с помощью атрибута _inversedBy_, который также используется в аннотациях _OneToOne_, _ManyToOne_ и _ManyToMany_. Этот атрибут указывает на поле сущности, которое является обратной стороной отношения.
*   В отношениях типа _OneToMany_ и _ManyToOne_ именно “Many”-сторона является прямой стороной связи, поэтому на ней нельзя использовать атрибут _mappedBy_ — он применяется только на обратной стороне.
*   Для двусторонних отношений типа _OneToOne_ прямой стороной связи является та, которая содержит соответствующий внешний ключ (он описывается аннотацией _@JoinColumn(s))_.
*   В отношениях типа _ManyToMany_ любая сторона может быть прямой. _(Непонятно: the side that defines the @JoinTable and/or does not make use of the mappedBy attribute, thus using a default join table.)_

Несколько запутанно, правда? На самом деле все не так сложно. Самое главное, запомните:

**Именно прямая сторона связи определяет какие изменения в существующем отношении попадут в базу данных.**

Чтобы понять это, давайте вспомним как работают двусторонние отношения в мире объектов. Возьмем два объекта, между которыми существует связь. На каждой стороне этой связи существует по ссылке, каждая из которых представляет эту самую связь, но изменять эти ссылки можно независимо друг от друга. Безусловно, в правильно спроектированном приложении вся семантика двусторонних связей должна полностью контролироваться разработчиком, это его ответственность. При использовании _Doctrine_ ей просто нужно указать, какую из этих ссылок нужно хранить в базе данных, а какая там хранится не должна, потому что обе ссылки хранить невозможно, это абсурд. В этом и заключается концепция прямой и обратной связей.

Когда изменения в отношение вносятся только с обратной стороны связи, _Doctrine_ их проигнорирует. Поэтому для двусторонних отношений нужно всегда обновлять обе стороны (ну или с точки зрения Doctrine, хотя бы прямую сторону). Сторона владельца в двусторонней связи — эта та точка, в которой находится условный наблюдатель от _Doctrine_ _(ха-ха, прямо как на выборах, прим. перев.)_, анализирующий связь, именно так она определяет текущее состояние связи и сам факт ее изменения (например, есть ли необходимость обновить ее в базе данных).

> Концепция прямой и обратной сторон является одним из краеугольных камней технологии ORM и к предметной области вашего приложения она не имеет отношения. Поэтому то, что в вашем приложении понимается под стороной владельца, в терминах Doctrine может трактоваться иначе. И на самом деле это не играет роли.

# 6.2. Коллекции

В примерах к этой главе при рассмотрении связей типа “ко многим” мы будет использовать специальный интерфейс _Collection_ и соответствующую ему реализацию _ArrayCollection_, которая определена в пространстве имен _Doctrine\Common\Collections_. Для чего это нужно и почему нельзя использовать простые массивы? К сожалению, массивы в _PHP_, конечно, удобны во многих случаях, но с их помощью нельзя полноценно представлять наборы объектов бизнес-логики, особенно вне контекста _ORM_. Причина состоит в том, что стандартные массивы _PHP_ не могут быть прозрачно расширены для работы с продвинутыми фишками _ORM_. Классы и интерфейсы, которые лежат ближе всего к концепции коллекции, это _ArrayAccess_ и _ArrayObject_, но пока экземпляры этих классов не смогут применятся в тех же конструкциях, где применяются обычные массивы, эффективность их будет ограничена. В принципе, можно использовать и _ArrayAccess_ вместо _Collection_, ведь интерфейс _Collection_ расширяет _ArrayAccess_, но это не даст вам нужной гибкости работы с коллекциями, потому что_API_ _ArrayAccess_ весьма примитивен (и это сделано специально), и, что более важно, нельзя будет передать эту коллекцию в всякие _PHP_ функции, что делает работу с ней очень сложной.

> Интерфейс Collection и класс ArrayCollection, как и все в пространстве имен Doctrine, не относится ни к компоненту ORM, ни к DBAL. Это просто обычный PHP класс,  не имеющий никаких внешних зависимостей за исключением разве что PHP и библиотеки SPL. Использование этого класса в приложении не требует его непосредственного контактирования со слоем, управляющим хранением данных (persistent layer). Класс Collection, как и все в модуле Common, не являются частью этого слоя. Вы можете вообще удалить Doctrine, оставив один этот класс, и весь код, его использующий продолжит нормально функционировать.

# 6.3. Параметры отображения по-умолчанию

Прежде чем мы начнем описывать все возможные отображения для связей, вам следует уяснить следующее. В процессе описания связей будут применяться аннотации _@JoinColumn_ и _@JoinTable_. Они определяют какие столбцы в БД будут непосредственно отвечать за связь. Эти аннотации являются опциональными и имеют значения по умолчанию. Для отношения типов _OneToOne_ или _ManyToOne_, используются следующие дефолтные значения:

```
name: "_id"  
referencedColumnName: "id"
```

Давайте для примера рассмотрим такое отображение:

**PHP**

```php
/** @OneToOne(targetEntity="Shipping") */  
private $shipping;
```

**XML**

```xml
<doctrine-mapping>  
    <entity class="Product">  
        <one-to-one field="shipping" target-entity="Shipping" />  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
Product:  
    type: entity  
    oneToOne:  
        shipping:  
            targetEntity: Shipping
```

Это абсолютно тоже самое, что и следующий, более навороченный вариант:

**PHP**

```php
/**  
* @OneToOne(targetEntity="Shipping")  
* @JoinColumn(name="shipping_id", referencedColumnName="id")  
*/  
private $shipping;
```

**XML**

```xml
<doctrine-mapping>  
    <entity class="Product">  
        <one-to-one field="shipping" target-entity="Shipping">  
            <join-column name="shipping_id" referenced-column-name="id" />  
        </one-to-one>  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
Product:  
    type: entity  
    oneToOne:  
        shipping:  
            targetEntity: Shipping  
            joinColumn:  
                name: shipping_id  
                referencedColumnName: id
```

Конструкция _@JoinTable_, используемая для отображения связей _ManyToMany_ имеет аналогичные значения по умолчанию:

**PHP**

```php
class User  
{  
    //...  
    /** @ManyToMany(targetEntity="Group") */  
    private $groups;  
    //...  
}
```

**XML**

```xml
<doctrine-mapping>  
    <entity class="User">  
        <many-to-many field="groups" target-entity="Group" />  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
User:  
    type: entity  
    manyToMany:  
        groups:  
            targetEntity: Group
```

В более полной нотации это выглядит так:

**PHP**

```php
class User  
{  
    //...  
    /**  
    * @ManyToMany(targetEntity="Group")  
    * @JoinTable(name="User_Group",  
    * joinColumns={@JoinColumn(name="User_id", referencedColumnName="id")},  
    * inverseJoinColumns={@JoinColumn(name="Group_id", referencedColumnName="id")}  
    * )  
    */  
    private $groups;  
    //...  
}
```

**XML**

```xml
<doctrine-mapping>  
    <entity class="User">  
        <many-to-many field="groups" target-entity="Group">  
            <join-table name="User_Group">  
                <join-columns>  
                    <join-column id="User_id" referenced-column-name="id" />  
                </join-columns>  
                <inverse-join-columns>  
                    <join-column id="Group_id" referenced-column-name="id" />  
                </inverse-join-columns>  
            </join-table>  
        </many-to-many>  
    </entity>  
 </doctrine-mapping>
```

**YAML**

```yaml
User:  
    type: entity  
    manyToMany:  
        groups:  
            targetEntity: Group  
            joinTable:  
                name: User_Group  
                joinColumns:  
                    User_id:  
                        referencedColumnName: id  
                inverseJoinColumns:  
                    Group_id:  
                        referencedColumnName: id
```

В этом варианте имя таблицы, используемой для связи по умолчанию соответствует неполным именам участвующих в отношении классов, разделенных символом подчеркивания. Имена столбцов в этой таблице по умолчанию складывается из неполного имени целевого класса с суффиксом “**_id**“. Параметр **referencedColumnName** по умолчанию всегда равен “**id**“, это справедливо как для отношений “один к одному”, так и для “многие к одному”.

Если вас устраивают значения по-умолчанию можно не писать лишнего кода.

# 6.4. Инициализация коллекций

При работе с полями, содержащими коллекции сущностей стоит быть внимательным. Допустим, у нас есть сущность _User_, которая содержит коллекцию групп:

```php
<?php  
/** @Entity */  
class User  
{  
    /** @ManyToMany(targetEntity="Group") */  
    private $groups;  

    public function getGroups()  
    {  
        return $this->groups;  
    }  
}
```

Если рассматривать этот код отдельно, то видно, что поле _$groups_ является только экземпляром класса _Doctrine\Common\Collections\Collection_, и пользователь может запросить его с помощью соответствующего метода. Однако,  сразу после создания объекта _User_ поле _$groups_, очевидно, будет иметь значение _NULL_.

Такие поля нужно заранее инициализировать в конструкторе пустыми объектами _ArrayCollection_:

```php
<?php  
use Doctrine\Common\Collections\ArrayCollection;  

/** @Entity */  
class User  
{  
    /** @ManyToMany(targetEntity="Group") */  
    private $groups;  

    public function __construct()  
    {  
        $this->groups = new ArrayCollection();  
    }  

    public function getGroups()  
    {  
        return $this->groups;  
    }  
}
```

Вот. И теперь следующий код будет нормально работать даже если сущность еще не связана с менеджером _EntityManager_:

```php
<?php  
$group = $entityManager->find('Group', $groupId);  
$user = new User();  
$user->getGroups()->add($group);
```

# 6.5. Валидация отображений в различных средах (Runtime и Development)

По причинам, связанным с производительностью _Doctrine 2_ не производит полную валидацию связи, т.е. проверку на предмет того правильно ли она мэппится на схему базы данных. Нужно самостоятельно проверять корректно ли настроена та или иная связь. Сделать это через командную строку:

```
doctrine orm:validate-schema
```

Либо выполнить валидацию вручную:

```php
<?php  
use Doctrine\ORM\Tools\SchemaValidator;  

$validator = new SchemaValidator($entityManager);  
$errors = $validator->validateMapping();  

if (count($errors) > 0) {  
    // Lots of errors!  
    echo implode("\n\n", $errors);  
}
```

> Если с отображением что-то не так, массив $errors будет содержать сообщения об ошибках. Единственный параметр, валидация которого не производится, это referencedColumnName. Он должен всегда равняться первичному ключу, иначе Doctrine вообще не будет работать.

> Основная ошибка заключается в использовании обратного слеша в полном имени класса. Когда вы записываете это имя в виде строки (например в настройках отображения) обратный слеш в начале строки нужно убрать. Для обратной совместимости PHP делает это с помощью функции get_class() или с помощью механизма рефлексии.

# 6.6. Отношения “один к одному”, односторонние

Односторонние связи типа “один к одному” являются, наверное, самыми распространенными. Вот вам пример: сущность _Product_(товар) имеет один объект _Shipping_ (отгрузка товара). При этом в _Shipping_ нет ссылки обратно на _Product_, поэтому отношение и называется односторонним: _Product -> Shipping_.

**PHP**

```php
/** @Entity */  
class Product  
{  
    // ...  

   /**  
    * @OneToOne(targetEntity="Shipping")  
    * @JoinColumn(name="shipping_id", referencedColumnName="id")  
    */  
    private $shipping;  

   // ...  
}  

/** @Entity */  
class Shipping  
{  
    // ...  
}
```

**XML**

```xml
<doctrine-mapping>  
    <entity class="Product">  
        <one-to-one field="shipping" target-entity="Shipping">  
            <join-column name="shipping_id" referenced-column-name="id" />  
        </one-to-one>  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
Product:  
    type: entity  
    oneToOne:  
        shipping:  
            targetEntity: Shipping  
            joinColumn:  
                name: shipping_id  
                referencedColumnName: id
```

Обратите внимание, что использовать аннотацию @JoinColumn здесь не обязательно, т.к. значение по умолчанию дадут то же результат.

Итоговая схема MySQL будет выглядеть так:

```sql
CREATE TABLE Product (  
    id INT AUTO_INCREMENT NOT NULL,  
    shipping_id INT DEFAULT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

CREATE TABLE Shipping (  
    id INT AUTO_INCREMENT NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

ALTER TABLE Product ADD FOREIGN KEY (shipping_id) REFERENCES Shipping(id);
```

# 6.7. Отношения “один к одному”, двусторонние

В качестве примера возьмем отношения между объектами _Customer_ (заказчик) и _Cart_ (корзина). Смотрите, у _Cart_ есть обратная ссылка на _Customer_, поэтому эта связь является двусторонней:

**PHP**

```php
/** @Entity */  
class Customer  
{  
    // ...  

   /**  
    * @OneToOne(targetEntity="Cart", mappedBy="customer")  
    */  
    private $cart;  

   // ...  
}  

/** @Entity */  
class Cart  
{  
    // ...  

   /**  
    * @OneToOne(targetEntity="Customer", inversedBy="cart")  
    * @JoinColumn(name="customer_id", referencedColumnName="id")  
    */  
    private $customer;  

   // ...  
}
```

**XML**

```xml
<doctrine-mapping>  
    <entity name="Customer">  
        <one-to-one field="cart" target-entity="Cart" mapped-by="customer" />  
    </entity>  
    <entity name="Cart">  
        <one-to-one field="customer" target-entity="Customer" inversed-by="cart">  
            <join-column name="customer_id" referenced-column-name="id" />  
        </one-to-one>  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
Customer:  
    oneToOne:  
        cart:  
            targetEntity: Cart  
            mappedBy: customer  
Cart:  
    oneToOne:  
        customer:  
            targetEntity: Customer  
            inversedBy: cart  
            joinColumn:  
                name: customer_id  
                referencedColumnName: id
```

Обратите внимание, что использовать аннотацию _@JoinColumn_ здесь не обязательно, т.к. значение по-умолчанию дадут то же результат.

Итоговая схема _MySQL_ будет выглядеть так:

```sql
CREATE TABLE Cart (  
    id INT AUTO_INCREMENT NOT NULL,  
    customer_id INT DEFAULT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

CREATE TABLE Customer (  
    id INT AUTO_INCREMENT NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

ALTER TABLE Cart ADD FOREIGN KEY (customer_id) REFERENCES Customer(id);
```

Посмотрите как определен внешний ключ на прямой стороне отношения — таблице _Cart_.

# 6.8. Отношения “один к одному” со ссылкой на себя же

Такие отношения в _Doctrine_ реализовываются весьма просто:

```php
/** @Entity */  
class Student  
{  
    // ...  

   /**  
    * @OneToOne(targetEntity="Student")  
    * @JoinColumn(name="mentor_id", referencedColumnName="id")  
    */  
    private $mentor;  

   // ...  
}
```

Обратите внимание, что использовать аннотацию _@JoinColumn_ здесь не обязательно, т.к. значение по умолчанию дадут то же результат.

Итоговая схема _MySQL_ будет выглядеть так:

```sql
CREATE TABLE Student (  
    id INT AUTO_INCREMENT NOT NULL,  
    mentor_id INT DEFAULT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

ALTER TABLE Student ADD FOREIGN KEY (mentor_id) REFERENCES Student(id);
```

# 6.9. Отношения “один ко многим”, односторонние, с использованием @JoinTable

Односторонние связи типа “один ко многим” можно определять через подсоединяемую таблицу. С точки зрения Doctrine это выглядит как одностороннее отношение “многие ко многим”, где у одной из подсоединяемых колонок указан флаг уникальности, это и обеспечивает функционирование подобно отношениям “один ко многим”. Следующий пример описывает сказанное:

**PHP**

```php
<?php  
/** @Entity */  
class User  
{  
    // ...  

    /**  
     * @ManyToMany(targetEntity="Phonenumber")  
     * @JoinTable(name="users_phonenumbers",  
     * joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},  
     * inverseJoinColumns={@JoinColumn(name="phonenumber_id", referencedColumnName="id", unique=true)}  
     * )  
     */  
    private $phonenumbers;  

    public function __construct() {  
        $this->phonenumbers = new \Doctrine\Common\Collections\ArrayCollection();  
    }  

    // ...  
}  

/** @Entity */  
class Phonenumber  
{  
    // ...  
}
```

**XML**

```xml
<doctrine-mapping>  
    <entity name="User">  
        <many-to-many field="phonenumbers" target-entity="Phonenumber">  
            <join-table name="users_phonenumbers">  
                <join-columns>  
                    <join-column name="user_id" referenced-column-name="id" />  
                </join-columns>  
                <inverse-join-columns>  
                    <join-column name="phonenumber_id" referenced-column-name="id" unique="true" />  
                </inverse-join-columns>  
            </join-table>  
        </many-to-many>  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
User:  
    type: entity  
    manyToMany:  
        phonenumbers:  
            targetEntity: Phonenumber  
            joinTable:  
                name: users_phonenumbers  
                joinColumns:  
                    user_id:  
                    referencedColumnName: id  
                inverseJoinColumns  
                    phonenumber_id:  
                        referencedColumnName: id  
                        unique: true
```

> Описанные отношения работают только с использованием аннотации @ManyToMany совместно с ограничителем unique.

Итоговая схема _MySQL_ будет выглядеть так:

```sql
CREATE TABLE USER (  
    id INT AUTO_INCREMENT NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

CREATE TABLE users_phonenumbers (  
    user_id INT NOT NULL,  
    phonenumber_id INT NOT NULL,  
    UNIQUE INDEX users_phonenumbers_phonenumber_id_uniq (phonenumber_id),  
    PRIMARY KEY(user_id, phonenumber_id)  
) ENGINE = InnoDB;  

CREATE TABLE Phonenumber (  
    id INT AUTO_INCREMENT NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

ALTER TABLE users_phonenumbers ADD FOREIGN KEY (user_id) REFERENCES USER(id);  
ALTER TABLE users_phonenumbers ADD FOREIGN KEY (phonenumber_id) REFERENCES Phonenumber(id);
```

# 6.10. Отношения “многие к одному”, односторонние

Отношение типа “многие к одному” определяются следующим образом:

**PHP**

```php
/** @Entity */  
class User  
{  
    // ...  

    /**  
     * @ManyToOne(targetEntity="Address")  
     * @JoinColumn(name="address_id", referencedColumnName="id")  
     */  
    private $address;  
}  

/** @Entity */  
class Address  
{  
    // ...  
}
```

**XML**

```xml
<doctrine-mapping>  
    <entity name="User">  
       <many-to-one field="address" target-entity="Address" />  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
User:  
    type: entity  
    manyToOne:  
        address:  
            targetEntity: Address
```

> Обратите внимание, что использовать аннотацию @JoinColumn здесь не обязательно, т.к. по умолчанию и так будут использоваться колонки address_id и id. Можно их и не указывать.

Итоговая схема MySQL будет выглядеть так:

```sql
CREATE TABLE USER (  
    id INT AUTO_INCREMENT NOT NULL,  
    address_id INT DEFAULT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

CREATE TABLE Address (  
    id INT AUTO_INCREMENT NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

ALTER TABLE USER ADD FOREIGN KEY (address_id) REFERENCES Address(id);
```

# 6.11. Отношения “один ко многим”, двусторонние

Двусторонние отношения вида “один ко многим” весьма распространены. Следующий пример показывает их реализацию на примере классов _Product_ и _Feature_:

**XML**

```xml
<doctrine-mapping>  
    <entity name="Product">  
        <one-to-many field="features" target-entity="Feature" mapped-by="product" />  
    </entity>  
    <entity name="Feature">  
        <many-to-one field="product" target-entity="Product" inversed-by="features">  
            <join-column name="product_id" referenced-column-name="id" />  
        </many-to-one>  
    </entity>  
</doctrine-mapping>
```

Обратите внимание, что использовать аннотацию_ @JoinColumn_ здесь не обязательно, т.к. значение по умолчанию дадут то же результат.

Итоговая схема _MySQL_ будет выглядеть так:

```sql
CREATE TABLE Product (  
    id INT AUTO_INCREMENT NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

CREATE TABLE Feature (  
    id INT AUTO_INCREMENT NOT NULL,  
    product_id INT DEFAULT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

ALTER TABLE Feature ADD FOREIGN KEY (product_id) REFERENCES Product(id);
```

# 6.12. Отношения “один ко многим” со ссылкой на себя

Пример показывает как настроить иерархию объектов Category с помощью отношения, ссылающегося на само себя. Этот подход позволяет реализовать иерархию категорий, в терминах БД называемой “списком смежных вершин”.

**PHP**

```php
<?php  
/** @Entity */  
class Category  
{  
    // ...  
    /**  
     * @OneToMany(targetEntity="Category", mappedBy="parent")  
     */  
    private $children;  

    /**  
     * @ManyToOne(targetEntity="Category", inversedBy="children")  
     * @JoinColumn(name="parent_id", referencedColumnName="id")  
     */  
    private $parent;  
    // ...  

    public function __construct() {  
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();  
    }  
}
```

**XML**

```xml
<doctrine-mapping>  
    <entity name="Category">  
        <one-to-many field="children" target-entity="Category" mapped-by="parent" />  
        <many-to-one field="parent" target-entity="Category" inversed-by="children" />  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
Category:  
    type: entity  
    oneToMany:  
        children:  
            targetEntity: Category  
            mappedBy: parent  
    manyToOne:  
        parent:  
            targetEntity: Category  
            inversedBy: children
```

Обратите внимание, что использовать аннотацию _@JoinColumn_ здесь не обязательно, т.к. значение по умолчанию дадут то же результат.

Итоговая схема _MySQL_ будет выглядеть так:

```sql
CREATE TABLE Category (  
    id INT AUTO_INCREMENT NOT NULL,  
    parent_id INT DEFAULT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

ALTER TABLE Category ADD FOREIGN KEY (parent_id) REFERENCES Category(id);
```

# 6.13. Отношения “многие ко многим”, односторонние

В реальных преложениях отношения типа “многие ко многим” встречаются реже. Следующий пример показывает как они определяются на примере сущностей _User_ и _Group_:

**PHP**

```php
<?php  
/** @Entity */  
class User  
{  
    // ...  

    /**  
     * @ManyToMany(targetEntity="Group")  
     * @JoinTable(name="users_groups",  
     * joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},  
     * inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}  
     * )  
     */  
    private $groups;  

    // ...  

    public function __construct() {  
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();  
    }  
}  

/** @Entity */  
class Group  
{  
    // ...  
}
```

**XML**

```xml
<doctrine-mapping>  
    <entity name="User">  
        <many-to-many field="groups" target-entity="Group">  
            <join-table name="users_groups">  
                <join-columns>  
                    <join-column name="user_id" referenced-column-name="id" />  
                </join-columns>  
                <inverse-join-columns>  
                    <join-column name="group_id" referenced-column-name="id" />  
                </inverse-join-columns>  
            </join-table>  
        </many-to-many>  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
User:  
    type: entity  
    manyToMany:  
        groups:  
            targetEntity: Group  
            joinTable:  
                name: users_groups  
                joinColumns:  
                    user_id:  
                        referencedColumnName: id  
                inverseJoinColumns:  
                    group_id:  
                        referencedColumnName: id
```

Итоговая схема MySQL будет выглядеть так:

```sql
CREATE TABLE USER (  
    id INT AUTO_INCREMENT NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

CREATE TABLE users_groups (  
    user_id INT NOT NULL,  
    group_id INT NOT NULL,  
    PRIMARY KEY(user_id, group_id)  
) ENGINE = InnoDB;  

CREATE TABLE GROUP (  
    id INT AUTO_INCREMENT NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

ALTER TABLE users_groups ADD FOREIGN KEY (user_id) REFERENCES USER(id);  
ALTER TABLE users_groups ADD FOREIGN KEY (group_id) REFERENCES GROUP(id);
```

> Так почему же такие связи реже встречаются в повседневной жизни? Все дело в том, что часто вам нужно привязать к связи какие-то дополнительные атрибуты, для чего под эту связь потребуется создать отдельный класс (связь ManyToMany это сделать не позволяет, здесь лишь будет создана таблица с двумя столбцами.) И, как следствие, связь “многие ко многим” в явном виде исчезает, а вместо нее появятся уже две связи — “один ко многим” и “многие к одному”, связывающие между собой три отдельных класса.

# 6.14. Отношения “многие ко многим”, двусторонние

Эти отношения аналогичны описанным выше, но они двусторонние:

**PHP**

```php
<?php  
/** @Entity */  
class User  
{  
    // ...  

    /**  
     * @ManyToMany(targetEntity="Group", inversedBy="users")  
     * @JoinTable(name="users_groups")  
     */  
    private $groups;  

    public function __construct() {  
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();  
    }  

    // ...  
}  

/** @Entity */  
class Group  
{  
    // ...  
    /**  
     * @ManyToMany(targetEntity="User", mappedBy="groups")  
     */  
    private $users;  

    public function __construct() {  
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();  
    }  

    // ...  
}
```

**XML  

```xml
<doctrine-mapping>  
    <entity name="User">  
        <many-to-many field="groups" inversed-by="users">  
            <join-table name="users_groups">  
                <join-columns>  
                    <join-column name="user_id" referenced-column-name="id" />  
                </join-columns>  
                <inverse-join-columns>  
                    <join-column name="group_id" referenced-column-name="id" />  
                </inverse-join-columns>  
            </join-table>  
        </many-to-many>  
    </entity>  

    <entity name="Group">  
        <many-to-many field="users" mapped-by="groups" />  
    </entity>  
</doctrine-mapping>
```

**YAML**

```yaml
User:  
    type: entity  
    manyToMany:  
        groups:  
            targetEntity: Group  
            inversedBy: users  
            joinTable:  
                name: users_groups  
                joinColumns:  
                    user_id:  
                        referencedColumnName: id  
                inverseJoinColumns:  
                    group_id:  
                        referencedColumnName: id  

Group:  
    type: entity  
    manyToMany:  
        users:  
            targetEntity: User  
            mappedBy: groups
```

Итоговая схема базы данных будет такая же как в предыдущем примере для односторонней связи.

## 6.14.1. Picking Owning and Inverse Side

Для связей “многие ко многим” можно указать какая сущность представляет прямую, а какая обратную сторону связи. Чтобы вам как разработчкику было проще определиться с тем, какая из сущностей больше подходит на роль прямой стороны связи, используйте следующее правило. Просто ответьте на вопрос, какая из сущностей отвечает за управление соединением, и это и будет прямая сторона.

Для примера возьмем две сущности: _Article_ (статья) и _Tag_ (тег). Всякий раз, когда вам нужно связать эти две сущности, в большинстве случаев именно _Article_ будет отвечать за эту связь. И всякий раз при создании новой статьи, вам нужно буде соединить ее с существующими или новыми тегами. HTML-форма, отвечающая за создание статей вероятно так и работает, позволяя непосредственно указывать теги. Вот почему в качестве прямой стороны нужно выбрать _Article_, ваш код в этом случае будет более понятен, т.к. вы создаете модель в _Doctrine_ в соответствии с тем, как эта связь функционирует в реальной жизни:

```php
<?php  
class Article  
{  
    private $tags;  

    public function addTag(Tag $tag)  
    {  
        $tag->addArticle($this); // synchronously updating inverse side  
        $this->tags[] = $tag;  
    }  
}  

class Tag  
{  
    private $articles;  

    public function addArticle(Article $article)  
    {  
       $this->articles[] = $article;  
    }  
}
```

Это позволит разместить механизм добавления тегов на Article-стороне связи:

```php
<?php  
$article = new Article();  
$article->addTag($tagA);  
$article->addTag($tagB);
```

# 6.15.  Отношения “многие ко многим” со ссылкой на себя

Да, они могу ссылаться на сами себя. Типичный сценарий выглядит так: у пользователя _User_ есть друзья, при этом целевая сущность этого отношения это тоже _User_, таким образом имеет место ссылка на самого себя. В этом примере используется двусторонняя связь: у _User_ есть поле _$friendsWithMe_ и поле _$myFriends_.

```php
<?php  
/** @Entity */  
class User  
{  
    // ...  

    /**  
     * @ManyToMany(targetEntity="User", mappedBy="myFriends")  
     */  
    private $friendsWithMe;  

    /**  
     * @ManyToMany(targetEntity="User", inversedBy="friendsWithMe")  
     * @JoinTable(name="friends",  
     * joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},  
     * inverseJoinColumns={@JoinColumn(name="friend_user_id", referencedColumnName="id")}  
     * )  
     */  
    private $myFriends;  

    public function __construct() {  
        $this->friendsWithMe = new \Doctrine\Common\Collections\ArrayCollection();  
        $this->myFriends = new \Doctrine\Common\Collections\ArrayCollection();  
    }  

    // ...  
}
```

Схема БД:

```sql
CREATE TABLE USER (  
    id INT AUTO_INCREMENT NOT NULL,  
    PRIMARY KEY(id)  
) ENGINE = InnoDB;  

CREATE TABLE friends (  
    user_id INT NOT NULL,  
    friend_user_id INT NOT NULL,  
    PRIMARY KEY(user_id, friend_user_id)  
) ENGINE = InnoDB;  

ALTER TABLE friends ADD FOREIGN KEY (user_id) REFERENCES USER(id);  
ALTER TABLE friends ADD FOREIGN KEY (friend_user_id) REFERENCES USER(id);
```

# 6.16. Сортировка коллекций в связях “To-Many”

Во многих случаях при запросе сущности из БД вам нужно получать коллекции в уже отсортированном виде. Чтобы сделать это нужно определить для коллекции аннотацию _@OrderBy_. В этой аннотации указывается специальное DQL-выражение, которое будет добавляться ко всем запросам к этой коллекции. Описать _@OrderBy_ для аннотаций _@OneToMany_ или _@ManyToMany_можно так:

```php
<?php  
/** @Entity */  
class User  
{  
    // ...  

    /**  
     * @ManyToMany(targetEntity="Group")  
     * @OrderBy({"name" = "ASC"})  
     */  
    private $groups;  
}
```

DQL должен состоять только из “чистых” имен полей без кавычек, а также опционального параметра ASC/DESC. Если нужна сортировка по нескольким полям, они разделяются запятой. Имена столбцов в этом выражении должны существовать в классе _targetEntity_, который описывается в аннотациях _@ManyToMany_ и _@OneToMany_.

Семантику использования этой функции можно описать так:

*   _@OrderBy_ выступает в роли неявного выражения _ORDER BY_, который будет явно добавляться к запросу при выборке набора.
*   Все такие коллекции всегда будут загружаться уже упорядоченными.
*   Чтоб сильно не влиять на работу БД этот неявный _ORDER BY_ добавляется к запросу только если коллекция выбирается явно с подсоединением (fetch joined).

Для вышеприведенного примера следующий DQL-запрос не будет добавлять _ORDER BY_, потому что сущность **g** здесь не присоединяется к запросу явно:

```sql
SELECT u FROM USER u JOIN u.groups g WHERE SIZE(g) > 10
```

Однако следующий пример:

```sql
SELECT u, g FROM USER u JOIN u.groups g WHERE u.id = 10
```

Будет автоматически переписан в:

```sql
SELECT u, g FROM USER u JOIN u.groups g WHERE u.id = 10 ORDER BY g.name ASC
```

И поменять порядок, явно указав его в DQL, нельзя:

```sql
SELECT u, g FROM USER u JOIN u.groups g WHERE u.id = 10 ORDER BY g.name DESC
```

Это будет автоматически переписано в:

```sql
SELECT u, g FROM USER u JOIN u.groups g WHERE u.id = 10 ORDER BY g.name DESC, g.name ASC
```
