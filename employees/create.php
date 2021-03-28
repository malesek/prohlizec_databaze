<?php
require "../_includes/bootstrap.inc.php";


final class CreateEmployeesPage extends BaseCRUDPage{

    private EmployeeModel $employee;
    private array $rooms;

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
                $this->title = "Zaměstnanec Přidán";
            }
            elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Přidání zaměstnance selhalo";
            }
        }
        elseif($this->state === self::STATE_FORM_SENT){
            $this->employee = $this->readPost();
            if($this->employee->isValid()){
                $token = bin2hex(random_bytes(20));

                if($this->employee->insert()){
                    $this->insertKey($this->employee->getPrimaryKey());
                    $this->sessionStorage->set($token, ['result' => self::RESULT_SUCCESS]);
                }
                else{
                    $this->sessionStorage->set($token, ['result' => self::RESULT_FAIL]);
                }

                $this->redirect($token);
            }
            else{
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Přidat zaměstnance: Špatný formulář";
            }
        }
        elseif ($this->state === self::STATE_FORM_REQUESTED){
            $this->title = "Přidat zaměstnance";
            $this->employee = new EmployeeModel();
        }
    }

    protected function body(): string{
        if($this->state===self::STATE_FORM_REQUESTED){
            $this->rooms = $this->loadRoom();
            return $this->m->render("employeeForm", ['create' => true, 'employee' => $this->employee, 'rooms' => $this->rooms]);
        }
        elseif ($this->state===self::STATE_PROCESSED){
            if($this->result === self::RESULT_SUCCESS){
                return $this->m->render("employeeSuccess", ["message" => "Zaměstnanec byl vytvořen."]);
            }
            elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("employeeFail", ["message" => "Zaměstnanec nebyl vytvořen."]);
            }
        }

    }

    protected function getState() : int{
        if($this->isProcessed()) return self::STATE_PROCESSED;
        $action = filter_input(INPUT_POST, 'action');
        if($action == 'create'){
            return self::STATE_FORM_SENT;
        }

        return self::STATE_FORM_REQUESTED;
    }

    private function readPost() : EmployeeModel{
        $employee = [];
        $employee['employee_id'] = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        $employee['name'] = filter_input(INPUT_POST, 'name');
        $employee['surname'] = filter_input(INPUT_POST, 'surname');
        $employee['job'] = filter_input(INPUT_POST, 'job');
        $employee['wage'] = filter_input(INPUT_POST, 'wage');
        $employee['room'] = filter_input( INPUT_POST, 'room');
        $employee['username'] = filter_input( INPUT_POST, 'username');
        $employee['password'] = password_hash(filter_input( INPUT_POST, 'password'), PASSWORD_BCRYPT);
        $employee['admin'] = filter_input( INPUT_POST, 'admin');

        if($employee['admin'] == null) $employee['admin'] = "0";
        if($employee['admin'] == 'on') $employee['admin'] = "1";

        return new EmployeeModel($employee);
    }

    private function loadRoom() : array{
        $stmt = $this->pdo->prepare("SELECT * FROM room");
        $stmt->execute();
        $rooms = [];
        $index = 0;
        while($row = $stmt->fetch()){
            $rooms[$index] = $row;
            $index++;
        }
        return $rooms;
    }

    private function insertKey(int $employee_id) : void{
        $key = [];
        $roomKeys = $_POST['roomKeys'];
        if(empty($roomKeys)){

        }
        else {
            for($i=0; $i<count($roomKeys);$i++){
                $key['employee'][] = $employee_id;
                $key['room'][] = $roomKeys[$i];
            }
        }
        for($i = 0; $i < count($key['employee']); $i++){

            $stmt = $this->pdo->prepare("INSERT INTO `key` (employee, room) VALUES ({$key['employee'][$i]},{$key['room'][$i]})");
            $stmt->execute();

        }
    }
}

$page = new CreateEmployeesPage();
$page->render();
?>