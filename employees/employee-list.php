<?php
require "../_includes/bootstrap.inc.php";

final class ListEmployeesPage extends BaseCRUDPage{
    private bool $admin;

    protected function getState(): int{}

    protected function setUp(): void
    {
        parent::setUp();
        $this->title = "Zaměstnanci";

        if($this->sessionStorage->get('isLoggedIn') === null || $this->sessionStorage->get('isLoggedIn') === false){
            header("Location: ../index.php");
            exit();
        }

        if($this->sessionStorage->get('isAdmin')) $this->admin = true;
        else $this->admin = false;


    }

    protected function body(): string{
        $stmt = $this->pdo->prepare("SELECT employee.employee_id, employee.name, employee.surname, employee.job, room.name AS r_name, 
                                room.phone FROM employee JOIN room ON room.room_id = employee.room");
        $stmt->execute();
        return $this->m->render("employeeList", ["employeeDetail" => "employee.php", "employees" => $stmt, "admin" => $this->admin]);
    }


}

$page = new ListEmployeesPage();
$page->render();
?>
