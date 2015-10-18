# PHP-Noorm
[![Build Status](https://travis-ci.org/tdebatty/php-noorm.svg?branch=master)](https://travis-ci.org/tdebatty/php-noorm) [![Latest Stable Version](https://img.shields.io/packagist/v/webd/noorm.svg)](https://packagist.org/packages/webd/noorm) [![API](http://api123.io/api123-head.svg)](http://api123.io/api/PHP-Noorm/head/index.html)

No-ORM storage of PHP object.

PHP-Noorm makes it easy to persist your PHP object, without the overhead of Object-Relation Mapping (ORM).

## Installation
Install the latest version using composer:

```bash
$ composer require webd/noorm
```

## Quickstart

First, load composer, and define a directory where your objects can be saved (it has to be writable).
```php
require_once __DIR__  . "/../vendor/autoload.php";
use noorm\Persistent;

// Indicate where to save data
Persistent::SetDirectory("/tmp/noorm-example");
```

Define the classes that you want to persist. They have to extend the class [**\noorm\Persitent**](./doc/persistent.md).
```php
class Client extends Persistent {

  public $name = "";
  private $val;
  
  /**
   * Use annotations to indicate $items is a many-to-many relation to class Item
   * @target Item
   * @var \noorm\ManyToManyRelation
   */
  public $items;
  
  public function SetName($name) {
    $this->name = $name;
    return $this;
  }

  public function GetVal() {
    return $this->val;
  }

  public function SetVal($val) {
    $this->val = $val;
    return $this;
  }
}

class Item extends Persistent {
  
  public $name = "";
  
  /**
   * Use annotations to indicate $clients is a many-to-many relation to Client
   * @target Client
   * @var \noorm\ManyToManyRelation
   */
  public $clients;
}
```

You can now create objects and save them on disk.
```php
// Create a new object and save it to disk
$client = new Client();
$client->name = "C1";
$client->Save();
```

The static method **All()** returns a [**\noorm\Dataset**](./doc/dataset.md) representing all the saved objects of this class. You can use this dataset to filter, sort or list your objects.
```php
// Show all clients
/* @var $client Client */
foreach (Client::All()->Collect() as $client) {
  echo $client->name . " : ";
  echo $client->items->Get()->Count() . " items\n";
}
```

PHP-Noorm also manages [**many-to-many relations**](./doc/many-to-many.md) for you. These are described using annotations in your class definition.
```php
// Create a new item
$item = new Item();
$item->name = "I";
$item->Save();

// Add this item to the first client
$client = Client::All()->First();
$client->items->Add($item);
```

## Known bugs and limitations

These are planned improvements:
- There can be only one many-to-many relation between two classes;
- You cannot define a many-to-many relation between a class and itself;
- All() will eagerly load all objects, which is memory expensive.
