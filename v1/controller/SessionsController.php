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
        if (array_key_exists("sessionid", $_GET)) {
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
