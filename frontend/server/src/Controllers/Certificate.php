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
     * @omegaup-request-param int|null $certificates_cutoff
     * @omegaup-request-param int|null $contest_id
     */
    public static function apiGenerateContestCertificates(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();

        $contestID = $r->ensureInt('contest_id');

        // obtain the contest
        $contest = \OmegaUp\DAO\Contests::getByPK($contestID);

        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        //check if is a certificate generator
        if (!\OmegaUp\Authorization::isCertificateGenerator($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if ($contest->certificates_status !== 'uninitiated' && $contest->certificates_status !== 'retryable_error') {
            return ['status' => 'ok'];
        }

        $certificateCutoff = $r->ensureOptionalInt('certificates_cutoff');

        // add certificates_cutoff value to the course
        if (!is_null($certificateCutoff)) {
            $contest->certificate_cutoff = $certificateCutoff;
        }

        // update contest with the new value
        \OmegaUp\DAO\Contests::update($contest);

        // get contest info
        $contestExtraInformation = \OmegaUp\DAO\Contests::getByAliasWithExtraInformation(
            $contest->alias
        );

        // set RabbitMQ client parameters
        $routing_key = 'ContestQueue';
        $exchange = 'certificates';

        //connection to rabbitmq
        $channel = \OmegaUp\RabbitMQConnection::getInstance()->channel();

        // Prepare the meessage
        $messageArray = [
            'certificate_cutoff' => $contestExtraInformation['certificate_cutoff'],
            'alias' => $contestExtraInformation['alias'],
            'scoreboard_url' => $contestExtraInformation['scoreboard_url'],
            'contest_id' => $contestExtraInformation['contest_id']
        ];
        $messageJSON = json_encode($messageArray);
        $message = new \PhpAmqpLib\Message\AMQPMessage($messageJSON);

        // send the message to RabbitMQ
        $channel->basic_publish($message, $exchange, $routing_key);
        $channel->close();

        return [
            'status' => 'ok',
        ];
    }
}
