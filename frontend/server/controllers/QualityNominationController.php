<?php
require_once 'libs/dao/QualityNominations.dao.php';
require_once 'libs/dao/QualityNomination_Reviewers.dao.php';

class QualityNominationController extends Controller {
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
     * * `reason`: One of `['duplicate', 'no-problem-statement', 'offensive', 'other', 'spam']`.
     * * `original`: If the `reason` is `duplicate`, the alias of the original
     *               problem.
     * # Dismissal
     * A user that has already solved a problem can dismiss suggestions. The
     * `contents` field is empty.
     *
     * @param Request $r
     *
     * @return array
     * @throws DuplicatedEntryInDatabaseException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiCreate(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Validate request
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');
        Validators::isInEnum($r['nomination'], 'nomination', ['suggestion', 'promotion', 'demotion', 'dismissal']);
        Validators::isStringNonEmpty($r['contents'], 'contents');
        $contents = json_decode($r['contents'], true /*assoc*/);
        if (!is_array($contents)) {
            throw new InvalidParameterException('parameterInvalid', 'contents');
        }
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        if ($r['nomination'] != 'demotion') {
            // All nominations types, except demotions, are only allowed for
            // uses who have already solved the problem.
            if (!ProblemsDAO::isProblemSolved($problem, $r['current_user'])) {
                throw new PreconditionFailedException('qualityNominationMustHaveSolvedProblem');
            }
        }
        if ($r['nomination'] == 'suggestion') {
                        $atLeastOneFieldIsPresent = false;
            if (isset($contents['difficulty'])) {
                if (!is_int($contents['difficulty'])) {
                    throw new InvalidParameterException('parameterInvalid', 'contents');
                }
                $atLeastOneFieldIsPresent = true;
            }
            if (isset($contents['tags'])) {
                if (!is_array($contents['tags'])) {
                    throw new InvalidParameterException('parameterInvalid', 'contents');
                }
                $atLeastOneFieldIsPresent = true;
            }
            if (isset($contents['quality'])) {
                if (!is_int($contents['quality'])) {
                    throw new InvalidParameterException('parameterInvalid', 'contents');
                }
                $atLeastOneFieldIsPresent = true;
            }
            if (!$atLeastOneFieldIsPresent) {
                throw new InvalidParameterException('parameterInvalid', 'contents');
            }
            // Tags must be strings.
            foreach ($contents['tags'] as &$tag) {
                if (!is_string($tag)) {
                    throw new InvalidParameterException('parameterInvalid', 'contents');
                }
                $tag = TagController::normalize($tag);
            }
        } elseif ($r['nomination'] == 'promotion') {
            if ((!isset($contents['statements']) || !is_array($contents['statements']))
                || (!isset($contents['source']) || !is_string($contents['source']) || empty($contents['source']))
                || (!isset($contents['tags']) || !is_array($contents['tags']))
            ) {
                throw new InvalidParameterException('parameterInvalid', 'contents');
            }
            // Tags must be strings.
            foreach ($contents['tags'] as &$tag) {
                if (!is_string($tag)) {
                    throw new InvalidParameterException('parameterInvalid', 'contents');
                }
                $tag = TagController::normalize($tag);
            }
            // Statements must be a dictionary of language => { 'markdown': string }.
            foreach ($contents['statements'] as $language => $statement) {
                if (!is_array($statement) || empty($language)
                    || (!isset($statement['markdown']) || !is_string($statement['markdown']) || empty($statement['markdown']))
                ) {
                    throw new InvalidParameterException('parameterInvalid', 'contents');
                }
            }
        } elseif ($r['nomination'] == 'demotion') {
            if (!isset($contents['reason']) || !in_array($contents['reason'], ['duplicate', 'no-problem-statement', 'offensive', 'other', 'spam'])) {
                throw new InvalidParameterException('parameterInvalid', 'contents');
            }
            // Duplicate reports need more validation.
            if ($contents['reason'] == 'duplicate') {
                if (!isset($contents['original']) || !is_string($contents['original']) || empty($contents['original'])) {
                    throw new InvalidParameterException('parameterInvalid', 'contents');
                }
                $original = ProblemsDAO::getByAlias($contents['original']);
                if (is_null($original)) {
                    throw new NotFoundException('problemNotFound');
                }
            }
        } elseif ($r['nomination'] == 'dismissal') {
            if (isset($contents['origin']) || isset($contents['difficulty']) || isset($contents['source'])
                || isset($contents['tags']) || isset($contents['statements']) || isset($statement['markdown'])
                || isset($contents['reason'])
            ) {
                throw new InvalidParameterException('parameterInvalid', 'contents');
            }
        }

        // Create object
        $nomination = new QualityNominations([
            'user_id' => $r['current_user_id'],
            'problem_id' => $problem->problem_id,
            'nomination' => $r['nomination'],
            'contents' => json_encode($contents), // re-encoding it for normalization.
            'status' => 'open',
        ]);
        QualityNominationsDAO::save($nomination);

        $qualityReviewerGroup = GroupsDAO::FindByAlias(
            Authorization::QUALITY_REVIEWER_GROUP_ALIAS
        );
        foreach (GroupsDAO::sampleMembers(
            $qualityReviewerGroup,
            self::REVIEWERS_PER_NOMINATION
        ) as $reviewer) {
            QualityNominationReviewersDAO::save(new QualityNominationReviewers([
                'qualitynomination_id' => $nomination->qualitynomination_id,
                'user_id' => $reviewer->user_id,
            ]));
        }

        return ['status' => 'ok'];
    }

