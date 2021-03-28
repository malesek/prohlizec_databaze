<?php
require "../_includes/bootstrap.inc.php";


final class DeleteRoomsPage extends BaseCRUDPage{

    private ?int $roomId;
    protected function setUp(): void
    {
        parent::setUp();

        if($this->sessionStorage->get('isLoggedIn') === null || $this->sessionStorage->get('isLoggedIn') === false){
            header("Location: ../index.php");
            exit();
        }

        if(!$this->sessionStorage->get('isAdmin')){
            header("Location: ./room-list.php");
            exit();
        }

        $this->state = $this->getState();

        if($this->state === self::STATE_PROCESSED){
            if($this->result === self::RESULT_SUCCESS){
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=./room-list.php'>";
                $this->title = "Místnost smazána";
            }
            elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Smazání selhalo";
            }
        }
        elseif($this->state === self::STATE_DELETE_REQUESTED){
            $this->roomId = $this->readPost();

            for($i = 0; $i < count($this->loadEmployee()); $i++){
                if($this->roomId == $this->loadEmployee()[$i]['room']){
                    header("Location: room-list.php");
                    exit;
                }
            }

            $this->deleteKey($this->roomId);
            if(!$this->roomId){
                throw new RequestException(400);
            }

            $token = bin2hex(random_bytes(20));

            if(RoomModel::deleteById($this->roomId)){
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
                return $this->m->render("roomSuccess", ["message" => "Místnost byla smazána."]);
            }
            elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("roomFail", ["message" => "Místnost nebyla smazána."]);
            }
    }

    protected function getState() : int{
        if($this->isProcessed()) return self::STATE_PROCESSED;

        return self::STATE_DELETE_REQUESTED;
    }

    private function readPost() : ?int{
        return filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    }

    private function deleteKey(int $room_id) : bool{
        $query = "DELETE FROM `key` WHERE room = {$room_id}";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(":room", $room_id);

        return $stmt->execute();
    }

    private function loadEmployee() : array{
        $stmt = $this->pdo->prepare("SELECT room FROM employee");
        $stmt->execute();
        $e_room = [];
        $index = 0;
        while($row = $stmt->fetch()){
            $e_room[$index] = $row;
            $index++;
        }
        return $e_room;
    }
}

$page = new DeleteRoomsPage();
$page->render();