<?php

if (isset($_POST['submit'])) {
    $property_name = $_POST['property_name'];
    $property_type = $_POST['property_type'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $data = [
        'property_name' => $property_name,
        'property_type' => $property_type,
        'city' => $city,
        'country' => $country,
        'address' => $address,
        'username' => $username,
        'password' => $password
    ];

    $url = 'http://localhost/v1/users';

    $json_data = json_encode($data);

    // open connection
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


    // execute post
    $result = curl_exec($ch);

    $returned_data = json_decode($result, true);

    $errors = $returned_data['messages'];

    // close connection
    curl_close($ch);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"
        integrity="sha384-q2kxQ16AaE6UbzuKqyBE9/u/KzioAlnx2maXQHiDX9d4/zp8Ok3f+M7DPm+Ib6IU" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.min.js"
        integrity="sha384-pQQkAEnwaBkjpqZ8RU1fF1AKtTcHJwFl3pblpTlHXybJjHpMYo79HY3hIi4NKxyj" crossorigin="anonymous">
    </script>
</head>

<body>
    <div class="container w-50 mt-5">
        <div>
            <h1 class="text-center mb-lg-5">Create User</h1>
        </div>
        <?php
            foreach ($errors as $error) { ?>
        <div class="alert alert-danger" role="alert">
            <?=$error?>
        </div>
        <?php } ?>
        <form action="" class="form" method="POST">
            <div class="mb-3">
                <label for="property_name" class="form-label">Property Name</label>
                <input type="text" class="form-control" name="property_name">
            </div>
            <div class="mb-3">
                <label for="property_type" class="form-label">Property Type</label>
                <select name="property_type" class="form-select">
                    <option selected>Open this select menu</option>
                    <option value="STAR HOTEL">Star Hotel</option>
                    <option value="LODGING">Lodging</option>
                    <option value="INN">Inn</option>
                    <option value="VILLA">Villa</option>
                    <option value="RESORT">Resort</option>
                    <option value="HOMESTAY">Homestay</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" name="city">
            </div>
            <div class="mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" class="form-control" name="country">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" name="address">
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">@</span>
                    <input type="text" class="form-control" placeholder="Username" name="username">
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password">
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary" name="submit">Submit</button>
            </div>
        </form>
    </div>
</body>

</html>