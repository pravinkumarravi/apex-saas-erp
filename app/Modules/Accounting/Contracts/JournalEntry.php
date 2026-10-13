<?php

namespace Modules\Accounting\Contracts;

interface JournalEntry
{
    public function isBalanced(): bool;
}
