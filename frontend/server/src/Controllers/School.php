<?php

 namespace OmegaUp\Controllers;

/**
 * SchoolController
 *
 * @psalm-type SchoolListItem=array{key: int, value: string}
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type School=array{country_id: string|null, name: string, ranking: int|null, school_id: int, score: float}
 * @psalm-type SchoolCoderOfTheMonth=array{time: string, username: string, classname: string}
 * @psalm-type SchoolProblemsSolved=array{month: int, problems_solved: int, year: int}
 * @psalm-type SchoolUser=array{username: string, classname: string, created_problems: int, solved_problems: int, organized_contests: int}
 * @psalm-type SchoolProfileDetailsPayload=array{school_id: int, school_name: string, ranking: int, country: array{id: string, name: string}|null, state_name: string|null, monthly_solved_problems: list<SchoolProblemsSolved>, school_users: list<SchoolUser>, coders_of_the_month: list<SchoolCoderOfTheMonth>}
 * @psalm-type SchoolRankPayload=array{page: int, length: int, rank: list<School>, totalRows: int, showHeader: bool, pagerItems: list<PageItem>}
 * @psalm-type SchoolOfTheMonthPayload=array{candidatesToSchoolOfTheMonth: list<array{country_id: string, name: string, ranking: int, school_id: int, school_of_the_month_id: int, score: float}>, isMentor: bool, options?: array{canChooseSchool: bool, schoolIsSelected: bool}, schoolsOfPreviousMonth: list<array{country_id: string, name: string, ranking: int, school_id: int}>, schoolsOfPreviousMonths: list<array{country_id: string, name: string, school_id: int, time: string}>}
 */
class School extends \OmegaUp\Controllers\Controller {
    /**
     * Gets a list of schools
     *
     * @omegaup-request-param null|string $query
     * @omegaup-request-param null|string $term
     *
     * @return array{results: list<SchoolListItem>}
     */
    public static function apiList(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        $param = $r->ensureOptionalString('query');
        if (is_null($param)) {
            $param = $r->ensureOptionalString('term');
        }
        if (is_null($param)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'query'
            );
        }

        $response = [];
        foreach (\OmegaUp\DAO\Schools::findByName($param) as $school) {
            $response[] = [
                'key' => intval($school->school_id),
                'value' => strval($school->name),
            ];
        }

        return [
            'results' => $response,
        ];
    }

    /**
     * Returns the basic details for school
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{entrypoint: string, templateProperties: array{payload: SchoolProfileDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param int $school_id
     */
    public static function getSchoolProfileDetailsForTypeScript(\OmegaUp\Request $r): array {
        $r->ensureInt('school_id');
        $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        $payload = [
            'school_id' => intval($school->school_id),
            'school_name' => strval($school->name),
            'ranking' => intval($school->ranking),
            'country' => null,
            'state_name' => null,
            'monthly_solved_problems' => self::getMonthlySolvedProblemsCount(
                intval($school->school_id)
            ),
            'school_users' => self::getUsers(
                intval($school->school_id)
            ),
            'coders_of_the_month' => self::getSchoolCodersOfTheMonth(
                intval($school->school_id)
            ),
        ];

        if (!is_null($school->country_id)) {
            $country = \OmegaUp\DAO\Countries::getByPK(
                strval(
                    $school->country_id
                )
            );
            if (!is_null($country)) {
                $payload['country'] = [
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
                    $payload['state_name'] = $state->name;
                }
            }
        }

        return [
            'templateProperties' => [
                'payload' => $payload,
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleSchoolProfile'
                ),
            ],
            'entrypoint' => 'school_profile',
        ];
    }

    /**
     * Api to create new school
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{school_id: int}
     *
     * @omegaup-request-param null|string $country_id
     * @omegaup-request-param string $name
     * @omegaup-request-param null|string $state_id
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
            'state_id'
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
     * @return list<SchoolCoderOfTheMonth>
     */
    public static function getSchoolCodersOfTheMonth(
        int $schoolId
    ): array {
        $school = \OmegaUp\DAO\Schools::getByPK($schoolId);
        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        return \OmegaUp\DAO\CoderOfTheMonth::getCodersOfTheMonthFromSchool(
            intval($school->school_id)
        );
    }

    /**
     * Returns the number of solved problems on the last
     * months (including the current one)
     *
     * @return list<SchoolProblemsSolved>
     */
    public static function getMonthlySolvedProblemsCount(
        int $schoolId
    ): array {
        $school = \OmegaUp\DAO\Schools::getByPK($schoolId);

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        return \OmegaUp\DAO\Schools::getMonthlySolvedProblemsCount(
            $schoolId
        );
    }

    /**
     * Returns the list of current students registered in a certain school
     * with the number of created problems, solved problems and organized contests.
     *
     * @return list<SchoolUser>
     */
    public static function getUsers(
        int $schoolId
    ): array {
        $school = \OmegaUp\DAO\Schools::getByPK($schoolId);

        if (is_null($school)) {
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
        }

        return \OmegaUp\DAO\Schools::getUsersFromSchool(
            intval($school->school_id)
        );
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
            fn () => \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth(
                $rowcount
            ),
            60 * 60 * 12 // 12 hours
        );
    }

    /**
     * Gets the details for historical rank of schools with pagination
     *
     * @return array{templateProperties: array{payload: SchoolRankPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     */
    public static function getRankForTypeScript(\OmegaUp\Request $r): array {
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('length');

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null($r['length']) ? 100 : intval($r['length']);

        $schoolRank = \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::SCHOOL_RANK,
            "{$page}-{$length}",
            fn () => \OmegaUp\DAO\Schools::getRank($page, $length),
            3600 // 1 hour
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'showHeader' => false,
                    'rank' => $schoolRank['rank'],
                    'totalRows' => $schoolRank['totalRows'],
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $schoolRank['totalRows'],
                        $length,
                        $page,
                        '/rank/schools/',
                        5,
                        []
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleSchoolsRank'
                )
            ],
            'entrypoint' => 'schools_rank',
        ];
    }

    /**
     * Gets all the information to be sent to TypeScript for the tabs
     * of School of the Month
     * @return array{templateProperties: array{payload: SchoolOfTheMonthPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getSchoolOfTheMonthDetailsForTypeScript(\OmegaUp\Request $r): array {
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
            'templateProperties' => [
                'payload' => [
                    'schoolsOfPreviousMonths' => \OmegaUp\DAO\SchoolOfTheMonth::getSchoolsOfTheMonth(),
                    'schoolsOfPreviousMonth' => \OmegaUp\DAO\SchoolOfTheMonth::getMonthlyList(
                        $currentDate
                    ),
                    'candidatesToSchoolOfTheMonth' => \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth(),
                    'isMentor' => $isMentor,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleSchoolOfTheMonth'
                )
            ],
            'entrypoint' => 'school_of_the_month',
        ];

        if (!$isMentor) {
            return $response;
        }

        $firstDayOfNextMonth = new \DateTime($currentDate);
        $firstDayOfNextMonth->modify('first day of next month');
        $dateToSelect = $firstDayOfNextMonth->format('Y-m-d');

        $response['templateProperties']['payload']['options'] = [
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
    public static function getSchoolOfTheMonth(?string $date = null): array {
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
     * @return array{status: string}
     *
     * @omegaup-request-param int $school_id
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
            throw new \OmegaUp\Exceptions\NotFoundException('schoolNotFound');
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
