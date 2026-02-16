<?php

namespace OmegaUp;

class ProblemParams extends BaseParams {
    // Constants for problem visibility.
    const VISIBILITY_DELETED = -10; // Problem that was logically deleted by its owner
    const VISIBILITY_PRIVATE_BANNED = -3; // Problem that was private before it was banned
    const VISIBILITY_PUBLIC_BANNED = -2; // Problem that was public before it was banned
    const VISIBILITY_PRIVATE_WARNING = -1; // Problem that was private before it was warning
    const VISIBILITY_PRIVATE = 0;
    const VISIBILITY_PUBLIC_WARNING = 1; // Problem that was public before it was warning
    const VISIBILITY_PUBLIC = 2;
    const VISIBILITY_PROMOTED = 3;

    // Do not update the published branch.
    const UPDATE_PUBLISHED_NONE = 'none';
    // Update only non-problemset runs.
    const UPDATE_PUBLISHED_NON_PROBLEMSET = 'non-problemset';
    // Update non-problemset runs and running problemsets that are owned by the
    // author.
    const UPDATE_PUBLISHED_OWNED_PROBLEMSETS = 'owned-problemsets';
    // Update non-problemset runs and running problemsets that the author has
    // edit privileges.
    const UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS = 'editable-problemsets';

    // Token by token validator
    const VALIDATOR_TOKEN = 'token';

    // Token by token, ignoring uppercase/lowercase validator
    const VALIDATOR_TOKEN_CASELESS = 'token-caseless';

    // Numeric token with tolerance 1e-9 validator
    const VALIDATOR_TOKEN_NUMERIC = 'token-numeric';

    // Interpret stdout as score validator
    const VALIDATOR_LITERAL = 'literal';

    // Custom validator
    const VALIDATOR_CUSTOM = 'custom';

    // Diffs to show when problem is educational
    const SHOW_DIFFS_NONE = 'none';
    const SHOW_DIFFS_EXAMPLES = 'examples';
    const SHOW_DIFFS_ALL = 'all';

    // Group score policy
    const GROUP_SCORE_POLICY_SUM_IF_NOT_ZERO = 'sum-if-not-zero';
    const GROUP_SCORE_POLICY_MIN = 'min';

    const VALID_GROUP_SCORE_POLICY_VALUES = [
        self::GROUP_SCORE_POLICY_SUM_IF_NOT_ZERO,
        self::GROUP_SCORE_POLICY_MIN,
    ];

    /**
     * @readonly
     * @var string
     */
    public $problemAlias;

    /**
     * @readonly
     * @var null|string
     */
    public $title;

    /**
     * @var \OmegaUp\ProblemParams::VISIBILITY_DELETED|\OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED|\OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED|\OmegaUp\ProblemParams::VISIBILITY_PRIVATE|\OmegaUp\ProblemParams::VISIBILITY_PUBLIC|\OmegaUp\ProblemParams::VISIBILITY_PROMOTED|null
     */
    public $visibility;

    /**
     * @readonly
     * @var null|string
     */
    public $languages;

    /**
     * @readonly
     * @var null|string
     */
    public $problemLevel;

    /**
     * @readonly
     * @var \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS
     */
    public $updatePublished;

    /**
     * @readonly
     * @var null|string
     */
    public $selectedTagsAsJSON = null;

    /**
     * @readonly
     * @var string|null
     */
    public $source;

    /**
     * @readonly
     * @var \OmegaUp\ProblemParams::VALIDATOR_TOKEN|\OmegaUp\ProblemParams::VALIDATOR_TOKEN_CASELESS|\OmegaUp\ProblemParams::VALIDATOR_TOKEN_NUMERIC|\OmegaUp\ProblemParams::VALIDATOR_LITERAL|null
     */
    public $validator;

    /**
     * @readonly
     * @var int|null
     */
    public $timeLimit;

    /**
     * @readonly
     * @var int|null
     */
    public $validatorTimeLimit;

    /**
     * @readonly
     * @var int|null
     */
    public $overallWallTimeLimit;

    /**
     * @readonly
     * @var int|null
     */
    public $extraWallTime;

    /**
     * @readonly
     * @var int|null
     */
    public $memoryLimit;

    /**
     * @readonly
     * @var int|null
     */
    public $outputLimit;

    /**
     * @readonly
     * @var int|null
     */
    public $inputLimit;

    /**
     * @readonly
     * @var bool|null
     */
    public $emailClarifications;

    /**
     * @readonly
     * @var string
     */
    public $order;

    /**
     * @readonly
     * @var string
     */
    public $showDiff;

    /**
     * @readonly
     * @var string|null
     */
    public $groupScorePolicy;

