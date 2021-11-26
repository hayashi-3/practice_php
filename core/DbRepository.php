<?php
  abstract class DbRepository {
    
    protected $con;

    public function __constract($con) {
      $this->setConnection($con);
    }

    public function setConnection($con) {
      $this->con = $con;
    }

    public function execute($sql, $params = array()) {
      return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll($sql, $params = array()) {
      return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

  }
?>