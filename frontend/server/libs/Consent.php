<?php

/**
 *
 * @author juan.pablo
 */
class PrivacyStatement {
    public static function getForProblemset($language_id, $problemset_type, $requests_user_information) {
        if ($requests_user_information == 'no') {
            return null;
        }
        Validators::isInEnum(
            $requests_user_information,
            'requests_user_information',
            ['required', 'optional']
        );
        $language = 'en';
        if ($language_id == UserController::LANGUAGE_ES) {
            $language = 'es';
        } elseif ($language_id == UserController::LANGUAGE_PT) {
            $language = 'pt';
        }

        return file_get_contents(
            OMEGAUP_ROOT .
            "/privacy/{$problemset_type}_{$requests_user_information}_consent/{$language}.md"
        );
    }
}
