<?php

namespace OmegaUp\Controllers;

/**
 * QualityNominationController
 *
 * @psalm-type ProblemStatement=array{images: array<string, string>, sources: array<string, string>, language: string, markdown: string}
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type NominationListItem=array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nominator: array{name: null|string, username: string}, problem: array{alias: string, title: string}, qualitynomination_id: int, status: string, time: \OmegaUp\Timestamp, votes: list<array{time: \OmegaUp\Timestamp|null, user: array{name: null|string, username: string}, vote: int}>}
 *
 */
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

    const ALLOWED_PUBLIC_TAGS = [
        'problemTagArithmetic',
        'problemTagConditionals',
        'problemTagLoops',
        'problemTagFunctions',
        'problemTagArrays',
        'problemTagMatrices',
        'problemTagCharsAndStrings',
        'problemTagFormattedInputAndOutput',
        'problemTagSimulation',
        'problemTagImplementation',
        'problemTagAnalyticGeometry',
        'problemTagSystemsOfEquations',
        'problemTagDiophantineEquations',
        'problemTagGCDAndLCM',
        'problemTagModularArithmetic',
        'problemTagModularMultiplicativeInverse',
        'problemTagChineseRemainderTheorem',
        'problemTagRecursion',
        'problemTagPermutations',
        'problemTagCombinations',
        'problemTagDivisibilityRules',
        'problemTagCountingProblems',
        'problemTagCombinatorialDesigns',
        'problemTagGameTheory',
        'problemTagNumberTheory',
        'problemTagNumericalSeries',
        'problemTagPartialSums',
        'problemTagPrimalityTest',
        'problemTagPrimeGeneration',
        'problemTagPrimeFactorization',
        'problemTagFourierTransformation',
        'problemTagBigNumbers',
        'problemTagBooleanAlgebra',
        'problemTagBitManipulation',
        'problemTagProbabilityAndStatistics',
        'problemTagExponentiationBySquaring',
        'problemTagSorting',
        'problemTagBinarySearch',
        'problemTagExponentialSearch',
        'problemTagStringMatching',
        'problemTagStacks',
        'problemTagQueues',
        'problemTagLinkedLists',
        'problemTagHeaps',
        'problemTagTreeTransversal',
        'problemTagBinarySearchTree',
        'problemTagGraphRepresentation',
        'problemTagGraphConnectivity',
        'problemTagDirectedGraphs',
        'problemTagTrees',
        'problemTagBreadthFirstSearch',
        'problemTagDepthFirstSearch',
        'problemTagShortestPaths',
        'problemTagMinimumSpanningTrees',
        'problemTagTopologicalSorting',
        'problemTagGraphsWithNegativeWeights',
        'problemTagDisjointSets',
        'problemTagHashing',
        'problemTagInvertedIndices',
        'problemTagInputAndOutput',
        'problemTagTries',
        'problemTagBruteForce',
        'problemTagIncrementalSearch',
        'problemTagBacktracking',
        'problemTagLocalSearch',
        'problemTagGreedyAlgorithms',
        'problemTagDivideAndConquer',
        'problemTagMemorization',
        'problemTagDynamicProgramming',
        'problemTagSubArraySearch',
        'problemTagSubsequenceSearch',
        'problemTagMeetInTheMiddle',
        'problemTagBipartiteMatching',
        'problemTagMaxFlow',
        'problemTagWaveletTrees',
        'problemTagSegmentTrees',
        'problemTagSuffixTrees',
        'problemTagFenwickTrees',
        'problemTagLeastCommonAncestor',
        'problemTagLazyPropagation',
        'problemTagOfflineQueries',
        'problemTagSlidingWindow',
        'problemTagMonotoneStack',
        'problemTagTwoPointersTechnique',
        'problemTagSQRTDecomposition',
        'problemTagPalindromeAlgorithms',
        'problemTagNearestNeighbors',
        'problemTagConvexHull',
        'problemTagSweepLine',
        'problemTagLexingAndParsing',
        'problemTagGeneticAlgorithms',
        'problemTagParticleSwarmOptimization',
        'problemTagHeuristics',
        'problemTagDataCompression',
        'problemTagBigData',
        'problemTagOFMI',
        'problemTagOMI',
        'problemTagOMIAguascalientes',
        'problemTagOMIBajaCalifornia',
        'problemTagOMIBajaCaliforniaSur',
        'problemTagOMICampeche',
        'problemTagOMICoahuila',
        'problemTagOMIColima',
        'problemTagOMIChiapas',
        'problemTagOMIChihuahua',
        'problemTagOMIDistritoFederal',
        'problemTagOMIDurango',
        'problemTagOMIGuanajuato',
        'problemTagOMIGuerrero',
        'problemTagOMIHidalgo',
        'problemTagOMIJalisco',
        'problemTagOMIMexico',
        'problemTagOMIMichoacan',
        'problemTagOMIMorelos',
        'problemTagOMINayarit',
        'problemTagOMINuevoLeon',
        'problemTagOMIOaxaca',
        'problemTagOMIPuebla',
        'problemTagOMIQueretaro',
        'problemTagOMIQuintanaRoo',
        'problemTagOMISanLuisPotosi',
        'problemTagOMISinaloa',
        'problemTagOMISonora',
        'problemTagOMITabasco',
        'problemTagOMITamaulipas',
        'problemTagOMITlaxcala',
        'problemTagOMIVeracruz',
        'problemTagOMIYucatan',
        'problemTagOMIZacatecas',
        'problemTagOMIPS',
        'problemTagIOI',
        'problemTagICPC',
        'problemTagCIIC',
        'problemTagCodingCup',
        'problemTagCodingRush',
        'problemTagCOCI',
        'problemTagBOI',
        'problemTagAnalysisOfRecurrences',
        'problemTagUnformattedInputAndOutput',
        'problemTagFileSeeking',
        'problemTagLinearSearch',
        'problemTagSetsMultisets',
        'problemTagMapsMultimaps',
        'problemTagNumericalMethods',
    ];

    const LEVEL_TAGS = [
        'problemLevelAdvancedCompetitiveProgramming',
        'problemLevelAdvancedSpecializedTopics',
        'problemLevelBasicIntroductionToProgramming',
        'problemLevelBasicKarel',
        'problemLevelIntermediateAnalysisAndDesignOfAlgorithms',
        'problemLevelIntermediateDataStructuresAndAlgorithms',
        'problemLevelIntermediateMathsInProgramming',
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

                $duplicatedTags = self::getDuplicatedTags($contents['tags']);

                if (!empty($duplicatedTags)) {
                    throw new \OmegaUp\Exceptions\DuplicatedEntryInArrayException(
                        'duplicateTagsNotAllowed',
                        'tags',
                        duplicatedItemsInArray: array_slice(
                            $duplicatedTags,
                            0,
                            20
                        )
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

            $duplicatedTags = self::getDuplicatedTags($contents['tags']);

            if (!empty($duplicatedTags)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInArrayException(
                    'duplicateTagsNotAllowed',
                    'tags',
                    duplicatedItemsInArray: array_slice($duplicatedTags, 0, 20)
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
            // TODO: rename 'tag' to 'level'.
            if (
                isset($contents['tag']) &&
                !in_array($contents['tag'], self::LEVEL_TAGS)
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'contents'
                );
            }

            if (
                isset($contents['tags'])
            ) {
                if (!is_array($contents['tags'])) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'contents'
                    );
                }
                /** @var mixed $tag */
                foreach ($contents['tags'] as &$tag) {
                    if (
                        !is_string($tag) ||
                        !in_array($tag, self::ALLOWED_PUBLIC_TAGS)
                    ) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'parameterInvalid',
                            'contents'
                        );
                    }
                }
            }

            if (
                \OmegaUp\DAO\QualityNominations::reviewerHasQualityTagNominatedProblem(
                    $identity,
                    $problem
                )
            ) {
                throw new \OmegaUp\Exceptions\PreconditionFailedException(
                    'qualityNominationReviewerHasAlreadySentNominationForProblem'
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
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{qualitynomination_id: int}
     *
     * @omegaup-request-param string $contents
     * @omegaup-request-param 'demotion'|'dismissal'|'promotion'|'quality_tag'|'suggestion' $nomination
     * @omegaup-request-param string $problem_alias
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Validate request
        $r->ensureMainUserIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['contents'], 'contents');
        /**
         * @var null|array{tags?: mixed, before_ac?: mixed, difficulty?: mixed, quality?: mixed, statements?: mixed, source?: mixed, reason?: mixed, original?: mixed} $contents
         */
        $contents = json_decode($r['contents'], associative: true);
        if (!is_array($contents)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'contents'
            );
        }
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $nomination = \OmegaUp\Controllers\QualityNomination::createNomination(
            $problem,
            $r->identity,
            $r->ensureEnum(
                'nomination',
                ['suggestion', 'promotion', 'demotion', 'dismissal', 'quality_tag']
            ),
            $contents
        );

        return [
            'qualitynomination_id' => intval($nomination->qualitynomination_id)
        ];
    }

    /**
     * Marks a problem of a nomination (only the demotion type supported for now) as (resolved, banned, warning).
     *
     * @return array{status: string}
     *
     * @omegaup-request-param bool|null $all
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int $qualitynomination_id
     * @omegaup-request-param string $rationale
     * @omegaup-request-param 'banned'|'open'|'resolved'|'warning' $status
     */
    public static function apiResolve(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Validate request
        $r->ensureMainUserIdentity();
        self::validateMemberOfReviewerGroup($r);

        $status = $r->ensureEnum(
            'status',
            ['open', 'resolved', 'banned', 'warning']
        );
        $rationale = $r->ensureString('rationale');

        $qualityNominationId = $r->ensureInt('qualitynomination_id');
        $qualitynomination = \OmegaUp\DAO\QualityNominations::getByPK(
            $qualityNominationId
        );
        if (is_null($qualitynomination)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'qualityNominationNotFound'
            );
        }
        if ($qualitynomination->nomination !== 'demotion') {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'onlyDemotionsSupported'
            );
        }
        if ($status === $qualitynomination->status) {
            return ['status' => 'ok'];
        }

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        if (is_null($qualitynomination->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        $problem = \OmegaUp\DAO\Problems::getByPK(
            $qualitynomination->problem_id
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $isProblemPublic = (
            $problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PUBLIC ||
            $problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING ||
            $problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED
        );
        $newProblemVisibility = $problem->visibility;
        switch ($status) {
            case 'banned':
                if ($isProblemPublic) {
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED;
                } else {
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED;
                }
                break;
            case 'resolved':
                if ($isProblemPublic) {
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PUBLIC;
                } else {
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PRIVATE;
                }
                break;
            case 'warning':
                if ($isProblemPublic) {
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING;
                } else {
                    $newProblemVisibility = \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_WARNING;
                }
                break;
            case 'open':
                // No-op.
                break;
        }

        $message = ($status === 'banned') ?
            \OmegaUp\Translations::getInstance()->get(
                'banningProblemDueToReport'
            ) :
            \OmegaUp\Translations::getInstance()->get(
                'banningDeclinedByReviewer'
            );

        if ($r->ensureOptionalBool('all') ?? false) {
            $nominations = \OmegaUp\DAO\QualityNominations::getAllDemotionsForProblem(
                $qualitynomination->problem_id
            );
        } else {
            $nominations = [$qualitynomination];
        }

        $problemParams = new \OmegaUp\ProblemParams([
            'visibility' => $newProblemVisibility,
            'problem_alias' => $problemAlias,
        ], isRequired: false);

        try {
            \OmegaUp\DAO\DAO::transBegin();
            \OmegaUp\Controllers\Problem::updateProblem(
                $r->identity,
                $r->user,
                $problemParams,
                $message,
                $problemParams->updatePublished,
                redirect: false
            );
            foreach ($nominations as $nomination) {
                \OmegaUp\DAO\QualityNominationLog::create(
                    new \OmegaUp\DAO\VO\QualityNominationLog([
                        'user_id' => $r->user->user_id,
                        'qualitynomination_id' => $nomination->qualitynomination_id,
                        'from_status' => $nomination->status,
                        'to_status' => $status,
                        'rationale' => $rationale
                    ])
                );
                /**
                * @var null|array{tags?: mixed, before_ac?: mixed, difficulty?: mixed, quality?: mixed, statements?: mixed, source?: mixed, reason?: mixed, original?: mixed} $contents
                */
                $contents = json_decode($nomination->contents ?? '{}', true);
                $contents['rationale'] = $rationale;
                $nomination->contents = json_encode($contents);
                $nomination->status = $status;
                \OmegaUp\DAO\QualityNominations::update($nomination);
            }
            if ($status == 'banned' || $status == 'warning') {
                self::sendNotificationEmail(
                    $problem,
                    $qualitynomination,
                    $rationale,
                    $status
                );
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            self::$log->error(
                'Failed to resolve demotion request',
                ['exception' => $e],
            );
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
    private static function sendNotificationEmail(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\QualityNominations $qualitynomination,
        string $rationale,
        string $status
    ): void {
        $adminUser = \OmegaUp\DAO\Problems::getAdminUser($problem);

        if (is_null($adminUser)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        [
            'email' => $email,
            'name' => $name,
            'user_id' => $userId,
        ] = $adminUser;

        $emailParams = [
            'reason' => htmlspecialchars($rationale),
            'problem_name' => htmlspecialchars(strval($problem->title)),
            'user_name' => $name,
        ];

        if ($status == 'banned') {
            $notificationContents = \OmegaUp\ApiUtils::formatString(
                \OmegaUp\Translations::getInstance()->get(
                    'demotionProblemNotificationBanned'
                ),
                ['problem_name' => strval($problem->title)]
            );
            $subject = \OmegaUp\ApiUtils::formatString(
                \OmegaUp\Translations::getInstance()->get(
                    'demotionProblemEmailBannedSubject'
                ),
                $emailParams
            );
            $body = \OmegaUp\ApiUtils::formatString(
                \OmegaUp\Translations::getInstance()->get(
                    'demotionProblemEmailBannedBody'
                ),
                $emailParams
            );
        } else {
            $notificationContents = \OmegaUp\ApiUtils::formatString(
                \OmegaUp\Translations::getInstance()->get(
                    'demotionProblemNotificationWarning'
                ),
                ['problem_name' => strval($problem->title)]
            );
            $subject = \OmegaUp\ApiUtils::formatString(
                \OmegaUp\Translations::getInstance()->get(
                    'demotionProblemEmailWarningSubject'
                ),
                $emailParams
            );
            $body = \OmegaUp\ApiUtils::formatString(
                \OmegaUp\Translations::getInstance()->get(
                    'demotionProblemEmailWarningBody'
                ),
                $emailParams
            );
        }

        \OmegaUp\DAO\Base\Notifications::create(
            new \OmegaUp\DAO\VO\Notifications([
                'user_id' => $userId,
                'contents' =>  json_encode(
                    [
                        'type' => \OmegaUp\DAO\Notifications::DEMOTION,
                        'message' => $notificationContents,
                        'status' => $status
                    ]
                ),
            ])
        );

        \OmegaUp\Email::sendEmail([$email], $subject, $body);
    }

    /**
     * Returns the list of nominations made by $nominator (if non-null),
     * assigned to $assignee (if non-null) or all nominations (if both
     * $nominator and $assignee are null).
     *
     * @param \OmegaUp\Request $r         The request.
     * @param int     $nominator The user id of the person that made the nomination.  May be null.
     * @param int     $assignee  The user id of the person assigned to review nominations.  May be null.
     *
     * @return array{nominations: list<array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nominator: array{name: null|string, username: string}, problem: array{alias: string, title: string}, qualitynomination_id: int, status: string, time: \OmegaUp\Timestamp, votes: list<array{time: \OmegaUp\Timestamp|null, user: array{name: null|string, username: string}, vote: int}>}>} The response.
     *
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     */
    private static function getListImpl(
        \OmegaUp\Request $r,
        ?int $nominator,
        ?int $assignee
    ): array {
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('page_size');

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
     * @return list<string>
     */
    private static function getDuplicatedTags(array $contents): array {
        $counts = array_count_values($contents);
        $duplicates = [];
        foreach ($counts as $value => $count) {
            if ($count > 1) {
                $duplicates[] = $value;
            }
        }
        return $duplicates;
    }

    /**
     * @omegaup-request-param 'author_username'|'nominator_username'|'problem_alias'|null $column
     * @omegaup-request-param int $offset
     * @omegaup-request-param null|string $query
     * @omegaup-request-param int $rowcount
     * @omegaup-request-param mixed $status
     *
     * @return array{nominations: list<NominationListItem>, pager_items: list<PageItem>}
     */
    public static function apiList(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();

        $r->ensureOptionalInt('offset');
        $r->ensureOptionalInt('rowcount');
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['status'],
            'status',
            ['all','open','resolved','banned','warning']
        );
        $status = $r['status'] ?? 'all';
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

        $query = $r->ensureOptionalString('query');
        $column = $r->ensureOptionalEnum(
            'column',
            ['problem_alias','nominator_username','author_username']
        );

        $response = \OmegaUp\DAO\QualityNominations::getNominations(
            nominatorUserId: null,
            assigneeUserId: null,
            page: $offset,
            rowcount: $rowCount,
            types: $types,
            status: $status,
            query: $query,
            column: $column,
        );

        $pagerItems = \OmegaUp\Pager::paginate(
            $response['totalRows'],
            $rowCount,
            $offset,
            adjacent: 5,
            params: []
        );

        return [
            'nominations' => $response['nominations'],
            'pager_items' => $pagerItems,
        ];
    }

    /**
     * Displays the nominations that this user has been assigned.
     *
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{nominations: list<array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nominator: array{name: null|string, username: string}, problem: array{alias: string, title: string}, qualitynomination_id: int, status: string, time: \OmegaUp\Timestamp, votes: list<array{time: \OmegaUp\Timestamp|null, user: array{name: null|string, username: string}, vote: int}>}>} The response.
     *
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     */
    public static function apiMyAssignedList(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Validate request
        $r->ensureMainUserIdentity();
        self::validateMemberOfReviewerGroup($r);

        return self::getListImpl(
            $r,
            nominator: null,
            assignee: $r->user->user_id,
        );
    }

    /**
     *
     * @omegaup-request-param int $offset
     * @omegaup-request-param int $rowcount
     *
     * @return array{nominations: list<NominationListItem>, pager_items: list<PageItem>}
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();

        $r->ensureOptionalInt('offset');
        $r->ensureOptionalInt('rowcount');

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
            nominatorUserId: $r->user->user_id,
            assigneeUserId: null,
            page: $offset,
            rowcount: $rowCount,
            types: $types,
        );

        $pagerItems = \OmegaUp\Pager::paginate(
            $response['totalRows'],
            $rowCount,
            $offset,
            adjacent: 5,
            params: []
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
     * @param \OmegaUp\Request $r
     *
     * @return array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nomination_status: string, nominator: array{name: null|string, username: string}, original_contents?: array{source: null|string, statements: array<string, ProblemStatement>|\object, tags?: list<array{source: string, name: string}>}, problem: array{alias: string, title: string}, qualitynomination_id: int, reviewer: bool, time: \OmegaUp\Timestamp, votes: list<array{time: \OmegaUp\Timestamp|null, user: array{name: null|string, username: string}, vote: int}>}
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param int $qualitynomination_id
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Validate request
        $r->ensureMainUserIdentity();

        $qualityNominationId = $r->ensureInt('qualitynomination_id');
        return self::getDetails($r->identity, $qualityNominationId);
    }

    /**
     * @return array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nomination_status: string, nominator: array{name: null|string, username: string}, original_contents?: array{source: null|string, statements: array<string, ProblemStatement>|object, tags?: list<array{source: string, name: string}>}, problem: array{alias: string, title: string}, qualitynomination_id: int, reviewer: bool, status: string, time: \OmegaUp\Timestamp, votes: list<array{time: \OmegaUp\Timestamp|null, user: array{name: null|string, username: string}, vote: int}>}
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
            /** @var array<string, ProblemStatement> */
            $originalStatements = [];
            $response['original_contents'] = [
                'statements' => $originalStatements,
                'source' => $problem->source,
            ];

            // Don't leak private problem tags to nominator
            if ($currentUserReviewer) {
                $response['original_contents']['tags'] = \OmegaUp\DAO\Problems::getTagsForProblem(
                    $problem,
                    public: false,
                    showUserTags: $problem->allow_user_add_tags,
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
     * @return array{templateProperties: array{payload: array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nomination_status: string, nominator: array{name: null|string, username: string}, original_contents?: array{source: null|string, statements: mixed|\object, tags?: list<array{source: string, name: string}>}, problem: array{alias: string, title: string}, qualitynomination_id: int, reviewer: bool, status: string, time: \OmegaUp\Timestamp, votes: list<array{time: \OmegaUp\Timestamp|null, user: array{name: null|string, username: string}, vote: int}>}, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int $qualitynomination_id
     */
    public static function getDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        $qualityNominationId = $r->ensureInt('qualitynomination_id');

        return [
            'templateProperties' => [
                'payload' => \OmegaUp\Controllers\QualityNomination::getDetails(
                    $r->identity,
                    $qualityNominationId
                ),
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleQualityNominationDetails'
                ),
            ],
            'entrypoint' => 'qualitynomination_details',
        ];
    }

    /**
     * Gets the details for the quality nomination's list
     * with pagination
     *
     * @return array{templateProperties: array{payload: array{page: int, length: int, myView: bool}, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     */
    public static function getListForTypeScript(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('length');
        self::validateMemberOfReviewerGroup($r);

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null(
            $r['length']
        ) ? self::PAGE_SIZE : intval(
            $r['length']
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'myView' => false,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleQualityNominationList'
                ),
            ],
            'entrypoint' => 'qualitynomination_list',
        ];
    }

    /**
     * Gets the details for the quality nomination's list
     * with pagination for a certain user
     *
     * @return array{templateProperties: array{payload: array{page: int, length: int, myView: bool}, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     */
    public static function getMyListForTypeScript(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('length');

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null(
            $r['length']
        ) ? self::PAGE_SIZE : intval(
            $r['length']
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'myView' => true,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleQualityNominationMyList'
                ),
            ],
            'entrypoint' => 'qualitynomination_list',
        ];
    }
}
