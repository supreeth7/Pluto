<?php
require_once 'controller/Database.php';
require_once 'model/Response.php';

class SessionsGateway
{
    private $writeDB;

    public function __construct()
    {
        try {
            $this->writeDB = Database::connectWriteDatabase();
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'Database error.');
            $response->send();
        }
    }

    //Login and create a new session
    public function create()
    {
        $inputData = file_get_contents('php://input');

        if (!$jsonData = json_decode($inputData)) {
            $response = new Response(400, false, null, 'Data not valid JSON.');
            $response->send();
        }

        if (!isset($jsonData->username) || !isset($jsonData->password)) {
            $response = new Response(400, false, null, 'Missing credentials.');
            !isset($jsonData->username) ? $response->addMessage('Username not provided.') : null;
            !isset($jsonData->password) ? $response->addMessage('Password not provided.') : null;
            $response->send();
        }

        try {
            $username = $jsonData->username;
            $password = $jsonData->password;

            $query = "SELECT id, password, is_active, login_attempts FROM hotels WHERE username = :username";
            $stmt = $this->writeDB->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $count = $stmt->rowCount();

            if ($count==0) {
                $response = new Response(401, false, null, 'Username does not exist.');
                $response->send();
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $returned_id = $row['id'];
            $returned_password = $row['password'];
            $returned_status = $row['is_active'];
            $returned_login_attempts = $row['login_attempts'];

            if (!$returned_status) {
                $response =  new Response(401, false, null, 'User account is inactive.');
                $response->send();
            }

            if ($returned_login_attempts >= 3) {
                $response =  new Response(401, false, null, 'Account locked due to too many login attempts.');
                $response->send();
            }

            if (!password_verify($password, $returned_password)) {
                $query = "UPDATE hotels SET login_attempts = login_attempts + 1 WHERE id = :id";
                $stmt = $this->writeDB->prepare($query);
                $stmt->bindParam(':id', $returned_id, PDO::PARAM_INT);
                $stmt->execute();

                $response = new Response(401, false, null, 'Incorrect password.');
                $response->send();
            }

            $access_token = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
            $refresh_token = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());

            $access_token_expiry = 1200;
            $refresh_token_expiry = 604800;

            try {
                $this->writeDB->beginTransaction();

                $query = "UPDATE hotels SET login_attempts = 0 WHERE id = :id";
                $stmt = $this->writeDB->prepare($query);
                $stmt->bindParam(':id', $returned_id, PDO::PARAM_INT);
                $stmt->execute();

                $query = "INSERT INTO sessions (user_id, access_token, access_token_expiry, refresh_token, refresh_token_expiry) VALUES (:user_id, :access_token, date_add(NOW(), INTERVAL :access_token_expiry SECOND), :refresh_token, date_add(NOW(), INTERVAL :refresh_token_expiry SECOND))";
                $stmt = $this->writeDB->prepare($query);
                $stmt->bindParam(':user_id', $returned_id);
                $stmt->bindParam(':access_token', $access_token);
                $stmt->bindParam(':refresh_token', $refresh_token);
                $stmt->bindParam(':access_token_expiry', $access_token_expiry);
                $stmt->bindParam(':refresh_token_expiry', $refresh_token_expiry);
                $stmt->execute();

                $session_id = intval($this->writeDB->lastInsertId());
                $this->writeDB->commit();

                $data['session_id'] = $session_id;
                $data['user_id'] = $returned_id;
                $data['access_token'] = $access_token;
                $data['access_token_expiry'] = $access_token_expiry;
                $data['refresh_token'] = $refresh_token;
                $data['refresh_token_expiry'] = $refresh_token_expiry;

                $response =  new Response(201, true, $data, 'Successfully logged in.');
                $response->send();
            } catch (PDOException $e) {
                $this->writeDB->rollback();
                $response = new Response(500, false, null, 'There was an error logging in. ' . $e->getMessage());
                $response->send();
            }
        } catch (PDOException $e) {
            $response = new Response(500, false, null, 'There was an error creating session.');
            $response->send();
        }
    }
}
