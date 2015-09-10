<?php

require_once __DIR__  . "/../vendor/autoload.php";

use noorm\Persistent;

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

Persistent::SetDirectory("/tmp/noorm-example");

// Create a new object
$client = new Client();
$client->name = "C1";

// save to disk
$client->Save();


// Show all clients
/* @var $client Client */
foreach (Client::All()->Collect() as $client) {
  echo $client->name . "\n";
}