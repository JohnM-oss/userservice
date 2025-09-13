<?php
declare(strict_types=1);

namespace Johnm\Userservice\Tests\Integration;

use Johnm\Userservice\UserService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class LiveApiTest extends TestCase
{
	private const BASE_URI = 'https://reqres.in/api/';
	private const API_KEY = 'reqres-free-v1';

	protected function setUp(): void
	{
		if (getenv('REQRES_LIVE_TESTS') !== '1') {
			$this->markTestSkipped('Set REQRES_LIVE_TESTS=1 to run live API tests');
		}
	}

	private function getSvc() : UserService
	{
		return UserService::withDefaults(self::BASE_URI, 10, self::API_KEY);
	}

	#[Group('integration')]
	public function testGetUserByIdLive(): void
	{
		$svc  = $this->getSvc();
		$user = $svc->getUserById(2);

		$this->assertSame(2, $user->id);
		$this->assertNotEmpty($user->email);
	}

	#[Group('integration')]
	public function testCreateUserLive(): void
	{
		$svc  = $this->getSvc();
		$created = $svc->createUser('Unit Tester', 'Dev');

		$this->assertNotSame('', $created->id);
		$this->assertSame('Unit Tester', $created->name);
		$this->assertSame('Dev', $created->job);
	}
}
