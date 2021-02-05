<?php

require_once 'model/Response.php';
require_once 'model/Room.php';
require_once 'controller/Database.php';

class RoomGateway
{
    private $readDB;
    private $writeDB;

    public function __construct()
    {
        try {
            $this->readDB = Database::connectReadDatabase();
            $this->writeDB = Database::connectWriteDatabase();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Internal Server Error.');
            $response->send();
        }
    }

    //Authenticates access_token and return the related user ID
    public function authorize($access_token)
    {
        try {
            $query = "SELECT user_id, access_token_expiry, is_active, login_attempts FROM
            sessions, hotels WHERE access_token = :access_token AND sessions.user_id = hotels.id";
            $stmt = $this->writeDB->prepare($query);
            $stmt->bindParam(':access_token', $access_token, PDO::PARAM_STR);
            $stmt->execute();

            $count = $stmt->rowCount();

            if ($count == 0) {
                $response = new Response(401, false, null, 'Unauthorized access token.');
                $response->send();
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $user_id = $row['user_id'];
            $login_attempts = $row['login_attempts'];
            $status = $row['is_active'];
            $access_token_expiry = $row['access_token_expiry'];

            if (!$status) {
                $response = new Response(401, false, null, 'User account is inactive.');
                $response->send();
            }

            if ($login_attempts >= 3) {
                $response = new Response(401, false, null, 'Account locked due to too many login attempts.');
                $response->send();
            }

            if (strtotime($access_token_expiry) < time()) {
                $response = new Response(401, false, null, 'Access token expired.');
                $response->send();
            }

            return $user_id;
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Internal Server Error.');
            $response->send();
        }
    }

    //Get all records
    public function index($user_id)
    {
        try {
            $query = "SELECT * FROM rooms WHERE hotel_id = :user_id";
            $stmt = $this->readDB->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $count = $stmt->rowCount();

            if ($count==0) {
                $response = new Response(404, false, null, 'No records found.');
                $response->send();
            }

            $data = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $room = new Room(
                    $row['id'],
                    $row['number'],
                    $row['type'],
                    $row['price'],
                    $row['currency'],
                    $row['description'],
                    $row['photos'],
                    $row['max_adults'],
                    $row['max_children'],
                    $row['status'],
                    $row['hotel_id']
                );

                $data['rooms'][] = $room->getRoomArray();
            }

            $response = new Response(200, true, $data, 'Records fetched successfully.');
            $response->send();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Internal Server Error.');
            $response->send();
        } catch (RoomException $e) {
            $response = new Response(500, false, null, 'Room exception: ' . $e->getMessage());
            $response->send();
        }
    }

    //Get single record
    public function show($room_id, $user_id)
    {
        try {
            $query = "SELECT * FROM rooms WHERE id = :room_id AND hotel_id = :user_id";
            $stmt = $this->readDB->prepare($query);
            $stmt->bindParam(':room_id', $room_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $count = $stmt->rowCount();

            if ($count == 0) {
                $response = new Response(404, false, null, 'Record not found.');
                $response->send();
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $room = new Room(
                $row['id'],
                $row['number'],
                $row['type'],
                $row['price'],
                $row['currency'],
                $row['description'],
                $row['photos'],
                $row['max_adults'],
                $row['max_children'],
                $row['status'],
                $row['hotel_id']
            );

            $data = $room->getRoomArray();

            $response =  new Response(200, true, $data, 'Record fetched successfully.');
            $response->send();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Database error. Cannot fetch record.');
            $response->send();
        }
    }

    //Delete a single record
    public function delete($room_id, $user_id)
    {
        try {
            $query = "SELECT * FROM rooms WHERE id = :room_id AND hotel_id = :user_id";
            $stmt = $this->readDB->prepare($query);
            $stmt->bindParam(':room_id', $room_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $count = $stmt->rowCount();

            if ($count == 0) {
                $response = new Response(404, false, null, 'Record not found.');
                $response->send();
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $room = new Room(
                $row['id'],
                $row['number'],
                $row['type'],
                $row['price'],
                $row['currency'],
                $row['description'],
                $row['photos'],
                $row['max_adults'],
                $row['max_children'],
                $row['status'],
                $row['hotel_id']
            );

            $data = $room->getRoomArray();


            $query = "DELETE FROM rooms WHERE id = :room_id AND hotel_id = :user_id";
            $stmt = $this->readDB->prepare($query);
            $stmt->bindParam(':room_id', $room_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();


            $response =  new Response(200, true, $data, 'Record deleted successfully.');
            $response->send();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Database error. Cannot delete record.');
            $response->send();
        }
    }
}
