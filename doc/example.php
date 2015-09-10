<?php

require_once __DIR__  . "/../vendor/autoload.php";

use noorm\Persistent;

class Client extends Persistent {

  public $name = "";
  private $val;
  
  /**
   * Use annotations to indicate $items is a many-to-many relation to class Item
   * @target Item
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
   * Use annotations to indicate $clients is a many-to-many relation to Client
   * @target Client
   * @var \noorm\ManyToManyRelation
   */
  public $clients;
}

// Indicate where to save data
Persistent::SetDirectory("/tmp/noorm-example");

// Create a new object and save it to disk
$client = new Client();
$client->name = "C1";
$client->Save();

// Show all clients
/* @var $client Client */
foreach (Client::All()->Collect() as $client) {
  echo $client->name . " : ";
  echo $client->items->Get()->Count() . " items\n";
}

// Create a new item
$item = new Item();
$item->name = "I";
$item->Save();

// Add this item to the first client
$client = Client::All()->First();
$client->items->Add($item);