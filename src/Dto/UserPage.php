<?php
declare(strict_types=1);

namespace Johnm\Userservice\Dto;

use JsonSerializable;

final class UserPage implements JsonSerializable, ArrayConvertible
{
	/** @param User[] $data */
	public function __construct(
		public readonly int $page,
		public readonly int $perPage,
		public readonly int $total,
		public readonly int $totalPages,
		public readonly array $data
	) {}

	/** @param array<string,mixed> $payload */
	public static function fromApi(array $payload): self
	{
		$users = array_map(
			fn(array $u) => User::fromApi($u),
			$payload['data'] ?? []
		);

		return new self(
			page: (int) $payload['page'],
			perPage: (int) $payload['per_page'],
			total: (int) $payload['total'],
			totalPages: (int) $payload['total_pages'],
			data: $users
		);
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'page'       => $this->page,
			'perPage'    => $this->perPage,
			'total'      => $this->total,
			'totalPages' => $this->totalPages,
			'data'       => array_map(fn(User $u) => $u->toArray(), $this->data),
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
