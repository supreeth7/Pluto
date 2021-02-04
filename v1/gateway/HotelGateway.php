<?php

require_once 'controller/Database.php';
require_once 'model/Hotel.php';

class HotelGateway
{
    private $writeDB;

    public function __construct()
    {
        try {
            $this->writeDB = Database::connectWriteDatabase();
        } catch (PDOException $e) {
            $response =  new Response(500, false, null, 'Internal Server Error.');
            $response->send();
        }
    }

    //Create a user
    public function create()
    {
        $rawData = file_get_contents('php://input');

        if (!$jsonData = json_decode($rawData)) {
            $response =  new Response(400, false, null, 'Content type not valid JSON.');
            $response->send();
        }

        try {
            if (
            !isset($jsonData->username) ||
            !isset($jsonData->password) ||
            !isset($jsonData->property_name) ||
            !isset($jsonData->property_type) ||
            !isset($jsonData->city) ||
            !isset($jsonData->country) ||
            !isset($jsonData->address)
            ) {
                $response =  new Response(400, false, null, 'Missing fields:');
                !isset($jsonData->username) ? $response->addMessage('Username should be provided.') : null;
                !isset($jsonData->password) ? $response->addMessage('Password should be provided.') : null;
                !isset($jsonData->property_name) ? $response->addMessage('Property name should be provided.') : null;
                !isset($jsonData->property_type) ? $response->addMessage('Property type should be provided.'): null;
                !isset($jsonData->city) ? $response->addMessage('City should be provided.') : null;
                !isset($jsonData->country) ? $response->addMessage('Country should be provided.'): null;
                !isset($jsonData->address) ? $response->addMessage('Address should be provided.'): null;
                $response->send();
            }

            $hotel = new Hotel(
                null,
                $jsonData->property_name,
                $jsonData->property_type,
                $jsonData->city,
                $jsonData->country,
                $jsonData->address,
                $jsonData->username,
                $jsonData->password,
            );

            $username = $hotel->getUsername();
            $hashed_password = password_hash($jsonData->password, PASSWORD_DEFAULT);
            $property_name = $hotel->getPropertyName();
            $property_type = $hotel->getPropertyType();
            $city = $hotel->getCity();
            $country = $hotel->getCountry();
            $address = $hotel->getAddress();

            $query = "SELECT id FROM hotels WHERE username = :username";
            $stmt = $this->writeDB->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $rowCount = $stmt->rowCount();

            if ($rowCount !== 0) {
                $response =  new Response(409, false, null, 'Username already exists.');
                $response->send();
            }

            $query = "INSERT INTO hotels (property_name, property_type, city, country, address, username, password) 
            VALUES (:property_name, :property_type, :city, :country, :address, :username, :password)";
            $stmt = $this->writeDB->prepare($query);
            $stmt->bindParam(':property_name', $property_name, PDO::PARAM_STR);
            $stmt->bindParam(':property_type', $property_type, PDO::PARAM_STR);
            $stmt->bindParam(':city', $city, PDO::PARAM_STR);
            $stmt->bindParam(':country', $country, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

            $stmt->execute();

            $rowCount = $stmt->rowCount();

            if ($rowCount == 0) {
                $response =  new Response(500, false, null, 'There was an error inserting the data into the database.');
                $response->send();
            }

            $hotel->setId($this->writeDB->lastInsertId());
            $data = $hotel->returnHotelArray();

            $response = new Response(201, true, $data, 'User created.');
            $response->send();
        } catch (PDOException $e) {
            $response =  new Response(500, false, null, 'Internal Server Error.');
            $response->send();
        } catch (HotelException $e) {
            $response =  new Response(500, false, null, $e->getMessage());
            $response->send();
        }
    }
}
