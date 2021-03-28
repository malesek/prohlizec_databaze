<?php
require "../_includes/bootstrap.inc.php";


final class DeleteEmployeesPage extends BaseCRUDPage{

    private ?int $employeeId;
    protected function setUp(): void
    {
        parent::setUp();

        if($this->sessionStorage->get('isLoggedIn') === null || $this->sessionStorage->get('isLoggedIn') === false){
            header("Location: ../index.php");
            exit();
        }

        if(!$this->sessionStorage->get('isAdmin')){
            header("Location: ./employee-list.php");
            exit();
        }

        $this->state = $this->getState();

        if($this->state === self::STATE_PROCESSED){
            if($this->result === self::RESULT_SUCCESS){
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=./employee-list.php'>";
                $this->title = "Zaměstnanec smazána";
            }
            elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Smazání zaměstnance selhalo";
            }
        }
        elseif($this->state === self::STATE_DELETE_REQUESTED){
            $this->employeeId = $this->readPost();
            $this->deleteKey($this->employeeId);
            if(!$this->employeeId) {
                throw new RequestException(400);
            }
            $token = bin2hex(random_bytes(20));

            if(EmployeeModel::deleteById($this->employeeId)){
                $this->sessionStorage->set($token, ['result' => self::RESULT_SUCCESS]);
            }
            else{
                $this->sessionStorage->set($token, ['result' => self::RESULT_FAIL]);
            }

            $this->redirect($token);
        }
    }

    protected function body(): string{
            if($this->result === self::RESULT_SUCCESS){
                return $this->m->render("employeeSuccess", ["message" => "Zaměstnanec byl smazán."]);
            }
            elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("employeeFail", ["message" => "Zaměstnanec nebyl smazán."]);
            }
    }

    protected function getState() : int{
        if($this->isProcessed()) return self::STATE_PROCESSED;

        return self::STATE_DELETE_REQUESTED;
    }

    private function readPost() : ?int{
        return filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
    }



    private function deleteKey(int $employee_id) : bool{
        $query = "DELETE FROM `key` WHERE employee = {$employee_id}";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(":employee", $employee_id);

        return $stmt->execute();
    }


}

$page = new DeleteEmployeesPage();
$page->render();