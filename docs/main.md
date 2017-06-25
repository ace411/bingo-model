# Bingo Model

The Bingo Model is a micro service, an ORM which abstracts SQL database interactions. The subsequent text is documentation of the library and should help you, the reader, understand how to go about using it.

## Installation

Before you can use the bingo-model software, you should have either Git or Composer installed on your system of preference. To install the package via Composer, type the following in your preferred command line interface:

```composer require chemem/bingo-model```

To install via Git, type:

```git clone https://github.com/ace411/bingo-model.git```


## Usage

The library enables those who choose to use it, the ability to generate insert, update, delete, join, as well as select statements in addition to providing support for transactions, parameter bindings, and function mapping.

### Establishing a SQL connection

In order to use any of the features that come with this package, you must first set up a connection to a database. PDO is the preferred and currently only supported means of creating this link. Fashion a script to connect to the database like this:

```php
require __DIR__ . '/vendor/autoload.php';

$pdo = new PDO(
    'mysql:host=localhost;dbname=DB_NAME;charset=utf8',
    DB_USER,
    DB_PASS
);

$query = new Chemem\Bingo\Model\Query($pdo);
```

The query object created in the above snippet is fundamentally useful and should persist throughout the rest of the documentation.

### Generating queries

Queries are essential building blocks of SQL interaction environments and can be created easily with the Bingo Model. Considering the functions used are curryied, an understanding of the parameter order should suffice.

#### Recurring parameters

```$fields``` An array of the fields to be included in query.

```$table``` The table on which the action will be performed.

```$condition``` The condition to further crystallize an action.

```$placeholders``` Colon prefixed values or question marks that can help with SQL parameter binding.

```$joinType``` The preferred SQL join type. Can be either LEFT, RIGHT, or just simply, JOIN.

#### Select queries

```php query object->select()(array $fields)(string $table)(string $condition)```

#### Insert queries

```php query object->insert()(string $table)(array $fields)(array $placeholders)```

#### Update queries

```php query object->update()(string $table)(string $condition)```

#### Delete queries

```php query object->update()(string $table)(string $condition)```

#### Conditions

```php query object->condition()(string $condition)```

#### Joins

The library currently supports only two table joins.

```php query object->join()(string $table)(string $table)(array $fields[])(string $joinType)```

#### Examples

```php
$select = $query->select()(['id', 'title'])('blog')('WHERE title LIKE :title'); //SELECT id, title FROM blog WHERE title LIKE :title

$insert = $query->insert()('blog')(['title', 'text'])([':title', ':text']); //INSERT INTO blog (posts, text) VALUES (':title', ':text')

$update = $query->update()('blog')('title = :title'); //UPDATE blog SET title = :title

$delete = $query->delete()('blog')('id = 12'); //DELETE FROM blog WHERE id = 12

$condition = $query->condition()('SET id = 21'); //SET id = 21

$join = $query->join()
    ('blog')
    ('token')
    ([
        ['id', 'token_id', 'title'],
        ['token_id', 'token_string']
    ])
    ('LEFT'); //SELECT blog.id, blog.token_id, blog.title, token.token_id, token.token_string FROM blog LEFT JOIN token ON blog.token_id = token.token_id

```

### Executing queries

Generated queries are useful but are nothing beyond string values. Executable queries are created by successively passing the right arguments to each of the curryied query functions. Executable queries usually appear in the format:

```php query object->query()(string $query)(array $parameters[])(string $fetchMethod)(callable $callback)```

The parameters are identified by keys ```param```, ```value``` and ```type``` which correspond to the ```paramBind()``` method arguments.

The ```$fetchMethod``` argument is a hyphenated lowercase function name of a specific fetch method and the ```$callback``` is a method only usable if the constant ```DB_CALLBACK_FETCH``` is set to true.

#### Example

```php
$rows = $query->query()
    ($select)
    ([
        ['param' => ':title', 'value' => '%awesome%', 'type' => PDO::PARAM_STR]
    ])
    ('fetch-all')
    ();

var_dump($rows); //returns all the available rows    
```
The conventional fetch methods supported by this library are ```fetchAll()```, ```fetch()```, and ```fetchColumn()``` which become ```fetch-all```, ```fetch```, and ```fetch-column``` parameters respectively.

Also, the data returned includes the **row count**, **error codes** and **messages**, as well as the **required records**.

### Using transactions

A transaction is a set of changes in a database. Initiating one requires using the Transaction class which, extends the Query class. A simple transaction looks like this:

```php
$transaction = new Chemem\Bingo\Model\Transaction($pdo);

$transaction->begin(); //initiate the transaction

$hello = $transaction->query()('SELECT "Hello"')()('fetch')(); //return the string "Hello"

$world = $transaction->query()('SELECT "World"')()('fetch')(); //return string "World"

$transaction->commit(); //commit the changes
```

The ```cancel()``` method is available in case you intend to roll back the changes made and restore a previous state.

### Using the Output class

The Output class is at its core, a very basic Functor that should provide some help dealing with database data. This class has three primary methods ```map()```, ```json()```, and ```getData()```. The first makes function mapping possible, the second converts the database data to json and the third, simply returns the data passed to the constructor.

The Output class can be used like this:

```php
//fetch some data from a database
$output = (new Chemem\Bingo\Model\Output($rows))->map(function ($data) {
    return array_keys($data); //returns all the array keys
});

$json = (new Chemem\Bingo\Model\Output($rows))->json(); //returns a json string
```
