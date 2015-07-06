<?php

class Broadcaster {
	// Logging.
	private $log = null;

	public function Broadcaster() {
		$this->log = Logger::getLogger("broadcaster");
	}

	public function broadcastClarification(Request $r, $time) {
		try {
			$message = json_encode(array(
				'message' => '/clarification/update/',
				'clarification' => array(
					'clarification_id' => $r['clarification']->clarification_id,
					'problem_alias' => $r['problem']->alias,
					'author' => $r['user']->username,
					'message' => $r['clarification']->message,
					'answer' => $r['clarification']->answer,
					'time' => $time,
					'public' => $r['clarification']->public != '0'
				)
			));

			$grader = new Grader();
			self::$log->debug("Sending update $message");
			$grader->broadcast(
				is_null($r['contest']) ? null : $r['contest']->alias,
				$message,
				$r['clarification']->public != '0',
				$r['clarification']->author_id
			);
		} catch(Exception $e) {
			self::$log->error("Failed to send to broadcaster " . $e->getMessage());
		}
		$this->sendClarificationEmail($r, $time);
	}

	protected function sendClarificationEmail(Request $r, $time) {
		if (!OMEGAUP_EMAIL_SEND_EMAILS ||
				!is_null($r['clarification']->answer) ||
				!$r['problem']->email_clarifications) {
			return;
		}
		try {
			$emails = ProblemsDAO::getExplicitAdminEmails($r['problem']);

			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->Host = OMEGAUP_EMAIL_SMTP_HOST;
			$mail->SMTPAuth = true;
			$mail->Password = OMEGAUP_EMAIL_SMTP_PASSWORD;
			$mail->From = OMEGAUP_EMAIL_SMTP_FROM;
			$mail->Port = 465;
			$mail->SMTPSecure = 'ssl';
			$mail->Username = OMEGAUP_EMAIL_SMTP_FROM;

			$mail->FromName = OMEGAUP_EMAIL_SMTP_FROM;
			foreach ($emails as $email) {
				$mail->AddAddress($email);
			}
			$mail->isHTML(true);
			global $smarty;
			$email_params = array(
				'clarification_id' => $r['clarification']->clarification_id,
				'clarification_body' => htmlspecialchars($r['clarification']->message),
				'problem_alias' => $r['problem']->alias,
				'problem_name' => htmlspecialchars($r['problem']->title),
				'url' => is_null($r['contest']) ?
					('https://omegaup.com/arena/problem/' . $r['problem']->alias . '#clarifications') :
					('https://omegaup.com/arena/' . $r['contest']->alias . '#clarifications'),
				'user_name' => $r['user']->username
			);
			$mail->Subject = ApiUtils::FormatString(
				$smarty->getConfigVars('clarificationEmailSubject'),
				$email_params
			);
			$mail->Body = ApiUtils::FormatString(
				$smarty->getConfigVars('clarificationEmailBody'),
				$email_params
			);

			if (!$mail->Send()) {
				self::$log->error("Failed to send mail: " . $mail->ErrorInfo);
			}
		} catch (Exception $e) {
			self::$log->error("Failed to send clarification email " . $e->getMessage());
		}
	}
}
