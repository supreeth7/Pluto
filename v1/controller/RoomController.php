<?php

require_once 'model/Response.php';
require_once 'gateway/RoomGateway.php';


class RoomController
{
    private $gateway;

    public function __construct()
    {
        $this->gateway = new RoomGateway();
    }

    public function processRequest()
    {
        //Authorization check
        if (!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1) {
            $response = new Response(401, false, null, 'Unauthorized Access. Access Token Missing.', false);
            $response->send();
        }

        $access_token = $_SERVER['HTTP_AUTHORIZATION'];

        $user_id = $this->gateway->authorize($access_token); //Fetch user ID matching the given access token

        //Get record matching {id}
        if (array_key_exists("room_id", $_GET)) {
            if ($_GET['room_id'] !== null && (!is_numeric($_GET['room_id']) || $_GET['room_id'] == '')) {
                $response = new Response(400, false, null, 'Invalid Room ID.', false);
                $response->send();
            }

            $room_id = $_GET['room_id'];

            //Get single record
            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $this->gateway->show($room_id, $user_id);
            }
            
            //Delete single record
            elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
                $this->gateway->delete($room_id, $user_id);
            }

            //Update single record
            elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
                $this->gateway->update($room_id, $user_id);
            }
            
            //404
            else {
                $response = new Response(405, false, null, 'Request method not allowed.', false);
                $response->send();
            }
        }
        
        
        //Get records per status
        elseif (array_key_exists("status", $_GET)) {
            if ($_GET["status"]!== null && $_GET["status"] !== "Y" && $_GET["status"] !== "N") {
                $response = new Response(405, false, null, 'Request method not allowed. Invalid status.', false);
                $response->send();
            }

            $status = $_GET["status"];

            //Get records as per status
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->gateway->showAvailable($status, $user_id);
            }
            
            //404
            else {
                $response = new Response(405, false, null, 'Request method not allowed.', false);
                $response->send();
            }
        }
        
        
        //Get all, POST
        elseif (empty($_GET)) {

            //Get all Records
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->gateway->index($user_id);
            }
            
            //Create new record
            elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->gateway->create($user_id);
            }

            //404
            else {
                $response = new Response(405, false, null, 'Request method not allowed.', false);
                $response->send();
            }
        }
        
        //404
        else {
            $response = new Response(405, false, null, 'Request method not allowed.', false);
            $response->send();
        }
    }
}
