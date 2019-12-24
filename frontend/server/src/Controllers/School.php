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
     * Gets the top five schools that are showed on the index page.
     * @return array{rank: list<array{school_id: int, name: string, country_id: string, score: float}>}
     */
    public static function apiSchoolsOfTheMonth(\OmegaUp\Request $r) {
        $r->ensureInt('rowcount', null, null, false);
        $rowcount = is_null($r['rowcount']) ? 100 : intval($r['rowcount']);

        $currentDate = new \DateTime(date('Y-m-d', \OmegaUp\Time::get()));
        $firstDayOfNextMonth = $currentDate->modify('first day of next month');
        $date = $firstDayOfNextMonth->format('Y-m-d');

        /** @var list<array{school_id: int, name: string, country_id: string, score: float}> */
        $rank = \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::SCHOOLS_OF_THE_MONTH,
            "{$date}-{$rowcount}",
            function () use (
                $date,
                $rowcount
            ): array {
                return \OmegaUp\DAO\SchoolOfTheMonth::calculateSchoolsOfMonthByGivenDate(
                    $date,
                    $rowcount
                );
            },
            60 * 60 * 12 // 12 hours
        );

        return [
            'rank' => $rank,
        ];
    }

    /**
     * Returns the historical rank of schools
     *
     * @return array{rank: list<array{school_id: int, rank: int, score: float, name: string, country_id: int}>, totalRows: int}
     */
    public static function apiRank(\OmegaUp\Request $r) {
        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);

        $offset = is_null($r['offset']) ? 1 : intval($r['offset']);
        $rowCount = is_null($r['rowcount']) ? 100 : intval($r['rowcount']);

        /** @var array{rank: list<array{school_id: int, country_id: int, rank: int, score: float, name: string}>, totalRows: int} */
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::SCHOOL_RANK,
            "{$offset}-{$rowCount}",
            function () use (
                $offset,
                $rowCount
            ): array {
                return \OmegaUp\DAO\Schools::getRank($offset, $rowCount);
            },
            60 * 60 * 24 // 1 day
        );
    }

    /**
     * Gets the details for historical rank of schools with pagination
     *
     * @return array{smartyProperties: array{schoolRankPayload: array{page: int, length: int, showHeader: bool}}, template: string}
     */
    public static function getRankForSmarty(\OmegaUp\Request $r): array {
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('length', null, null, false);

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null($r['length']) ? 100 : intval($r['length']);

        return [
            'smartyProperties' => [
                'schoolRankPayload' => [
                    'page' => $page,
                    'length' => $length,
                    'showHeader' => false,
                ],
            ],
            'template' => 'rank.schools.tpl',
        ];
    }

    /**
     * Returns the first school of the previous month or the one selected by
     * the mentor, if it has already been stored. Otherwise, calculates and
     * saves the new schools of the month, and returns the first one of them.
     *
     * @return array{schoolinfo: null|array{school_id: int, name: string, country_id: string|null}}
     */
    public static function getSchoolOfTheMonth(string $date = null): array {
        $firstDay = self::getCurrentMonthFirstDay($date);
        $schoolsOfTheMonth = \OmegaUp\DAO\SchoolOfTheMonth::getByTime(
            $firstDay
        );
        if (empty($schoolsOfTheMonth)) {
            // Calculate and store new schools of the month
            $schools = \OmegaUp\DAO\SchoolOfTheMonth::calculateSchoolsOfMonthByGivenDate(
                $firstDay
            );
            if (empty($schools)) {
                return [
                    'schoolinfo' => null,
                ];
            }

            try {
                \OmegaUp\DAO\DAO::transBegin();
                $schoolOfTheMonthId = $schools[0]['school_id'];
                foreach ($schools as $index => $school) {
                    \OmegaUp\DAO\SchoolOfTheMonth::create(
                        new \OmegaUp\DAO\VO\SchoolOfTheMonth([
                            'school_id' => $school['school_id'],
                            'time' => $firstDay,
                            'rank' => $index + 1,
                        ])
                    );
                }
                \OmegaUp\DAO\DAO::transEnd();
            } catch (\Exception $e) {
                \OmegaUp\DAO\DAO::transRollback();
                throw $e;
            }
        } else {
            $schoolOfTheMonthId = $schoolsOfTheMonth[0]->school_id;
            foreach ($schoolsOfTheMonth as $school) {
                if (isset($school->selected_by)) {
                    $schoolOfTheMonthId = $school->school_id;
                    break;
                }
            }
        }

        if (is_null($schoolOfTheMonthId)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'schoolOfTheMonthNotFound'
            );
        }

        // Now get the school data
        $school = \OmegaUp\DAO\Schools::getByPK($schoolOfTheMonthId);

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'schoolOfTheMonthNotFound'
            );
        }

        return [
            'schoolinfo' => [
                'school_id' => intval($school->school_id),
                'name' => strval($school->name),
                'country_id' => $school->country_id,
            ],
        ];
    }

    /**
     * Selects a certain school as school of the month
     *
     * @return array{status: string}
     */
    public static function apiSelectSchoolOfTheMonth(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $currentTimestamp = \OmegaUp\Time::get();

        if (!\OmegaUp\Authorization::isMentor($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        if (
            !\OmegaUp\Authorization::canChooseCoderOrSchool(
                $currentTimestamp
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'schoolOfTheMonthIsNotInPeriodToBeChosen'
            );
        }

        $r->ensureInt('school_id');
        $selectedSchool = \OmegaUp\DAO\Schools::getByPK(
            intval(
                $r['school_id']
            )
        );

        if (is_null($selectedSchool)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        $currentDate = date('Y-m-d', $currentTimestamp);
        $firstDayOfNextMonth = new \DateTime($currentDate);
        $firstDayOfNextMonth->modify('first day of next month');
        $dateToSelect = $firstDayOfNextMonth->format('Y-m-d');

        $schoolsOfTheMonth = \OmegaUp\DAO\SchoolOfTheMonth::getByTime(
            $dateToSelect
        );
        if (!empty($schoolsOfTheMonth)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'schoolOfTheMonthAlreadySelected'
            );
        }

        $schools = \OmegaUp\DAO\SchoolOfTheMonth::calculateSchoolsOfMonthByGivenDate(
            $dateToSelect
        );

        if (empty($schools)) {
            throw new \OmegaUp\Exceptions\NotFoundException('noSchools');
        }

        try {
            \OmegaUp\DAO\DAO::transBegin();
            foreach ($schools as $index => $school) {
                $newSchoolOfTheMonth = new \OmegaUp\DAO\VO\SchoolOfTheMonth([
                    'school_id' => $school['school_id'],
                    'time' => $dateToSelect,
                    'rank' => $index + 1,
                ]);

                if ($school['school_id'] === $selectedSchool->school_id) {
                    $newSchoolOfTheMonth->selected_by = $r->identity->identity_id;
                }
                \OmegaUp\DAO\SchoolOfTheMonth::create($newSchoolOfTheMonth);
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
        return ['status' => 'ok'];
    }
}
