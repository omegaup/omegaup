<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
\ParagonIE_Sodium_Compat::$fastMult = true;

echo(\ParagonIE\Paseto\Keys\SymmetricKey::generate(
    new \ParagonIE\Paseto\Protocol\Version2()
)->encode() . "\n");
