<?php
declare(strict_types=1);

namespace Johnm\Userservice\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Johnm\Userservice\UserService;
use PHPUnit\Framework\TestCase;

final class UserserviceTest extends TestCase
{
	private const BASE_URI = 'https://reqres.in/api';
	private const API_KEY = 'reqres-free-v1';

	private function makeService(array $responses): Userservice
	{
		$mock = new MockHandler($responses);
		$stack = HandlerStack::create($mock);
		$client = new Client(['handler' => $stack, 'base_uri' => self::BASE_URI]);
		return new Userservice($client, self::API_KEY);
	}

	public function testGetUserById(): void
	{
		$payload = [
			'data' => [
				'id' => 2,
				'email' => 'janet.weaver@reqres.in',
				'first_name' => 'Janet',
				'last_name' => 'Weaver',
				'avatar' => 'https://reqres.in/img/faces/2-image.jpg'
			]
		];

		$svc = $this->makeService([
			new Response(200, ['Content-Type' => 'application/json'], json_encode($payload, JSON_THROW_ON_ERROR))
		]);

		$user = $svc->getUserById(2);

		$this->assertSame(2, $user->id);
		$this->assertSame('Janet', $user->firstName);
		$this->assertSame('Weaver', $user->lastName);
		$this->assertSame('janet.weaver@reqres.in', $user->email);

		$arr = $user->toArray();
		$this->assertArrayHasKey('firstName', $arr);
		$this->assertSame('Janet', $arr['firstName']);
		$this->assertJson(json_encode($user));
	}

	public function testListUsers(): void
	{
		$payload = [
			'page' => 1,
			'per_page' => 2,
			'total' => 12,
			'total_pages' => 6,
			'data' => [
				[
					'id' => 1,
					'email' => 'george.bluth@reqres.in',
					'first_name' => 'George',
					'last_name' => 'Bluth',
					'avatar' => 'https://reqres.in/img/faces/1-image.jpg'
				],
				[
					'id' => 2,
					'email' => 'janet.weaver@reqres.in',
					'first_name' => 'Janet',
					'last_name' => 'Weaver',
					'avatar' => 'https://reqres.in/img/faces/2-image.jpg'
				]
			]
		];

		$svc = $this->makeService([
			new Response(200, ['Content-Type' => 'application/json'], json_encode($payload, JSON_THROW_ON_ERROR))
		]);

		$page = $svc->listUsers(1, 2);

		$this->assertSame(1, $page->page);
		$this->assertSame(2, $page->perPage);
		$this->assertCount(2, $page->data);
		$this->assertSame('George', $page->data[0]->firstName);

		$this->assertJson(json_encode($page));
		$this->assertSame(12, $page->total);
	}

	public function testCreateUser(): void
	{
		$apiResponse = [
			'id' => '123',
			'createdAt' => '2025-01-01T12:00:00.000Z'
		];

		$svc = $this->makeService([
			new Response(201, ['Content-Type' => 'application/json'], json_encode($apiResponse, JSON_THROW_ON_ERROR))
		]);

		$created = $svc->createUser('Neo', 'The One');

		$this->assertSame('123', $created->id);
		$this->assertSame('Neo', $created->name);
		$this->assertSame('The One', $created->job);
		$this->assertSame('2025-01-01T12:00:00+00:00', $created->createdAt->format(DATE_ATOM));

		$this->assertJson(json_encode($created));
		$this->assertArrayHasKey('createdAt', $created->toArray());
	}

	public function testApiKeyHeaderIsAdded(): void
	{
		$history = [];
		$mock = new MockHandler([
			new Response(200, ['Content-Type' => 'application/json'], json_encode(['data' => [
				'id' => 2, 'email' => 'a@b', 'first_name' => 'A', 'last_name' => 'B', 'avatar' => ''
			]], JSON_THROW_ON_ERROR)),
		]);

		$stack = HandlerStack::create($mock);
		$stack->push(Middleware::history($history));

		$client = new Client(['handler' => $stack, 'base_uri' => self::BASE_URI]);

		$svc = new UserService($client, self::API_KEY);
		$svc->getUserById(2);

		$this->assertNotEmpty($history);
		$sent = $history[0]['request'];
		$this->assertSame(self::API_KEY, $sent->getHeaderLine('x-api-key'));
	}
}
