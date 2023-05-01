<?php

namespace Gvlasov\XhamsterTestTask3;

interface TrustedDomains
{

    public function isDomainTrusted(string $domain): bool;

}