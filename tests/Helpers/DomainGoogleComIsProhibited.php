<?php

namespace Tests\Helpers;

use Gvlasov\XhamsterTestTask3\TrustedDomains;

class DomainGoogleComIsProhibited implements TrustedDomains
{

    public function isDomainTrusted(string $domain): bool
    {
        return $domain !== 'google.com';
    }
}
