<?php

namespace noorm;

class Dataset {

  protected $collection;
  protected $data = array();

  public function __construct($collection) {
    $this->collection = $collection;
  }

  
  /**
   * 
   * @return \noorm\Dataset
   */
  public function Load() {
    $folder = Persistent::GetDirectory() . DIRECTORY_SEPARATOR . 
        str_replace('\\', DIRECTORY_SEPARATOR, trim($this->collection, '\\'));
    if (!is_dir($folder)) {
      return;
    }

    foreach (scandir($folder) as $file) {
      if ($file == "." || $file == "..") {
        continue;
      }

      $item = unserialize(file_get_contents($folder . DIRECTORY_SEPARATOR . $file));
      $this->data[] = $item;
    }
    return $this;
  }

  /* Transformations */

  /**
   * Filter the objects in the dataset.
   * The filter function must return true or false
   * For example, to get the number of houses with more than 3 rooms
   * House::All()
   *         ->Filter(function($house){return $house->rooms > 3; })
   *         ->Count();
   * 
   * @param \Closure $func
   * @return \dframework\Dataset
   */
  public function Filter(\Closure $func) {
    $this->data = \array_filter($this->data, $func);
    return $this;
  }

  /**
   * Just like SQL LIMIT
   * @param int $offset
   * @param int $length
   * @return \dframework\Dataset
   */
  public function Limit($offset, $length) {
    $this->data = array_slice($this->data, $offset, $length);
    return $this;
  }

  /**
   * Sort objects by one of the fields (public, protected or private)
   * Eg to get the 10 best students:
   * Student::All()
   *     ->SortBy("score", false)
   *     ->Limit(0, 10)
   *     ->Collect();
   * @param String $field
   * @param boolean $ascending
   * @return \dframework\Dataset
   */
  public function SortBy($field, $ascending = true) {
    $reflection = new \ReflectionClass($this->collection);
    $property = $reflection->getProperty($field);
    $property->setAccessible(true);

    usort($this->data, function($a, $b) use($property, $ascending) {
      return !$ascending XOR $property->getValue($a) > $property->getValue($b);
    });

    return $this;
  }

  /**
   * Apply a function to all elements in the dataset.
   * E.g.
   * House::All()
   *     ->Map(function($house){ return $house->rooms; })
   *     ->Collect();
   * @param \Closure $func
   * @return \noorm\Dataset
   */
  public function Map(\Closure $func) {
    $this->data = array_map($func, $this->data);
    return $this;
  }

  /**
   * Apply a function to all elements in the dataset. The function must return an
   * array of items, that will all be merged in a single Dataset.
   * E.g.
   * Measure::All()
   *     ->FlatMap(function($measure){
   *         return new array($measure->value, $measure->value * $measure->value); })
   *     ->Collect();
   * @param \Closure $func
   * @return \noorm\Dataset
   */
  public function FlatMap(\Closure $func) {

    $d = array();
    foreach ($this->data as $v) {
      $r = $func($v);
      if (!is_array($r)) {
        throw new Exception("Function $func should return an array!");
      }
      $d = array_merge($d, $r);
    }
    $this->data = $d;
    return $this;
  }

  /**
   * Sort the dataset using a user defined function.
   * E.g.
   * Result::All()
   *     ->Sort(function($r1, $r2){ return $r1->value >= $r2->value })
   *     ->Limit(0, 10)
   *     ->Collect();
   * 
   * @param \Closure $func
   * @return \noorm\Dataset
   */
  public function Sort(\Closure $func) {
    usort($this->data, $func);
    return $this;
  }

  /* Actions */

  /**
   * Return the content of the dataset as an array
   * @return mixed[]
   */
  public function Collect() {
    return $this->data;
  }

  /**
   * Return the first element in the dataset
   * @return Persistent || false 
   */
  public function First() {
    if (count($this->data) == 0) {
      return false;
    }
    reset($this->data);
    return current($this->data);
  }

  /**
   * Return the number of elements in the dataset
   * @return int
   */
  public function Count() {
    return count($this->data);
  }

  public function Reduce(\Closure $func, $initial = null) {
    return array_reduce($this->data, $func, $initial);
  }
}