    /**
     * @readonly
     * @var bool
     */
    public $allowUserAddTags;

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType
     * @param array{allow_user_add_tags?: bool, email_clarifications?: bool, extra_wall_time?: int, group_score_policy?: string, input_limit?: int, languages?: string, memory_limit?: int|null, order?: string, output_limit?: int|null, overall_wall_time_limit?: int|null, problem_alias: string, problem_level?: string, selected_tags?: string, show_diff?: string, source?: string, time_limit?: int|null, title?: string, update_published?: \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS, validator?: \OmegaUp\ProblemParams::VALIDATOR_TOKEN|\OmegaUp\ProblemParams::VALIDATOR_TOKEN_CASELESS|\OmegaUp\ProblemParams::VALIDATOR_TOKEN_NUMERIC|\OmegaUp\ProblemParams::VALIDATOR_LITERAL, validator_time_limit?: int|null, visibility?: \OmegaUp\ProblemParams::VISIBILITY_DELETED|\OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED|\OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED|\OmegaUp\ProblemParams::VISIBILITY_PRIVATE|\OmegaUp\ProblemParams::VISIBILITY_PUBLIC|\OmegaUp\ProblemParams::VISIBILITY_PROMOTED} $params
     */
    public function __construct($params, bool $isRequired = true) {
        $isUpdate = !$isRequired;
        $visibilityStatements = $isUpdate ? [
            \OmegaUp\ProblemParams::VISIBILITY_PRIVATE,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED,
            \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED,
            \OmegaUp\ProblemParams::VISIBILITY_PROMOTED,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING,
            \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_WARNING,
        ] : [
            \OmegaUp\ProblemParams::VISIBILITY_PRIVATE,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
        ];
        if (isset($params['visibility'])) {
            \OmegaUp\Validators::validateInEnum(
                $params['visibility'],
                'visibility',
                $visibilityStatements
            );
        }
        if (isset($params['show_diff'])) {
            \OmegaUp\Validators::validateInEnum(
                $params['show_diff'],
                'show_diff',
                ['none', 'examples', 'all']
            );
        }
        if (isset($params['group_score_policy'])) {
            \OmegaUp\Validators::validateInEnum(
                $params['group_score_policy'],
                'group_score_policy',
                self::VALID_GROUP_SCORE_POLICY_VALUES
            );
        }
        if (isset($params['validator'])) {
            \OmegaUp\Validators::validateInEnum(
                $params['validator'],
                'validator',
                [
                    \OmegaUp\ProblemParams::VALIDATOR_TOKEN,
                    \OmegaUp\ProblemParams::VALIDATOR_TOKEN_CASELESS,
                    \OmegaUp\ProblemParams::VALIDATOR_TOKEN_NUMERIC,
                    \OmegaUp\ProblemParams::VALIDATOR_LITERAL,
                    \OmegaUp\ProblemParams::VALIDATOR_CUSTOM,
                ]
            );
        }
        if (isset($params['time_limit'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['time_limit'],
                'time_limit',
                lowerBound: 0,
                upperBound: null,
                required: $isRequired
            );
        }
        if (isset($params['memory_limit'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['memory_limit'],
                'memory_limit',
                lowerBound: 0,
                upperBound: null,
                required: $isRequired
            );
        }
        if (isset($params['validator_time_limit'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['validator_time_limit'],
                'validator_time_limit',
                lowerBound: 0,
                upperBound: null,
                required: $isRequired
            );
        }
        if (isset($params['overall_wall_time_limit'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['overall_wall_time_limit'],
                'overall_wall_time_limit',
                lowerBound: 0,
                upperBound: 60000,
                required: $isRequired
            );
        }
        if (isset($params['extra_wall_time'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['extra_wall_time'],
                'extra_wall_time',
                lowerBound: 0,
                upperBound: 5000,
                required: $isRequired
            );
        }
        if (isset($params['output_limit'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['output_limit'],
                'output_limit',
                lowerBound: 0,
                upperBound: null,
                required: $isRequired
            );
        }
        $this->problemAlias = $params['problem_alias'];
        $this->title = $params['title'] ?? null;
        $this->visibility = $params['visibility'] ?? null;
        $this->languages = $params['languages'] ?? null;
        $this->updatePublished = $params['update_published'] ?? \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS;
        $this->problemLevel = $params['problem_level'] ?? null;
        $this->selectedTagsAsJSON = $params['selected_tags'] ?? null;
        $this->source = $params['source'] ?? null;
        $this->validator = $params['validator'] ?? null;
        $this->timeLimit = $params['time_limit'] ?? null;
        $this->validatorTimeLimit = $params['validator_time_limit'] ?? null;
        $this->overallWallTimeLimit = $params['overall_wall_time_limit'] ?? null;
        $this->extraWallTime = $params['extra_wall_time'] ?? null;
        $this->memoryLimit = $params['memory_limit'] ?? null;
        $this->outputLimit = $params['output_limit'] ?? null;
        $this->inputLimit = $params['input_limit'] ?? 10240;
        $this->emailClarifications = $params['email_clarifications'] ?? null;
        $this->allowUserAddTags = $params['allow_user_add_tags'] ?? false;
        $this->order = $params['order'] ?? 'normal';
        $this->showDiff = $params['show_diff'] ?? 'none';
        $this->groupScorePolicy = $params['group_score_policy'] ?? self::GROUP_SCORE_POLICY_SUM_IF_NOT_ZERO;
    }

    /**
    * Convert string visibility to numeric visibility
    */
    public static function stringVisibilityToNumeric(string $visibility): int {
        switch ($visibility) {
            case 'deleted':
                return \OmegaUp\ProblemParams::VISIBILITY_DELETED;
            case 'private_banned':
                return \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED;
            case 'public_banned':
                return \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED;
            case 'private_warning':
                return \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_WARNING;
            case 'private':
                return \OmegaUp\ProblemParams::VISIBILITY_PRIVATE;
            case 'public_warning':
                return \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING;
            case 'public':
                return \OmegaUp\ProblemParams::VISIBILITY_PUBLIC;
            case 'promoted':
                return \OmegaUp\ProblemParams::VISIBILITY_PROMOTED;
            default:
                // TODO(#4144): Remove this when the migration is complete.
                if (is_numeric($visibility)) {
                    return intval($visibility);
                }
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'invalidVisibility',
                    'visibility'
                );
        }
    }
}
