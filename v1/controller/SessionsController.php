<?php

require_once 'model/Response.php';
require_once 'gateway/SessionsGateway.php';

class SessionsController
{
    private $gateway;

    public function __construct()
    {
        $this->gateway = new SessionsGateway();
    }

    public function processRequest()
    {
        //Delete (or) Update an existing session
        if (array_key_exists('session_id', $_GET)) {
            if ($_GET["session_id"] == '' || !is_numeric($_GET["session_id"])) {
                $response = new Response(400, false, null, 'Invalid session ID.');
                $response->send();
            }

            $session_id = intval($_GET["session_id"]);

            if (!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1) {
                $response = new Response(401, false, null, 'Unauthorized access.');
                $response->send();
            }

            $access_token = $_SERVER['HTTP_AUTHORIZATION'];

            if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
                $this->gateway->delete($session_id, $access_token);
            } elseif ($_SERVER["REQUEST_METHOD"] === "PATCH" || $_SERVER["REQUEST_METHOD"] === "PUT") {
                $this->gateway->update($session_id, $access_token);
            } else {
                $response = new Response(405, false, null, 'Request method not allowed.');
                $response->send();
            }
        }

        //Create a new session
        elseif (empty($_GET)) {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response = new Response(405, false, null, 'Request method not allowed.');
                $response->send();
            }

            sleep(1);
            $this->gateway->create();
        }
        
        //404 endpoint
        else {
            $response = new Response(405, false, null, 'Endpoint not found.');
            $response->send();
        }
    }
}
