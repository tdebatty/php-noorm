<?php
require_once __DIR__  . "/../vendor/autoload.php";

\noorm\Persistent::SetDirectory("/tmp/noorm-example");

class Easy extends \noorm\Persistent {
  
  public $name;
}

$e = new Easy();
$e->name = "My name";
$e->Save();
var_dump($e);

$id = $e->Id();
$same_e = Easy::One($id);
var_dump($e === $same_e);

var_dump(Easy::All()->Count());


class Client extends \noorm\Persistent {
  
  protected $name;
  
  // If your class has a constructor:
  // 1. your class must implement an empty constructor
  public function __construct($name = null) {
    if ($name !== null) {
      $this->name = $name;
    }
    
    // 2. don't forget to call the parent constructor
    parent::__construct();
  }
  
  public function getName() {
    return $this->name;
  }
}


$client = new Client("Me inc.");
echo $client->getName();
echo $client->Id();
$client->Save();