    /**
     * Returns the list of nominations made by $nominator (if non-null),
     * assigned to $assignee (if non-null) or all nominations (if both
     * $nominator and $assignee are null).
     *
     * @param Request $r         The request.
     * @param int     $nominator The user id of the person that made the
     *                           nomination.  May be null.
     * @param int     $assignee  The user id of the person assigned to review
     *                           nominations.  May be null.
     *
     * @return array The response.
     */
    private static function getListImpl(Request $r, $nominator, $assignee) {
        Validators::isNumber($r['page'], 'page', false);
        Validators::isNumber($r['page_size'], 'page_size', false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        $nominations = null;
        try {
            $nominations = QualityNominationsDAO::getNominations(
                $nominator,
                $assignee,
                $page,
                $pageSize
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
            'nominations' => $nominations,
        ];
    }

    /**
     * Validates that the user making the request is member of the
     * `omegaup:quality-reviewer` group.
     *
     * @param Request $r The request.
     *
     * @return void
     * @throws ForbiddenAccessException
     */
    private static function validateMemberOfReviewerGroup(Request $r) {
        if (!Authorization::isQualityReviewer($r['current_user_id'])) {
            throw new ForbiddenAccessException('userNotAllowed');
        }
    }

    /**
     * Displays all the nominations.
     *
     * @param Request $r
     * @return array
     * @throws ForbiddenAccessException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiList(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Validate request
        self::authenticateRequest($r);
        self::validateMemberOfReviewerGroup($r);

        return self::getListImpl($r, null /* nominator */, null /* assignee */);
    }

    /**
     * Displays the nominations that this user has been assigned.
     *
     * @param Request $r
     * @return array
     * @throws ForbiddenAccessException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiMyAssignedList(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Validate request
        self::authenticateRequest($r);
        self::validateMemberOfReviewerGroup($r);

        return self::getListImpl($r, null /* nominator */, $r['current_user_id']);
    }

    /**
     * Displays the nominations that this user has created. The user does
     * not need to be a member of the reviewer group.
     *
     * @param Request $r
     * @return array
     * @throws ForbiddenAccessException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiMyList(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Validate request
        self::authenticateRequest($r);

        return self::getListImpl($r, $r['current_user_id'], null /* assignee */);
    }

    /**
     * Displays the details of a nomination. The user needs to be either the
     * nominator or a member of the reviewer group.
     *
     * @param Request $r
     * @return array
     * @throws ForbiddenAccessException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiDetails(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Validate request
        self::authenticateRequest($r);

        Validators::isNumber($r['qualitynomination_id'], 'qualitynomination_id');
        $response = QualityNominationsDAO::getByID($r['qualitynomination_id']);
        if (is_null($response)) {
            throw new NotFoundException('qualityNominationNotFound');
        }

        // The nominator can see the nomination, as well as all the members of
        // the reviewer group.
        $currentUserIsNominator = ($r['current_user']->username == $response['nominator']['username']);
        $currentUserReviewer = Authorization::isQualityReviewer($r['current_user_id']);
        if (!$currentUserIsNominator && !$currentUserReviewer) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        // Get information from the original problem.
        $problem = ProblemsDAO::getByAlias($response['problem']['alias']);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        $response['original_contents'] = [
            'statements' => [],
            'source' => $problem->source,
            'tags' => ProblemsDAO::getTagsForProblem($problem, false /* public */),
        ];

        // Don't leak private problem tags to nominator
        if (!$currentUserReviewer) {
            unset($response['original_contents']['tags']);
        }

        foreach ($response['contents']['statements'] as $language => $_) {
            // There might be the case that the language is not originally
            // present, in which case it will be changed to Spanish.
            $actualLanguage = $language;
            $markdown = ProblemController::getProblemStatement(
                $problem->alias,
                $actualLanguage,
                'markdown'
            );
            $response['original_contents']['statements'][$language] = [
                'language' => $actualLanguage,
                'markdown' => $markdown,
            ];
        }
        if (empty($response['original_contents']['statements'])) {
            // Force 'statements' to be an object.
            $response['original_contents']['statements'] = (object)[];
        }
        $response['status'] = 'ok';

        return $response;
    }
}
