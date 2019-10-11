<?php

 namespace OmegaUp\Controllers;

class QualityNomination extends \OmegaUp\Controllers\Controller {
    /**
     * Number of reviewers to automatically assign each nomination.
     */
    const REVIEWERS_PER_NOMINATION = 2;

    /**
     * Creates a new QualityNomination
     *
     * There are three ways in which users can interact with this:
     *
     * # Suggestion
     *
     * A user that has already solved a problem can make suggestions about a
     * problem. This expects the `nomination` field to be `suggestion` and the
     * `contents` field should be a JSON blob with at least one the following fields:
     *
     * * `difficulty`: (Optional) A number in the range [0-4] indicating the
     *                 difficulty of the problem.
     * * `quality`: (Optional) A number in the range [0-4] indicating the quality
     *             of the problem.
     * * `tags`: (Optional) An array of tag names that will be added to the
     *           problem upon promotion.
     * * `before_ac`: (Optional) Boolean indicating if the suggestion has been sent
     *                before receiving an AC verdict for problem run.
     *
     * # Promotion
     *
     * A user that has already solved a problem can nominate it to be promoted
     * as a Quality Problem. This expects the `nomination` field to be
     * `promotion` and the `contents` field should be a JSON blob with the
     * following fields:
     *
     * * `statements`: A dictionary of languages to objects that contain a
     *                 `markdown` field, which is the markdown-formatted
     *                 problem statement for that language.
     * * `source`: A URL or string clearly documenting the source or full name
     *             of original author of the problem.
     * * `tags`: An array of tag names that will be added to the problem upon
     *           promotion.
     *
     * # Demotion
     *
     * A demoted problem is banned, and cannot be un-banned or added to any new
     * problemsets. This expects the `nomination` field to be `demotion` and
     * the `contents` field should be a JSON blob with the following fields:
     *
     * * `rationale`: A small text explaining the rationale for demotion.
     * * `reason`: One of `['duplicate', 'no-problem-statement', 'offensive', 'other', 'spam']`.
     * * `original`: If the `reason` is `duplicate`, the alias of the original
     *               problem.
     * # Dismissal
     * A user that has already solved a problem can dismiss suggestions. The
     * `contents` field is empty.
     *
     * @param \OmegaUp\Request $r
     *
     * @return array
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Validate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');
        \OmegaUp\Validators::validateInEnum($r['nomination'], 'nomination', ['suggestion', 'promotion', 'demotion', 'dismissal']);
        \OmegaUp\Validators::validateStringNonEmpty($r['contents'], 'contents');
        /**
         * @var null|array{tags?: mixed, before_ac?: mixed, difficulty?: mixed, quality?: mixed, statements?: mixed, source?: mixed, reason?: mixed, original?: mixed} $contents
         */
        $contents = json_decode($r['contents'], true /*assoc*/);
        if (!is_array($contents)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
        }
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if ($r['nomination'] !== 'demotion') {
            if (isset($contents['before_ac']) &&
                boolval($contents['before_ac']) &&
                ($r['nomination'] === 'dismissal' ||
                 $r['nomination'] === 'suggestion')
            ) {
                // Before AC suggestions or dismissals are only allowed
                // for users who didn't solve a problem, but tried to.
                if (\OmegaUp\DAO\Problems::isProblemSolved(
                    $problem,
                    intval($r->identity->identity_id)
                )) {
                    throw new \OmegaUp\Exceptions\PreconditionFailedException(
                        'qualityNominationMustNotHaveSolvedProblem'
                    );
                }

                if (!\OmegaUp\DAO\Problems::hasTriedToSolveProblem(
                    $problem,
                    intval($r->identity->identity_id)
                )) {
                    throw new \OmegaUp\Exceptions\PreconditionFailedException(
                        'qualityNominationMustHaveTriedToSolveProblem'
                    );
                }
            } else {
                // All nominations types, except demotions and before AC
                // suggestions/demotions,are only allowed for users who
                // have already solved the problem.
                if (!\OmegaUp\DAO\Problems::isProblemSolved($problem, intval($r->identity->identity_id))) {
                    throw new \OmegaUp\Exceptions\PreconditionFailedException('qualityNominationMustHaveSolvedProblem');
                }
            }
        }

