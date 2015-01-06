<?php
class ResetController extends Controller {
	/**
	 * Creates a reset operation, the first of two steps needed to reset a
	 * password. The first step consist of sending an email to the user with
	 * instructions to reset he's password, if and only if the email is valid.
	 * @param Request $r
	 * return array
	 */
	public static function apiCreate(Request $r) {
		$email = $r['email'];
		if (is_null($email)) {
			return self::badRequest('Missing parameter.');
		} else {
			// Email should be registered to begin with, right?
			$user = UsersDAO::FindByEmail($email);
			if (is_null($user)) {
				return self::badRequest('Invalid email.');
			}
			// OK, send email with instructions.
			$token = ApiUtils::GetRandomString();
			$reset_digest = hash('sha1', $token);
			$reset_sent_at = ApiUtils::GetStringTime();

			if (IS_TEST) {
				$ret = UsersDAO::UpdateResetInfo(
					$user->user_id,
					$reset_digest,
					$reset_sent_at
				);

				if ($ret) {
					return Array('status' => STATUS_OK, 'token' => $token);
				} else {
					return Array('status' => STATUS_INTERNAL_SERVER_ERROR);
				}
			} else {
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
				$mail->AddAddress($email);
				$mail->isHTML(true);
				$mail->Subject = "Restablecer contraseña en OmegaUp";

				$link = $_SERVER['SERVER_NAME'] . '/reset_password.php?';
				$link .= 'email=' . rawurlencode($email)
					  . '&reset_token=' . $token;
				$mail->Body = "Da clic en el siguiente enlace para restablecer "
							. "tu contraseña: <a href='$link'>$link</a><br><br>"
							. "Si tu no solicitaste esta operación no hagas "
							. "clic y verifica que tu cuenta este a salvo.";
				if (!$mail->Send()) {
					self::$log->error("Failed to send mail:". $mail->ErrorInfo);
					return Array('status' => STATUS_INTERNAL_SERVER_ERROR);
				} else {
					$ret = UsersDAO::UpdateResetInfo(
						$user->user_id,
						$reset_digest,
						$reset_sent_at
					);
					if ($ret) {
						return Array('status' => STATUS_OK);
					} else {
						return Array('status' => STATUS_INTERNAL_SERVER_ERROR);
					}
				}
			}
		}
	}

	/**
	 * Updates the password of a given user, this is the second and last step
	 * in order to reset the password. This operation is done if and only if
	 * the correct parameters are suplied.
	 * @param Request $r
	 * @return array
	 */
	public static function apiUpdate(Request $r) {
		$email = $r['email'];
		$reset_token = $r['reset_token'];
		$password = $r['password'];
		$password_confirmation = $r['password_confirmation'];

		// All parameters MUST be present
		if (is_null($email)
			|| is_null($reset_token)
			|| is_null($password)
			|| is_null($password_confirmation)
		) {
			return self::badRequest('Missing parameters.');
		} else {
			// Verify that the email is valid.
			$user = UsersDAO::FindByEmail($email);
			if (is_null($user)) {
				return self::badRequest('Invalid address.');
			}

			$reset_digest = hash('sha1', $reset_token);
			if ($reset_digest !== $user->reset_digest) {
				return self::badRequest('Invalid parameters.');
			}

			// Reset request expires after 2 hours.
			$seconds = time() - strtotime($user->reset_sent_at);
			if ($seconds > 2 * 3600) {
				return self::badRequest('Token expired.');
			}

			if ($password !== $password_confirmation) {
				return self::badRequest('Password mismatch.');
			}

			if (!SecurityTools::IsValidPassword($password)) {
				return self::badRequest('Invalid password.');
			}

			// OK, let's reset your password.
			if (UsersDAO::ResetPassword($user->user_id, $password)) {
				UsersDAO::UpdateResetInfo($user->user_id, NULL, NULL);
				return Array('status' => STATUS_OK);
			} else {
				return Array('status' => STATUS_INTERNAL_SERVER_ERROR);
			}
		}
	}

	private function badRequest($message) {
		return Array(
			'status' => STATUS_BAD_REQUEST,
			'message' => $message
		);
	}
}
