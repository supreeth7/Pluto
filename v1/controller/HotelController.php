<?php

require_once 'gateway/HotelGateway.php';
require_once 'model/Response.php';

class HotelController
{
    private $gateway; //variable to access hotel table gatewat

    public function __construct()
    {
        $this->gateway = new HotelGateway();
    }

    public function processRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response = new Response(405, false, null, 'Method Not Allowed.');
            $response->send();
        }

        $this->gateway->create(); //create a user
    }
}
