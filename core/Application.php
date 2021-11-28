<?php

  abstract class Application {

    protected $debug = false;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;

    public function __constract($debug = false) {
      $this->setDebugMode($debug);
      $this->initialize();
      $this->configure();
    }

    public function setDebugMode($debug) {
      if ($debug) {
        $this->debug = true;
        ini_set('desplay_errors', 1);
        error_reporting(-1);
      } else {
        $this->debug = false;
        ini_set('desplay_errors', 0);
      }
    }

    public function initialize() {
      $this->request = new Request();
      $this->response = new Response();
      $this->session = new Session();
      $this->db_manager = new DbManager();
      $this->router = new Router($this->registerRoutes());
    }

    protected function configure() {
    }

    abstract public function getRootDir();

    abstract protected function registerRoutes();

    public function isDebugMode() {
      return $this->debug;
    }

    public function getRequest() {
      return $this->request;
    }

    public function getResponse() {
      return $this->response;
    }

    public function getSession() {
      return $this->session;
    }

    public function getDbManager() {
      return $this->dbManager;
    }

    public function getControllerDir() {
      return $this->getRootDir() . '/controllers';
    }

    public function getViewDir() {
      return $this->getRootDir() . '/views';
    }

    public function getMoelDir() {
      return $this->getRootDir() . '/models';
    }

    public function getWebDir() {
      return $this->getRootDir() . '/web';
    }

  }

?>