<?php

declare(strict_types=1);

namespace App\Exceptions\Marketing;

use App\Enums\Marketing\DropStatus;

final class InvalidDropTransition extends \DomainException
{
    public function __construct(
        public readonly DropStatus $from,
        public readonly DropStatus $to,
    ) {
        parent::__construct("Invalid drop transition {$from->value} -> {$to->value}");
    }
}
