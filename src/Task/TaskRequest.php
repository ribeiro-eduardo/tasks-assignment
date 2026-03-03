<?php

namespace App\Task;

use App\Core\Request;

class TaskRequest extends Request
{
    private const VALID_STATUSES = ['todo', 'in_progress', 'done'];

    public function validateStore(): void {
        if (empty($this->body['title'])) {
            throw new \InvalidArgumentException('INVALID_TITLE');
        }

        if (empty($this->body['id_assigned_user'])) {
            throw new \InvalidArgumentException('INVALID_ASSIGNED_USER');
        }

        if (!is_numeric($this->body['id_assigned_user'])) {
            throw new \InvalidArgumentException('INVALID_ASSIGNED_USER');
        }

        if (isset($this->body['status']) && !in_array($this->body['status'], self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('INVALID_STATUS');
        }
    }

    public function validateUpdate(): void {
        if (empty($this->routeParams['id'])) {
            throw new \InvalidArgumentException('INVALID_ID');
        }

        if (empty($this->body['title'])) {
            throw new \InvalidArgumentException('INVALID_TITLE');
        }

        if (empty($this->body['id_assigned_user'])) {
            throw new \InvalidArgumentException('INVALID_ASSIGNED_USER');
        }

        if (!is_numeric($this->body['id_assigned_user'])) {
            throw new \InvalidArgumentException('INVALID_ASSIGNED_USER');
        }

        if (isset($this->body['status']) && !in_array($this->body['status'], self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('INVALID_STATUS');
        }
    }

    public function validateShow(): void {
        if (empty($this->routeParams['id'])) {
            throw new \InvalidArgumentException('INVALID_ID');
        }
    }

    public function validateDelete(): void {
        if (empty($this->routeParams['id'])) {
            throw new \InvalidArgumentException('INVALID_ID');
        }
    }
}
