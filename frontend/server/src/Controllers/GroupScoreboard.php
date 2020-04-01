<?php

 namespace OmegaUp\Controllers;

/**
 *  GroupScoreboardController
 *
 * @author joemmanuel
 */

class GroupScoreboard extends \OmegaUp\Controllers\Controller {
    /**
     * Validate group scoreboard request
     */
    private static function validateGroupScoreboard(
        string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity,
        ?string $scoreboardAlias
    ): \OmegaUp\DAO\VO\GroupsScoreboards {
        \OmegaUp\Controllers\Group::validateGroup($groupAlias, $identity);

        \OmegaUp\Validators::validateValidAlias(
            $scoreboardAlias,
            'scoreboard_alias'
        );
        $scoreboard = \OmegaUp\DAO\GroupsScoreboards::getByAlias(
            $scoreboardAlias
        );
        if (is_null($scoreboard)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'Scoreboard'
            );
        }
        return $scoreboard;
    }

    /**
     * Validates that group alias and contest alias do exist
     * @return array{contest: \OmegaUp\DAO\VO\Contests, scoreboard: \OmegaUp\DAO\VO\GroupsScoreboards}
     */
    private static function validateGroupScoreboardAndContest(
        string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity,
        string $scoreboardAlias,
        ?string $contestAlias
    ): array {
        $scoreboard = self::validateGroupScoreboard(
            $groupAlias,
            $identity,
            $scoreboardAlias
        );

        \OmegaUp\Validators::validateValidAlias($contestAlias, 'contest_alias');
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'Contest'
            );
        }

        if (
            !\OmegaUp\Controllers\Contest::isPublic($contest->admission_mode) &&
            !\OmegaUp\Authorization::isContestAdmin($identity, $contest)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        return [
            'contest' => $contest,
            'scoreboard' => $scoreboard,
        ];
    }

    /**
     * Add contest to a group scoreboard
     *
     * @omegaup-request-param mixed $contest_alias
     * @omegaup-request-param mixed $group_alias
     * @omegaup-request-param mixed $only_ac
     * @omegaup-request-param mixed $scoreboard_alias
     * @omegaup-request-param mixed $weight
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     */
    public static function apiAddContest(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['group_alias'],
            'group_alias'
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['scoreboard_alias'],
            'scoreboard_alias'
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['contest_alias'],
            'contest_alias'
        );
        $contestScoreboard = self::validateGroupScoreboardAndContest(
            $r['group_alias'],
            $r->identity,
            $r['scoreboard_alias'],
            $r['contest_alias']
        );

        $r->ensureBool('only_ac');
        $r->ensureFloat('weight');

        \OmegaUp\DAO\GroupsScoreboardsProblemsets::create(new \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets([
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
     * @omegaup-request-param mixed $contest_alias
     * @omegaup-request-param mixed $group_alias
     * @omegaup-request-param mixed $scoreboard_alias
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     */
    public static function apiRemoveContest(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['group_alias'],
            'group_alias'
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['scoreboard_alias'],
            'scoreboard_alias'
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['contest_alias'],
            'contest_alias'
        );
        $contestScoreboard = self::validateGroupScoreboardAndContest(
            $r['group_alias'],
            $r->identity,
            $r['scoreboard_alias'],
            $r['contest_alias']
        );

        $gscs = \OmegaUp\DAO\GroupsScoreboardsProblemsets::getByPK(
            $contestScoreboard['scoreboard']->group_scoreboard_id,
            $contestScoreboard['contest']->problemset_id
        );
        if (empty($gscs)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'Contest'
            );
        }

        \OmegaUp\DAO\GroupsScoreboardsProblemsets::delete($gscs);

        self::$log->info(
            "Contest {$r['contest_alias']} removed from group {$r['group_alias']}"
        );

        return ['status' => 'ok'];
    }

    /**
     * Details of a scoreboard. Returns a list with all contests that belong to
     * the given scoreboard_alias
     *
     * @omegaup-request-param mixed $group_alias
     * @omegaup-request-param mixed $scoreboard_alias
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{ranking: list<array{name: null|string, username: string, contests: array<string, array{points: float, penalty: float}>, total: array{points: float, penalty: float}}>, scoreboard: array{group_scoreboard_id: int, group_id: int, create_time: int, alias: string, name: string, description: string}, contests: list<array{contest_id: int, problemset_id: int, acl_id: int, title: string, description: string, start_time: int, finish_time: int, last_updated: int, window_length: null|int, rerun_id: int, admission_mode: string, alias: string, scoreboard: int, points_decay_factor: float, partial_score: bool, submissions_gap: int, feedback: string, penalty: string, penalty_calc_policy: string, show_scoreboard_after: bool, urgent: bool, languages: string, recommended: bool, only_ac?: bool, weight?: float}>}
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['group_alias'],
            'group_alias'
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['scoreboard_alias'],
            'scoreboard_alias'
        );
        $scoreboard = self::validateGroupScoreboard(
            $r['group_alias'],
            $r->identity,
            $r['scoreboard_alias']
        );

        // Fill contests
        /** @var list<array{contest_id: int, problemset_id: int, acl_id: int, title: string, description: string, start_time: int, finish_time: int, last_updated: int, window_length: null|int, rerun_id: int, admission_mode: string, alias: string, scoreboard: int, points_decay_factor: float, partial_score: bool, submissions_gap: int, feedback: string, penalty: string, penalty_calc_policy: string, show_scoreboard_after: bool, urgent: bool, languages: string, recommended: bool, only_ac?: bool, weight?: float}> */
        $contests = [];
        $gscs = \OmegaUp\DAO\GroupsScoreboardsProblemsets::getByGroupScoreboard(
            intval($scoreboard->group_scoreboard_id)
        );

        /** @var array<string, array{only_ac: bool, weight: float}> */
        $contestParams = [];
        foreach ($gscs as $gsc) {
            $contest = \OmegaUp\DAO\Contests::getByProblemset(
                intval($gsc->problemset_id)
            );
            if (empty($contest)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }
            /** @var array{contest_id: int, problemset_id: int, acl_id: int, title: string, description: string, start_time: int, finish_time: int, last_updated: int, window_length: null|int, rerun_id: int, admission_mode: string, alias: string, scoreboard: int, points_decay_factor: float, partial_score: bool, submissions_gap: int, feedback: string, penalty: string, penalty_calc_policy: string, show_scoreboard_after: bool, urgent: bool, languages: string, recommended: bool} */
            $currentContest = $contest->asArray();
            $currentContest['only_ac'] = $gsc->only_ac;
            $currentContest['weight'] = floatval($gsc->weight);

            // Fill contest params to pass to scoreboardMerge
            $contestParams[strval($contest->alias)] = [
                'only_ac' => boolval($gsc->only_ac),
                'weight' => floatval($gsc->weight),
            ];

            $contests[] = $currentContest;
        }

        // Fill details of this scoreboard
        $response = [
            'ranking' => [],
            'contests' => $contests,
        ];
        /** @var array{alias: string, create_time: int, description: string, group_id: int, group_scoreboard_id: int, name: string} */
        $response['scoreboard'] = $scoreboard->asArray();

        // If we have contests, calculate merged&filtered scoreboard
        if (!empty($contests)) {
            // Get merged scoreboard
            /** @var list<string> */
            $contestAliases = [];
            /** @var array{contest_id: int, problemset_id: int, acl_id: int, title: string, description: string, start_time: int, finish_time: int, last_updated: int, window_length: null|int, rerun_id: int, admission_mode: string, alias: string, scoreboard: int, points_decay_factor: float, partial_score: bool, submissions_gap: int, feedback: string, penalty: string, penalty_calc_policy: string, show_scoreboard_after: bool, urgent: bool, languages: string, recommended: bool, only_ac?: bool, weight?: float} $contest */
            foreach ($contests as $contest) {
                $contestAliases[] = $contest['alias'];
            }

            $usernames = \OmegaUp\DAO\GroupsIdentities::getUsernamesByGroupId(
                intval($scoreboard->group_id)
            );

            $response['ranking'] = \OmegaUp\Controllers\Contest::getMergedScoreboard(
                $contestAliases,
                $usernames,
                $contestParams
            );
        }

        return $response;
    }

    /**
     * Details of a scoreboard
     *
     * @omegaup-request-param mixed $group_alias
     *
     * @return array{scoreboards: list<array{group_scoreboard_id: int, group_id: int, create_time: int, alias: string, name: string, description: string}>}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['group_alias'],
            'group_alias'
        );
        $group = \OmegaUp\Controllers\Group::validateGroup(
            $r['group_alias'],
            $r->identity
        );
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group_alias'
            );
        }

        $scoreboards = [];
        $groupScoreboards = \OmegaUp\DAO\GroupsScoreboards::getByGroup(
            intval($group->group_id)
        );
        foreach ($groupScoreboards as $scoreboard) {
            /** @var array{group_scoreboard_id: int, group_id: int, create_time: int, alias: string, name: string, description: string} */
            $scoreboards[] = $scoreboard->asArray();
        }

        return [
            'scoreboards' => $scoreboards,
        ];
    }
}
