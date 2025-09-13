<?php
declare(strict_types=1);

namespace Johnm\Userservice\Dto;

interface ArrayConvertible
{
	/** @return array<string, mixed> */
	public function toArray(): array;
}
