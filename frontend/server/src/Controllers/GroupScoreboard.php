<?php

 namespace OmegaUp\Controllers;

/**
 *  GroupScoreboardController
 *
 * @psalm-type ScoreboardContest=array{contest_id: int, problemset_id: int, acl_id: int, title: string, description: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp, last_updated: int, window_length: null|int, rerun_id: int, admission_mode: string, alias: string, scoreboard: int, points_decay_factor: float, score_mode: string, submissions_gap: int, feedback: string, penalty: string, penalty_calc_policy: string, show_scoreboard_after: bool, urgent: bool, languages: string, recommended: bool, only_ac?: bool, weight?: float}
 * @psalm-type ScoreboardRanking=array{name: null|string, username: string, contests: array<string, array{points: float, penalty: float}>, total: array{points: float, penalty: float}}
 * @psalm-type ScoreboardDetails=array{group_scoreboard_id: int, group_id: int, create_time: int, alias: string, name: string, description: string}
 * @psalm-type GroupScoreboardDetails=array{ranking: list<ScoreboardRanking>, scoreboard: ScoreboardDetails, contests: list<ScoreboardContest>}
 * @psalm-type GroupScoreboardDetailsPayload=array{groupAlias: string, details: GroupScoreboardDetails, scoreboardAlias: string}
 */

class GroupScoreboard extends \OmegaUp\Controllers\Controller {
    /**
     * Validate group scoreboard request
     */
    private static function validateGroupScoreboard(
        string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity,
        string $scoreboardAlias
    ): \OmegaUp\DAO\VO\GroupsScoreboards {
        \OmegaUp\Controllers\Group::validateGroupAndOwner(
            $groupAlias,
            $identity
        );

        $scoreboard = \OmegaUp\DAO\GroupsScoreboards::getByAlias(
            $scoreboardAlias
        );
        if ($scoreboard === null) {
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
        string $contestAlias
    ): array {
        $scoreboard = self::validateGroupScoreboard(
            $groupAlias,
            $identity,
            $scoreboardAlias
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if ($contest === null) {
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
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param bool|null $only_ac
     * @omegaup-request-param string $scoreboard_alias
     * @omegaup-request-param float $weight
     */
    public static function apiAddContest(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $scoreboardAlias = $r->ensureString(
            'scoreboard_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contestScoreboard = self::validateGroupScoreboardAndContest(
            $groupAlias,
            $r->identity,
            $scoreboardAlias,
            $contestAlias
        );

        \OmegaUp\DAO\GroupsScoreboardsProblemsets::replace(
            new \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets([
                'group_scoreboard_id' => $contestScoreboard['scoreboard']->group_scoreboard_id,
                'problemset_id' => $contestScoreboard['contest']->problemset_id,
                'only_ac' => $r->ensureBool('only_ac'),
                'weight' => $r->ensureFloat('weight'),
            ])
        );

        self::$log->info(
            "Contest {$contestAlias} added to scoreboard {$scoreboardAlias}"
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Add contest to a group scoreboard
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param string $scoreboard_alias
     */
    public static function apiRemoveContest(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $scoreboardAlias = $r->ensureString(
            'scoreboard_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contestScoreboard = self::validateGroupScoreboardAndContest(
            $groupAlias,
            $r->identity,
            $scoreboardAlias,
            $contestAlias
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
            "Contest {$contestAlias} removed from group {$groupAlias}"
        );

        return ['status' => 'ok'];
    }

    /**
     * Details of a scoreboard. Returns a list with all contests that belong to
     * the given scoreboard_alias
     *
     * @param \OmegaUp\Request $r
     *
     * @return GroupScoreboardDetails
     *
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param string $scoreboard_alias
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $scoreboardAlias = $r->ensureString(
            'scoreboard_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        return self::getScoreboardDetails(
            $groupAlias,
            $r->identity,
            $scoreboardAlias
        );
    }

    /**
     * @return GroupScoreboardDetails
     */
    public static function getScoreboardDetails(
        string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity,
        string $scoreboardAlias
    ): array {
        $scoreboard = self::validateGroupScoreboard(
            $groupAlias,
            $identity,
            $scoreboardAlias
        );

        // Fill contests
        /** @var list<ScoreboardContest> */
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
            /** @var ScoreboardContest */
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
            /** @var ScoreboardContest $contest */
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
     * @return array{scoreboards: list<array{group_scoreboard_id: int, group_id: int, create_time: int, alias: string, name: string, description: string}>}
     *
     * @omegaup-request-param null|string $group_alias
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $group = \OmegaUp\Controllers\Group::validateGroupAndOwner(
            $groupAlias,
            $r->identity
        );
        if ($group === null) {
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

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: GroupScoreboardDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $group
     * @omegaup-request-param string $scoreboard
     */
    public static function getGroupScoreboardDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        $groupAlias = $r->ensureString(
            'group',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $scoreboardAlias = $r->ensureString(
            'scoreboard',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'groupAlias' => $groupAlias,
                    'scoreboardAlias' => $scoreboardAlias,
                    'details' => self::getScoreboardDetails(
                        $groupAlias,
                        $r->identity,
                        $scoreboardAlias
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleGroupScoreboardDetails'
                )
            ],
            'entrypoint' => 'group_scoreboard_details',
        ];
    }
}
