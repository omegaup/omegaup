<?php

namespace OmegaUp;

/**
 * ScoreboardParams
 */
class ScoreboardParams {
    /** @var string */
    public $alias;

    /** @var string */
    public $title;

    /** @var int */
    public $problemset_id;

    /** @var \OmegaUp\Timestamp */
    public $start_time;

    /** @var \OmegaUp\Timestamp|null */
    public $finish_time;

    /** @var int */
    public $acl_id;

    /** @var null|int */
    public $group_id;

    /** @var int */
    public $penalty;

    /** @var string */
    public $penalty_calc_policy;

    /** @var bool */
    public $virtual;

    /** @var bool */
    public $show_scoreboard_after;

    /** @var int */
    public $scoreboard_pct;

    /** @var bool */
    public $admin;

    /** @var null|string */
    public $auth_token;

    /** @var bool */
    public $only_ac;

    /** @var bool */
    public $show_all_runs;

    /** @var string */
    public $score_mode;

    /**
     * @param array<string, \OmegaUp\Timestamp|null|int|string|bool> $params
     */
    public function __construct(array $params) {
        ScoreboardParams::validateParameter(
            'alias',
            $params,
            required: true,
        );
        $this->alias = strval($params['alias']);

        ScoreboardParams::validateParameter(
            'title',
            $params,
            required: true,
        );
        $this->title = strval($params['title']);

        ScoreboardParams::validateParameter(
            'problemset_id',
            $params,
            required: true,
        );
        $this->problemset_id = intval($params['problemset_id']);

        ScoreboardParams::validateParameter(
            'start_time',
            $params,
            required: true,
        );
        if ($params['start_time'] instanceof \OmegaUp\Timestamp) {
            $this->start_time = $params['start_time'];
        } else {
            $this->start_time = new \OmegaUp\Timestamp(
                intval(
                    is_string($params['start_time'])
                        ? strtotime(strval($params['start_time']))
                        : $params['start_time']
                )
            );
        }

        ScoreboardParams::validateParameter(
            'finish_time',
            $params,
            required: false,
        );
        if ($params['finish_time'] !== null) {
            if ($params['finish_time'] instanceof \OmegaUp\Timestamp) {
                $this->finish_time = $params['finish_time'];
            } else {
                $this->finish_time = new \OmegaUp\Timestamp(
                    intval(
                        is_string($params['finish_time'])
                            ? strtotime(strval($params['finish_time']))
                            : $params['finish_time']
                    )
                );
            }
        } else {
            $this->finish_time = null;
        }

        ScoreboardParams::validateParameter(
            'acl_id',
            $params,
            required: true,
        );
        $this->acl_id = intval($params['acl_id']);

        ScoreboardParams::validateParameter(
            'group_id',
            $params,
            required: false,
            default: null,
        );
        $this->group_id = $params['group_id'] === null ? null : intval(
            $params['group_id']
        );

        ScoreboardParams::validateParameter(
            'penalty',
            $params,
            required: false,
            default: 0,
        );
        $this->penalty = intval($params['penalty']);

        ScoreboardParams::validateParameter(
            'virtual',
            $params,
            required: false,
            default: false,
        );
        $this->virtual = boolval($params['virtual']);

        ScoreboardParams::validateParameter(
            'penalty_calc_policy',
            $params,
            required: false,
            default: 'sum',
        );
        $this->penalty_calc_policy = strval($params['penalty_calc_policy']);

        ScoreboardParams::validateParameter(
            'show_scoreboard_after',
            $params,
            required: false,
            default: 1,
        );
        $this->show_scoreboard_after = boolval(
            $params['show_scoreboard_after']
        );

        ScoreboardParams::validateParameter(
            'scoreboard_pct',
            $params,
            required: false,
            default: 100,
        );
        $this->scoreboard_pct = intval($params['scoreboard_pct']);

        ScoreboardParams::validateParameter(
            'admin',
            $params,
            required: false,
            default: false,
        );
        $this->admin = boolval($params['admin']);

        ScoreboardParams::validateParameter(
            'auth_token',
            $params,
            required: false,
            default: null,
        );
        $this->auth_token = $params['auth_token'] === null ? null : strval(
            $params['auth_token']
        );

        ScoreboardParams::validateParameter(
            'only_ac',
            $params,
            required: false,
            default: false,
        );
        $this->only_ac = boolval($params['only_ac']);

        ScoreboardParams::validateParameter(
            'show_all_runs',
            $params,
            required: false,
            default: true,
        );
        $this->show_all_runs = boolval($params['show_all_runs']);

        ScoreboardParams::validateParameter(
            'score_mode',
            $params,
            required: false,
            default: 'all_or_nothing',
        );
        $this->score_mode = strval($params['score_mode']);
    }

    public static function fromContest(
        \OmegaUp\DAO\VO\Contests $contest
    ): ScoreboardParams {
        return new ScoreboardParams([
            'alias' => $contest->alias,
            'title' => $contest->title,
            'problemset_id' => $contest->problemset_id,
            'start_time' => $contest->start_time,
            'finish_time' => $contest->finish_time,
            'acl_id' => $contest->acl_id,
            'penalty' => $contest->penalty,
            'virtual' => \OmegaUp\DAO\Contests::isVirtual($contest),
            'penalty_calc_policy' => $contest->penalty_calc_policy,
            'show_scoreboard_after' => $contest->show_scoreboard_after,
            'scoreboard_pct' => $contest->scoreboard,
            'score_mode' => $contest->score_mode,
        ]);
    }

    public static function fromAssignment(
        \OmegaUp\DAO\VO\Assignments $assignment,
        int $groupId,
        bool $showAllRuns
    ): ScoreboardParams {
        return new ScoreboardParams([
            'alias' => $assignment->alias,
            'title' => $assignment->name,
            'admin' => $showAllRuns,
            'problemset_id' => $assignment->problemset_id,
            'start_time' => $assignment->start_time,
            'finish_time' => $assignment->finish_time,
            'acl_id' => $assignment->acl_id,
            'group_id' => $groupId,
            'show_all_runs' => $showAllRuns,
        ]);
    }

    /**
     * Checks if array contains a key defined by $parameter
     * @param string $parameter
     * @param array<string, \OmegaUp\Timestamp|null|int|string|bool> $array
     * @param boolean $required
     * @param \OmegaUp\Timestamp|null|int|string|bool $default
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateParameter(
        string $parameter,
        array &$array,
        bool $required = true,
        $default = null
    ): void {
        if (isset($array[$parameter])) {
            return;
        }
        if ($required) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $parameter
            );
        }
        $array[$parameter] = $default;
    }
}
