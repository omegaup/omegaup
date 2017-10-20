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

        try {
            $schools = SchoolsDAO::findByName($r[$param]);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response = [];
        foreach ($schools as $school) {
            $entry = ['label' => $school->name, 'value' => $school->name, 'id' => $school->school_id];
            array_push($response, $entry);
        }

        return $response;
    }

    /**
     * Create new school
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    public static function apiCreate(Request $r) {
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r['name'], 'name');

        $state = null;
        if (!is_null($r['country_id']) || !is_null($r['state_id'])) {
            // Both state and country must be specified together.
            Validators::isStringNonEmpty($r['country_id'], 'country_id', true);
            Validators::isStringNonEmpty($r['state_id'], 'state_id', true);
            try {
                $state = StatesDAO::getByPK($r['country_id'], $r['state_id']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($state)) {
                throw new InvalidParameterException('parameterNotFound', 'state_id');
            }
        }

        // Create school object
        $school = new Schools([
            'name' => $r['name'],
            'country_id' => $state != null ? $state->country_id : null,
            'state_id' => $state != null ? $state->state_id : null,
        ]);

        $school_id = 0;
        try {
            $existing = SchoolsDAO::findByName($r['name']);
            if (count($existing) > 0) {
                $school_id = $existing[0]->school_id;
            } else {
                // Save in db
                SchoolsDAO::save($school);
                $school_id = $school->school_id;
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok', 'school_id' => $school_id];
    }

    /**
     * Returns rank of best schools in last month
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    public static function apiRank(Request $r) {
        Validators::isNumber($r['offset'], 'offset', false);
        Validators::isNumber($r['rowcount'], 'rowcount', false);
        Validators::isNumber($r['start_time'], 'start_time', false); // Unix timestamp
        Validators::isNumber($r['finish_time'], 'finish_time', false); // Unix timestamp

        try {
            self::authenticateRequest($r);
        } catch (UnauthorizedException $e) {
            if (!is_null($r['start_time']) || !is_null($r['finish_time'])) {
                throw new InvalidParameterException('paramterInvalid', 'start_time');
            }
        }

        // Defaults for offset and rowcount
        if (null == $r['offset']) {
            $r['offset'] = 0;
        }
        if (null == $r['rowcount']) {
            $r['rowcount'] = 100;
        }

        $canUseCache = is_null($r['start_time']) && is_null($r['finish_time']);

        if (is_null($r['start_time'])) {
            $r['start_time'] = date('Y-m-01', Time::get());
        } else {
            $r['start_time'] = gmdate('Y-m-d', $r['start_time']);
        }

        if (is_null($r['finish_time'])) {
            $r['finish_time'] = date('Y-m-d', strtotime('first day of next month'));
        } else {
            $r['finish_time'] = gmdate('Y-m-d', $r['finish_time']);
        }

        $fetch = function (Request $r) {
            try {
                return SchoolsDAO::getRankByUsersAndProblemsWithAC(
                    $r['start_time'],
                    $r['finish_time'],
                    $r['offset'],
                    $r['rowcount']
                );
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
        };

        $result = [];
        if ($canUseCache) {
            $cache_key = $r['offset'] .'-'. $r['rowcount'];
            Cache::getFromCacheOrSet(
                Cache::SCHOOL_RANK,
                $cache_key,
                $r,
                $fetch,
                $result,
                60 * 60 * 24 // 1 day
            );
        } else {
            $result = $fetch($r);
        }

        return ['status' => 'ok', 'rank' => $result];
    }
}
