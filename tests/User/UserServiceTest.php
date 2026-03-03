<?php

namespace Tests\User;

use App\Task\TaskRepository;
use App\User\UserRepository;
use App\User\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserRepository $repository;
    private TaskRepository $taskRepository;
    private UserService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->service = new UserService($this->repository, $this->taskRepository);
    }

    public function testGetAllReturnsUsers(): void
    {
        $users = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
        ];

        $this->repository->method('findAll')->willReturn($users);

        $this->assertSame($users, $this->service->getAll());
    }

    public function testGetByIdReturnsUser(): void
    {
        $user = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];

        $this->repository->method('findById')->with(1)->willReturn($user);

        $this->assertSame($user, $this->service->getById(1));
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $this->repository->method('findById')->with(999)->willReturn(null);

        $this->assertNull($this->service->getById(999));
    }

    public function testCreateSucceeds(): void
    {
        $data = ['name' => 'John', 'email' => 'john@example.com'];
        $created = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];

        $this->repository->method('findByEmail')->with('john@example.com')->willReturn(null);
        $this->repository->method('create')->with($data)->willReturn($created);

        $this->assertSame($created, $this->service->create($data));
    }

    public function testCreateThrowsOnDuplicateEmail(): void
    {
        $data = ['name' => 'John', 'email' => 'john@example.com'];

        $this->repository->method('findByEmail')
            ->with('john@example.com')
            ->willReturn(['1' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Email already exists');
        $this->expectExceptionCode(409);

        $this->service->create($data);
    }

    public function testUpdateSucceeds(): void
    {
        $existing = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
        $data = ['name' => 'Jane', 'email' => 'jane@example.com'];

        $this->repository->method('findById')->with(1)->willReturn($existing);
        $this->repository->method('findByEmail')->with('jane@example.com')->willReturn(null);
        $this->repository->method('update')->with(1, $data)->willReturn(true);

        $this->assertTrue($this->service->update(1, $data));
    }

    public function testUpdateThrowsWhenUserNotFound(): void
    {
        $this->repository->method('findById')->with(999)->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not found');
        $this->expectExceptionCode(404);

        $this->service->update(999, ['name' => 'Jane', 'email' => 'jane@example.com']);
    }

    public function testUpdateThrowsOnDuplicateEmail(): void
    {
        $existing = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
        $data = ['name' => 'John', 'email' => 'taken@example.com'];

        $this->repository->method('findById')->with(1)->willReturn($existing);
        $this->repository->method('findByEmail')->with('taken@example.com')->willReturn(['1' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Email already exists');
        $this->expectExceptionCode(409);

        $this->service->update(1, $data);
    }

    public function testUpdateSkipsEmailCheckWhenUnchanged(): void
    {
        $existing = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
        $data = ['name' => 'Jane', 'email' => 'john@example.com'];

        $this->repository->method('findById')->with(1)->willReturn($existing);
        $this->repository->expects($this->never())->method('findByEmail');
        $this->repository->method('update')->with(1, $data)->willReturn(true);

        $this->assertTrue($this->service->update(1, $data));
    }

    public function testDeleteSucceeds(): void
    {
        $user = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];

        $this->repository->method('findById')->with(1)->willReturn($user);
        $this->taskRepository->method('findByUserId')->with(1)->willReturn(null);
        $this->repository->method('delete')->with(1)->willReturn(true);

        $this->assertTrue($this->service->delete(1));
    }

    public function testDeleteThrowsWhenUserNotFound(): void
    {
        $this->repository->method('findById')->with(999)->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not found');
        $this->expectExceptionCode(404);

        $this->service->delete(999);
    }

    public function testDeleteThrowsWhenUserHasTasks(): void
    {
        $user = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];

        $this->repository->method('findById')->with(1)->willReturn($user);
        $this->taskRepository->method('findByUserId')->with(1)->willReturn(['1' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User has assigned tasks');
        $this->expectExceptionCode(409);

        $this->service->delete(1);
    }
}
