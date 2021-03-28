<?php

require "../_includes/bootstrap.inc.php";

use Tracy\Debugger;

Debugger::enable();

$m = new MustacheRunner();

$pdo = DB::getConnection();
$employeeId = (int) ($_GET["employeeId"] ?? -1);
$stmt = $pdo->prepare('SELECT employee.name, employee.surname, employee.job, employee.wage, 
                                room.name AS "r_name", room_id FROM employee JOIN room 
                                ON room.room_id = employee.room WHERE employee_id=?');
$stmt->execute([$employeeId]);
$stmt2 = $pdo->prepare("SELECT `key`.room, room_id, `name`, employee  FROM `key` JOIN room
                                    ON employee=? WHERE room.room_id = `key`.room");
$stmt2->execute([$employeeId]);

echo $m->render("head", ["title" => "Zaměstnanec"]);

echo $m->render("employee", ["roomDetail" => "../rooms/room.php", "employee" => $stmt, "keys" => $stmt2]);

echo $m->render("foot");
