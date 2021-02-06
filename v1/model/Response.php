<?php

class Response
{
    private $status_code;
    private $success;
    private $data;
    private $messages = array();
    private $is_cache;
    private $response_data = array();

    public function __construct($status_code, $success, $data, $message, $is_cache)
    {
        $this->setStatusCode($status_code);
        $this->setSuccess($success);
        $this->setData($data);
        $this->addMessage($message);
        $this->isCache($is_cache);
    }

    public function setStatusCode($status_code)
    {
        $this->status_code = $status_code;
    }

    public function setSuccess($success)
    {
        $this->success = $success;
    }

    public function addMessage($message)
    {
        array_push($this->messages, $message);
    }

    public function isCache($flag)
    {
        $this->is_cache = $flag;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function send()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($this->is_cache) {
            header('Cache-Control: max-age=60');
        } else {
            header('Cache-Control: no-cache, no-store, must-revalidate');
        }

        if (($this->success !== true && $this->success !== false) || !is_numeric($this->status_code)) {
            http_response_code(500);
            $this->response_data['statusCode'] = 500;
            $this->response_data['success'] = false;
            $this->addMessage('Internal Server Error.');
            $this->addMessage('Response Error.');
            $this->response_data['messages'] = $this->messages;
        } else {
            http_response_code($this->status_code);
            $this->response_data['statusCode'] = $this->status_code;
            $this->response_data['success'] = $this->success;
            $this->response_data['messages'] = $this->messages;
            $this->response_data['data'] = $this->data;
        }

        echo json_encode($this->response_data);
        exit;
    }
}
