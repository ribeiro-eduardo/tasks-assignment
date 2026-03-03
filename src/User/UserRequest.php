<?php
/**
 * Created by PhpStorm.
 * User: edri01
 * Date: 03/03/2026
 * Time: 18:17
 */

namespace App\User;

use \App\Core\Request;
use http\Exception\InvalidArgumentException;

class UserRequest extends Request
{
    public function validateShow(): void {
        if (empty($this->routeParams['id'])) {
            throw new \InvalidArgumentException('INVALID_ID');
        }
    }

    public function validateStore(): void {
        if (empty($this->body['name'])) {
            throw new \InvalidArgumentException('INVALID_NAME');
        }

        if (empty($this->body['email'])) {
            throw new \InvalidArgumentException('INVALID_EMAIL');
        }
    }

    public function validateUpdate(): void {
        if (empty($this->routeParams['id'])) {
            throw new \InvalidArgumentException('INVALID_ID');
        }

        if (empty($this->body['name'])) {
            throw new \InvalidArgumentException('INVALID_NAME');
        }

        if (empty($this->body['email'])) {
            throw new \InvalidArgumentException('INVALID_EMAIL');
        }
    }

    public function validateDelete(): void {
        if (empty($this->routeParams['id'])) {
            throw new \InvalidArgumentException('INVALID_ID');
        }
    }
}