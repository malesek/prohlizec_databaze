<?php
require "bootstrap.inc.php";
final class passCreated extends BaseCRUDPage{
    protected function getState(): int{}

    protected function setUp(): void
    {
        parent::setUp();
        $this->title = "Nesnasim svuj zivot";
        if(isset($_POST['passChange'])){
            $this->updatePass($this->sessionStorage->get('user'), password_hash($_POST['passChange'], PASSWORD_BCRYPT));
            $this->sessionStorage->set('isLoggedIn', false);
            $this->sessionStorage->set('isAdmin', false);

        }

    }

    private function updatePass(string $username, string $password){
        if($password != null){
            $query = "UPDATE employee SET password = :password WHERE username = :username";
            $stmt = DB::getConnection()->prepare($query);
            $stmt->bindParam(":password", $password);
            $stmt->bindParam(":username", $username);
            return $stmt->execute();
        }
        return false;
    }

    protected function body(): string{
        header("Location: ../index.php");
        exit();
    }

}
$page =  new passCreated();
$page->render();
