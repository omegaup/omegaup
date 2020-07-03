<?php

namespace OmegaUp;

class ProblemParams {
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
     * @var null|list<string>
     */
    public $languages;

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
     * @var bool
     */
    public $allowUserAddTags;

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType
     * @param array{allow_user_add_tags?: bool, email_clarifications?: bool, extra_wall_time?: int, input_limit?: int, languages?: string, memory_limit?: int, order?: string, output_limit?: int, overall_wall_time_limit?: int, problem_alias: string, selected_tags?: string, show_diff?: string, source?: string, time_limit?: int, title?: string, update_published?: \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS, validator?: \OmegaUp\ProblemParams::VALIDATOR_TOKEN|\OmegaUp\ProblemParams::VALIDATOR_TOKEN_CASELESS|\OmegaUp\ProblemParams::VALIDATOR_TOKEN_NUMERIC|\OmegaUp\ProblemParams::VALIDATOR_LITERAL, validator_time_limit?: int, visibility?: \OmegaUp\ProblemParams::VISIBILITY_DELETED|\OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED|\OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED|\OmegaUp\ProblemParams::VISIBILITY_PRIVATE|\OmegaUp\ProblemParams::VISIBILITY_PUBLIC|\OmegaUp\ProblemParams::VISIBILITY_PROMOTED} $params
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
                /*$lowerBound=*/ 0,
                /*$uppperBound=*/ null,
                $isRequired
            );
        }
        if (isset($params['memory_limit'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['memory_limit'],
                'memory_limit',
                /*$lowerBound=*/ 0,
                /*$uppperBound=*/ null,
                $isRequired
            );
        }
        if (isset($params['validator_time_limit'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['validator_time_limit'],
                'validator_time_limit',
                /*$lowerBound=*/ 0,
                /*$uppperBound=*/ null,
                $isRequired
            );
        }
        if (isset($params['overall_wall_time_limit'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['overall_wall_time_limit'],
                'overall_wall_time_limit',
                /*$lowerBound=*/ 0,
                /*$uppperBound=*/ 60000,
                $isRequired
            );
        }
        if (isset($params['extra_wall_time'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['extra_wall_time'],
                'extra_wall_time',
                /*$lowerBound=*/ 0,
                /*$uppperBound=*/ 5000,
                $isRequired
            );
        }
        if (isset($params['output_limit'])) {
            \OmegaUp\Validators::validateNumberInRange(
                $params['output_limit'],
                'output_limit',
                /*$lowerBound=*/ 0,
                /*$uppperBound=*/ null,
                $isRequired
            );
        }
        $this->problemAlias = $params['problem_alias'];
        $this->title = $params['title'] ?? null;
        $this->visibility = $params['visibility'] ?? null;
        $this->languages = isset(
            $params['languages']
        ) ? explode(
            ',',
            $params['languages']
        ) : null;
        $this->updatePublished = $params['update_published'] ?? \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS;
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
    }

    /**
     * Update properties of $object based on what is provided in this class.
     *
     * @param object $object
     * @param array<int|string, string|array{transform?: callable(mixed):mixed, important?: bool, alias?: string}> $properties
     * @return bool True if there were changes to any property marked as 'important'.
     */
    public function updateValueParams(
        object $object,
        array $properties
    ): bool {
        $importantChange = false;
        foreach ($properties as $source => $info) {
            /** @var null|callable(mixed):mixed */
            $transform = null;
            $important = false;
            $fieldAlias = null;
            if (is_int($source)) {
                $thisFieldName = $info;
                $objectFieldName = $info;
            } else {
                $thisFieldName = $source;
                if (isset($info['transform'])) {
                    $transform = $info['transform'];
                }
                if (isset($info['important']) && $info['important'] === true) {
                    $important = $info['important'];
                }
                if (!empty($info['alias'])) {
                    $objectFieldName = $info['alias'];
                } else {
                    $objectFieldName = $thisFieldName;
                }
            }
            // Get or calculate new value.
            /** @var null|mixed */
            $value = $this->$thisFieldName;
            if (is_null($value)) {
                continue;
            }
            if (!is_null($transform)) {
                /** @var mixed */
                $value = $transform($value);
            }
            // Important property, so check if it changes.
            if ($important) {
                $importantChange |= ($value != $object->$objectFieldName);
            }
            $object->$objectFieldName = $value;
        }
        return $importantChange;
    }
}
