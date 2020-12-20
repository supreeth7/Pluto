<?php

require_once "./includes/classes/Database.php";
require_once "./includes/classes/Hotel.php";
require_once "./includes/classes/Seeder.php";


$db = new Database();
$hotel = new Hotel($db);

$seeder = new Seeder($hotel);

$seeder->seed();
