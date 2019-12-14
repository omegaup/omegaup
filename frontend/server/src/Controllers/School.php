<?php

 namespace OmegaUp\Controllers;

/**
 * Description of SchoolController
 *
 * @author joemmanuel
 */
class School extends \OmegaUp\Controllers\Controller {
    /**
     * Gets a list of schools
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiList(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        $param = '';
        if (!is_null($r['term'])) {
            $param = 'term';
        } elseif (!is_null($r['query'])) {
            $param = 'query';
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'query'
            );
        }

        $schools = \OmegaUp\DAO\Schools::findByName($r[$param]);
        if (is_null($schools)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        $response = [];
        foreach ($schools as $school) {
            $entry = ['label' => $school->name, 'value' => $school->name, 'id' => $school->school_id];
            array_push($response, $entry);
        }

        return $response;
    }

    /**
     * Returns the basic details for school
     * @param \OmegaUp\Request $r
     * @return array{template: string, smartyProperties: array{details: array{school_id: int, school_name: string, rank: int, country: array{id: string, name: string}|null, state_name: string|null}}}
     */
    public static function getSchoolProfileDetailsForSmarty(\OmegaUp\Request $r): array {
        $r->ensureInt('school_id');
        $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        $details = [
            'school_id' => intval($school->school_id),
            'school_name' => strval($school->name),
            'rank' => intval($school->rank),
            'country' => null,
            'state_name' => null,
        ];

        if (!is_null($school->country_id)) {
            $country = \OmegaUp\DAO\Countries::getByPK(
                strval(
                    $school->country_id
                )
            );
            if (!is_null($country)) {
                $details['country'] = [
                    'id' => strval($country->country_id),
                    'name' => strval($country->name),
                ];
            }

            if (!is_null($school->state_id)) {
                $state = \OmegaUp\DAO\States::getByPK(
                    strval($school->country_id),
                    strval($school->state_id)
                );
                if (!is_null($state)) {
                    $details['state_name'] = $state->name;
                }
            }
        }

        return [
            'smartyProperties' => [
                'details' => $details
            ],
            'template' => 'school.profile.tpl'  ,
        ];
    }

