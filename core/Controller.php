<?php
  abstract class Controller{
    protected $controller_name;
    protected $action_name;
    protected $application;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;

    public function __constract($application){
      $this->controller_name = strtolower(substr(get_class($this), 0, -10));

      $this->application = $application;
      $this->request     = $application->getRequest();
      $this->response    = $application->getResponse();
      $this->session     = $application->getSession();
      $this->db_managert = $application->getDbManager();
    }

    public function run($action, $params = array()){
      $this->action_name = $action;

      $action_method = $action . 'Action';
      if(!method_exists($this, $action_method)){
        $this->forward404();
      }

      $content = $this->action_method($params);

      return $content;
    }

    protected function render($valiables = array(), $template = null, $layout = 'layout') {
      $defaults = array(
        'request' => $this->request,
        'base_url' => $this->request->getBaseUrl(),
        'session' => $this->session,
      );

      $view = new View($this->application->getDir(), $defaults);

      if (is_null($template)) {
        $template = $this->action_name;
      }

      $path = $this->controller_name . '/' . $template;

      return $view->render($path, $valiables, $layout);
    }

    protected function forword404() {
      throw new HttpNotFoundException('Forwarded 404 page from ' . $this->controller_name . '/' . $this->action_name);
    }

    protected function redirect($url) {
      if(!preg_match('#https?://#', $url)) {
        $protocol = $this->request->isSsl() ? 'https://' : 'http://';
        $host = $this->request->getHost();
        $base_url = $this->request->getBaseUrl();

        $url = $protocol . $host . $base_url . $url;
      }
      $this->response->etStatusCode(302, 'Found');
      $this->response->setHttpHeader('Location', $url);
    }
  }
?>