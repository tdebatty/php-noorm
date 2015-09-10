<?php namespace noorm;

/**
 * Description of ManyToManyRelation
 *
 * @author Thibault Debatty
 */
class ManyToManyRelation {
  
  private $parent;
  private $target;
  private $file;
  private $modified = false;
  private $ids = array();
  
  public function __construct($target, Persistent $parent) {
    $this->parent = $parent;
    $this->target = $target;
    
    $this->file = Persistent::GetDirectory() . DIRECTORY_SEPARATOR . 
        str_replace('\\', DIRECTORY_SEPARATOR, trim(get_class($this->parent), '\\')) . DIRECTORY_SEPARATOR . 
        str_replace('\\', DIRECTORY_SEPARATOR, trim($this->target, '\\')) . DIRECTORY_SEPARATOR .
        $this->parent->Id();
    
    if (!is_file($this->file)) {
      return;
    }
    
    // Pay attention, each id will end with a "\n" !
    $this->ids = file($this->file);
  }
  
  public function __destruct() {
    if (!$this->modified) {
      return;
    }
    
    // Save the file
    if (!is_dir(dirname($this->file))) {
      mkdir(dirname($this->file), 0700, true);
    }
    
    file_put_contents(
        $this->file,
        implode("\n", $this->ids),
        LOCK_EX);
  }
  
  /**
   * 
   * @return \noorm\Dataset
   */
  public function Get() {
    $ds = new Dataset($this->target);
    $ds->LoadIds($this->ids);
    return $ds;
  }
  
  public function Add(Persistent $other) {
    $this->AddNP($other);
    
    // Add to the reverse relation
    $reverse_attribute = Persistent::$m2m_relations[get_class($other)][get_class($this->parent)];
    $other->$reverse_attribute->AddNP($this->parent);
  }
  
  /**
   * Add a relation to $other, and do not propagate the reverse relation
   * @param type $other
   */
  function AddNP($other) {
    $this->modified = true;
    $this->ids[] = $other->Id();
  }
  
  public function Remove(Persistent $other) {
    $this->RemoveNP($other);
    
    // Remove from reverse relation
    $reverse_attribute = Persistent::$m2m_relations[get_class($other)][get_class($this->parent)];
    $other->$reverse_attribute->RemoveNP($this->parent);
  }
  
  function RemoveNP($other) {
    $this->modified = true;
    
    foreach($this->ids as $key => $id) {
      if ($other->Id() === $id) {
        unset($this->ids[$key]);
        $this->ids = array_values($this->ids);
        break;
      }
    }
  }
}
