<?php
// Given that we already have an autoload configured, we cannot use
// sodium_compat's (fast) autoloader. Instead, simulate what it does
// here, with the full path of the standard autoload file.
ini_set(
    'include_path',
    ini_get('include_path') . PATH_SEPARATOR . dirname(__DIR__)
);
require_once 'libs/third_party/sodium_compat/autoload.php';
\ParagonIE_Sodium_Compat::$fastMult = true;

require_once 'libs/third_party/constant_time_encoding/src/EncoderInterface.php';
require_once 'libs/third_party/constant_time_encoding/src/Base64.php';
require_once 'libs/third_party/constant_time_encoding/src/Base64UrlSafe.php';
require_once 'libs/third_party/constant_time_encoding/src/Binary.php';

require_once 'libs/third_party/paseto/src/KeyInterface.php';
require_once 'libs/third_party/paseto/src/SendingKey.php';
require_once 'libs/third_party/paseto/src/ReceivingKey.php';
require_once 'libs/third_party/paseto/src/Keys/SymmetricKey.php';
require_once 'libs/third_party/paseto/src/Keys/AsymmetricSecretKey.php';
require_once 'libs/third_party/paseto/src/Keys/AsymmetricPublicKey.php';
require_once 'libs/third_party/paseto/src/ProtocolInterface.php';
require_once 'libs/third_party/paseto/src/Protocol/Version1.php';
require_once 'libs/third_party/paseto/src/Protocol/Version2.php';
require_once 'libs/third_party/paseto/src/Traits/RegisteredClaims.php';

echo(\ParagonIE\Paseto\Keys\SymmetricKey::generate(
    new \ParagonIE\Paseto\Protocol\Version2()
)->encode() . "\n");