    /**
     * Api to create new school
     *
     * @param \OmegaUp\Request $r
     * @return array{status: string, school_id: int}
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['country_id'],
            'country_id'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['state_id'],
            'country_id'
        );

        $state = null;
        if (!is_null($r['country_id']) && !is_null($r['state_id'])) {
            $state = \OmegaUp\DAO\States::getByPK(
                $r['country_id'],
                $r['state_id']
            );
        }

        return [
            'status' => 'ok',
            'school_id' => self::createSchool($r['name'], $state)
        ];
    }

    /**
     * Create new school
     * @param string $name
     * @param null|\OmegaUp\DAO\VO\States $state
     * @return int the school ID
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function createSchool(
        string $name,
        ?\OmegaUp\DAO\VO\States $state
    ): int {
        // Create school object
        $school = new \OmegaUp\DAO\VO\Schools([
            'name' => $name,
            'country_id' => !is_null($state) ? $state->country_id : null,
            'state_id' => !is_null($state) ? $state->state_id : null,
        ]);

        $school_id = 0;
        $existing = \OmegaUp\DAO\Schools::findByName($name);
        if (!empty($existing)) {
            /** @var int $existing[0]->school_id */
            return $existing[0]->school_id;
        }
        \OmegaUp\DAO\Schools::create($school);
        /** @var int $school->school_id */
        return $school->school_id;
    }

    /**
     * Ensures that all the numeric parameters have valid values.
     *
     * @param \OmegaUp\Request $r
     * @return array{offset: int, rowcount: int, start_time: int, finish_time: int, can_use_cache: bool}
     */
    private static function validateRankDetails(\OmegaUp\Request $r): array {
        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', 5, 100, false);
        $r->ensureInt('start_time', null, null, false);
        $r->ensureInt('finish_time', null, null, false);

        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            if (!is_null($r['start_time'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'paramterInvalid',
                    'start_time'
                );
            }
            if (!is_null($r['finish_time'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'paramterInvalid',
                    'finish_time'
                );
            }
            // Both endpoints were not specified, so the API can be used
            // unauthenticated since it'll be cached.
        }

        return [
            'offset' => intval($r['offset']) ?: 0,
            'rowcount' => intval($r['rowcount']) ?: 100,
            'start_time' => intval($r['start_time']) ?:
                            strtotime(
                                'first day of this month',
                                \OmegaUp\Time::get()
                            ),
            'finish_time' => intval($r['finish_time']) ?:
                            strtotime(
                                'first day of next month',
                                \OmegaUp\Time::get()
                            ),
            'can_use_cache' => is_null(
                $r['start_time']
            ) && is_null(
                $r['finish_time']
            )
        ];
    }

    /**
     * Returns rank of best schools in last month
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiRank(\OmegaUp\Request $r) {
        [
            'offset' => $offset,
            'rowcount' => $rowCount,
            'start_time' => $startTime,
            'finish_time' => $finishTime,
            'can_use_cache' => $canUseCache,
        ] = self::validateRankDetails($r);
        return [
            'status' => 'ok',
            'rank' => self::getSchoolsRank(
                $offset,
                $rowCount,
                $startTime,
                $finishTime,
                $canUseCache
            ),
        ];
    }

    /**
     * Returns rank of best schools in last month
     *
     * @param \OmegaUp\Request $r
     * @return array{coders: array{time: string, username: string, classname: string}[]}
     */
    public static function apiSchoolCodersOfTheMonth(\OmegaUp\Request $r): array {
        $r->ensureInt('school_id');
        $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        return [
            'coders' => \OmegaUp\DAO\CoderOfTheMonth::getCodersOfTheMonthFromSchool(
                intval($school->school_id)
            )
        ];
    }

    /**
     * Returns the number of solved problems on the last X
     * months (including the current one)
     * @param \OmegaUp\Request $r
     * @return array{distinct_problems_solved: array{year: int, month: int, count: int}[], status: string}
     */
    public static function apiMonthlySolvedProblemsCount(\OmegaUp\Request $r): array {
        $r->ensureInt('school_id');
        $r->ensureInt('months_count');
        $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        return [
            'distinct_problems_solved' => \OmegaUp\DAO\Schools::getMonthlySolvedProblemsCount(
                intval($r['school_id']),
                intval($r['months_count'])
            ),
            'status' => 'ok'
        ];
    }

    /**
     * Returns the list of current students registered in a certain school
     * with the number of created problems, solved problems and organized contests.
     *
     * @param \OmegaUp\Request $r
     * @return array{status: string, users: array{username: string, classname: string, created_problems: int, solved_problems: int, organized_contests: int}[]}
     */
    public static function apiUsers(\OmegaUp\Request $r): array {
        $r->ensureInt('school_id');
        $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        return [
            'status' => 'ok',
            'users' => \OmegaUp\DAO\Schools::getUsersFromSchool(
                intval($school->school_id)
            ),
        ];
    }

    /**
     * Returns rank of best schools in last month
     *
     * @param int $offset
     * @param int $rowCount
     * @param int $startTime
     * @param int $finishTime
     * @param bool $canUseCache
     * @return list<array{school_id: int, name: string, country_id: string, score: float}>
     */
    private static function getSchoolsRank(
        int $offset,
        int $rowCount,
        int $startTime,
        int $finishTime,
        bool $canUseCache
    ): array {
        $fetch = function () use (
            $offset,
            $rowCount,
            $startTime,
            $finishTime
        ): array {
            return \OmegaUp\DAO\Schools::getRankByProblemsScore(
                $startTime,
                $finishTime,
                $offset,
                $rowCount
            );
        };

        if ($canUseCache) {
            /**
             * @var list<array{school_id: int, name: string, country_id: string, score: float}>
             */
            return \OmegaUp\Cache::getFromCacheOrSet(
                \OmegaUp\Cache::SCHOOL_RANK,
                "{$offset}-{$rowCount}",
                $fetch,
                60 * 60 * 24 // 1 day
            );
        }
        return $fetch();
    }

    /**
     * Gets the rank of best schools in last month with smarty format
     *
     * @return array{smartyProperties: array{schoolRankPayload: array{rank: list<array{school_id: int, name: string, country_id: string, score: float}>, rowCount: int}}, template: string}
     */
    public static function getSchoolsRankForSmarty(int $rowCount = 100): array {
        return [
            'smartyProperties' => \OmegaUp\Controllers\School::getSchoolsRankList(
                $rowCount
            ),
            'template' => 'rank.schools.tpl'
        ];
    }

    /**
     * @return array{schoolRankPayload: array{rank: list<array{school_id: int, name: string, country_id: string, score: float}>, rowCount: int}}
     */
    public static function getSchoolsRankList(int $rowCount) {
        return [
            'schoolRankPayload' => [
                'rowCount' => $rowCount,
                'rank' => self::getSchoolsRank(
                    /*$offset=*/0,
                    $rowCount,
                    /*$startTime=*/strtotime(
                        'first day of this month',
                        \OmegaUp\Time::get()
                    ),
                    /*$finishTime=*/strtotime(
                        'first day of next month',
                        \OmegaUp\Time::get()
                    ),
                    /*$canUseCache=*/true
                ),
            ],
        ];
    }
}
