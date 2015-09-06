<?php namespace noorm;

use \zpt\anno\Annotations;

abstract class Persistent
{
    
    protected static $DIR;
    
    /**
     * Returns a Dataset representing all saved objects of this class
     * @return Dataset
     */
    public static function All() {
        $data = new Dataset(\get_called_class());
        $data->Load();
        return $data;
    }
    
    /**
     * Fetch one saved object by id
     * @param int $id
     * @return Persistent
     */
    public static function One($id) {
        $file = self::File($id);
        return unserialize(file_get_contents($file));
    }
    
    /**
     * Create and return an object.
     * ! your class should implement an empty constructor !
     * @return Persistent
     */
    public static function Factory() {
      $class = get_called_class();
      return new $class;
    }
    
    protected static function File($id) {
        
        // A valid class name starts with a letter or underscore, followed by any number of letters, numbers, or underscores.
        // If using namespaces, replace \ by DIRECTORY_SEPARATOR
        return self::$DIR . DIRECTORY_SEPARATOR .
                str_replace('\\', DIRECTORY_SEPARATOR, trim(get_called_class(), '\\')) . DIRECTORY_SEPARATOR . 
                $id . ".phpd";
    }
    
    /**
     * Set directory where all persistent objects and relations will be saved.
     * @param String $dir
     * @throws \InvalidArgumentException
     */
    public static function SetDirectory($dir) {
        if (!is_dir($dir)) {
            if (! @\mkdir($dir, 0700, TRUE)) {
                throw new \InvalidArgumentException("dir $dir does not exist and could not be created");
            }
        }
        
        self::$DIR = realpath($dir);
    }
    
    /**
     * Return the path where persistent objects are saved
     * @return String
     */
    public static function GetDirectory() {
        return self::$DIR;
    }
    
    protected $id = 0;
    
    public function __construct() {
      list($usec, $sec) = explode(" ", microtime(false));
      // Will only work on 64 bit systems...
      $this->id = $sec * 1000000 + (int) ($usec * 1000000);
      
      // Init all empty ManyToManyDatasets using annotations
      $reflectionClass = new \ReflectionClass(get_called_class());
      foreach ($reflectionClass->getProperties() as $reflectionProperty) {
        
        /* @var $reflectionProperty ReflectionProperty */
        $annotations = new Annotations($reflectionProperty);
        if (isset($annotations['var'])
            && $annotations['var'] === "ManyToManyMDataset") {
          
          //$property = $reflectionProperty->getName();
          $target = $annotations['target'];
          $reflectionProperty->setValue($this, new ManyToManyMDataset($target, $this));
        }
      }
    }
    
    public function __sleep() {
      // Remove ManyToManyMDataset using annotations
      
      
      $attributes = array();
      $reflectionClass = new \ReflectionClass(get_called_class());
      foreach ($reflectionClass->getProperties() as $reflectionProperty) {
        /* @var $reflectionProperty \ReflectionProperty */
        
        // don't save static properties
        if ($reflectionProperty->isStatic()) {
          continue;
        }
        
        // don't save ManyToMany relations
        $annotations = new Annotations($reflectionProperty);
        if (isset($annotations['var'])
            && $annotations['var'] === "ManyToManyDataset") {
          continue;
        }
        
        $attributes[] = $reflectionProperty->getName();
            
      }
      
      return $attributes;
    }
    
    public function __wakeup() {
      // Init and load ManyToManyDatasets using annotations
      $reflectionClass = new \ReflectionClass(get_called_class());
      foreach ($reflectionClass->getProperties() as $reflectionProperty) {
        /* @var $reflectionProperty ReflectionProperty */
        
        $annotations = new Annotations($reflectionProperty);
        if (isset($annotations['var'])
            && $annotations['var'] === "ManyToManyDataset") {
          
          //$property = $reflectionProperty->getName();
          $target = $annotations['target'];
          $dataset = new ManyToManyDataset($target, $this);
          $dataset->Load();
          $reflectionProperty->setValue($this, $dataset);
        }
      }
    }
    
    /**
     * 
     * @return int id
     */
    public function Id() {
        return $this->id;
    }
    
    /**
     * Override this method to have a pre-save hook. This method must return true
     * for the object to be saved. Otherwize Save() will trigger an exception.
     * @return boolean
     */
    public function Validate() {
      return true;
    }
    
    /**
     * Save this object.
     * @return boolean true on success
     */
    public function Save() {
        if (!$this->Validate()) {
          throw new Exception("Validation failed");
        }
        
        $file = self::File($this->id);
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }
        
        file_put_contents($file, serialize($this), LOCK_EX);
        return $this;
    }
    
    /**
     * Remove this object
     */
    public function Delete() {
        unlink(self::File($this->id));
    }
    
    public function Parse($array) {
        foreach (get_object_vars($this) as $key => $value) {
            if (isset($array[$key])) {
                $this->$key = $array[$key];
            }
        }
    }
}

