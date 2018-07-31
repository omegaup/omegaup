<?php

class Broadcaster {
    // Logging.
    private $log = null;

    public function __construct() {
        $this->log = Logger::getLogger('broadcaster');
    }

    public function broadcastClarification(Request $r, $time) {
        try {
            $message = json_encode([
                'message' => '/clarification/update/',
                'clarification' => [
                    'clarification_id' => (int)$r['clarification']->clarification_id,
                    'problem_alias' => $r['problem']->alias,
                    'author' => $r['user']->username,
                    'message' => $r['clarification']->message,
                    'answer' => $r['clarification']->answer,
                    'time' => $time,
                    'public' => $r['clarification']->public != '0'
                ]
            ]);

            $grader = new Grader();
            $this->log->debug("Sending update $message");
            $grader->broadcast(
                is_null($r['contest']) ? null : $r['contest']->alias,
                is_null($r['contest']) ? null : (int)$r['contest']->problemset_id,
                is_null($r['problem']) ? null : $r['problem']->alias,
                $message,
                $r['clarification']->public != '0',
                $r['user']->username,
                $r['clarification']->author_id,
                false  // user_only
            );
        } catch (Exception $e) {
            $this->log->error('Failed to send to broadcaster ' . $e->getMessage());
        }
        $this->sendClarificationEmail($r, $time);
    }

    protected function sendClarificationEmail(Request $r) {
        if (!is_null($r['clarification']->answer) ||
                !$r['problem']->email_clarifications) {
            return;
        }
        try {
            $emails = ProblemsDAO::getExplicitAdminEmails($r['problem']);

            global $smarty;
            $email_params = [
                'clarification_id' => $r['clarification']->clarification_id,
                'clarification_body' => htmlspecialchars($r['clarification']->message),
                'problem_alias' => $r['problem']->alias,
                'problem_name' => htmlspecialchars($r['problem']->title),
                'url' => is_null($r['contest']) ?
                    ('https://omegaup.com/arena/problem/' . $r['problem']->alias . '#clarifications') :
                    ('https://omegaup.com/arena/' . $r['contest']->alias . '#clarifications'),
                'user_name' => $r['user']->username
            ];
            $subject = ApiUtils::FormatString(
                $smarty->getConfigVars('clarificationEmailSubject'),
                $email_params
            );
            $body = ApiUtils::FormatString(
                $smarty->getConfigVars('clarificationEmailBody'),
                $email_params
            );

            Email::sendEmail($emails, $subject, $body);
        } catch (Exception $e) {
            $this->log->error('Failed to send clarification email ' . $e->getMessage());
        }
    }
}
