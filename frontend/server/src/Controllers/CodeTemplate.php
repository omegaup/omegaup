<?php

namespace OmegaUp\Controllers;

/**
 * CodeTemplateController
 *
 * @psalm-type CodeTemplate=array{template_id: int, language: string, template_name: string, code: string, created_at: \OmegaUp\Timestamp, updated_at: \OmegaUp\Timestamp}
 */
class CodeTemplate extends \OmegaUp\Controllers\Controller {
    /**
     * List all templates for current user
     *
     * @omegaup-request-param null|string $language
     *
     * @return array{templates: list<CodeTemplate>}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureIdentity();

        $language = $r->ensureOptionalString(
            'language',
            required: false,
            validator: fn (string $language) => array_key_exists(
                $language,
                \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES()
            )
        );

        $templates = [];
        if (!is_null($language)) {
            $templateVOs = \OmegaUp\DAO\UserCodeTemplates::getByUserIdAndLanguage(
                $r->user->user_id,
                $language
            );
        } else {
            $templateVOs = \OmegaUp\DAO\UserCodeTemplates::getByUserId(
                $r->user->user_id
            );
        }

        foreach ($templateVOs as $template) {
            $templates[] = [
                'template_id' => intval($template->template_id),
                'language' => strval($template->language),
                'template_name' => strval($template->template_name),
                'code' => strval($template->code),
                'created_at' => $template->created_at,
                'updated_at' => $template->updated_at,
            ];
        }

        return [
            'templates' => $templates,
        ];
    }

    /**
     * Create or update a code template
     *
     * @omegaup-request-param string $language
     * @omegaup-request-param string $template_name
     * @omegaup-request-param string $code
     *
     * @return array{status: string}
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureIdentity();

        $language = $r->ensureString(
            'language',
            fn (string $language) => array_key_exists(
                $language,
                \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES()
            )
        );
        $templateName = $r->ensureString(
            'template_name',
            fn (string $name) => (
                \OmegaUp\Validators::stringOfLengthInRange(
                    $name,
                    1,
                    100
                )
            )
        );
        $code = $r->ensureString(
            'code',
            fn (string $code) => !empty(trim($code))
        );

        $existingTemplate = \OmegaUp\DAO\UserCodeTemplates::getByUserLanguageAndName(
            $r->user->user_id,
            $language,
            $templateName
        );

        if (!is_null($existingTemplate)) {
            // Update existing template
            $existingTemplate->code = $code;
            \OmegaUp\DAO\UserCodeTemplates::update($existingTemplate);
        } else {
            // Create new template
            $template = new \OmegaUp\DAO\VO\UserCodeTemplates([
                'user_id' => $r->user->user_id,
                'language' => $language,
                'template_name' => $templateName,
                'code' => $code,
            ]);
            \OmegaUp\DAO\UserCodeTemplates::create($template);
        }

        return ['status' => 'ok'];
    }

    /**
     * Update a code template name
     *
     * @omegaup-request-param int $template_id
     * @omegaup-request-param string $new_name
     *
     * @return array{status: string}
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureIdentity();

        $templateId = $r->ensureInt('template_id');
        $newName = $r->ensureString(
            'new_name',
            fn (string $name) => (
                \OmegaUp\Validators::stringOfLengthInRange(
                    $name,
                    1,
                    100
                )
            )
        );

        $template = \OmegaUp\DAO\UserCodeTemplates::getByPK($templateId);
        if (is_null($template)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'templateNotFound'
            );
        }

        if ($template->user_id !== $r->user->user_id) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $existing = \OmegaUp\DAO\UserCodeTemplates::getByUserLanguageAndName(
            $r->user->user_id,
            strval($template->language),
            $newName
        );
        if (!is_null($existing) && $existing->template_id !== $templateId) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'templateNameAlreadyExists'
            );
        }

        $template->template_name = $newName;
        \OmegaUp\DAO\UserCodeTemplates::update($template);

        return ['status' => 'ok'];
    }

    /**
     * Delete a code template
     *
     * @omegaup-request-param int $template_id
     *
     * @return array{status: string}
     */
    public static function apiDelete(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureIdentity();

        $templateId = $r->ensureInt('template_id');

        $template = \OmegaUp\DAO\UserCodeTemplates::getByPK($templateId);
        if (is_null($template)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'templateNotFound'
            );
        }

        if ($template->user_id !== $r->user->user_id) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\DAO\UserCodeTemplates::delete($template);

        return ['status' => 'ok'];
    }
}
