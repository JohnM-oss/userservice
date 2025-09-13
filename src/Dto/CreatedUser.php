<?php
declare(strict_types=1);

namespace Johnm\Userservice\Dto;

use JsonSerializable;

final class CreatedUser implements JsonSerializable, ArrayConvertible
{
	public function __construct(
		public readonly string $id,
		public readonly string $name,
		public readonly string $job,
		public readonly \DateTimeImmutable $createdAt
	) {}

	/** @param array<string,mixed> $payload */
	public static function fromApi(array $payload, string $name, string $job): self
	{
		return new self(
			id: (string) $payload['id'],
			name: $name,
			job: $job,
			createdAt: new \DateTimeImmutable((string) $payload['createdAt'])
		);
	}

	/** @return array<string,mixed> */
	public function toArray(): array
	{
		return [
			'id'        => $this->id,
			'name'      => $this->name,
			'job'       => $this->job,
			'createdAt' => $this->createdAt->format(DATE_ATOM),
		];
	}

	/** @return array<mixed> */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
