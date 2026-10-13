<?php

namespace Modules\Accounting\Domain\Contracts;

interface JournalEntry
{
    public function isBalanced(): bool;
}
