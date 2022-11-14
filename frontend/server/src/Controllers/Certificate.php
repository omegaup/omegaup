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
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param mixed $certificates_cutoff
     * @omegaup-request-param mixed $contest_id
     */
    public static function apiGenerateContestCertificates(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();

        // obtain the contest
        $contest = \OmegaUp\DAO\Contests::getByPK($r['contest_id']);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        error_log(print_r($contest, true));
        //check if is a certificate generator
        if (!\OmegaUp\Authorization::isCertificateGenerator($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if ($contest->certificates_status !== 'uninitiated' && $contest->certificates_status !== 'retryable_error') {
            return ['status' => 'ok'];
        }

        // add certificates_cutoff value to the course
        if (!is_null($r['certificates_cutoff'])) {
            $contest->certificate_cutoff = $r['certificates_cutoff'];
        }

        // update contest with the new value
        \OmegaUp\DAO\Contests::update($contest);

        //connection to rabbitmq
        $channel = \OmegaUp\RabbitMQConnection::getInstance()->channel();

        $channel->queue_declare(
            'contest_certificate',
            false,
            false,
            false,
            false
        );
        $msg = new \PhpAmqpLib\Message\AMQPMessage(
            'Message to contest_certificates queue!'
        );
        $channel->basic_publish($msg, '', 'contest_certificate');
        ///como checo que si se le envio
        ///como lo pruebo
        $channel->close();

        $channel1 = \OmegaUp\RabbitMQConnection::getInstance()->channel();
        error_log(print_r('hello world!', true));

        /// hacer un receiver
        $channel1->queue_declare(
            'contest_certificate',
            false,
            false,
            false,
            false
        );

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $callback = function ($msge) {
            error_log(print_r('hola como estan', true));
            echo ' [x] Received ', $msge->body, "\n";
        };
        error_log(print_r('holaaaaaaaaaa', true));

        $channel1->basic_consume(
            'contest_certificate',
            '',
            false,
            true,
            false,
            false,
            $callback
        );

        while ($channel->is_open()) {
            $channel1->wait();
        }

        $channel1->close();
        return [
            'status' => 'ok',
        ];
    }
}
