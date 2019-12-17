<?php

namespace OmegaUp;

class ProblemParams {
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
     * @var \OmegaUp\Controllers\Problem::VISIBILITY_DELETED|\OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE_BANNED|\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED|\OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE|\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC|\OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED
     */
    public $visibility = \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE;

    /**
     * @readonly
     * @var null|list<string>
     */
    public $languages;

    /**
     * @readonly
     * @var \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NONE|\OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NON_PROBLEMSET|\OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_OWNED_PROBLEMSETS|\OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS
     */
    public $updatePublished = \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS;

    /**
     * @readonly
     * @var null|string
     */
    public $selectedTagsAsJSON = null;

    /**
     * @readonly
     * @var string
     */
    public $source;

    /**
     * @readonly
     * @var \OmegaUp\Controllers\Problem::VALIDATOR_TOKEN|\OmegaUp\Controllers\Problem::VALIDATOR_TOKEN_CASELESS|\OmegaUp\Controllers\Problem::VALIDATOR_TOKEN_NUMERIC|\OmegaUp\Controllers\Problem::VALIDATOR_LITERAL
     */
    public $validator = \OmegaUp\Controllers\Problem::VALIDATOR_TOKEN;

    /**
     * @readonly
     * @var int
     */
    public $timeLimit = 1000;

    /**
     * @readonly
     * @var int
     */
    public $validatorTimeLimit = 1000;

    /**
     * @readonly
     * @var int
     */
    public $overallWallTimeLimit = 60000;

    /**
     * @readonly
     * @var int
     */
    public $extraWallTime = 0;

    /**
     * @readonly
     * @var int
     */
    public $memoryLimit = 32768;

    /**
     * @readonly
     * @var int
     */
    public $outputLimit = 10240;

    /**
     * @readonly
     * @var int
     */
    public $inputLimit = 10240;

    /**
     * @readonly
     * @var bool
     */
    public $emailClarifications = false;

    /**
     * @param array{email_clarifications?: bool, extra_wall_time?: int, input_limit?: int, languages?: string, memory_limit?: int, output_limit?: int, overall_wall_time_limit?: int, problem_alias: string, selected_tags?: string, source?: string, time_limit?: int, title?: string, update_published?: \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NONE|\OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NON_PROBLEMSET|\OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_OWNED_PROBLEMSETS|\OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS, validator?: \OmegaUp\Controllers\Problem::VALIDATOR_TOKEN|\OmegaUp\Controllers\Problem::VALIDATOR_TOKEN_CASELESS|\OmegaUp\Controllers\Problem::VALIDATOR_TOKEN_NUMERIC|\OmegaUp\Controllers\Problem::VALIDATOR_LITERAL, validator_time_limit?: int, visibility?: \OmegaUp\Controllers\Problem::VISIBILITY_DELETED|\OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE_BANNED|\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED|\OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE|\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC|\OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED} $params
     */
    public function __construct($params) {
        $isRequired = true;
        $this->problemAlias = $params['problem_alias'];
        $this->title = $params['title'] ?? $this->title;
        $this->visibility = $params['visibility'] ?? $this->visibility;
        $this->languages = isset(
            $params['languages']
        ) ? explode(
            ',',
            $params['languages']
        ) : null;
        $this->updatePublished = $params['update_published'] ?? $this->updatePublished;
        $this->selectedTagsAsJSON = $params['selected_tags'] ?? $this->selectedTagsAsJSON;
        $this->source = $params['source'] ?? '';
        $this->validator = $params['validator'] ?? $this->validator;
        $this->timeLimit = $params['time_limit'] ?? $this->timeLimit;
        $this->validatorTimeLimit = $params['validator_time_limit'] ?? $this->validatorTimeLimit;
        $this->overallWallTimeLimit = $params['overall_wall_time_limit'] ?? $this->overallWallTimeLimit;
        $this->extraWallTime = $params['extra_wall_time'] ?? $this->extraWallTime;
        $this->memoryLimit = $params['memory_limit'] ?? $this->memoryLimit;
        $this->outputLimit = $params['output_limit'] ?? $this->outputLimit;
        $this->inputLimit = $params['input_limit'] ?? $this->inputLimit;
        $this->emailClarifications = $params['email_clarifications'] ?? $this->emailClarifications;
    }
}
