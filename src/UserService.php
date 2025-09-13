<?php
declare(strict_types=1);

namespace Johnm\Userservice;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Johnm\Userservice\Dto\CreatedUser;
use Johnm\Userservice\Dto\User;
use Johnm\Userservice\Dto\UserPage;
use Johnm\Userservice\Exception\ApiException;

final class UserService
{
	private const BASE_URI = 'https://reqres.in/api';

	public function __construct(
		private readonly ClientInterface $http,
		private readonly ?string $apiKey = null
	) {}

	public static function withDefaults(
		?string $baseUri = null,
		?float $timeout = 10.0,
		?string $apiKey = null
	): self
	{
		// allow env fallback (set REQRES_API_KEY in your system or web server)
		$apiKey = $apiKey ?? (getenv('REQRES_API_KEY') ?: null);

		$headers = [
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		];

		return new self(new Client([
			'base_uri' => $baseUri ?? self::BASE_URI,
			'timeout'  => $timeout,
			'http_errors' => false,
			'headers'  => $headers,
		]));
	}

	/** Retrieve a single user by ID */
	public function getUserById(int $id): User
	{
		$json = $this->requestJson('GET', "/users/{$id}");
		if (!isset($json['data'])) {
			throw new ApiException('Malformed response: missing data');
		}
		return User::fromApi($json['data']);
	}

	/** Retrieve a paginated list of users */
	public function listUsers(int $page = 1, int $perPage = 6): UserPage
	{
		$json = $this->requestJson('GET', '/users', ['query' => ['page' => $page, 'per_page' => $perPage]]);
		return UserPage::fromApi($json);
	}

	/** Create a new user; returns new user ID and metadata */
	public function createUser(string $name, string $job): CreatedUser
	{
		$json = $this->requestJson('POST', '/users', ['json' => ['name' => $name, 'job' => $job]]);
		if (!isset($json['id'], $json['createdAt'])) {
			throw new ApiException('Malformed response: missing id/createdAt');
		}
		return CreatedUser::fromApi($json, $name, $job);
	}

	/**
	 * @param array<string,mixed> $options
	 * @return array<string,mixed>
	 */
	private function requestJson(string $method, string $uri, array $options = []): array
	{
		$headers = array_change_key_case($options['headers'] ?? [], CASE_LOWER);
		if ($this->apiKey && !array_key_exists('x-api-key', $headers)) {
			$headers['x-api-key'] = $this->apiKey;
		}
		$options['headers'] = $headers;

		try {
			$res = $this->http->request($method, $uri, $options);
		} catch (GuzzleException $e) {
			throw new ApiException('HTTP error: ' . $e->getMessage(), 0, $e);
		}

		$code = $res->getStatusCode();
		$body = (string) $res->getBody();

		/** @var array<string,mixed>|null $json */
		$json = json_decode($body, true);

		if ($json === null && $body !== '' && json_last_error() !== JSON_ERROR_NONE) {
			throw new ApiException('Invalid JSON from API: ' . json_last_error_msg());
		}

		if ($code < 200 || $code >= 300) {
			$msg = $json['error'] ?? "Unexpected status code {$code}";
			throw new ApiException((string) $msg);
		}

		return $json ?? [];
	}
}
