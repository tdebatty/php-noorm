<?php namespace noorm;

class ManyToManyDataset extends Dataset
{

  private $parent;

  /**
   * E.g.: Person_Course_123 = courses linked to Person 123
   * @return type
   */
  protected function direct_file() {
    return Persistent::GetDirectory() . DIRECTORY_SEPARATOR . 
        str_replace('\\', '_', trim(get_class($this->parent), '\\')) . '_' . 
        str_replace('\\', '_', $this->collection) . '_' .
        $this->parent->Id();
  }
  
  protected function reverse_file(Persistent $other) {
    return Persistent::GetDirectory() . DIRECTORY_SEPARATOR . 
        str_replace('\\', '_', $this->collection) . '_' .
        str_replace('\\', '_', trim(get_class($this->parent), '\\')) . '_' . 
        $other->Id();
  }
      
  public function __construct($other_collection, Persistent $parent) {
    $this->collection = $other_collection;
    $this->parent = $parent;
  }
  
  public function Load() {
    // Read existing relations
    $file = $this->direct_file();
    
    if (is_file($file)) {
      $ids = file($file);
      $collection = $this->collection;
      foreach ($ids as $id) {
        $this->data[] = $collection::One(trim($id));
      }
    }
  }
  
  /**
   * Add a relation to an object from other collection
   * The reverse link is diretly saved
   * 
   * @param Persistent $other
   */
  public function Add(Persistent $other) {
    $this->data[] = $other;
    
    // Add relation to direct list
    $file = $this->direct_file();
    file_put_contents(
        $file,
        $other->Id() . "\n",
        FILE_APPEND | LOCK_EX);
    
    // Add relation to reverse list
    $reverse_file = $this->reverse_file($other);
    file_put_contents(
        $reverse_file,
        $this->parent->Id() . "\n",
        FILE_APPEND | LOCK_EX);
  }
  
  public function Remove (Persistent $other) {
    
  }
  
}



