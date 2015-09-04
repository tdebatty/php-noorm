<?php namespace noorm;

class Pair {
    public $key;
    public $value;
    
    public function __construct($k, $v) {
        $this->key = $k;
        $this->value = $v;
    }
}