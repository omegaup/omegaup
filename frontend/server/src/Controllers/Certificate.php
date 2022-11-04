<?php

namespace OmegaUp\Controllers;

/**
 * CertificateController

 * @psalm-type CertificateDetailsPayload=array{uuid: string}
 */
class Certificate extends \OmegaUp\Controllers\Controller {
    /**
     * @return array{templateProperties: array{payload: CertificateDetailsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $uuid
     */
    public static function getDetailsForTypeScript(\OmegaUp\Request $r) {
        return [
            'templateProperties' => [
                'payload' => [
                    'uuid' => $r->ensureString('uuid'),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCertificate'
                ),
            ],
            'entrypoint' => 'certificate_details',
        ];
    }

    /**
     * Creates a Clarification for a contest or an assignment of a course
     *
     * @return Clarification
     *
     * @omegaup-request-param mixed $contest_id
     */
    public static function apiGenerateContestCertificates(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();

        // obtain the contest
        $contest = \OmegaUp\DAO\Contests::getByPK($r['contest_id']);

        //check if is a certificate generator
        if (\OmegaUp\Authorization::isCertificateGenerator($r->identity)) {
            if ($contest->certificates_status === 'uninitiated' || $contest->certificates_status === 'retryable_error') {
                // add certificates_cutoff value to the course
                $contest->certificate_cutoff = $r['certificates_cutoff'];

                // update contest with the new value
                \OmegaUp\DAO\Contests::update($contest);

                //connection to rabbitmq
                $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                    'rabbitmq',
                    5672,
                    'omegaup',
                    'omegaup'
                );

                $channel = $connection->channel();

                $channel->queue_declare('hello', false, false, false, false);
                $msg = new \PhpAmqpLib\Message\AMQPMessage(
                    'Message to contest_certificates queue!'
                );
                $channel->basic_publish($msg, '', 'hello');
                echo " [x] Message to contest_certificates queue!'\n";
                $channel->close();
                $connection->close();
            }
        }

        return [
            'status' => 'ok',
        ];
    }
}
