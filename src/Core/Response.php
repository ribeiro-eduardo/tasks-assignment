<?php

namespace App\Core;

class Response
{
    public function __construct(
        public readonly mixed $data,
        public readonly int $status = 200,
    ) {
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}
