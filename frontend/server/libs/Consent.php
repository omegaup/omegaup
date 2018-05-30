<?php

/**
 *
 * @author juan.pablo
 */
class Consent {
    public static function get($language_id, $problemset_type, $requests_user_information) {
        $language = 'en';
        if ($language_id == UserController::LANGUAGE_ES) {
            $language = 'es';
        } elseif ($language_id == UserController::LANGUAGE_PT) {
            $language = 'pt';
        }

        $request_info = $requests_user_information == 'no' ? null : $requests_user_information;

        if (is_null($request_info)) {
            return null;
        }
        $git = new Git(OMEGAUP_ROOT);
        $privacy_consent_path = "frontend/privacy/{$problemset_type}_{$request_info}_consent/";
        $privacy_consent_file = "{$privacy_consent_path}{$language}.md";
        $git_object_id = $git->get(['rev-parse', 'HEAD:' . $privacy_consent_path]);

        return file_get_contents(OMEGAUP_ROOT . '/../' . $privacy_consent_file);
    }
}
