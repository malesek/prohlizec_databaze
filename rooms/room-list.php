<?php
require "../_includes/bootstrap.inc.php";


final class ListRoomsPage extends BaseCRUDPage{
    private bool $admin;
    protected function getState(): int{}
    protected function setUp(): void
    {
        parent::setUp();
        $this->title = "Místnosti";

        if($this->sessionStorage->get('isLoggedIn') === null || $this->sessionStorage->get('isLoggedIn') === false){
            header("Location: ../index.php");
            exit();
        }

        if($this->sessionStorage->get('isAdmin')) $this->admin = true;
        else $this->admin = false;
    }

    protected function body(): string{
        $stmt = $this->pdo->prepare("SELECT * FROM `room`");
        $stmt->execute();
        return $this->m->render("roomList", ["roomDetail" => "room.php", "rooms" => $stmt, "admin" => $this->admin]);
    }
}

$page = new ListRoomsPage();
$page->render();
?>

