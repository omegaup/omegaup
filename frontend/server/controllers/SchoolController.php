<?php

/**
 * Description of SchoolController
 *
 * @author joemmanuel
 */
class SchoolController extends Controller {
    /**
     * Gets a list of schools
     *
     * @param Request $r
     */
    public static function apiList(Request $r) {
        self::authenticateRequest($r);

        $param = '';
        if (!is_null($r['term'])) {
            $param = 'term';
        } elseif (!is_null($r['query'])) {
            $param = 'query';
        } else {
            throw new InvalidParameterException('parameterEmpty', 'query');
        }

        $schools = SchoolsDAO::findByName($r[$param]);
        if (is_null($schools)) {
            throw new NotFoundException('schoolNotFound');
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
     * @param Request $r
     * @return array
     */
    public static function apiCreate(Request $r) {
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['name'], 'name');

        $state = self::getStateIdFromCountryAndState($r['country_id'], $r['state_id']);

        return [
            'status' => 'ok',
            'school_id' => self::createSchool($r['name'], $state)
        ];
    }

    /**
     * Create new school
     * @param $name
     * @param $state
     * @return $school_id
     * @throws InvalidParameterException
     */
    public static function createSchool($name, $state) {
        // Create school object
        $school = new Schools([
            'name' => $name,
            'country_id' => $state != null ? $state->country_id : null,
            'state_id' => $state != null ? $state->state_id : null,
        ]);

        $school_id = 0;
        $existing = SchoolsDAO::findByName($name);
        if (!empty($existing)) {
            return $existing[0]->school_id;
        }
        // Save in db
        SchoolsDAO::create($school);
        return $school->school_id;
    }

    /**
     * Returns rank of best schools in last month
     *
     * @param Request $r
     * @return array
     * @throws InvalidParameterException
     */
    public static function apiRank(Request $r) {
        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', 100, 100, false);
        $r->ensureInt('start_time', null, null, false);
        $r->ensureInt('finish_time', null, null, false);

        $canUseCache = is_null($r['start_time']) && is_null($r['finish_time']);

        try {
            self::authenticateRequest($r);
        } catch (UnauthorizedException $e) {
            if (!is_null($r['start_time'])) {
                throw new InvalidParameterException('paramterInvalid', 'start_time');
            }
            if (!is_null($r['finish_time'])) {
                throw new InvalidParameterException('paramterInvalid', 'finish_time');
            }
            // Both endpoints were not specified, so the API can be used
            // unauthenticated since it'll be cached.
        }

        if (is_null($r['offset'])) {
            $r['offset'] = 0;
        }
        if (is_null($r['rowcount'])) {
            $r['rowcount'] = 100;
        }
        if (is_null($r['start_time'])) {
            $r['start_time'] = strtotime('first day of this month', Time::get());
        }
        if (is_null($r['finish_time'])) {
            $r['finish_time'] = strtotime('first day of next month', Time::get());
        }

        $fetch = function () use ($r) {
            return SchoolsDAO::getRankByUsersAndProblemsWithAC(
                $r['start_time'],
                $r['finish_time'],
                $r['offset'],
                $r['rowcount']
            );
        };

        if ($canUseCache) {
            $result = Cache::getFromCacheOrSet(
                Cache::SCHOOL_RANK,
                "{$r['offset']}-{$r['rowcount']}",
                $fetch,
                60 * 60 * 24 // 1 day
            );
        } else {
            $result = $fetch();
        }

        return ['status' => 'ok', 'rank' => $result];
    }

    /**
     * @param $countryId
     * @param $stateId
     * @throws InvalidParameterException
     */
    public static function getStateIdFromCountryAndState($countryId, $stateId) {
        if (is_null($countryId) || is_null($stateId)) {
            // Both state and country must be specified together.
            return null;
        }
        return StatesDAO::getByPK($countryId, $stateId);
    }
}
