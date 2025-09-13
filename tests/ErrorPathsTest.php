<?php
declare(strict_types=1);

namespace Johnm\Userservice\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Johnm\Userservice\Exception\ApiException;
use Johnm\Userservice\UserService;
use PHPUnit\Framework\TestCase;

final class ErrorPathsTest extends TestCase
{
	private const BASE_URI = 'https://reqres.in/api/';
	private const API_KEY = 'reqres-free-v1';

	private function svcWithResponses(array $responses): UserService
	{
		$mock  = new MockHandler($responses);
		$stack = HandlerStack::create($mock);
		$http  = new Client(['handler' => $stack, 'base_uri' => self::BASE_URI]);
		return new UserService($http, self::API_KEY);
	}

	public function test404ThrowsWithUsefulMessage(): void
	{
		$svc = $this->svcWithResponses([
			new Response(404, ['Content-Type' => 'text/plain'], 'Not found'),
		]);

		$this->expectException(ApiException::class);
		$this->expectExceptionMessageMatches('/Status:\s?404/i');
		$this->expectExceptionMessageMatches('#' . self::BASE_URI . 'users/9999#');

		$svc->getUserById(9999);
	}

	public function testInvalidJsonThrows(): void
	{
		$svc = $this->svcWithResponses([
			new Response(200, ['Content-Type' => 'application/json'], '<<<not json>>>'),
		]);

		$this->expectException(ApiException::class);
		$this->expectExceptionMessageMatches('/Invalid JSON/i');

		$svc->getUserById(2);
	}
}
