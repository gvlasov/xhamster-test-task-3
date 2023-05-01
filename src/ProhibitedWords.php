<?php

namespace Gvlasov\XhamsterTestTask3;

interface ProhibitedWords
{

    public function hasProhibitedWords(string $text): bool;

}