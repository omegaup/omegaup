<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

if (!isset($_GET['verification_code'])) {
    die();
}
/** @var string */
$verificationCode = $_GET['verification_code'];
$fileName = "certificate_{$verificationCode}.pdf";
$blobData = \OmegaUp\Controllers\Certificate::getCertificatePdf(
    $verificationCode
);
if (is_null($blobData)) {
    die();
}
header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename={$fileName}");
echo base64_decode($blobData);
