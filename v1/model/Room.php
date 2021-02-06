<?php

require_once 'model/RoomException.php';

class Room
{
    private $id;
    private $number;
    private $type;
    private $price;
    private $currency;
    private $description;
    private $photos = array();
    private $occupancy = array();
    private $status;
    private $hotel_id;

    public function __construct($id, $number, $type, $price, $currency, $description, $photos, $adults, $children, $status, $hotel_id)
    {
        $this->setId($id);
        $this->setNumber($number);
        $this->setType($type);
        $this->setPrice($price);
        $this->setCurrency($currency);
        $this->setDescription($description);
        $this->addPhotos($photos);
        $this->setOccupancy($adults, $children);
        $this->setStatus($status);
        $this->setHotelID($hotel_id);
    }

    public function validateInputText($input, $message)
    {
        if ((isset($input) && $input !==null) && (strlen($input) < 1 || strlen($input) > 255)) {
            throw new RoomException('Invalid Data: ' . $message);
        }

        $input = strip_tags($input);
        return $input;
    }

    public function setID($id)
    {
        if ($id !== null && !is_numeric($id)) {
            throw new RoomException('Invalid ID.');
        }

        $this->id = $id;
    }

    public function getID()
    {
        return $this->id;
    }

    public function setNumber($number)
    {
        $this->number = $this->validateInputText($number, 'Invalid room number.');
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function setType($type)
    {
        $room_types = [
            "SINGLE",
            "DOUBLE",
            "DELUXE",
            "TWIN",
            "SUITE",
            "VILLA",
            "STUDIO"
        ];

        $type = strtoupper($type);

        if (($type !== null) && (!in_array($type, $room_types))) {
            throw new RoomException('Invalid room type.');
        }

        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setPrice($price)
    {
        if ($price!==null && !is_numeric($price)) {
            throw new RoomException('Invalid price.');
        }

        $this->price = $price;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setCurrency($currency)
    {
        $currencies = ['USD','AU','JPY','INR','GBP'];

        if ($currency!==null && !in_array($currency, $currencies)) {
            throw new RoomException('Currency should be either USD, AU, JPY, INR, GBP.');
        }
        $this->currency = $currency;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function addPhotos($photos)
    {
        $photos_array = explode(',', $photos);
        foreach ($photos_array as $photo) {
            array_push($this->photos, $photo);
        }
    }

    public function getPhotos()
    {
        return $this->photos;
    }

    public function getPhotosString()
    {
        $photos_string = implode(', ', $this->photos);
        return $photos_string;
    }

    public function setOccupancy($adults, $children)
    {
        if (($adults !== null && $children !== null) && (!is_numeric($adults) && !is_numeric($children))) {
            throw new RoomException('Occupants details must be a number.');
        }

        $this->occupancy = [
            "max-adults" => $adults,
            "max-children" => $children,
            "max-occupancy" => $adults + $children
        ];
    }

    public function setAdultOccupancy($input)
    {
        if (($input !== null) && (!is_numeric($input))) {
            throw new RoomException('Occupants details must be a number.');
        }

        $this->occupancy["max_adults"] = $input;
    }

    public function setChildrenOccupancy($input)
    {
        if (($input !== null) && (!is_numeric($input))) {
            throw new RoomException('Occupants details must be a number.');
        }

        $this->occupancy["max_children"] = $input;
    }

    public function getOccupancy()
    {
        return $this->occupancy;
    }

    public function setStatus($status)
    {
        if (($status !== null) && ($status !== 'Y' && $status !== 'N')) {
            throw new RoomException("Status should be 'Y' or 'N'.");
        }

        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setHotelID($id)
    {
        if ($id !== null && $id == '') {
            throw new RoomException('Invalid Hotel ID.');
        }

        $this->hotel_id = $id;
    }

    public function getHotelID()
    {
        return $this->hotel_id;
    }

    public function getRoomArray()
    {
        $room = array();
        $room['id'] = $this->getId();
        $room['hotel_id'] = $this->getHotelID();
        $room['room_number'] = $this->getNumber();
        $room['type'] = $this->getType();
        $room['description'] = $this->getDescription();
        $room['price'] = $this->getPrice();
        $room['currency'] = $this->getCurrency();
        $room['occupancy'] = $this->getOccupancy();
        $room['images'] = $this->getPhotos();
        $room['status'] = $this->getStatus();

        return $room;
    }
}
