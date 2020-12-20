<?php

 class Hotel
 {
     private $name;
     private $city;
     private $country;
     private $state;
     private $pincode;
     private $rating;
     private $star;
     private $id;
     private $rooms;
     private $address;
     private $phone;

     public $db = null;

     public function __construct($db)
     {
         $this->db = $db;
     }

     public function insert($name, $city, $country, $state, $pincode, $address, $phone, $star, $rooms, $rating)
     {
         $query = "INSERT INTO hotel (name,city,country,state,pincode,address,phone,star,rooms,rating) VALUES (?,?,?,?,?,?,?,?,?,?)";
         $stmt = $this->db->con->prepare($query);
         $stmt->bind_param('sssssssiii', $name, $city, $country, $state, $pincode, $address, $phone, $star, $rooms, $rating);
         $stmt->execute();
         $result = $stmt->get_result();
         return $result;
     }
 }
