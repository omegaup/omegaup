<?php

/**
 * Description of SchoolController
 *
 * @author joemmanuel
 */
class SchoolController extends \OmegaUp\Controllers\Controller {
    /**
     * Gets a list of schools
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiList(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        $param = '';
        if (!is_null($r['term'])) {
            $param = 'term';
        } elseif (!is_null($r['query'])) {
            $param = 'query';
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', 'query');
        }

        $schools = SchoolsDAO::findByName($r[$param]);
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
     * Api to create new school
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');

        $state = self::getStateIdFromCountryAndState($r['country_id'], $r['state_id']);

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
    public static function createSchool(string $name, ?\OmegaUp\DAO\VO\States $state) : int {
        // Create school object
        $school = new \OmegaUp\DAO\VO\Schools([
            'name' => $name,
            'country_id' => $state != null ? $state->country_id : null,
            'state_id' => $state != null ? $state->state_id : null,
        ]);

        $school_id = 0;
        $existing = SchoolsDAO::findByName($name);
        if (!empty($existing)) {
            /** @var int $existing[0]->school_id */
            return $existing[0]->school_id;
        }
        SchoolsDAO::create($school);
        /** @var int $school->school_id */
        return $school->school_id;
    }

    /**
     * Ensures that all the numeric parameters have valid values.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    private static function validateRankDetails(\OmegaUp\Request $r) : array {
        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', 5, 100, false);
        $r->ensureInt('start_time', null, null, false);
        $r->ensureInt('finish_time', null, null, false);

        try {
            self::authenticateRequest($r);
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            if (!is_null($r['start_time'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('paramterInvalid', 'start_time');
            }
            if (!is_null($r['finish_time'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('paramterInvalid', 'finish_time');
            }
            // Both endpoints were not specified, so the API can be used
            // unauthenticated since it'll be cached.
        }

        return [
            'offset' => $r['offset'] ?: 0,
            'rowcount' => $r['rowcount'] ?: 100,
            'start_time' => $r['start_time'] ?:
                            strtotime('first day of this month', \OmegaUp\Time::get()),
            'finish_time' => $r['finish_time'] ?:
                             strtotime('first day of next month', \OmegaUp\Time::get()),
            'can_use_cache' => is_null($r['start_time']) && is_null($r['finish_time'])
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
     * @param int $offset
     * @param int $rowCount
     * @param int $startTime
     * @param int $finishTime
     * @param bool $canUseCache
     * @return array
     */
    private static function getSchoolsRank(
        int $offset,
        int $rowCount,
        int $startTime,
        int $finishTime,
        bool $canUseCache
    ) : array {
        $fetch = function () use ($offset, $rowCount, $startTime, $finishTime) {
            return SchoolsDAO::getRankByUsersAndProblemsWithAC(
                $startTime,
                $finishTime,
                $offset,
                $rowCount
            );
        };

        if ($canUseCache) {
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
     * @param int $rowCount
     * @param bool $isIndex
     * @return array
     */
    public static function getSchoolsRankForSmarty(
        int $rowCount,
        bool $isIndex
    ) : array {
        $schoolsRank = [
            'schoolRankPayload' => [
                'rowCount' => $rowCount,
                'rank' => self::getSchoolsRank(
                    /*$offset=*/0,
                    $rowCount,
                    /*$startTime=*/strtotime('first day of this month', \OmegaUp\Time::get()),
                    /*$finishTime=*/strtotime('first day of next month', \OmegaUp\Time::get()),
                    /*$canUseCache=*/true
                ),
            ]
        ];
        if (!$isIndex) {
            return $schoolsRank;
        }
        $schoolsRank['rankTablePayload'] = [
            'length' => $rowCount,
            'isIndex' => $isIndex,
            'availableFilters' => [],
        ];
        return $schoolsRank;
    }

    public static function getStateIdFromCountryAndState(
        ?string $countryId,
        ?string $stateId
    ) : ?\OmegaUp\DAO\VO\States {
        if (is_null($countryId) || is_null($stateId)) {
            // Both state and country must be specified together.
            return null;
        }
        return StatesDAO::getByPK($countryId, $stateId);
    }
}
