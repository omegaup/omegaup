<?php

namespace OmegaUp;

use \ContestsDAO;

/**
 * ScoreboardParams
 *
 * @author joemmanuel
 */
class ScoreboardParams {
    /** @var string */
    public $alias;

    /** @var string */
    public $title;

    /** @var int */
    public $problemset_id;

    /** @var int */
    public $start_time;

    /** @var int */
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

    /**
     * @param array<string, mixed> $params
     */
    public function __construct(array $params) {
        ScoreboardParams::validateParameter('alias', $params, true /*is_required*/);
        $this->alias = strval($params['alias']);

        ScoreboardParams::validateParameter('title', $params, true /*is_required*/);
        $this->title = strval($params['title']);

        ScoreboardParams::validateParameter('problemset_id', $params, true /*is_required*/);
        $this->problemset_id = intval($params['problemset_id']);

        ScoreboardParams::validateParameter('start_time', $params, true /*is_required*/);
        $this->start_time = is_int($params['start_time'])
            ? $params['start_time']
            : strtotime(strval($params['start_time']));

        ScoreboardParams::validateParameter('finish_time', $params, true /*is_required*/);
        $this->finish_time = is_int($params['finish_time'])
            ? $params['finish_time']
            : strtotime(strval($params['finish_time']));

        ScoreboardParams::validateParameter('acl_id', $params, true /*is_required*/);
        $this->acl_id = intval($params['acl_id']);

        ScoreboardParams::validateParameter('group_id', $params, false /*is_required*/, null);
        $this->group_id = is_null($params['group_id']) ? null : intval($params['group_id']);

        ScoreboardParams::validateParameter('penalty', $params, false /*is_required*/, 0);
        $this->penalty = intval($params['penalty']);

        ScoreboardParams::validateParameter('virtual', $params, false /*is_required */, false);
        $this->virtual = boolval($params['virtual']);

        ScoreboardParams::validateParameter('penalty_calc_policy', $params, false /*is_required*/, 'sum');
        $this->penalty_calc_policy = strval($params['penalty_calc_policy']);

        ScoreboardParams::validateParameter('show_scoreboard_after', $params, false /*is_required*/, 1);
        $this->show_scoreboard_after = boolval($params['show_scoreboard_after']);

        ScoreboardParams::validateParameter('scoreboard_pct', $params, false /*is_required*/, 100);
        $this->scoreboard_pct = intval($params['scoreboard_pct']);

        ScoreboardParams::validateParameter('admin', $params, false /*is_required*/, false);
        $this->admin = boolval($params['admin']);

        ScoreboardParams::validateParameter('auth_token', $params, false /*is_required*/, null);
        $this->auth_token = is_null($params['auth_token']) ? null : strval($params['auth_token']);

        ScoreboardParams::validateParameter('only_ac', $params, false /*is_required*/, false);
        $this->only_ac = boolval($params['only_ac']);

        ScoreboardParams::validateParameter('show_all_runs', $params, false /*is_required*/, true);
        $this->show_all_runs = boolval($params['show_all_runs']);
    }

    public static function fromContest(
        \OmegaUp\DAO\VO\Contests $contest
    ) : ScoreboardParams {
        return new ScoreboardParams([
            'alias' => $contest->alias,
            'title' => $contest->title,
            'problemset_id' => $contest->problemset_id,
            'start_time' => $contest->start_time,
            'finish_time' => $contest->finish_time,
            'acl_id' => $contest->acl_id,
            'penalty' => $contest->penalty,
            'virtual' => ContestsDAO::isVirtual($contest),
            'penalty_calc_policy' => $contest->penalty_calc_policy,
            'show_scoreboard_after' => $contest->show_scoreboard_after,
            'scoreboard_pct' => $contest->scoreboard
        ]);
    }

    public static function fromAssignment(
        \OmegaUp\DAO\VO\Assignments $assignment,
        int $groupId,
        bool $showAllRuns
    ) : ScoreboardParams {
        return new ScoreboardParams([
            'alias' => $assignment->alias,
            'title' => $assignment->name,
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
     * @param array<string, mixed> $array
     * @param boolean $required
     * @param ?mixed $default
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateParameter(
        string $parameter,
        array& $array,
        bool $required = true,
        $default = null
    ) : void {
        if (isset($array[$parameter])) {
            return;
        }
        if ($required) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', $parameter);
        }
        /** @var mixed */
        $array[$parameter] = $default;
    }
}
