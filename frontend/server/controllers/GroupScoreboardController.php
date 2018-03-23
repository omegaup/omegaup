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
     * @param Request $r
     */
    private static function validateGroupScoreboard(Request $r) {
        GroupController::validateGroup($r);

        Validators::isValidAlias($r['scoreboard_alias'], 'scoreboard_alias');
        try {
            $r['scoreboards'] = GroupsScoreboardsDAO::search(new GroupsScoreboards([
                'alias' => $r['scoreboard_alias']
            ]));
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        if (is_null($r['scoreboards']) || count($r['scoreboards']) === 0 || is_null($r['scoreboards'][0])) {
            throw new InvalidParameterException('parameterNotFound', 'Scoreboard');
        }

        $r['scoreboard'] = $r['scoreboards'][0];
    }

    /**
     * Validates that group alias and contest alias do exist
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    private static function validateGroupScoreboardAndContest(Request $r) {
        self::validateGroupScoreboard($r);

        Validators::isValidAlias($r['contest_alias'], 'contest_alias');
        try {
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        if (is_null($r['contest'])) {
            throw new InvalidParameterException('parameterNotFound', 'Contest');
        }

        if ($r['contest']->public != '1' && !Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Add contest to a group scoreboard
     *
     * @param Request $r
     */
    public static function apiAddContest(Request $r) {
        self::validateGroupScoreboardAndContest($r);

        Validators::isInEnum($r['only_ac'], 'only_ac', [0,1]);
        Validators::isNumber($r['weight'], 'weight');

        try {
            $groupScoreboardProblemset = new GroupsScoreboardsProblemsets([
                'group_scoreboard_id' => $r['scoreboard']->group_scoreboard_id,
                'problemset_id' => $r['contest']->problemset_id,
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
        self::validateGroupScoreboardAndContest($r);

        try {
            $groupScoreboardProblemsetKey = new GroupsScoreboardsProblemsets([
                'group_scoreboard_id' => $r['scoreboard']->group_scoreboard_id,
                'problemset_id' => $r['contest']->problemset_id
            ]);

            $gscs = GroupsScoreboardsProblemsetsDAO::search($groupScoreboardProblemsetKey);
            if (is_null($gscs) || count($gscs) === 0) {
                throw new InvalidParameterException('parameterNotFound', 'Contest');
            }

            GroupsScoreboardsProblemsetsDAO::delete($groupScoreboardProblemsetKey);

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
        self::validateGroupScoreboard($r);

        $response = [];

        // Fill contests
        $response['contests'] = [];
        $response['ranking'] = [];
        try {
            $groupScoreboardProblemsetKey = new GroupsScoreboardsProblemsets([
                'group_scoreboard_id' => $r['scoreboard']->group_scoreboard_id,
            ]);

            $r['gscs'] = GroupsScoreboardsProblemsetsDAO::search($groupScoreboardProblemsetKey);
            $i = 0;
            $contest_params = [];
            foreach ($r['gscs'] as $gsc) {
                $contest = ContestsDAO::getByProblemset($gsc->problemset_id);
                if (is_null($contest)) {
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
        $response['scoreboard'] = $r['scoreboard']->asArray();

        // If we have contests, calculate merged&filtered scoreboard
        if (count($response['contests']) > 0) {
            // Get merged scoreboard
            $r['contest_aliases'] = '';
            foreach ($response['contests'] as $contest) {
                $r['contest_aliases'] .= $contest['alias'] . ',';
            }

            $r['contest_aliases'] = rtrim($r['contest_aliases'], ',');

            try {
                $groupUsers = GroupsUsersDAO::search(new GroupsUsers([
                    'group_id' => $r['scoreboard']->group_id
                ]));

                $r['usernames_filter'] = '';
                foreach ($groupUsers as $groupUser) {
                    $user = UsersDAO::getByPK($groupUser->user_id);
                    $r['usernames_filter'] .= $user->username . ',';
                }

                $r['usernames_filter'] = rtrim($r['usernames_filter'], ',');
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
        GroupController::validateGroup($r);

        $response = [];
        $response['scoreboards'] = [];
        try {
            $key = new GroupsScoreboards([
                'group_id' => $r['group']->group_id
            ]);

            $scoreboards = GroupsScoreboardsDAO::search($key);
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
