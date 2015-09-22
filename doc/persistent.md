# Persistent
The persistent class allows to easily save (persist) and load objects.

## Saving objects
To save (persist) objects:
1. Choose a directory where to save your objects using **Persistent::SetDirectory( String $dir )**
2. Extend the Persistent class
3. Use the **Save()** method

That's it!

```php
require_once __DIR__  . "/../vendor/autoload.php";
\noorm\Persistent::SetDirectory("/tmp/noorm-example");

class Easy extends \noorm\Persistent {
  public $name;
}

$e = new Easy();
$e->name = "My name";
$e->Save();
```

## Loading objects
To load a single object, for which you know the id, use the static method **One( integer $id )**

```php
$id = $e->Id();
$same_e = Easy::One($id);

// this wil return true
var_dump($e === $same_e);
```

To load multiple objects, or an object for which you don't know the id, use the static method **All()** that will return a [Dataset](./dataset.md) representing all objects of your class. This Dataset can be used to further filter or process the objects.

```php
var_dump(Easy::All()->Count());
```