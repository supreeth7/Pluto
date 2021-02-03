<?php

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
    private $is_active;
    private $login_attempts;

    public function __construct($id, $property_name, $property_type, $city, $country, $address, $username, $password)
    {
        $this->setId($id);
        $this->setPropertyName($property_name);
        $this->setPropertyType($property_type);
        $this->setCity($city);
        $this->setCountry($country);
        $this->setAddress($address);
        $this->setUsername($username);
        $this->setPassword($password);
    }

    public function validateInputText($input)
    {
        if (($input !==null) && (strlen($input) < 1 || strlen($input) > 255)) {
            throw new Exception('Invalid Data: ' . $input);
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
            throw new Exception('Invalid Hotel ID');
        }

        $this->id = $id;
    }

    public function setPropertyName($name)
    {
        $this->property_name = $this->validateInputText($name);
    }

    public function getPropertyName()
    {
        return $this->property_name;
    }

    public function setPropertyType($type)
    {
        $type = strtoupper($type);
        if (($type !== null) && ($type !== 'STAR HOTEL' || $type !== 'LODGING' || $type !== 'INN' || $type !== 'RESORT' || $type !== 'HOMESTAY')) {
            throw new Exception('Invalid Property Type: ' . $type);
        }

        $this->property_type = $type;
    }

    public function getPropertyType()
    {
        return $this->property_type;
    }

    public function setCity($city)
    {
        $this->city = $this->validateInputText($city);
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCountry($country)
    {
        $this->country = $this->validateInputText($country);
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setAddress($address)
    {
        $this->address = $this->validateInputText($address);
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setUsername($username)
    {
        $username = trim($username);
        $this->username = $this->validateInputText($username);
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setPassword($password)
    {
        $this->password = $this->validateInputText($password);
    }
    
    public function getPassword()
    {
        return $this->password;
    }

    public function setLoginAttempts($login_attempts)
    {
        $this->login_attempts = $login_attempts;
    }

    public function getLoginAttempts()
    {
        return $this->login_attempts;
    }

    public function setActive($status)
    {
        if (($status !== null) && ($status !== 0 || $status !== 1)) {
            throw new Exception('Invalid User Status');
        }
        $this->is_active = $status;
    }

    public function getActive()
    {
        return $this->is_active;
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
        $hotel['password'] = $this->getPassword();
        $hotel['is_active'] = $this->getActive();
        $hotel['login_attempts'] = $this->getLoginAttempts();

        return $hotel;
    }
}
