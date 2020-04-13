<?php

 namespace OmegaUp\Controllers;

class QualityNomination extends \OmegaUp\Controllers\Controller {
    /**
     * Number of reviewers to automatically assign each nomination.
     */
    const REVIEWERS_PER_NOMINATION = 2;

    // Number of rows shown in nominations list
    const PAGE_SIZE = 100;

    const ALLOWED_TAGS = [
        'problemTopic2Sat',
        'problemTopicArrays',
        'problemTopicBacktracking',
        'problemTopicBigNumbers',
        'problemTopicBinarySearch',
        'problemTopicBitmasks',
        'problemTopicBreadthDepthFirstSearch',
        'problemTopicBruteForce',
        'problemTopicBuckets',
        'problemTopicCombinatorics',
        'problemTopicDataStructures',
        'problemTopicDisjointSets',
        'problemTopicDivideAndConquer',
        'problemTopicDynamicProgramming',
        'problemTopicFastFourierTransform',
        'problemTopicGameTheory',
        'problemTopicGeometry',
        'problemTopicGraphTheory',
        'problemTopicGreedy',
        'problemTopicHashing',
        'problemTopicIfElseSwitch',
        'problemTopicImplementation',
        'problemTopicInputOutput',
        'problemTopicLoops',
        'problemTopicMath',
        'problemTopicMatrices',
        'problemTopicMaxFlow',
        'problemTopicMeetInTheMiddle',
        'problemTopicNumberTheory',
        'problemTopicParsing',
        'problemTopicProbability',
        'problemTopicShortestPath',
        'problemTopicSimulation',
        'problemTopicSorting',
        'problemTopicStackQueue',
        'problemTopicStrings',
        'problemTopicSuffixArray',
        'problemTopicSuffixTree',
        'problemTopicTernarySearch',
        'problemTopicTrees',
        'problemTopicTwoPointers',
    ];

    const CATEGORY_TAGS = [
        'problemCategoryOpenResponse',
        'problemCategoryKarelEducation',
        'problemCategoryIntroductionToProgramming',
        'problemCategoryMathematicalProblems',
        'problemCategoryElementaryDataStructures',
        'problemCategoryAlgorithmAndNetworkOptimization',
        'problemCategoryCompetitiveProgramming',
        'problemCategorySpecializedTopics',
    ];

