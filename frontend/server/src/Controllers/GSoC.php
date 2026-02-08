<?php

namespace OmegaUp\Controllers;

/**
 * GSoC Controller
 *
 * @psalm-type GSoCEdition=array{edition_id: int, year: int, is_active: bool, application_deadline: string|null, created_at: string, updated_at: string}
 * @psalm-type GSoCIdea=array{idea_id: int, edition_id: int, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|null, skill_level: string|null, status: string, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}
 * @psalm-type GSoCEditionListPayload=array{editions: list<GSoCEdition>}
 * @psalm-type GSoCIdeaListPayload=array{ideas: list<GSoCIdea>}
 */
class GSoC extends \OmegaUp\Controllers\Controller {
    /**
     * Returns a list of all GSoC editions
     *
     * @return GSoCEditionListPayload
     */
    public static function apiListEditions(\OmegaUp\Request $r): array {
        return [
            'editions' => \OmegaUp\DAO\GSoC::getEditions(),
        ];
    }

    /**
     * Returns a list of GSoC ideas, optionally filtered by edition and status
     *
     * @return GSoCIdeaListPayload
     *
     * @omegaup-request-param int|null $edition_id
     * @omegaup-request-param null|string $status
     */
    public static function apiListIdeas(\OmegaUp\Request $r): array {
        $editionId = null;
        if (!is_null($r['edition_id'])) {
            \OmegaUp\Validators::validateNumber(
                $r['edition_id'],
                'edition_id'
            );
            $editionId = intval($r['edition_id']);
        }

        $status = null;
        if (!is_null($r['status'])) {
            $status = $r->ensureString('status');
            $validStatuses = ['Proposed', 'Accepted', 'Archived'];
            if (!in_array($status, $validStatuses)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'status'
                );
            }
        }

