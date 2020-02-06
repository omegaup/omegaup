<?php

namespace OmegaUp;

class ProblemParams {
    // Constants for problem visibility.
    const VISIBILITY_DELETED = -10; // Problem that was logically deleted by its owner
    const VISIBILITY_PRIVATE_BANNED = -2; // Problem that was private before it was banned
    const VISIBILITY_PUBLIC_BANNED = -1; // Problem that was public before it was banned
    const VISIBILITY_PRIVATE = 0;
    const VISIBILITY_PUBLIC = 1;
    const VISIBILITY_PROMOTED = 2;

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
     * @var int
     */
    public $validatorTimeLimit;

    /**
     * @readonly
     * @var int
     */
    public $overallWallTimeLimit;

    /**
     * @readonly
     * @var int
     */
    public $extraWallTime;

    /**
     * @readonly
     * @var int|null
     */
    public $memoryLimit;

    /**
     * @readonly
     * @var int
     */
    public $outputLimit;

    /**
     * @readonly
     * @var int
     */
    public $inputLimit;

    /**
     * @readonly
     * @var bool
     */
    public $emailClarifications;

    /**
     * @readonly
     * @var string
     */
    public $order;

    /**
     * @param array{email_clarifications?: bool, extra_wall_time?: int, input_limit?: int, languages?: string, memory_limit?: int, order?: string, output_limit?: int, overall_wall_time_limit?: int, problem_alias: string, selected_tags?: string, source?: string, time_limit?: int, title?: string, update_published?: \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS|\OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS, validator?: \OmegaUp\ProblemParams::VALIDATOR_TOKEN|\OmegaUp\ProblemParams::VALIDATOR_TOKEN_CASELESS|\OmegaUp\ProblemParams::VALIDATOR_TOKEN_NUMERIC|\OmegaUp\ProblemParams::VALIDATOR_LITERAL, validator_time_limit?: int, visibility?: \OmegaUp\ProblemParams::VISIBILITY_DELETED|\OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED|\OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED|\OmegaUp\ProblemParams::VISIBILITY_PRIVATE|\OmegaUp\ProblemParams::VISIBILITY_PUBLIC|\OmegaUp\ProblemParams::VISIBILITY_PROMOTED} $params
     */
    public function __construct($params, bool $isRequired = true) {
        $isUpdate = !$isRequired;
        $visibilityStatements = $isUpdate ? [
            \OmegaUp\ProblemParams::VISIBILITY_PRIVATE,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED,
            \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED,
            \OmegaUp\ProblemParams::VISIBILITY_PROMOTED,
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
        $this->validatorTimeLimit = $params['validator_time_limit'] ?? 1000;
        $this->overallWallTimeLimit = $params['overall_wall_time_limit'] ?? 60000;
        $this->extraWallTime = $params['extra_wall_time'] ?? 0;
        $this->memoryLimit = $params['memory_limit'] ?? null;
        $this->outputLimit = $params['output_limit'] ?? 10240;
        $this->inputLimit = $params['input_limit'] ?? 10240;
        $this->emailClarifications = $params['email_clarifications'] ?? false;
        $this->order = $params['order'] ?? 'normal';
    }

    /**
     * Update properties of $object based on what is provided in this class.
     *
     * @param object $object
     * @param array<int|string, string|array{transform?: callable(mixed):mixed, important?: bool}> $properties
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
            if (is_int($source)) {
                $fieldName = $info;
            } else {
                $fieldName = $source;
                if (
                    isset($info['transform']) &&
                    is_callable($info['transform'])
                ) {
                    $transform = $info['transform'];
                }
                if (isset($info['important']) && $info['important'] === true) {
                    $important = $info['important'];
                }
            }
            $value = null;
            if (is_null($this->$fieldName)) {
                continue;
            }
            // Get or calculate new value.
            /** @var null|mixed */
            $value = $this->$fieldName;
            if (is_null($value)) {
                continue;
            }
            if (!is_null($transform)) {
                /** @var mixed */
                $value = $transform($value);
            }
            // Important property, so check if it changes.
            if ($important) {
                $importantChange |= ($value != $object->$fieldName);
            }
            $object->$fieldName = $value;
        }
        return $importantChange;
    }
}
