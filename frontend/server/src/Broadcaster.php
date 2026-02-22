<?php

namespace OmegaUp;

class Broadcaster {
    /** @var \Monolog\Logger */
    private $log;

    public function __construct() {
        $this->log = \Monolog\Registry::omegaup()->withName('broadcaster');
    }

    public function broadcastClarification(
        \OmegaUp\DAO\VO\Clarifications $clarification,
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Contests $contest
    ): void {
        try {
            $message = json_encode([
                'message' => '/clarification/update/',
                'clarification' => [
                    'clarification_id' => $clarification->clarification_id,
                    'problem_alias' => $problem->alias,
                    'author' => $identity->username,
                    'message' => $clarification->message,
                    'answer' => $clarification->answer,
                    'time' => $clarification->time,
                    'public' => boolval($clarification->public),
                ],
            ]);

            $this->log->debug("Sending update $message");
            \OmegaUp\Grader::getInstance()->broadcast(
                $contest === null ? null : $contest->alias,
                $contest === null ? null : $contest->problemset_id,
                $problem->alias,
                $message,
                boolval($clarification->public),
                $identity->username,
                $clarification->author_id ?: -1,
                false  // user_only
            );
        } catch (\Exception $e) {
            $this->log->error(
                'Failed to send to broadcaster',
                ['exception' => $e],
            );
        }
        $this->sendClarificationEmail(
            $contest,
            $problem,
            $identity,
            $clarification
        );
    }

    private function sendClarificationEmail(
        ?\OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Clarifications $clarification
    ): void {
        if (
            $clarification->answer !== null ||
                !$problem->email_clarifications
        ) {
            return;
        }
        try {
            $emails = \OmegaUp\DAO\Problems::getExplicitAdminEmails($problem);

            $emailParams = [
                'clarification_id' => strval($clarification->clarification_id),
                'clarification_body' => htmlspecialchars(
                    strval($clarification->message)
                ),
                'problem_alias' => strval($problem->alias),
                'problem_name' => htmlspecialchars(strval($problem->title)),
                'url' => $contest === null ?
                    ("https://omegaup.com/arena/problem/{$problem->alias}#clarifications") :
                    ("https://omegaup.com/arena/{$contest->alias}#clarifications"),
                'user_name' => strval($identity->username),
            ];
            $subject = \OmegaUp\ApiUtils::formatString(
                \OmegaUp\Translations::getInstance()->get(
                    'clarificationEmailSubject'
                ),
                $emailParams
            );
            $body = \OmegaUp\ApiUtils::formatString(
                \OmegaUp\Translations::getInstance()->get(
                    'clarificationEmailBody'
                ),
                $emailParams
            );

            \OmegaUp\Email::sendEmail($emails, $subject, $body);
        } catch (\Exception $e) {
            $this->log->error(
                'Failed to send clarification email ' . $e->getMessage()
            );
        }
    }
}
