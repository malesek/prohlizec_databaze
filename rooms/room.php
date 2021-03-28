<?php

require "../_includes/bootstrap.inc.php";

$m = new MustacheRunner();

$pdo = DB::getConnection();
$roomId = (int) ($_GET["roomId"] ?? -1);
$stmt = $pdo->prepare("SELECT * FROM room WHERE room_id=?");
$stmt->execute([$roomId]);

$stmt2 = $pdo->prepare("SELECT employee.employee_id, employee.room, employee.name, employee.surname 
                                    FROM employee JOIN `key`
                                    ON `key`.room=? AND employee = employee_id");
$stmt2->execute([$roomId]);

$stmt3 = $pdo->prepare("SELECT employee.name, employee.surname, employee_id FROM employee JOIN room 
                                        ON room_id=? WHERE room.room_id = employee.room");
$stmt3->execute([$roomId]);

/*$stmt4 = $pdo->prepare("SELECT AVG(wage) AS avgwage FROM employee JOIN room ON room_id=? AND room_id = employee.room");
$stmt4->execute([$roomId]);
$row = $stmt4->fetch();*/

echo $m->render("head", ["title" => "Místnost"]);

echo $m->render("room", ["employeeDetail" => "../employees/employee.php", "room" => $stmt, "keys" => $stmt2, "people" => $stmt3]);

/*if(!is_null($row['avgwage'])){
    echo $m->render("room", ["avgwage" => $stmt4]);
}*/

echo $m->render("foot");



?>