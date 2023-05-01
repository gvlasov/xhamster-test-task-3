<?php

namespace Tests\Helpers;

use Gvlasov\XhamsterTestTask3\User;
use Gvlasov\XhamsterTestTask3\UserModificationLog;

class NullUserModificationLog implements UserModificationLog
{

    public function logCreation(User $user): void
    {
        // Does nothing
    }

    public function logUpdate(User $user): void
    {
        // Does nothing
    }

    public function logDeletion(User $user): void
    {
        // Does nothing
    }
}
