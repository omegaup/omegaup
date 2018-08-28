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
    private static function validateGroupScoreboard($groupAlias, $identityId, $scoreboardAlias) {
        GroupController::validateGroup($groupAlias, $identityId);

        Validators::isValidAlias($scoreboardAlias, 'scoreboard_alias');
        try {
            $scoreboard = GroupsScoreboardsDAO::getByAlias($scoreboardAlias);
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        if (is_null($scoreboard)) {
            throw new InvalidParameterException('parameterNotFound', 'Scoreboard');
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
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    private static function validateGroupScoreboardAndContest($groupAlias, $identityId, $scoreboardAlias, $contestAlias) {
        $scoreboard = self::validateGroupScoreboard($groupAlias, $identityId, $scoreboardAlias);

        Validators::isValidAlias($contestAlias, 'contest_alias');
        try {
            $contest = ContestsDAO::getByAlias($contestAlias);
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        if (is_null($contest)) {
            throw new InvalidParameterException('parameterNotFound', 'Contest');
        }

        if (!ContestController::isPublic($contest->admission_mode) && !Authorization::isContestAdmin($identityId, $contest)) {
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
     * @param Request $r
     */
    public static function apiAddContest(Request $r) {
        self::authenticateRequest($r);
        $contestScoreboard = self::validateGroupScoreboardAndContest($r['group_alias'], $r['current_identity_id'], $r['scoreboard_alias'], $r['contest_alias']);

        Validators::isInEnum($r['only_ac'], 'only_ac', [0,1]);
        Validators::isNumber($r['weight'], 'weight');

        try {
            $groupScoreboardProblemset = new GroupsScoreboardsProblemsets([
                'group_scoreboard_id' => $contestScoreboard['scoreboard']->group_scoreboard_id,
                'problemset_id' => $contestScoreboard['contest']->problemset_id,
                'only_ac' => $r['only_ac'],
                'weight' => $r['weight']
            ]);

            GroupsScoreboardsProblemsetsDAO::save($groupScoreboardProblemset);

            self::$log->info('Contest ' . $r['contest_alias'] . 'added to scoreboard ' . $r['scoreboard_alias']);
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        return ['status' => 'ok'];
    }

    /**
     * Add contest to a group scoreboard
     *
     * @param Request $r
     */
    public static function apiRemoveContest(Request $r) {
        self::authenticateRequest($r);
        $contestScoreboard = self::validateGroupScoreboardAndContest($r['group_alias'], $r['current_identity_id'], $r['scoreboard_alias'], $r['contest_alias']);

        try {
            $gscs = GroupsScoreboardsProblemsetsDAO::getByPK(
                $contestScoreboard['scoreboard']->group_scoreboard_id,
                $contestScoreboard['contest']->problemset_id
            );
            if (empty($gscs)) {
                throw new InvalidParameterException('parameterNotFound', 'Contest');
            }

            GroupsScoreboardsProblemsetsDAO::delete($gscs);

            self::$log->info('Contest ' . $r['contest_alias'] . 'removed from group ' . $r['group_alias']);
        } catch (ApiException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        return ['status' => 'ok'];
    }

    /**
     * Details of a scoreboard. Returns a list with all contests that belong to
     * the given scoreboard_alias
     *
     * @param Request $r
     */
    public static function apiDetails(Request $r) {
        self::authenticateRequest($r);
        $scoreboard = self::validateGroupScoreboard($r['group_alias'], $r['current_identity_id'], $r['scoreboard_alias']);

        $response = [];

        // Fill contests
        $response['contests'] = [];
        $response['ranking'] = [];
        try {
            $gscs = GroupsScoreboardsProblemsetsDAO::getByGroupScoreboard(
                $scoreboard->group_scoreboard_id
            );
            $i = 0;
            $contest_params = [];
            foreach ($gscs as $gsc) {
                $contest = ContestsDAO::getByProblemset($gsc->problemset_id);
                if (empty($contest)) {
                    throw new NotFoundException('contestNotFound');
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
        } catch (ApiException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        $r['contest_params'] = $contest_params;

        // Fill details of this scoreboard
        $response['scoreboard'] = $scoreboard->asArray();

        // If we have contests, calculate merged&filtered scoreboard
        if (count($response['contests']) > 0) {
            // Get merged scoreboard
            $r['contest_aliases'] = '';
            foreach ($response['contests'] as $contest) {
                $r['contest_aliases'] .= $contest['alias'] . ',';
            }

            $r['contest_aliases'] = rtrim($r['contest_aliases'], ',');

            try {
                $usernames = GroupsIdentitiesDAO::getUsernamesByGroupId($scoreboard->group_id);
                $r['usernames_filter'] = implode(',', $usernames);
            } catch (Exception $ex) {
                throw new InvalidDatabaseOperationException($ex);
            }

            $mergedScoreboardResponse = ContestController::apiScoreboardMerge($r);
            $response['ranking'] = $mergedScoreboardResponse['ranking'];
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Details of a scoreboard
     *
     * @param Request $r
     */
    public static function apiList(Request $r) {
        self::authenticateRequest($r);
        $group = GroupController::validateGroup($r['group_alias'], $r['current_identity_id']);

        $response = [];
        $response['scoreboards'] = [];
        try {
            $scoreboards = GroupsScoreboardsDAO::getByGroup($group->group_id);
            foreach ($scoreboards as $scoreboard) {
                $response['scoreboards'][] = $scoreboard->asArray();
            }
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        $response['status'] = 'ok';
        return $response;
    }
}
