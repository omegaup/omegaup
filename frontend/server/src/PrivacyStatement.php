<?php

namespace OmegaUp;

/**
 * @author juan.pablo
 */
class PrivacyStatement {
    /**
     * @param null|int $languageId
     * @param 'course'|'contest' $problemsetType
     * @param string $requestsUserInformation
     */
    public static function getForProblemset(
        ?int $languageId,
        string $problemsetType,
        string $requestsUserInformation
    ) : ?string {
        if ($requestsUserInformation == 'no') {
            return null;
        }
        \OmegaUp\Validators::validateInEnum(
            $requestsUserInformation,
            'requestsUserInformation',
            ['required', 'optional']
        );
        $language = self::getLanguage($languageId);

        return file_get_contents(
            OMEGAUP_ROOT .
            "/privacy/{$problemsetType}_{$requestsUserInformation}_consent/{$language}.md"
        );
    }

    /**
     * @param null|int $languageId
     * @param 'accept_teacher'|'privacy_policy' $type
     */
    public static function getForConsent(?int $languageId, string $type) : ?string {
        $language = self::getLanguage($languageId);

        return file_get_contents(
            OMEGAUP_ROOT .
            "/privacy/{$type}/{$language}.md"
        );
    }

    /**
     * @param null|int $languageId
     * @return 'en'|'pt'|'es'
     */
    private static function getLanguage(?int $languageId) : string {
        if ($languageId == \OmegaUp\Controllers\User::LANGUAGE_EN
            || $languageId == \OmegaUp\Controllers\User::LANGUAGE_PSEUDO
        ) {
            return 'en';
        } elseif ($languageId == \OmegaUp\Controllers\User::LANGUAGE_PT) {
            return 'pt';
        }
        return 'es';
    }
}
