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
     * @omegaup-request-param mixed $query
     * @omegaup-request-param mixed $term
     *
     * @return list<array{id: int, label: string, value: string}>
     */
    public static function apiList(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        $param = '';
        if (is_string($r['term'])) {
            $param = $r['term'];
        } elseif (is_string($r['query'])) {
            $param = $r['query'];
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'query'
            );
        }

        $response = [];
        foreach (\OmegaUp\DAO\Schools::findByName($param) as $school) {
            $response[] = [
                'label' => strval($school->name),
                'value' => strval($school->name),
                'id' => intval($school->school_id),
            ];
        }

        return $response;
    }

    /**
     * Returns the basic details for school
     *
     * @omegaup-request-param mixed $school_id
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{template: string, smartyProperties: array{details: array{school_id: int, school_name: string, ranking: int, country: array{id: string, name: string}|null, state_name: string|null}}}
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
            'ranking' => intval($school->ranking),
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
     * @omegaup-request-param mixed $country_id
     * @omegaup-request-param mixed $name
     * @omegaup-request-param mixed $state_id
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{school_id: int}
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
            'school_id' => self::createSchool($r['name'], $state),
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
     * @omegaup-request-param mixed $school_id
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{coders: list<array{time: string, username: string, classname: string}>}
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
     * Returns the number of solved problems on the last
     * months (including the current one)
     *
     * @omegaup-request-param mixed $school_id
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{distinct_problems_solved: list<array{month: int, problems_solved: int, year: int}>}
     */
    public static function apiMonthlySolvedProblemsCount(\OmegaUp\Request $r): array {
        $r->ensureInt('school_id');
        $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        return [
            'distinct_problems_solved' => \OmegaUp\DAO\Schools::getMonthlySolvedProblemsCount(
                intval($r['school_id'])
            ),
        ];
    }

    /**
     * Returns the list of current students registered in a certain school
     * with the number of created problems, solved problems and organized contests.
     *
     * @omegaup-request-param mixed $school_id
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{users: list<array{username: string, classname: string, created_problems: int, solved_problems: int, organized_contests: int}>}
     */
    public static function apiUsers(\OmegaUp\Request $r): array {
        $r->ensureInt('school_id');
        $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        return [
            'users' => \OmegaUp\DAO\Schools::getUsersFromSchool(
                intval($school->school_id)
            ),
        ];
    }

    /**
     * Gets the top X schools of the month
     * @return list<array{name: string, ranking: int, school_id: int, school_of_the_month_id: int, score: float}>
     */
    public static function getTopSchoolsOfTheMonth(
        int $rowcount
    ): array {
        $currentDate = new \DateTime(date('Y-m-d', \OmegaUp\Time::get()));
        $firstDayOfNextMonth = $currentDate->modify('first day of next month');
        $date = $firstDayOfNextMonth->format('Y-m-d');
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::SCHOOLS_OF_THE_MONTH,
            "{$date}-{$rowcount}",
            /** @return list<array{name: string, ranking: int, school_id: int, school_of_the_month_id: int, score: float}> */
            function () use (
                $rowcount
            ): array {
                return \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth(
                    $rowcount
                );
            },
            60 * 60 * 12 // 12 hours
        );
    }

    /**
     * Returns the historical rank of schools
     *
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param mixed $rowcount
     *
     * @return array{rank: list<array{country_id: string|null, name: string, ranking: int|null, school_id: int, score: float}>, totalRows: int}
     */
    public static function apiRank(\OmegaUp\Request $r) {
        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);

        $offset = is_null($r['offset']) ? 1 : intval($r['offset']);
        $rowCount = is_null($r['rowcount']) ? 100 : intval($r['rowcount']);

        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::SCHOOL_RANK,
            "{$offset}-{$rowCount}",
            /** @return array{rank: list<array{country_id: string|null, name: string, ranking: int|null, school_id: int, score: float}>, totalRows: int} */
            function () use (
                $offset,
                $rowCount
            ): array {
                return \OmegaUp\DAO\Schools::getRank($offset, $rowCount);
            },
            3600 // 1 hour
        );
    }

    /**
     * Gets the details for historical rank of schools with pagination
     *
     * @omegaup-request-param mixed $length
     * @omegaup-request-param mixed $page
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
     * Gets all the information to be sent to smarty for the tabs
     * of School of the Month
     * @return array{template: string, smartyProperties: array{schoolOfTheMonthPayload: array{candidatesToSchoolOfTheMonth: list<array{country_id: string, name: string, ranking: int, school_id: int, school_of_the_month_id: int, score: float}>, schoolsOfPreviousMonths: list<array{school_id: int, name: string, country_id: string, time: string}>, schoolsOfCurrentMonth: list<array{school_id: int, ranking: int, name: string, country_id: string}>, isMentor: bool, options?: array{canChooseSchool: bool, schoolIsSelected: bool}}}}
     */
    public static function getSchoolOfTheMonthDetailsForSmarty(\OmegaUp\Request $r): array {
        try {
            $r->ensureIdentity();
            $identity = $r->identity;
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $identity = null;
        }

        $isMentor = !is_null(
            $identity
        ) && \OmegaUp\Authorization::isMentor(
            $identity
        );

        $currentTimestamp = \OmegaUp\Time::get();
        $currentDate = date('Y-m-d', $currentTimestamp);

        $response = [
            'smartyProperties' => [
                'schoolOfTheMonthPayload' => [
                    'schoolsOfPreviousMonths' => \OmegaUp\DAO\SchoolOfTheMonth::getSchoolsOfTheMonth(),
                    'schoolsOfCurrentMonth' => \OmegaUp\DAO\SchoolOfTheMonth::getMonthlyList(
                        $currentDate
                    ),
                    'candidatesToSchoolOfTheMonth' => \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth(),
                    'isMentor' => $isMentor,
                ],
            ],
            'template' => 'schoolofthemonth.tpl',
        ];

        if (!$isMentor) {
            return $response;
        }

        $firstDayOfNextMonth = new \DateTime($currentDate);
        $firstDayOfNextMonth->modify('first day of next month');
        $dateToSelect = $firstDayOfNextMonth->format('Y-m-d');

        $response['smartyProperties']['schoolOfTheMonthPayload']['options'] = [
            'canChooseSchool' =>
                \OmegaUp\Authorization::canChooseCoderOrSchool(
                    $currentTimestamp
                ),
            'schoolIsSelected' =>
                \OmegaUp\DAO\SchoolOfTheMonth::isSchoolOfTheMonthAlreadySelected(
                    $dateToSelect
                ),
        ];
        return $response;
    }

    /**
     * Returns the first school of the previous month or the one selected by
     * the mentor, if it has already been stored.
     *
     * @return array{schoolinfo: null|array{school_id: int, name: string, country_id: string|null, country: string|null, state: string|null}}
     */
    public static function getSchoolOfTheMonth(string $date = null): array {
        $firstDay = self::getCurrentMonthFirstDay($date);
        $schoolsOfTheMonth = \OmegaUp\DAO\SchoolOfTheMonth::getByTime(
            $firstDay
        );

        if (empty($schoolsOfTheMonth)) {
            return [
                'schoolinfo' => null,
            ];
        }

        $schoolOfTheMonthId = $schoolsOfTheMonth[0]->school_id;
        foreach ($schoolsOfTheMonth as $school) {
            if (isset($school->selected_by)) {
                $schoolOfTheMonthId = $school->school_id;
                break;
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

        $countryName = null;
        $stateName = null;
        $country = \OmegaUp\DAO\Countries::getByPK($school->country_id);

        if (!is_null($country)) {
            $countryName = $country->name;

            $state = \OmegaUp\DAO\States::getByPK(
                $country->country_id,
                $school->state_id
            );
            if (!is_null($state)) {
                $stateName = $state->name;
            }
        }

        return [
            'schoolinfo' => [
                'school_id' => intval($school->school_id),
                'name' => strval($school->name),
                'country_id' => $school->country_id,
                'country' => $countryName,
                'state' => $stateName,
            ],
        ];
    }

    /**
     * Selects a certain school as school of the month
     *
     * @omegaup-request-param mixed $school_id
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

        $schoolsOfTheMonth = \OmegaUp\DAO\SchoolOfTheMonth::getByTimeAndSelected(
            $dateToSelect
        );
        if (!empty($schoolsOfTheMonth)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'schoolOfTheMonthAlreadySelected'
            );
        }

        $schools = \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth(
            100,
            $dateToSelect
        );

        if (empty($schools)) {
            throw new \OmegaUp\Exceptions\NotFoundException('noSchools');
        }

        try {
            \OmegaUp\DAO\DAO::transBegin();
            foreach ($schools as $school) {
                if ($school['school_id'] === $selectedSchool->school_id) {
                    $selectedSchoolOfTheMonth = \OmegaUp\DAO\SchoolOfTheMonth::getByPK(
                        $school['school_of_the_month_id']
                    );
                    if (is_null($selectedSchoolOfTheMonth)) {
                        throw new \OmegaUp\Exceptions\NotFoundException(
                            'schoolNotFound'
                        );
                    }
                    $selectedSchoolOfTheMonth->selected_by = $r->identity->identity_id;
                    \OmegaUp\DAO\SchoolOfTheMonth::update(
                        $selectedSchoolOfTheMonth
                    );
                }
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
        return ['status' => 'ok'];
    }
}
