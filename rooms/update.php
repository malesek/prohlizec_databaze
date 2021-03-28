<?php
require "../_includes/bootstrap.inc.php";


final class UpdateRoomsPage extends BaseCRUDPage{

    private RoomModel $room;

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
                $this->title = "Místnost upravena";
            }
            elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Upravení selhalo";
            }
        }
        elseif($this->state === self::STATE_FORM_SENT){
            $this->room = $this->readPost();

            if($this->room->isValid()){

                $token = bin2hex(random_bytes(20));

                if($this->room->update()){
                    $this->sessionStorage->set($token, ['result' => self::RESULT_SUCCESS]);
                }
                else{
                    $this->sessionStorage->set($token, ['result' => self::RESULT_FAIL]);
                }

                $this->redirect($token);
            }
            else{
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Aktualizovat Místnost: Špatný formulář";
            }
        }
        elseif ($this->state === self::STATE_FORM_REQUESTED){
            $this->title = "Aktualizovat Místnost";
            $room_id = $this->findId();
            if(!$room_id)
                throw new RequestException(400);
            $this->room = RoomModel::getById($room_id);
            if(!$this->room)
                throw new RequestException(404);
        }
    }

    protected function body(): string{
        if($this->state===self::STATE_FORM_REQUESTED){
            return $this->m->render("roomForm", ['update' => true, 'room' => $this->room]);
        }
        elseif ($this->state===self::STATE_PROCESSED){
            if($this->result === self::RESULT_SUCCESS){
                return $this->m->render("roomSuccess", ["message" => "Místnost byla aktualizována."]);
            }
            elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("roomFail", ["message" => "Místnost nebyla aktualizována."]);
            }
        }
    }

    protected function getState() : int{
        if($this->isProcessed()) return self::STATE_PROCESSED;

        $action = filter_input(INPUT_POST, 'action');
        if($action == 'update'){
            return self::STATE_FORM_SENT;
        }

        return self::STATE_FORM_REQUESTED;
    }

    private function findId() : ?int{
        return filter_input(INPUT_GET, 'room_id', FILTER_VALIDATE_INT);
    }

    private function readPost() : RoomModel{
        $room = [];
        $room['room_id'] = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        $room['name'] = filter_input(INPUT_POST, 'name');
        $room['no'] = filter_input(INPUT_POST, 'no');
        $room['phone'] = filter_input(INPUT_POST, 'phone');

        if(!$room['phone']) $room['phone'] = null;

        return new RoomModel($room);
    }


}

$page = new UpdateRoomsPage();
$page->render();
?>