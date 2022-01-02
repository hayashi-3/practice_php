<?php
  abstract class Controller{
    protected $controller_name;
    protected $action_name;
    protected $application;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $auth_actions = array();

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

      // ログインチェック needsAuthentication()メソッドの戻り値がtrueで未ログインの場合例外を投げる
      if ($this->needsAuthentication($action) && !$this->session->isAuthenticated()) {
        throw new UnauthorizedActionException();
      }

      $content = $this->$action_method($params);

      return $content;
    }

    protected function needsAuthentication($action) {
      // is_arrayで配列かを確認、in_arrayで配列に値があるかをチェックする
      if ($this->auth_actions === true || (is_array($this->auth_actions) && in_array($action, $this->auth_actions))) {
        return true;
      }
      return false;
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

    // csrf対策
    protected function generateCsrfToken($from_name) {
      $key = 'csrf_tokens/' . $from_name;
      $tokens = $this->session->get($key, array());
        // 10個トークンを保持できる。10を超えていたらarray_shiftで古いものから削除する。
        if (count($tokens) >= 10) {
          array_shift($tokens);
        }
      // session_idとmicrotimeで適当なハッシュ関数を作成。session_idは現在のセッションが存在しない時は空文字を返す
      $token = sha1($from_name . session_id() . microtime());
      $tokens[] = $token;

      $this->session->get($key, array());

      // trueで$tokenの厳密な型比較を行う $tokensと比較
      // セッション上に格納されたトークンからpostされたトークンを探す
      if (false !== ($pos = array_search($token, $tokens, true))) {
        // 1度使ったので$tokens[$pos]を破棄
        unset($tokens[$pos]);
        $this->session->set($key, $tokens);
        return true;
      }
      return false;
    }
  }
?>