    /**
     * @param array{tags?: mixed, before_ac?: mixed, difficulty?: mixed, quality?: mixed, statements?: mixed, source?: mixed, reason?: mixed, original?: mixed, tag?: mixed, quality_seal?: bool} $contents
     * @return \OmegaUp\DAO\VO\QualityNominations
     */
    public static function createNomination(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity,
        string $nominationType,
        array $contents
    ): \OmegaUp\DAO\VO\QualityNominations {
        if ($nominationType !== 'demotion' && $nominationType !== 'quality_tag') {
            if (
                isset($contents['before_ac']) &&
                boolval($contents['before_ac']) &&
                ($nominationType === 'dismissal' ||
                 $nominationType === 'suggestion')
            ) {
                // Before AC suggestions or dismissals are only allowed
                // for users who didn't solve a problem, but tried to.
                if (
                    \OmegaUp\DAO\Problems::isProblemSolved(
                        $problem,
                        intval($identity->identity_id)
                    )
                ) {
                    throw new \OmegaUp\Exceptions\PreconditionFailedException(
                        'qualityNominationMustNotHaveSolvedProblem'
                    );
                }

                if (
                    !\OmegaUp\DAO\Problems::hasTriedToSolveProblem(
                        $problem,
                        intval($identity->identity_id)
                    )
                ) {
                    throw new \OmegaUp\Exceptions\PreconditionFailedException(
                        'qualityNominationMustHaveTriedToSolveProblem'
                    );
                }
            } else {
                // All nominations types, except demotions and before AC
                // suggestions/demotions, are only allowed for users who
                // have already solved the problem.
                if (
                    !\OmegaUp\DAO\Problems::isProblemSolved(
                        $problem,
                        intval($identity->identity_id)
                    )
                ) {
                    throw new \OmegaUp\Exceptions\PreconditionFailedException(
                        'qualityNominationMustHaveSolvedProblem'
                    );
                }
            }
        }

        if ($nominationType === 'suggestion') {
            $atLeastOneFieldIsPresent = false;
            if (isset($contents['difficulty'])) {
                if (
                    !is_int($contents['difficulty']) ||
                    $contents['difficulty'] < 0 ||
                    $contents['difficulty'] > 4
                ) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'contents'
                    );
                }
                $atLeastOneFieldIsPresent = true;
            }
            if (isset($contents['tags'])) {
                if (!is_array($contents['tags'])) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'contents'
                    );
                }
                if (!empty($contents['tags'])) {
                    $atLeastOneFieldIsPresent = true;
                }
            }
            if (isset($contents['quality'])) {
                if (
                    !is_int($contents['quality']) ||
                    $contents['quality'] < 0 ||
                    $contents['quality'] > 4
                ) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'contents'
                    );
                }
                $atLeastOneFieldIsPresent = true;
            }
            if (!$atLeastOneFieldIsPresent) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'contents'
                );
            }
            // Tags must be strings.
            if (isset($contents['tags']) && is_array($contents['tags'])) {
                /** @var mixed $tag */
                foreach ($contents['tags'] as &$tag) {
                    if (
                        !is_string($tag) ||
                        !in_array($tag, self::ALLOWED_TAGS)
                    ) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'parameterInvalid',
                            'contents'
                        );
                    }
                }
                if (self::hasDuplicates($contents['tags'])) {
                    throw new \OmegaUp\Exceptions\DuplicatedEntryInArrayException(
                        'duplicateTagsNotAllowed'
                    );
                }
            }
        } elseif ($nominationType === 'promotion') {
            if (
                (!isset($contents['statements'])
                || !is_array($contents['statements']))
                || (!isset($contents['source'])
                || !is_string($contents['source'])
                || empty($contents['source']))
                || (!isset($contents['tags'])
                || !is_array($contents['tags']))
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'contents'
                );
            }
            // Tags must be strings.
            /** @var mixed $tag */
            foreach ($contents['tags'] as &$tag) {
                if (
                    !is_string($tag) ||
                    !in_array($tag, self::ALLOWED_TAGS)
                ) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'contents'
                    );
                }
            }
            if (self::hasDuplicates($contents['tags'])) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInArrayException(
                    'duplicateTagsNotAllowed'
                );
            }

            /**
             * Statements must be a dictionary of language => { 'markdown': string }.
             * @var string $language
             * @var mixed $statement
             */
            foreach ($contents['statements'] as $language => $statement) {
                if (
                    !is_array($statement) ||
                    empty($language) ||
                    !isset($statement['markdown']) ||
                    !is_string($statement['markdown']) ||
                    empty($statement['markdown'])
                ) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'contents'
                    );
                }
            }
        } elseif ($nominationType === 'demotion') {
            if (
                !isset($contents['reason']) ||
                !in_array(
                    $contents['reason'],
                    ['duplicate', 'no-problem-statement', 'offensive', 'other', 'spam', 'wrong-test-cases', 'poorly-described']
                )
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'contents'
                );
            }
            if (
                $contents['reason'] === 'other' &&
                !isset($contents['rationale'])
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'contents'
                );
            }
            // Duplicate reports need more validation.
            if ($contents['reason'] === 'duplicate') {
                if (
                    !isset($contents['original']) ||
                    !is_string($contents['original']) ||
                    empty($contents['original'])
                ) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'contents'
                    );
                }
                $original = \OmegaUp\DAO\Problems::getByAlias(
                    $contents['original']
                );
                if (is_null($original)) {
                    $contents['original'] = self::extractAliasFromArgument(
                        $contents['original']
                    );
                    if (is_null($contents['original'])) {
                        throw new \OmegaUp\Exceptions\NotFoundException(
                            'problemNotFound'
                        );
                    }
                    $original = \OmegaUp\DAO\Problems::getByAlias(
                        $contents['original']
                    );
                    if (is_null($original)) {
                        throw new \OmegaUp\Exceptions\NotFoundException(
                            'problemNotFound'
                        );
                    }
                }
            }
        } elseif ($nominationType === 'dismissal') {
            if (
                isset($contents['origin'])
                || isset($contents['difficulty'])
                || isset($contents['source'])
                || isset($contents['tags'])
                || isset($contents['statements'])
                || isset($contents['reason'])
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'contents'
                );
            }
        } elseif ($nominationType === 'quality_tag') {
            // Only reviewers are allowed to send this type of nominations
            if (!\OmegaUp\Authorization::isQualityReviewer($identity)) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'userNotAllowed'
                );
            }

            if (
                !isset($contents['quality_seal']) ||
                (
                    $contents['quality_seal'] &&
                    !isset($contents['tag'])
                )
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'contents'
                );
            }

            if (
                isset($contents['tag']) &&
                !in_array($contents['tag'], self::CATEGORY_TAGS)
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'contents'
                );
            }

            if (
                \OmegaUp\DAO\QualityNominations::reviewerHasQualityTagNominatedProblem(
                    $identity,
                    $problem
                )
            ) {
                throw new \OmegaUp\Exceptions\PreconditionFailedException(
                    'reviewerHasAlreadySentNominationForProblem'
                );
            }
        }

        $nomination = new \OmegaUp\DAO\VO\QualityNominations([
            'user_id' => $identity->user_id,
            'problem_id' => $problem->problem_id,
            'nomination' => $nominationType,
            'contents' => json_encode($contents),
            'status' => 'open',
        ]);
        \OmegaUp\DAO\QualityNominations::create($nomination);

        if ($nomination->nomination == 'promotion') {
            $qualityReviewerGroup = \OmegaUp\DAO\Groups::findByAlias(
                \OmegaUp\Authorization::QUALITY_REVIEWER_GROUP_ALIAS
            );
            if (is_null($qualityReviewerGroup)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'groupNotFound'
                );
            }
            foreach (
                \OmegaUp\DAO\Groups::sampleMembers(
                    $qualityReviewerGroup,
                    self::REVIEWERS_PER_NOMINATION
                ) as $reviewer
            ) {
                \OmegaUp\DAO\QualityNominationReviewers::create(new \OmegaUp\DAO\VO\QualityNominationReviewers([
                    'qualitynomination_id' => $nomination->qualitynomination_id,
                    'user_id' => $reviewer->user_id,
                ]));
            }
        }
        return $nomination;
    }

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
     * # Quality tag
     *
     * A reviewer could send this type of nomination to make the user marked as
     * a quality problem or not. The reviewer could also specify which category
     * is the one the problem belongs to. The 'contents' field should have the
     * following subfields:
     *
     * * tag: The name of the tag corresponding to the category of the problem
     * * quality_seal: A boolean that if activated, means that the problem is a
     *   quality problem
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
     * @omegaup-request-param mixed $contents
     * @omegaup-request-param mixed $nomination
     * @omegaup-request-param mixed $problem_alias
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{qualitynomination_id: int}
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Validate request
        $r->ensureMainUserIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateInEnum(
            $r['nomination'],
            'nomination',
            ['suggestion', 'promotion', 'demotion', 'dismissal', 'quality_tag']
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['contents'], 'contents');
        /**
         * @var null|array{tags?: mixed, before_ac?: mixed, difficulty?: mixed, quality?: mixed, statements?: mixed, source?: mixed, reason?: mixed, original?: mixed} $contents
         */
        $contents = json_decode($r['contents'], true /*assoc*/);
        if (!is_array($contents)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'contents'
            );
        }
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $nomination = \OmegaUp\Controllers\QualityNomination::createNomination(
            $problem,
            $r->identity,
            strval($r['nomination']),
            $contents
        );

        return [
            'qualitynomination_id' => intval($nomination->qualitynomination_id)
        ];
    }

    /**
     * Marks a nomination (only the demotion type supported for now) as resolved (approved or denied).
     *
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $qualitynomination_id
     * @omegaup-request-param mixed $rationale
     * @omegaup-request-param mixed $status
     *
     * @return array{status: string}
     */
    public static function apiResolve(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        \OmegaUp\Validators::validateInEnum(
            $r['status'],
            'status',
            ['open', 'approved', 'denied']
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['rationale'],
            'rationale'
        );

        // Validate request
        $r->ensureMainUserIdentity();
        self::validateMemberOfReviewerGroup($r);

        \OmegaUp\Validators::validateNumber(
            $r['qualitynomination_id'],
            'qualitynomination_id'
        );
        $qualitynomination = \OmegaUp\DAO\QualityNominations::getByPK(
            intval($r['qualitynomination_id'])
        );
        if (is_null($qualitynomination)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'qualitynominationNotFound'
            );
        }
        if ($qualitynomination->nomination !== 'demotion') {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'onlyDemotionsSupported'
            );
        }
        if ($r['status'] === $qualitynomination->status) {
            return ['status' => 'ok'];
        }

        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $newProblemVisibility = $problem->visibility;
        switch ($r['status']) {
            case 'approved':
                if ($problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PRIVATE) {
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED;
                } elseif ($problem->visibility == \OmegaUp\ProblemParams::VISIBILITY_PUBLIC) {
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED;
                }
                break;
            case 'denied':
                if ($problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED) {
                    // If banning is reverted, problem will become private.
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PRIVATE;
                } elseif ($problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED) {
                    // If banning is reverted, problem will become public.
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PUBLIC;
                }
                break;
            case 'open':
                // No-op.
                break;
        }

        $message = ($r['status'] === 'approved') ? 'banningProblemDueToReport' : 'banningDeclinedByReviewer';

        $qualitynominationlog = new \OmegaUp\DAO\VO\QualityNominationLog([
            'user_id' => $r->user->user_id,
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'from_status' => $qualitynomination->status,
            'to_status' => $r['status'],
            'rationale' => $r['rationale']
        ]);
        $qualitynomination->status = $qualitynominationlog->to_status;

        $problemParams = new \OmegaUp\ProblemParams([
            'visibility' => $newProblemVisibility,
            'problem_alias' => $r['problem_alias'],
        ], /*$isRequired=*/ false);

        \OmegaUp\DAO\DAO::transBegin();
        try {
            \OmegaUp\Controllers\Problem::updateProblem(
                $r->identity,
                $r->user,
                $problemParams,
                $message,
                $problemParams->updatePublished,
                /*$redirect=*/ false
            );
            \OmegaUp\DAO\QualityNominations::update($qualitynomination);
            \OmegaUp\DAO\QualityNominationLog::create($qualitynominationlog);
            \OmegaUp\DAO\DAO::transEnd();
            if (
                $newProblemVisibility == \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED  ||
                $newProblemVisibility == \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED
            ) {
                self::sendDemotionEmail(
                    $problem,
                    $qualitynomination,
                    $qualitynominationlog->rationale ?? ''
                );
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
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\QualityNominations $qualitynomination,
        string $rationale
    ): void {
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
            \OmegaUp\Translations::getInstance()->get(
                'demotionProblemEmailSubject'
            )
                ?: 'demotionProblemEmailSubject',
            $emailParams
        );
        $body = \OmegaUp\ApiUtils::formatString(
            \OmegaUp\Translations::getInstance()->get(
                'demotionProblemEmailBody'
            )
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
     * @omegaup-request-param mixed $page
     * @omegaup-request-param mixed $page_size
     *
     * @param \OmegaUp\Request $r         The request.
     * @param int     $nominator The user id of the person that made the nomination.  May be null.
     * @param int     $assignee  The user id of the person assigned to review nominations.  May be null.
     *
     * @return array{nominations: list<array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nominator: array{name: null|string, username: string}, problem: array{alias: string, title: string}, qualitynomination_id: int, status: string, time: int, votes: list<array{time: int|null, user: array{name: null|string, username: string}, vote: int}>}|null>} The response.
     */
    private static function getListImpl(
        \OmegaUp\Request $r,
        ?int $nominator,
        ?int $assignee
    ): array {
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $pageSize = is_null(
            $r['page_size']
        ) ? self::PAGE_SIZE : intval(
            $r['page_size']
        );

        $types = $r->getStringList('types', ['promotion', 'demotion']);

        return [
            'nominations' => \OmegaUp\DAO\QualityNominations::getNominations(
                $nominator,
                $assignee,
                $page,
                $pageSize,
                $types
            )['nominations'],
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
        $r->ensureIdentity();
        if (!\OmegaUp\Authorization::isQualityReviewer($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
    }

    /**
     * Checks if the given array has duplicate entries.
     *
     * @template T
     * @param array<T> $contents
     */
    private static function hasDuplicates(array $contents): bool {
        return count($contents) !== count(array_unique($contents));
    }

    /**
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param mixed $rowcount
     *
     * @return array{nominations: list<array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nominator: array{name: null|string, username: string}, problem: array{alias: string, title: string}, qualitynomination_id: int, status: string, time: int, votes: list<array{time: int|null, user: array{name: null|string, username: string}, vote: int}>}|null>, pager_items: list<array{class: string, label: string, page: int}>}
     */
    public static function apiList(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);
        self::validateMemberOfReviewerGroup($r);

        $offset = is_null($r['offset']) ? 1 : intval($r['offset']);
        $rowCount = is_null(
            $r['rowcount']
        ) ? self::PAGE_SIZE : intval(
            $r['rowcount']
        );

        $types = $r->getStringList('types', ['promotion', 'demotion']);
        \OmegaUp\Validators::validateValidSubset(
            $types,
            'types',
            ['promotion', 'demotion']
        );

        $response = \OmegaUp\DAO\QualityNominations::getNominations(
            /* nominator */ null,
            /* assignee */ null,
            $offset,
            $rowCount,
            $types
        );

        $pagerItems = \OmegaUp\Pager::paginate(
            $response['totalRows'],
            $rowCount,
            $offset,
            /*$adjacent=*/5,
            /*$params=*/[]
        );

        return [
            'nominations' => $response['nominations'],
            'pager_items' => $pagerItems,
        ];
    }

    /**
     * Displays the nominations that this user has been assigned.
     *
     * @omegaup-request-param mixed $page
     * @omegaup-request-param mixed $page_size
     *
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{nominations: list<array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nominator: array{name: null|string, username: string}, problem: array{alias: string, title: string}, qualitynomination_id: int, status: string, time: int, votes: list<array{time: int|null, user: array{name: null|string, username: string}, vote: int}>}|null>} The response.
     */
    public static function apiMyAssignedList(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Validate request
        $r->ensureMainUserIdentity();
        self::validateMemberOfReviewerGroup($r);

        return self::getListImpl($r, null /* nominator */, $r->user->user_id);
    }

    /**
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param mixed $rowcount
     *
     * @return array{nominations: list<array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nominator: array{name: null|string, username: string}, problem: array{alias: string, title: string}, qualitynomination_id: int, status: string, time: int, votes: list<array{time: int|null, user: array{name: null|string, username: string}, vote: int}>}|null>, pager_items: list<array{class: string, label: string, page: int}>}
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);

        $offset = is_null($r['offset']) ? 1 : intval($r['offset']);
        $rowCount = is_null(
            $r['rowcount']
        ) ? self::PAGE_SIZE : intval(
            $r['rowcount']
        );

        $types = $r->getStringList('types', ['promotion', 'demotion']);

        if (empty($types)) {
            $types = ['promotion', 'demotion'];
        }

        $response = \OmegaUp\DAO\QualityNominations::getNominations(
            $r->user->user_id,
            /* assignee */ null,
            $offset,
            $rowCount,
            $types
        );

        $pagerItems = \OmegaUp\Pager::paginate(
            $response['totalRows'],
            $rowCount,
            $offset,
            /*$adjacent=*/5,
            /*$params=*/[]
        );

        return [
            'nominations' => $response['nominations'],
            'pager_items' => $pagerItems,
        ];
    }

    /**
     * Displays the details of a nomination. The user needs to be either the
     * nominator or a member of the reviewer group.
     *
     * @omegaup-request-param mixed $qualitynomination_id
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nomination_status: string, nominator: array{name: null|string, username: string}, original_contents?: array{source: null|string, statements: array<string, array{language: string, markdown: string, images: array<string, string>}>|\stdClass, tags?: list<array{source: string, name: string}>}, problem: array{alias: string, title: string}, qualitynomination_id: int, reviewer: bool, time: int, votes: list<array{time: int|null, user: array{name: null|string, username: string}, vote: int}>}
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }
        // Validate request
        $r->ensureMainUserIdentity();

        $r->ensureInt('qualitynomination_id');
        return self::getDetails(
            $r->identity,
            intval($r['qualitynomination_id'])
        );
    }

    /**
     * @return array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nomination_status: string, nominator: array{name: null|string, username: string}, original_contents?: array{source: null|string, statements: array<string, array{language: string, markdown: string, images: array<string, string>}>|\stdClass, tags?: list<array{source: string, name: string}>}, problem: array{alias: string, title: string}, qualitynomination_id: int, reviewer: bool, status: string, time: int, votes: list<array{time: int|null, user: array{name: null|string, username: string}, vote: int}>}
     */
    private static function getDetails(
        \OmegaUp\DAO\VO\Identities $identity,
        int $qualityNominationId
    ) {
        $response = \OmegaUp\DAO\QualityNominations::getById(
            $qualityNominationId
        );
        if (is_null($response)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'qualityNominationNotFound'
            );
        }

        // The nominator can see the nomination, as well as all the members of
        // the reviewer group.
        $currentUserIsNominator = ($identity->username === $response['nominator']['username']);
        $currentUserReviewer = \OmegaUp\Authorization::isQualityReviewer(
            $identity
        );
        if (!$currentUserIsNominator && !$currentUserReviewer) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Get information from the original problem.
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $response['problem']['alias']
        );
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Adding in the response object a flag to know whether the user is a reviewer
        $response['reviewer'] = $currentUserReviewer;

        if ($response['nomination'] === 'promotion') {
            /** @var array<string, array{language: string, markdown: string, images: array<string, string>}> */
            $originalStatements = [];
            $response['original_contents'] = [
                'statements' => $originalStatements,
                'source' => $problem->source,
            ];

            // Don't leak private problem tags to nominator
            if ($currentUserReviewer) {
                $response['original_contents']['tags'] = \OmegaUp\DAO\Problems::getTagsForProblem(
                    $problem,
                    /*$public=*/false,
                    $problem->allow_user_add_tags
                );
            }

            if (
                isset($response['contents']) &&
                isset($response['contents']['statements'])
            ) {
                /**
                 * Pull original problem statements in every language the nominator is trying to override.
                 * @var string $language
                 * @var string $_
                 */
                foreach ($response['contents']['statements'] as $language => $_) {
                    $originalStatements[$language] = \OmegaUp\Controllers\Problem::getProblemStatement(
                        $problem->alias,
                        'published',
                        $language
                    );
                }
            }
            /** @psalm-suppress RedundantCondition $response['original_contents']['statements'] can be an array but still be empty. */
            if (empty($originalStatements)) {
                // Force 'statements' to be an object.
                $response['original_contents']['statements'] = (object)[];
            }
        }
        $response['nomination_status'] = $response['status'];
        return $response;
    }

    /**
     * @omegaup-request-param mixed $qualitynomination_id
     *
     * @return array{smartyProperties: array{payload: array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nomination_status: string, nominator: array{name: null|string, username: string}, original_contents?: array{source: null|string, statements: mixed|\stdClass, tags?: list<array{source: string, name: string}>}, problem: array{alias: string, title: string}, qualitynomination_id: int, reviewer: bool, status: string, time: int, votes: list<array{time: int|null, user: array{name: null|string, username: string}, vote: int}>}}, template: string}
     */
    public static function getDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        $r->ensureInt('qualitynomination_id', null, null, true);

        $qualityNominationId = intval($r['qualitynomination_id']);

        return [
            'smartyProperties' => [
                'payload' => \OmegaUp\Controllers\QualityNomination::getDetails(
                    $r->identity,
                    $qualityNominationId
                ),
            ],
            'template' => 'quality.nomination.details.tpl',
        ];
    }

    /**
     * Gets the details for the quality nomination's list
     * with pagination
     *
     * @omegaup-request-param mixed $length
     * @omegaup-request-param mixed $page
     *
     * @return array{smartyProperties: array{payload: array{page: int, length: int, myView: bool}}, template: string}
     */
    public static function getListForSmarty(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('length', null, null, false);
        self::validateMemberOfReviewerGroup($r);

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null(
            $r['length']
        ) ? self::PAGE_SIZE : intval(
            $r['length']
        );

        return [
            'smartyProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'myView' => false,
                ],
            ],
            'template' => 'quality.nomination.list.tpl',
        ];
    }

    /**
     * Gets the details for the quality nomination's list
     * with pagination for a certain user
     *
     * @omegaup-request-param mixed $length
     * @omegaup-request-param mixed $page
     *
     * @return array{smartyProperties: array{payload: array{page: int, length: int, myView: bool}}, template: string}
     */
    public static function getMyListForSmarty(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('length', null, null, false);

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null(
            $r['length']
        ) ? self::PAGE_SIZE : intval(
            $r['length']
        );

        return [
            'smartyProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'myView' => true,
                ],
            ],
            'template' => 'quality.nomination.list.tpl',
        ];
    }
}