        if ($r['nomination'] === 'suggestion') {
            $atLeastOneFieldIsPresent = false;
            if (isset($contents['difficulty'])) {
                if (!is_int($contents['difficulty']) || $contents['difficulty'] < 0 || $contents['difficulty'] > 4) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
                }
                $atLeastOneFieldIsPresent = true;
            }
            if (isset($contents['tags'])) {
                if (!is_array($contents['tags'])) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
                }
                if (!empty($contents['tags'])) {
                    $atLeastOneFieldIsPresent = true;
                }
            }
            if (isset($contents['quality'])) {
                if (!is_int($contents['quality']) || $contents['quality'] < 0 || $contents['quality'] > 4) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
                }
                $atLeastOneFieldIsPresent = true;
            }
            if (!$atLeastOneFieldIsPresent) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
            }
            // Tags must be strings.
            if (isset($contents['tags'])) {
                /** @var mixed $tag */
                foreach ($contents['tags'] as &$tag) {
                    if (!is_string($tag)) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
                    }
                    $tag = \OmegaUp\Controllers\Tag::normalize($tag);
                }
                if (self::hasDuplicates($contents['tags'])) {
                    throw new \OmegaUp\Exceptions\DuplicatedEntryInArrayException('duplicateTagsNotAllowed');
                }
            }
        } elseif ($r['nomination'] === 'promotion') {
            if ((!isset($contents['statements']) || !is_array($contents['statements']))
                || (!isset($contents['source']) || !is_string($contents['source']) || empty($contents['source']))
                || (!isset($contents['tags']) || !is_array($contents['tags']))
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
            }
            // Tags must be strings.
            /** @var mixed $tag */
            foreach ($contents['tags'] as &$tag) {
                if (!is_string($tag)) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
                }
                $tag = \OmegaUp\Controllers\Tag::normalize($tag);
            }
            if (self::hasDuplicates($contents['tags'])) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInArrayException('duplicateTagsNotAllowed');
            }

            /**
             * Statements must be a dictionary of language => { 'markdown': string }.
             * @var string $language
             * @var mixed $statement
             */
            foreach ($contents['statements'] as $language => $statement) {
                if (!is_array($statement) || empty($language)
                    || (!isset($statement['markdown']) || !is_string($statement['markdown']) || empty($statement['markdown']))
                ) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
                }
            }
        } elseif ($r['nomination'] === 'demotion') {
            if (!isset($contents['reason']) || !in_array($contents['reason'], ['duplicate', 'no-problem-statement', 'offensive', 'other', 'spam', 'wrong-test-cases'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
            }
            if ($contents['reason'] === 'other' && !isset($contents['rationale'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
            }
            // Duplicate reports need more validation.
            if ($contents['reason'] === 'duplicate') {
                if (!isset($contents['original']) || !is_string($contents['original']) || empty($contents['original'])) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
                }
                $original = \OmegaUp\DAO\Problems::getByAlias($contents['original']);
                if (is_null($original)) {
                    $contents['original'] = self::extractAliasFromArgument($contents['original']);
                    if (is_null($contents['original'])) {
                        throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
                    }
                    $original = \OmegaUp\DAO\Problems::getByAlias($contents['original']);
                    if (is_null($original)) {
                        throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
                    }
                }
            }
        } elseif ($r['nomination'] === 'dismissal') {
            if (isset($contents['origin']) || isset($contents['difficulty']) || isset($contents['source'])
                || isset($contents['tags']) || isset($contents['statements']) || isset($contents['reason'])
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'contents');
            }
        }

        // Create object
        $nomination = new \OmegaUp\DAO\VO\QualityNominations([
            'user_id' => $r->user->user_id,
            'problem_id' => $problem->problem_id,
            'nomination' => $r['nomination'],
            'contents' => json_encode($contents), // re-encoding it for normalization.
            'status' => 'open',
        ]);
        \OmegaUp\DAO\QualityNominations::create($nomination);

        if ($nomination->nomination == 'promotion') {
            $qualityReviewerGroup = \OmegaUp\DAO\Groups::findByAlias(
                \OmegaUp\Authorization::QUALITY_REVIEWER_GROUP_ALIAS
            );
            if (is_null($qualityReviewerGroup)) {
                throw new \OmegaUp\Exceptions\NotFoundException('groupNotFound');
            }
            foreach (\OmegaUp\DAO\Groups::sampleMembers(
                $qualityReviewerGroup,
                self::REVIEWERS_PER_NOMINATION
            ) as $reviewer) {
                \OmegaUp\DAO\QualityNominationReviewers::create(new \OmegaUp\DAO\VO\QualityNominationReviewers([
                    'qualitynomination_id' => $nomination->qualitynomination_id,
                    'user_id' => $reviewer->user_id,
                ]));
            }
        }

        return [
            'status' => 'ok',
            'qualitynomination_id' => $nomination->qualitynomination_id
        ];
    }

    /**
     * Marks a nomination (only the demotion type supported for now) as resolved (approved or denied).
     *
     * @param \OmegaUp\Request $r         The request.
     *
     * @return array The response.
     */
    public static function apiResolve(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        \OmegaUp\Validators::validateInEnum($r['status'], 'status', ['open', 'approved', 'denied'], true /*is_required*/);
        \OmegaUp\Validators::validateStringNonEmpty($r['rationale'], 'rationale');

        // Validate request
        $r->ensureIdentity();
        self::validateMemberOfReviewerGroup($r);

        $qualitynomination = \OmegaUp\DAO\QualityNominations::getByPK($r['qualitynomination_id']);
        if (is_null($qualitynomination)) {
            throw new \OmegaUp\Exceptions\NotFoundException('qualitynominationNotFound');
        }
        if ($qualitynomination->nomination != 'demotion') {
            throw new \OmegaUp\Exceptions\InvalidParameterException('onlyDemotionsSupported');
        }
        if ($r['status'] == $qualitynomination->status) {
            return ['status' => 'ok'];
        }

        $r['problem'] = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($r['problem'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $newProblemVisibility = $r['problem']->visibility;
        switch ($r['status']) {
            case 'approved':
                if ($r['problem']->visibility == \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE) {
                    $newProblemVisibility = \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE_BANNED;
                } elseif ($r['problem']->visibility == \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC) {
                    $newProblemVisibility = \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED;
                }
                break;
            case 'denied':
                if ($r['problem']->visibility == \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE_BANNED) {
                    // If banning is reverted, problem will become private.
                    $newProblemVisibility = \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE;
                } elseif ($r['problem']->visibility == \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED) {
                    // If banning is reverted, problem will become public.
                    $newProblemVisibility = \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC;
                }
                break;
            case 'open':
                // No-op.
                break;
        }

        $r['message'] = ($r['status'] == 'approved') ? 'banningProblemDueToReport' : 'banningDeclinedByReviewer';

        $r['visibility'] = $newProblemVisibility;

        $qualitynominationlog = new \OmegaUp\DAO\VO\QualityNominationLog([
            'user_id' => $r->user->user_id,
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'from_status' => $qualitynomination->status,
            'to_status' => $r['status'],
            'rationale' => $r['rationale']
        ]);
        $qualitynomination->status = $r['status'];

        \OmegaUp\DAO\DAO::transBegin();
        try {
            $response = [];
            \OmegaUp\Controllers\Problem::apiUpdate($r);
            \OmegaUp\DAO\QualityNominations::update($qualitynomination);
            \OmegaUp\DAO\QualityNominationLog::create($qualitynominationlog);
            \OmegaUp\DAO\DAO::transEnd();
            if ($newProblemVisibility == \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED  ||
              $newProblemVisibility == \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE_BANNED) {
                $response = self::sendDemotionEmail($r, $qualitynomination, $qualitynominationlog->rationale);
            }
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            self::$log->error('Failed to resolve demotion request', $e);
            throw $e;
        }

        return ['status' => 'ok'];
    }

    public static function extractAliasFromArgument(string $problemUrl): ?string {
        $aliasRegex = '/.*[#\/]problem[s]?[#\/]([a-zA-Z0-9-_]+)[\/#$]*/';
        preg_match($aliasRegex, $problemUrl, $matches);
        if (sizeof($matches) < 2) {
            return null;
        }
        return $matches[1];
    }

    /**
     * Send a mail with demotion notification to the original creator
     */
    private static function sendDemotionEmail(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\QualityNominations $qualitynomination,
        string $rationale
    ) : void {
        /** @var \OmegaUp\DAO\VO\Problems */
        $problem = $r['problem'];
        $adminUser = \OmegaUp\DAO\Problems::getAdminUser($problem);
        if (is_null($adminUser)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
        }
        [
            'email' => $email,
            'name' => $username,
        ] = $adminUser;

        $emailParams = [
            'reason' => htmlspecialchars($rationale),
            'problem_name' => htmlspecialchars(strval($problem->title)),
            'user_name' => $username,
        ];
        $subject = \OmegaUp\ApiUtils::formatString(
            \OmegaUp\Translations::getInstance()->get('demotionProblemEmailSubject')
                ?: 'demotionProblemEmailSubject',
            $emailParams
        );
        $body = \OmegaUp\ApiUtils::formatString(
            \OmegaUp\Translations::getInstance()->get('demotionProblemEmailBody')
                ?: 'demotionProblemEmailBody',
            $emailParams
        );

        \OmegaUp\Email::sendEmail([$email], $subject, $body);
    }

    /**
     * Returns the list of nominations made by $nominator (if non-null),
     * assigned to $assignee (if non-null) or all nominations (if both
     * $nominator and $assignee are null).
     *
     * @param \OmegaUp\Request $r         The request.
     * @param int     $nominator The user id of the person that made the
     *                           nomination.  May be null.
     * @param int     $assignee  The user id of the person assigned to review
     *                           nominations.  May be null.
     *
     * @return array The response.
     */
    private static function getListImpl(\OmegaUp\Request $r, $nominator, $assignee) {
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);
        $types = (isset($r['types']) ? $r['types'] : ['promotion', 'demotion']);

        return [
            'status' => 'ok',
            'nominations' => \OmegaUp\DAO\QualityNominations::getNominations(
                $nominator,
                $assignee,
                $page,
                $pageSize,
                $types
            ),
        ];
    }

    /**
     * Validates that the user making the request is member of the
     * `omegaup:quality-reviewer` group.
     *
     * @param \OmegaUp\Request $r The request.
     *
     * @return void
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateMemberOfReviewerGroup(\OmegaUp\Request $r) {
        if (!\OmegaUp\Authorization::isQualityReviewer($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
        }
    }

    /**
     * Checks if the given array has duplicate entries.
     *
     * @param $contents array
     * @return boolean
     */
    private static function hasDuplicates($contents) {
        return count($contents) !== count(array_unique($contents));
    }

    /**
     * Displays all the nominations.
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiList(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Validate request
        $r->ensureIdentity();
        self::validateMemberOfReviewerGroup($r);

        return self::getListImpl($r, null /* nominator */, null /* assignee */);
    }

    /**
     * Displays the nominations that this user has been assigned.
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiMyAssignedList(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Validate request
        $r->ensureIdentity();
        self::validateMemberOfReviewerGroup($r);

        return self::getListImpl($r, null /* nominator */, $r->user->user_id);
    }

    /**
     * Displays the nominations that this user has created. The user does
     * not need to be a member of the reviewer group.
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Validate request
        $r->ensureMainUserIdentity();

        return self::getListImpl($r, $r->user->user_id, null /* assignee */);
    }

    /**
     * Displays the details of a nomination. The user needs to be either the
     * nominator or a member of the reviewer group.
     *
     * @param \OmegaUp\Request $r
     * @return array{qualitynomination_id: int, nomination: string, contents: mixed, time: int, status: string, nominator: array{username: string, name: string}, author: array{username: string, name: string}, problem: array{alias: string, title: string}, votes: array{time: int, vote: int, user: array{username: string, name: string}}[], reviewer: bool, original_contents?: array{statements: array<string, array{language: string, markdown: string, images: array<string, string>}>|object, source: string|null, tags?: array{name: string, autogenerated: bool}[]}, nomination_status: string}
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Validate request
        $r->ensureIdentity();

        $r->ensureInt('qualitynomination_id');
        $response = \OmegaUp\DAO\QualityNominations::getByID(intval($r['qualitynomination_id']));
        if (is_null($response)) {
            throw new \OmegaUp\Exceptions\NotFoundException('qualityNominationNotFound');
        }
        if (is_null($r->user)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        // The nominator can see the nomination, as well as all the members of
        // the reviewer group.
        $currentUserIsNominator = ($r->identity->username == $response['nominator']['username']);
        $currentUserReviewer = \OmegaUp\Authorization::isQualityReviewer($r->identity);
        if (!$currentUserIsNominator && !$currentUserReviewer) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
        }

        // Get information from the original problem.
        $problem = \OmegaUp\DAO\Problems::getByAlias($response['problem']['alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Adding in the response object a flag to know whether the user is a reviewer
        $response['reviewer'] = $currentUserReviewer;

        if ($response['nomination'] == 'promotion') {
            $response['original_contents'] = [
                'statements' => [],
                'source' => $problem->source,
            ];

            // Don't leak private problem tags to nominator
            if ($currentUserReviewer) {
                $response['original_contents']['tags'] = \OmegaUp\DAO\Problems::getTagsForProblem($problem, false /* public */);
            }

            /**
             * Pull original problem statements in every language the nominator is trying to override.
             * @var string $language
             * @var string $_
            */
            foreach ($response['contents']['statements'] as $language => $_) {
                $response['original_contents']['statements'][$language] = \OmegaUp\Controllers\Problem::getProblemStatement(
                    $problem,
                    'published',
                    $language
                );
            }
            if (empty($response['original_contents']['statements'])) {
                // Force 'statements' to be an object.
                $response['original_contents']['statements'] = (object)[];
            }
        }
        $response['nomination_status'] = $response['status'];
        $response['status'] = 'ok';

        return $response;
    }
}
