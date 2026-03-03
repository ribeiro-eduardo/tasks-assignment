<?php

namespace Tests\User;

use App\User\UserRequest;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class UserRequestTest extends TestCase
{
    private function makeRequest(array $body = [], array $routeParams = []): UserRequest
    {
        $request = new UserRequest();

        $bodyProp = new ReflectionProperty($request, 'body');
        $bodyProp->setValue($request, $body);

        $paramsProp = new ReflectionProperty($request, 'routeParams');
        $paramsProp->setValue($request, $routeParams);

        return $request;
    }

    public function testValidateStorePassesWithValidData(): void
    {
        $request = $this->makeRequest(['name' => 'John', 'email' => 'john@example.com']);

        $request->validateStore();

        $this->assertTrue(true);
    }

    public function testValidateStoreThrowsWhenNameMissing(): void
    {
        $request = $this->makeRequest(['email' => 'john@example.com']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INVALID_NAME');

        $request->validateStore();
    }

    public function testValidateStoreThrowsWhenEmailMissing(): void
    {
        $request = $this->makeRequest(['name' => 'John']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INVALID_EMAIL');

        $request->validateStore();
    }

    public function testValidateShowPassesWithId(): void
    {
        $request = $this->makeRequest([], ['id' => '1']);

        $request->validateShow();

        $this->assertTrue(true);
    }

    public function testValidateShowThrowsWhenIdMissing(): void
    {
        $request = $this->makeRequest();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INVALID_ID');

        $request->validateShow();
    }

    public function testValidateUpdatePassesWithValidData(): void
    {
        $request = $this->makeRequest(
            ['name' => 'John', 'email' => 'john@example.com'],
            ['id' => '1']
        );

        $request->validateUpdate();

        $this->assertTrue(true);
    }

    public function testValidateUpdateThrowsWhenIdMissing(): void
    {
        $request = $this->makeRequest(['name' => 'John', 'email' => 'john@example.com']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INVALID_ID');

        $request->validateUpdate();
    }

    public function testValidateDeleteThrowsWhenIdMissing(): void
    {
        $request = $this->makeRequest();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INVALID_ID');

        $request->validateDelete();
    }
}