        return [
            'ideas' => \OmegaUp\DAO\GSoC::getIdeas($editionId, $status),
        ];
    }

    /**
     * Creates a new GSoC edition (admin only)
     *
     * @return array{edition_id: int}
     *
     * @omegaup-request-param int $year
     * @omegaup-request-param bool|null $is_active
     * @omegaup-request-param null|string $application_deadline
     */
    public static function apiCreateEdition(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $year = $r->ensureInt('year');
        if ($year < 2005 || $year > 2100) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'year'
            );
        }

        // Check if edition for this year already exists
        $existingEdition = \OmegaUp\DAO\GSoC::getEditionByYear($year);
        if (!is_null($existingEdition)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'editionAlreadyExists'
            );
        }

        $isActive = $r->ensureOptionalBool('is_active') ?? false;
        $applicationDeadline = null;
        if (!is_null($r['application_deadline'])) {
            $applicationDeadline = $r->ensureString('application_deadline');
            // Validate timestamp format
            if (strtotime($applicationDeadline) === false) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'application_deadline'
                );
            }
        }

        $editionId = \OmegaUp\DAO\GSoC::createEdition(
            $year,
            $isActive,
            $applicationDeadline
        );

        return [
            'edition_id' => $editionId,
        ];
    }

    /**
     * Updates a GSoC edition (admin only)
     *
     * @return array{updated: bool}
     *
     * @omegaup-request-param int $edition_id
     * @omegaup-request-param int|null $year
     * @omegaup-request-param bool|null $is_active
     * @omegaup-request-param null|string $application_deadline
     */
    public static function apiUpdateEdition(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $editionId = $r->ensureInt('edition_id');

        // Verify edition exists
        $edition = \OmegaUp\DAO\GSoC::getEditionById($editionId);
        if (is_null($edition)) {
            throw new \OmegaUp\Exceptions\NotFoundException('editionNotFound');
        }

        $year = null;
        if (!is_null($r['year'])) {
            $year = $r->ensureInt('year');
            if ($year < 2005 || $year > 2100) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'year'
                );
            }
            // Check if another edition with this year exists
            $existingEdition = \OmegaUp\DAO\GSoC::getEditionByYear($year);
            if (!is_null($existingEdition) && $existingEdition['edition_id'] != $editionId) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'editionAlreadyExists'
                );
            }
        }

        $isActive = $r->ensureOptionalBool('is_active');
        $applicationDeadline = null;
        if (!is_null($r['application_deadline'])) {
            $applicationDeadline = $r->ensureString('application_deadline');
            if (strtotime($applicationDeadline) === false) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'application_deadline'
                );
            }
        }

        $affectedRows = \OmegaUp\DAO\GSoC::updateEdition(
            $editionId,
            $year,
            $isActive,
            $applicationDeadline
        );

        return [
            'updated' => $affectedRows > 0,
        ];
    }

    /**
     * Creates a new GSoC idea (admin only)
     *
     * @return array{idea_id: int}
     *
     * @omegaup-request-param int $edition_id
     * @omegaup-request-param string $title
     * @omegaup-request-param null|string $brief_description
     * @omegaup-request-param null|string $expected_results
     * @omegaup-request-param null|string $preferred_skills
     * @omegaup-request-param null|string $possible_mentors
     * @omegaup-request-param int|null $estimated_hours
     * @omegaup-request-param null|string $skill_level
     * @omegaup-request-param null|string $status
     * @omegaup-request-param null|string $blog_link
     * @omegaup-request-param null|string $contributor_username
     */
    public static function apiCreateIdea(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $editionId = $r->ensureInt('edition_id');
        // Verify edition exists
        $edition = \OmegaUp\DAO\GSoC::getEditionById($editionId);
        if (is_null($edition)) {
            throw new \OmegaUp\Exceptions\NotFoundException('editionNotFound');
        }

        $title = $r->ensureString('title');
        if (empty(trim($title))) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'title'
            );
        }

        $briefDescription = $r->ensureOptionalString('brief_description');
        $expectedResults = $r->ensureOptionalString('expected_results');
        $preferredSkills = $r->ensureOptionalString('preferred_skills');
        $possibleMentors = $r->ensureOptionalString('possible_mentors');
        $estimatedHours = $r->ensureOptionalInt('estimated_hours');

        $skillLevel = null;
        if (!is_null($r['skill_level'])) {
            $skillLevel = $r->ensureString('skill_level');
            $validSkillLevels = ['Low', 'Medium', 'Advanced'];
            if (!in_array($skillLevel, $validSkillLevels)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'skill_level'
                );
            }
        }

        $status = $r->ensureOptionalString('status') ?? 'Proposed';
        $validStatuses = ['Proposed', 'Accepted', 'Archived'];
        if (!in_array($status, $validStatuses)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'status'
            );
        }

        $blogLink = $r->ensureOptionalString('blog_link');
        if (!is_null($blogLink) && !filter_var($blogLink, FILTER_VALIDATE_URL)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'blog_link'
            );
        }

        $contributorUsername = $r->ensureOptionalString('contributor_username');
        if (!is_null($contributorUsername)) {
            \OmegaUp\Validators::validateValidUsername(
                $contributorUsername,
                'contributor_username'
            );
        }

        $ideaId = \OmegaUp\DAO\GSoC::createIdea(
            $editionId,
            $title,
            $briefDescription,
            $expectedResults,
            $preferredSkills,
            $possibleMentors,
            $estimatedHours,
            $skillLevel,
            $status,
            $blogLink,
            $contributorUsername
        );

        return [
            'idea_id' => $ideaId,
        ];
    }

    /**
     * Updates a GSoC idea (admin only)
     *
     * @return array{updated: bool}
     *
     * @omegaup-request-param int $idea_id
     * @omegaup-request-param int|null $edition_id
     * @omegaup-request-param null|string $title
     * @omegaup-request-param null|string $brief_description
     * @omegaup-request-param null|string $expected_results
     * @omegaup-request-param null|string $preferred_skills
     * @omegaup-request-param null|string $possible_mentors
     * @omegaup-request-param int|null $estimated_hours
     * @omegaup-request-param null|string $skill_level
     * @omegaup-request-param null|string $status
     * @omegaup-request-param null|string $blog_link
     * @omegaup-request-param null|string $contributor_username
     */
    public static function apiUpdateIdea(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $ideaId = $r->ensureInt('idea_id');

        // Verify idea exists
        $idea = \OmegaUp\DAO\GSoC::getIdeaById($ideaId);
        if (is_null($idea)) {
            throw new \OmegaUp\Exceptions\NotFoundException('ideaNotFound');
        }

        $editionId = null;
        if (!is_null($r['edition_id'])) {
            $editionId = $r->ensureInt('edition_id');
            // Verify edition exists
            $edition = \OmegaUp\DAO\GSoC::getEditionById($editionId);
            if (is_null($edition)) {
                throw new \OmegaUp\Exceptions\NotFoundException('editionNotFound');
            }
        }

        $title = null;
        if (!is_null($r['title'])) {
            $title = $r->ensureString('title');
            if (empty(trim($title))) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterEmpty',
                    'title'
                );
            }
        }

        $briefDescription = $r->ensureOptionalString('brief_description');
        $expectedResults = $r->ensureOptionalString('expected_results');
        $preferredSkills = $r->ensureOptionalString('preferred_skills');
        $possibleMentors = $r->ensureOptionalString('possible_mentors');
        $estimatedHours = $r->ensureOptionalInt('estimated_hours');

        $skillLevel = null;
        if (!is_null($r['skill_level'])) {
            $skillLevel = $r->ensureString('skill_level');
            $validSkillLevels = ['Low', 'Medium', 'Advanced'];
            if (!in_array($skillLevel, $validSkillLevels)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'skill_level'
                );
            }
        }

        $status = null;
        if (!is_null($r['status'])) {
            $status = $r->ensureString('status');
            $validStatuses = ['Proposed', 'Accepted', 'Archived'];
            if (!in_array($status, $validStatuses)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'status'
                );
            }
        }

        $blogLink = null;
        if (!is_null($r['blog_link'])) {
            $blogLink = $r->ensureString('blog_link');
            if (!filter_var($blogLink, FILTER_VALIDATE_URL)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'blog_link'
                );
            }
        }

        $contributorUsername = null;
        if (!is_null($r['contributor_username'])) {
            $contributorUsername = $r->ensureString('contributor_username');
            \OmegaUp\Validators::validateValidUsername(
                $contributorUsername,
                'contributor_username'
            );
        }

        $affectedRows = \OmegaUp\DAO\GSoC::updateIdea(
            $ideaId,
            $editionId,
            $title,
            $briefDescription,
            $expectedResults,
            $preferredSkills,
            $possibleMentors,
            $estimatedHours,
            $skillLevel,
            $status,
            $blogLink,
            $contributorUsername
        );

        return [
            'updated' => $affectedRows > 0,
        ];
    }

    /**
     * Deletes a GSoC idea (admin only)
     *
     * @return array{deleted: bool}
     *
     * @omegaup-request-param int $idea_id
     */
    public static function apiDeleteIdea(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $ideaId = $r->ensureInt('idea_id');

        // Verify idea exists
        $idea = \OmegaUp\DAO\GSoC::getIdeaById($ideaId);
        if (is_null($idea)) {
            throw new \OmegaUp\Exceptions\NotFoundException('ideaNotFound');
        }

        $affectedRows = \OmegaUp\DAO\GSoC::deleteIdea($ideaId);

        return [
            'deleted' => $affectedRows > 0,
        ];
    }

    /**
     * Entry point for GSoC ideas page
     *
     * @return array{entrypoint: string, templateProperties: array{payload: array<empty, empty>, title: \OmegaUp\TranslationString}}
     */
    public static function getIdeasForTypeScript(\OmegaUp\Request $r): array {
        return [
            'entrypoint' => 'gsoc_ideas',
            'templateProperties' => [
                'payload' => [],
                'title' => new \OmegaUp\TranslationString('gsocIdeasList'),
            ],
        ];
    }
}
