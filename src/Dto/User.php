<?php
declare(strict_types=1);

namespace Johnm\Userservice\Dto;

use JsonSerializable;

final class User implements JsonSerializable, ArrayConvertible
{
	public function __construct(
		public readonly int $id,
		public readonly string $email,
		public readonly string $firstName,
		public readonly string $lastName,
		public readonly string $avatar
	) {}

	/** @param array<string,mixed> $data */
	public static function fromApi(array $data): self
	{
		return new self(
			id: (int) $data['id'],
			email: (string) $data['email'],
			firstName: (string) $data['first_name'],
			lastName: (string) $data['last_name'],
			avatar: (string) $data['avatar']
		);
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'id'         => $this->id,
			'email'      => $this->email,
			'firstName'  => $this->firstName,
			'lastName'   => $this->lastName,
			'avatar'     => $this->avatar,
		];
	}

	/** @return array<mixed> */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
