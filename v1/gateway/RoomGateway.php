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
            $response = new Response(500, false, null, 'Internal Server Error.', false);
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
                $response = new Response(401, false, null, 'Unauthorized access token.', false);
                $response->send();
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $user_id = $row['user_id'];
            $login_attempts = $row['login_attempts'];
            $status = $row['is_active'];
            $access_token_expiry = $row['access_token_expiry'];

            if (!$status) {
                $response = new Response(401, false, null, 'User account is inactive.', false);
                $response->send();
            }

            if ($login_attempts >= 3) {
                $response = new Response(401, false, null, 'Account locked due to too many login attempts.', false);
                $response->send();
            }

            if (strtotime($access_token_expiry) < time()) {
                $response = new Response(401, false, null, 'Access token expired.', false);
                $response->send();
            }

            return $user_id;
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Internal Server Error.', false);
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
                $response = new Response(404, false, null, 'No records found.', false);
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

                $data['total_records_fetched'] = $count;
                $data['rooms'][] = $room->getRoomArray();
            }

            $response = new Response(200, true, $data, 'Records fetched successfully.', true);
            $response->send();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Internal Server Error.', false);
            $response->send();
        } catch (RoomException $e) {
            $response = new Response(500, false, null, 'Room exception: ' . $e->getMessage(), false);
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
                $response = new Response(404, false, null, 'Record not found.', false);
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

            $response =  new Response(200, true, $data, 'Record fetched successfully.', true);
            $response->send();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Database error. Cannot fetch record.', false);
            $response->send();
        }
    }

    //Create a record
    public function create($user_id)
    {
        try {
            $rawData = file_get_contents("php://input");

            if (!$jsonData = json_decode($rawData)) {
                $response = new Response(400, false, null, 'Invalid JSON data.', false);
                $response->send();
            }

            if (!isset($jsonData->type) || !isset($jsonData->number) || !isset($jsonData->price) ||
                !isset($jsonData->max_adults) || !isset($jsonData->max_children)) {
                $response = new Response(400, false, null, 'Missing fields:', false);
                !isset($jsonData->type) ? $response->addMessage('Room type is required.') : null;
                !isset($jsonData->number) ? $response->addMessage('Room number is required.') : null;
                !isset($jsonData->price) ? $response->addMessage('Price is required.') : null;
                !isset($jsonData->max_children) ? $response->addMessage('Maximum children occupancy is required.') : null;
                !isset($jsonData->max_adults) ? $response->addMessage('Maximum adult occupancy is required.') : null;
                $response->send();
            }

            $room = new Room(
                null,
                $jsonData->number,
                $jsonData->type,
                $jsonData->price,
                $jsonData->currency,
                isset($jsonData->description) ? $jsonData->description : null,
                isset($jsonData->photos) ? $jsonData->photos : null,
                $jsonData->max_adults,
                $jsonData->max_children,
                isset($jsonData->status) ? $jsonData->status : 'Y',
                $user_id
            );

            $number = $room->getNumber();
            $type = $room->getType();
            $price = $room->getPrice();
            $currency = $room->getCurrency();
            $description = $room->getDescription();
            $photos = $room->getPhotosString();
            $max_adults = $jsonData->max_adults;
            $max_children = $jsonData->max_children;
            $status = $room->getStatus();

            $query = "INSERT INTO rooms (number, type, price, currency, description, photos, max_adults, max_children, status, hotel_id)
            VALUES (:number, :type, :price, :currency, :description, :photos, :max_adults, :max_children, :status, :hotel_id)";
            $stmt = $this->writeDB->prepare($query);
            $stmt->bindParam(':number', $number);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':currency', $currency);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':photos', $photos);
            $stmt->bindParam(':max_adults', $max_adults);
            $stmt->bindParam(':max_children', $max_children);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':hotel_id', $user_id);
            $stmt->execute();

            $count = $stmt->rowCount();

            if ($count == 0) {
                $response = new Response(500, false, null, 'Record creation failed.', false);
                $response->send();
            }

            $room->setID($this->writeDB->lastInsertId());

            $data = $room->getRoomArray();

            $response = new Response(201, true, $data, 'Record created successfully.', false);
            $response->send();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Internal server error. Cannot create new record.', false);
            $response->send();
        } catch (RoomException $e) {
            $response = new Response(500, false, null, 'Room Exception: ' . $e->getMessage(), false);
            $response->send();
        }
    }

    //Update a record
    public function update($room_id, $user_id)
    {
        try {
            $rawData = file_get_contents('php://input');

            if (!$jsonData = json_decode($rawData)) {
                $response = new Response(400, false, null, 'Data not valid JSON.', false);
                $response->send();
            }

            $query = "SELECT * FROM rooms WHERE id = :room_id AND hotel_id = :user_id";
            $stmt = $this->readDB->prepare($query);
            $stmt->bindParam(':room_id', $room_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $count = $stmt->rowCount();

            if ($count == 0) {
                $response = new Response(404, false, null, 'Record not found', false);
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

            $update_number = false;
            $update_type = false;
            $update_price = false;
            $update_currency = false;
            $update_max_adults = false;
            $update_max_children = false;
            $update_status = false;
            $update_description = false;
            $update_photos = false;

            $fields = "";

            if (isset($jsonData->number)) {
                $update_number = true;
                $room->setNumber($jsonData->number);
                $fields .= "number = :number, ";
            }

            if (isset($jsonData->status)) {
                $update_status = true;
                $room->setStatus($jsonData->status);
                $fields .= "status = :status, ";
            }

            if (isset($jsonData->type)) {
                $update_type = true;
                $room->setType($jsonData->type);
                $fields .= "type = :type, ";
            }

            if (isset($jsonData->max_adults)) {
                $update_max_adults = true;
                $room->setAdultOccupancy($jsonData->max_adults);
                $fields .= "max_adults = :max_adults, ";
            }

            if (isset($jsonData->max_children)) {
                $update_max_children = true;
                $room->setChildrenOccupancy($jsonData->max_children);
                $fields .= "max_children = :max_children, ";
            }

            if (isset($jsonData->description)) {
                $update_description = true;
                $room->setDescription($jsonData->description);
                $fields .= "description = :description, ";
            }

            if (isset($jsonData->price)) {
                $update_price = true;
                $room->setPrice($jsonData->price);
                $fields .= "price = :price, ";
            }

            if (isset($jsonData->currency)) {
                $update_currency = true;
                $room->setCurrency($jsonData->currency);
                $fields .= "currency = :currency, ";
            }
            
            if (isset($jsonData->photos)) {
                $update_photos = true;
                $room->addPhotos($jsonData->photos);
                $fields .= "photos = :photos, ";
            }

            $fields = rtrim($fields, ', ');

            if ($update_number == false && $update_currency == false &&
            $update_description == false && $update_max_adults == false &&
            $update_max_children == false && $update_price == false &&
            $update_type == false && $update_photos == false && $update_status == false) {
                $response = new Response(400, false, null, 'No data available for updation.', false);
                $response->send();
            }

            $query = "UPDATE rooms SET {$fields} WHERE id = :id AND hotel_id = :user_id";
            $stmt= $this->writeDB->prepare($query);

            $stmt->bindParam(':id', $room_id);
            $stmt->bindParam(':user_id', $user_id);

            if ($update_number == true) {
                $number = $room->getNumber();
                $stmt->bindParam(':number', $number);
            }

            if ($update_description == true) {
                $description = $room->getDescription();
                $stmt->bindParam(':description', $description);
            }

            if ($update_status == true) {
                $status = $room->getStatus();
                $stmt->bindParam(':status', $status);
            }

            if ($update_type == true) {
                $type = $room->getType();
                $stmt->bindParam(':type', $type);
            }

            if ($update_max_adults == true) {
                $max_adults = $jsonData->max_adults;
                $stmt->bindParam(':max_adults', $max_adults);
            }

            if ($update_max_children == true) {
                $max_children = $jsonData->max_children;
                $stmt->bindParam(':max_children', $max_children);
            }

            if ($update_price == true) {
                $price = $room->getPrice();
                $stmt->bindParam(':price', $price);
            }

            if ($update_currency == true) {
                $currency = $room->getCurrency();
                $stmt->bindParam(':currency', $currency);
            }

            if ($update_photos == true) {
                $photos = $room->getPhotosString();
                $stmt->bindParam(':photos', $photos);
            }


            $stmt->execute();

            $count = $stmt->rowCount();

            if ($count == 0) {
                $response = new Response(409, false, null, 'There was an error in updation.', false);
                $response->send();
            }

            $data = $room->getRoomArray();

            $response = new Response(200, true, $data, 'Record updated.', true);
            $response->send();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Database error. Cannot update record' . $e->getMessage(), false);
            $response->send();
        } catch (RoomException $e) {
            $response = new Response(400, false, null, 'Room exception: '. $e->getMessage(), false);
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
                $response = new Response(404, false, null, 'Record not found.', false);
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


            $response =  new Response(200, true, $data, 'Record deleted successfully.', false);
            $response->send();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Database error. Cannot delete record.', false);
            $response->send();
        }
    }

    //Get available rooms
    public function showAvailable($status, $user_id)
    {
        try {
            $query = "SELECT * FROM rooms WHERE hotel_id = :user_id AND status = :status";
            $stmt = $this->readDB->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            $count = $stmt->rowCount();

            if ($count == 0) {
                $response = new Response(404, false, null, 'No record found.', false);
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

                $data['total_records_fetched'] = $count;
                $data['rooms'][] = $room->getRoomArray();
            }

            $response =  new Response(200, true, $data, 'Records fetched successfully.', true);
            $response->send();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Database error.'. $e->getMessage(), false);
            $response->send();
        }
    }
}
