# Dataset

A Dataset represents a collection objects from a givent class. 

Dataset methods are loosely inspired from Spark Resilient Distributed Datasets (RDD). There are two kinds of operations: Transformations and Actions

## Transformations

Transformations allow to modify the dataset, but do not actually return data. All transformation methods return the dataset itself.

| Transformation | Meaning |
|--------|--------|
| **Filter**( Closure $func )       | Filter the objects in the dataset. The filter function must return true or false. |
| **Limit**( integer $offset, integer $length ) | Just like SQL LIMIT. |
| **SortBy**( String $field, boolean $ascending = true ) | Sort objects by one of the fields (public, protected or private). |
| **Sort**( Closure $func ) | Sort the dataset using a user defined function. |
| **Map**( Closure $func ) | Apply a function to all elements in the dataset. |
| **FlatMap**( Closure $func ) | Apply a function to all elements in the dataset. The function must return an array of items, that will all be merged to form a single (bigger) Dataset. |

Here is an example:

```php
require_once __DIR__  . "/../vendor/autoload.php";
use noorm\Persistent;

// Indicate where to save data
Persistent::SetDirectory("/tmp/noorm-example");

class House extends Persistent {
  public $price = 0;
  public $rooms = 0;
  public $size = 0; // in m²
}

// Create some houses
for ($i = 0; $i < 20; $i++) {
  $house = new House();
  $house->price = rand(100000, 200000);
  $house->rooms = rand(1, 5);
  $house->size = rand(80, 250);
  $house->Save();
}

var_dump(House::All()
    // Sort by price / m²
    ->Sort(function($house1, $house2){
      return $house1->price / $house1->size > $house2->price / $house2->size;
    })
    // Take the 10 with highest price / m²
    ->Limit(0, 10)
    // Keep only the number of rooms
    ->Map(function($house){ return $house->rooms; })
    ->Collect());
```

## Actions

Actions allow to actually collect the data from the dataset.

| Action | Meaning |
|--------|--------|
| **Collect( )** | Return the content of the dataset as an array |
| **First( )** | Return the first element in the dataset |
| **Count( )** | Return the number of elements in the dataset |
| **Reduce**( Closure $func, mixed $initial = null ) | Combine all elements using a reduce operation |

For example, to get the total number of rooms in all our rooms:

```php
var_dump(House::All()->Reduce(
    function($aggregator, $house){
      return $house->rooms + $aggregator;
    }, 0));
```
