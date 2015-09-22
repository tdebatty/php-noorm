<?php

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
/*
for ($i = 0; $i < 20; $i++) {

  $house = new House();
  $house->price = rand(100000, 200000);
  $house->rooms = rand(1, 5);
  $house->size = rand(80, 250);
  $house->Save();
}
 * 
 */

var_dump(House::All()
    // Sort by price / m²
    ->Sort(function($house1, $house2){
      return $house1->price / $house1->size > $house2->price / $house2->size;
    })
    // Take the 10 with highest price
    ->Limit(0, 10)
    // Keep only the number of rooms
    ->Map(function($house){ return $house->rooms; })
    ->Collect());
    
    
    
var_dump(House::All()->Reduce(
    function($aggregator, $house){
      return $house->rooms + $aggregator;
    }, 0));