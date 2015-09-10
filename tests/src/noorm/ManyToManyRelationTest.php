<?php

namespace noorm;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2015-09-10 at 11:00:34.
 */
class ManyToManyRelationTest extends \PHPUnit_Framework_TestCase {

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    Persistent::SetDirectory(tempdir());
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
    
  }

  public function testInit() {
    $client = new Client();
    
    $constraint = $this->isInstanceOf("\\noorm\ManyToManyRelation");
    $constraint->evaluate($client->items);
  }

  /**
   * @covers noorm\ManyToManyRelation::Add
   */
  public function testAdd() {
    $client = new Client();
    $client->items->Add(new Item());
    $client->items->Add(new Item());
    
    $this->assertEquals(2, $client->items->Get()->Count());
    
    // Test reverse relation...
    $item3 = new Item();
    $client->items->Add($item3);
    $this->assertEquals(1, $item3->clients->Get()->Count());
    $this->assertEquals($client, $item3->clients->Get()->First());
    
  }

  /**
   * @covers noorm\ManyToManyRelation::Remove
   */
  public function testRemove() {
    $client = new Client();
    $client->items->Add(new Item());
    $client->items->Add(new Item());
    $item3 = new Item();
    $client->items->Add($item3);
    
    $client->items->Remove($item3);
    $this->assertEquals(2, $client->items->Get()->Count());
    
    // Test reverse relation is correctly removed
    $this->assertEquals(0, $item3->clients->Get()->Count());
  }

}