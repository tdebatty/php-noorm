<?php namespace noorm;

/**
 * @author Thibault Debatty
 */

ini_set('include_path',
        ini_get('include_path') . PATH_SEPARATOR .
        __DIR__ . '/../src/');

spl_autoload_register(function($class) {
    $parts = explode('\\', $class);
   
    $parts[] = str_replace('_', DIRECTORY_SEPARATOR, array_pop($parts));

    $path = implode(DIRECTORY_SEPARATOR, $parts);
   
    $file = stream_resolve_include_path($path.'.php');
    if($file !== false) {
        require $file;
    }
});

require_once __DIR__ . "/../vendor/autoload.php";

class Client extends Persistent {

  public $name = "";
  private $val;
  
  /**
   * @target \noorm\Item
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
   * @target \noorm\Client
   * @var \noorm\ManyToManyRelation
   */
  public $clients;
}

class NoValidate extends Persistent {
  public function Validate() {
    return false;
  }
}

function tempdir($dir = null, $prefix = 'php') {
  if ($dir === null) {
    $dir = sys_get_temp_dir();
  }
  $tempfile = tempnam($dir, $prefix);
  if (file_exists($tempfile)) {
    unlink($tempfile);
  }
  mkdir($tempfile);
  if (is_dir($tempfile)) {
    return $tempfile;
  }
}
