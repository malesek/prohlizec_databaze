<?php
require "_includes/bootstrap.inc.php";

final class LoginPage extends BaseCRUDPage{
    private array $login;
    private bool $fail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->title = "Přihlášení";

        if($this->sessionStorage->get('isLoggedIn')){
            $this->sessionStorage->set('isLoggedIn', false);
        }

        if(isset($_POST['logout'])){
            if($_POST['logout'] == 'logout'){
                $this->sessionStorage->set('isLoggedIn', false);
                $this->sessionStorage->set('isAdmin', false);
            }
        }

        $this->state = $this->getState();

        if($this->state === self::STATE_FORM_SENT) {
            $this->login = $this->readPost();
            foreach ($this->readLogin() as $item){
                if($this->login['password'] != null && $this->login['username'] != null){
                    if($this->login['username'] == $item['username']){
                        if (password_verify("{$this->login['password']}", "{$item['password']}")) {
                            $this->sessionStorage->set('user', $item['username']);
                            $this->sessionStorage->set('isLoggedIn', true);

                            if($item['admin'] == 1) $this->sessionStorage->set('isAdmin', true);
                            else $this->sessionStorage->set('isAdmin', false);
                            header("Location: homepage.html");
                            exit;
                        }
                        $this->fail = true;
                        $this->result = self::RESULT_FAIL;
                    }
                    $this->fail = true;
                    $this->result = self::RESULT_FAIL;
                }
            }
        }
        elseif ($this->state === self::STATE_FORM_REQUESTED){
            $this->login = [];
        }
    }

    protected function body(): string{
        if($this->state===self::STATE_FORM_REQUESTED){
            return $this->m->render("loginForm", ['login' => $this->login]);
        }
        if($this->state === self::STATE_PROCESSED){
            return "";
        }
        if($this->result === self::RESULT_FAIL){
            return $this->m->render("loginForm", ['login' => $this->login, 'fail' => $this->fail]);
        }
    }

    protected function getState() : int{
        $action = filter_input(INPUT_POST, 'action');
        if($action == 'login'){
            return self::STATE_FORM_SENT;
        }

        return self::STATE_FORM_REQUESTED;
    }

    private function readLogin() : array{
        $stmt = $this->pdo->prepare("SELECT username, password, `admin` FROM employee");
        $stmt->execute();
        $login = [];
        $index = 0;
        while($row = $stmt->fetch()){
            $login[$index] = $row;
            $index++;
        }
        return $login;
    }

    private function readPost() : array{
        $login = [];
        $login['username'] = filter_input(INPUT_POST, 'username');
        $login['password'] = filter_input(INPUT_POST, 'password');
        return $login;
    }

}
$page = new LoginPage();
$page->render();
