<?php

/**
 *  GroupScoreboardController
 *
 * @author joemmanuel
 */

class GroupScoreboardController extends Controller {
    /**
     * Validate group scoreboard request
     *
     * @param $groupAlias
     * @param $identityId
     * @param $scoreboardAlias
     */
    private static function validateGroupScoreboard(
        string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity,
        string $scoreboardAlias
    ) {
        GroupController::validateGroup($groupAlias, $identity);

        \OmegaUp\Validators::validateValidAlias($scoreboardAlias, 'scoreboard_alias');
        $scoreboard = GroupsScoreboardsDAO::getByAlias($scoreboardAlias);
        if (is_null($scoreboard)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'Scoreboard');
        }
        return $scoreboard;
    }

    /**
     * Validates that group alias and contest alias do exist
     *
     * @param $groupAlias
     * @param $identityId
     * @param $scoreboardAlias
     * @param $contestAlias
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateGroupScoreboardAndContest(
        string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity,
        string $scoreboardAlias,
        string $contestAlias
    ) {
        $scoreboard = self::validateGroupScoreboard(
            $groupAlias,
            $identity,
            $scoreboardAlias
        );

        \OmegaUp\Validators::validateValidAlias($contestAlias, 'contest_alias');
        $contest = ContestsDAO::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'Contest');
        }

        if (!ContestController::isPublic($contest->admission_mode) &&
            !Authorization::isContestAdmin($identity, $contest)) {
            throw new ForbiddenAccessException();
        }
        return [
            'contest' => $contest,
            'scoreboard' => $scoreboard,
        ];
    }

    /**
     * Add contest to a group scoreboard
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiAddContest(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        $contestScoreboard = self::validateGroupScoreboardAndContest(
            $r['group_alias'],
            $r->identity,
            $r['scoreboard_alias'],
            $r['contest_alias']
        );

        $r->ensureBool('only_ac');
        $r->ensureFloat('weight');

        GroupsScoreboardsProblemsetsDAO::create(new \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets([
            'group_scoreboard_id' => $contestScoreboard['scoreboard']->group_scoreboard_id,
            'problemset_id' => $contestScoreboard['contest']->problemset_id,
            'only_ac' => $r['only_ac'],
            'weight' => $r['weight'],
        ]));

        self::$log->info(
            "Contest {$r['contest_alias']} added to scoreboard {$r['scoreboard_alias']}"
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Add contest to a group scoreboard
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiRemoveContest(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        $contestScoreboard = self::validateGroupScoreboardAndContest(
            $r['group_alias'],
            $r->identity,
            $r['scoreboard_alias'],
            $r['contest_alias']
        );

        $gscs = GroupsScoreboardsProblemsetsDAO::getByPK(
            $contestScoreboard['scoreboard']->group_scoreboard_id,
            $contestScoreboard['contest']->problemset_id
        );
        if (empty($gscs)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'Contest');
        }

        GroupsScoreboardsProblemsetsDAO::delete($gscs);

        self::$log->info('Contest ' . $r['contest_alias'] . 'removed from group ' . $r['group_alias']);

        return ['status' => 'ok'];
    }

    /**
     * Details of a scoreboard. Returns a list with all contests that belong to
     * the given scoreboard_alias
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        $scoreboard = self::validateGroupScoreboard(
            $r['group_alias'],
            $r->identity,
            $r['scoreboard_alias']
        );

        $response = [];

        // Fill contests
        $response['contests'] = [];
        $response['ranking'] = [];
        $gscs = GroupsScoreboardsProblemsetsDAO::getByGroupScoreboard(
            $scoreboard->group_scoreboard_id
        );
        $i = 0;
        $contest_params = [];
        foreach ($gscs as $gsc) {
            $contest = ContestsDAO::getByProblemset($gsc->problemset_id);
            if (empty($contest)) {
                throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
            }
            $response['contests'][$i] = $contest->asArray();
            $response['contests'][$i]['only_ac'] = $gsc->only_ac;
            $response['contests'][$i]['weight'] = $gsc->weight;

            // Fill contest params to pass to scoreboardMerge
            $contest_params[$contest->alias] = [
                'only_ac' => ($gsc->only_ac == 0) ? false : true,
                'weight' => $gsc->weight
            ];

            $i++;
        }

        $r['contest_params'] = $contest_params;

        // Fill details of this scoreboard
        $response['scoreboard'] = $scoreboard->asArray();

        // If we have contests, calculate merged&filtered scoreboard
        if (!empty($response['contests'])) {
            // Get merged scoreboard
            $r['contest_aliases'] = '';
            foreach ($response['contests'] as $contest) {
                $r['contest_aliases'] .= $contest['alias'] . ',';
            }

            $r['contest_aliases'] = rtrim($r['contest_aliases'], ',');

            $usernames = GroupsIdentitiesDAO::getUsernamesByGroupId($scoreboard->group_id);
            $r['usernames_filter'] = implode(',', $usernames);

            $mergedScoreboardResponse = ContestController::apiScoreboardMerge($r);
            $response['ranking'] = $mergedScoreboardResponse['ranking'];
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Details of a scoreboard
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiList(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        $group = GroupController::validateGroup($r['group_alias'], $r->identity);

        $response = [];
        $response['scoreboards'] = [];
        $scoreboards = GroupsScoreboardsDAO::getByGroup($group->group_id);
        foreach ($scoreboards as $scoreboard) {
            $response['scoreboards'][] = $scoreboard->asArray();
        }

        $response['status'] = 'ok';
        return $response;
    }
}
