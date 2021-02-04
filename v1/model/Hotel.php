<?php

require_once 'model/HotelException.php';

class Hotel
{
    private $id;
    private $property_name;
    private $property_type;
    private $city;
    private $country;
    private $address;
    private $username;
    private $password;

    public function __construct($id, $property_name, $property_type, $city, $country, $address, $username, $password)
    {
        $this->setId($id);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setPropertyName($property_name);
        $this->setPropertyType($property_type);
        $this->setCity($city);
        $this->setCountry($country);
        $this->setAddress($address);
    }

    public function validateInputText($input, $message)
    {
        if ((isset($input) && $input !==null) && (strlen($input) < 1 || strlen($input) > 255)) {
            throw new HotelException('Invalid Data: ' . $message);
        }

        $input = strip_tags($input);
        return $input;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        if (($id != null) && ($id == '' || !is_numeric($id))) {
            throw new HotelException('Invalid Hotel ID');
        }

        $this->id = $id;
    }

    public function setPropertyName($name)
    {
        $this->property_name = $this->validateInputText($name, 'Property name error.');
    }

    public function getPropertyName()
    {
        return $this->property_name;
    }

    public function setPropertyType($type)
    {
        $properties = [
            "STAR HOTEL",
            "LODGING",
            "INN",
            "RESORT",
            "HOMESTAY"
        ];
        $type = strtoupper($type);
        if (($type !== null) && (!in_array($type, $properties))) {
            throw new HotelException('Invalid Property Type: ' . $type);
        }

        $this->property_type = $type;
    }

    public function getPropertyType()
    {
        return $this->property_type;
    }

    public function setCity($city)
    {
        $this->city = $this->validateInputText($city, 'City is invalid.');
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCountry($country)
    {
        $this->country = $this->validateInputText($country, 'Country is invalid.');
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setAddress($address)
    {
        $this->address = $this->validateInputText($address, 'Address is invalid.');
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setUsername($username)
    {
        $username = trim($username);
        $this->username = $this->validateInputText($username, 'Username is invalid.');
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setPassword($password)
    {
        $this->password = $this->validateInputText($password, 'Password is invalid.');
    }

    public function returnHotelArray()
    {
        $hotel = array();
        $hotel['id'] = $this->getId();
        $hotel['property_name'] = $this->getPropertyName();
        $hotel['property_type'] = $this->getPropertyType();
        $hotel['city'] = $this->getCity();
        $hotel['country'] = $this->getCountry();
        $hotel['address'] = $this->getAddress();
        $hotel['username'] = $this->getUsername();

        return $hotel;
    }
}
