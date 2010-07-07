<?php

class MongoBackend {
  function __construct($database) {
    $this->connection = new Mongo("localhost:27017", array("persist" => "MongoBackend"));
    $this->database = $database;
    $this->db = $this->connection->$database;
  }
  function save($klass, $data) {
    $klass = strtolower($klass);
    
    $instance = new $klass();
    $fields = $instance->fields();
    foreach($fields as $f) {
      if (isset($data[$f->name])) {
        $data[$f->name] = $f->normalize_to_db($data[$f->name]);
      }
    }
    
    if (!isset($data['_id']) || empty($data['_id'])) {
      unset($data['_id']);
      $this->db->$klass->insert($data);
      $data['_id'] = $data['_id']->__toString();
    } else {
      $updates = array();
      foreach($data as $k => $v) {
        if ($k != '_id') {
          $updates[$k] = $v;
        }
      }
      $this->db->$klass->update(
        array('_id' => new MongoId($data['_id'])),
        array(
          '$set' => $updates
        )
      );
    }    
    return new $klass($data);
  }
  function delete($klass, $_id) {
    $klass = strtolower($klass);
    if (is_string($_id)) $_id = new MongoId($_id);
    return $this->db->$klass->remove(array('_id' => $_id));
  }
  function find($klass, $query=array()) {
    $klass = strtolower($klass);
    return new MongoBackendQueryset($klass, $this->db, $query);
  }
  function get($klass, $_id) {
    $klass = strtolower($klass);
    $q = $this->find($klass, array('_id'=> new MongoId($_id)));
    return $q->getNext();
  }
  
  function new_from_db($klass, $data=array()) {
    $instance = new $klass();
    $fields = $instance->fields();
    foreach($fields as $f) {
      $name = $f->name;
      if (isset($data[$name])) {
        $data[$name] = $f->normalize_from_db($data[$name]);
      }
    }
    return new $klass($data);
  }
}

class MongoBackendQueryset implements Iterator {
  function __construct($klass, $db, array $query = array() ) {
    $klass = strtolower($klass);
    $this->klass = $klass;   
    $this->query = $query;
    $this->db = $db;
    $this->cursor = false;
    $this->sort = array(); 
  }
  function find($additional_query) {
    $query = array();
    foreach( ($this->query + $additional_query) as $k => $v) {
      if (!empty($k) && !empty($v)) {
        $query[$k] = $v;
      }
    }
    $this->query = $query;
    return $this;
  }
  function sort($fields) {
    $this->sort = $fields;
    return $this;
  }
  function __initialize_query() {
    if (!$this->cursor) {
      $klass = $this->klass;
      $this->cursor = $this->db->$klass->find($this->query)->sort($this->sort);
    }
  }
  function current() {
    $this->__initialize_query();
    $klass = $this->klass;
    return MongoBackend::new_from_db($klass, $this->cursor->current());
  }
  function next() {
    $this->__initialize_query();
    return $this->cursor->next();
  }
  function key() {
    $this->__initialize_query();
    return $this->cursor->key();
  }
  function rewind() {
    $this->__initialize_query();
    return $this->cursor->rewind();
  }
  function valid() {
    $this->__initialize_query();
    return $this->cursor->valid();
  }
  function getNext() {
    $this->__initialize_query();
    $this->cursor->next();
    return $this->current();
  }
}