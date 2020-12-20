<?php

require_once "./includes/classes/Database.php";
require_once "./includes/classes/Hotel.php";
require_once './vendor/autoload.php';

class Seeder
{
    private $faker = null;
    private $hotel = null;

    public function __construct($hotel)
    {
        $this->hotel = $hotel;
        $this->faker = Faker\Factory::create();
    }

    public function seed()
    {
        for ($i=0; $i < 25; $i++) {
            $name = $this->faker->company;
            $state = $this->faker->state;
            $city = $this->faker->city;
            $phone = $this->faker->tollFreePhoneNumber;
            $country = $this->faker->country;
            $address = $this->faker->address;
            $star = rand(1, 5);
            $rating = rand(1, 10);
            $rooms = rand(1, 100);
            $pincode = $this->faker->postcode;

            $this->hotel->insert($name, $city, $country, $state, $pincode, $address, $phone, $star, $rooms, $rating);
        }
    }
}
