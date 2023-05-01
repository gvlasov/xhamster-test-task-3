<?php

namespace Gvlasov\XhamsterTestTask3;

interface UserModificationLog
{

    public function logCreation(User $user): void;
    public function logUpdate(User $user): void;
    public function logDeletion(User $user): void;

}