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
            $response = new Response(401, false, null, 'Unauthorized Access. Access Token Missing.');
            $response->send();
        }

        $access_token = $_SERVER['HTTP_AUTHORIZATION'];

        $user_id = $this->gateway->authorize($access_token); //Fetch user ID matching the given access token

        //Get record matching {id}
        if (array_key_exists("room_id", $_GET)) {
            if ($_GET['room_id'] !== null && (!is_numeric($_GET['room_id']) || $_GET['room_id'] == '')) {
                $response = new Response(400, false, null, 'Invalid Room ID.');
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
            
            //404
            else {
                $response = new Response(405, false, null, 'Request method not allowed.');
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
            }

            //404
            else {
                $response = new Response(405, false, null, 'Request method not allowed.');
                $response->send();
            }
        } else {
            $response = new Response(405, false, null, 'Request method not allowed.');
            $response->send();
        }
    }
}
