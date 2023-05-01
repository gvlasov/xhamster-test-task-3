<?php

namespace Tests\Helpers;

use Gvlasov\XhamsterTestTask3\ProhibitedWords;

class WordBollocksIsProhibited implements ProhibitedWords
{

    public function hasProhibitedWords(string $text): bool
    {
        return str_contains($text, 'bollocks');
    }

}
