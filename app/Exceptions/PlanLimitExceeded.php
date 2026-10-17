<?php

namespace App\Exceptions;

use RuntimeException;

class PlanLimitExceeded extends RuntimeException
{
    public static function forLimit(string $label, int $limit): self
    {
        return new self("The current plan allows {$limit} {$label}. Upgrade the team plan to add more.");
    }
}
