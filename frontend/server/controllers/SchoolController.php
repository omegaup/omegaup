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
        Validators::isNumber($r['state_id'], 'state_id', false);

        if (!is_null($r['state_id'])) {
            try {
                $r['state'] = StatesDAO::getByPK($r['state_id']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($r['state'])) {
                throw new InvalidParameterException('parameterNotFound', 'state');
            }
        }

        // Create school object
        $school = new Schools(['name' => $r['name'], 'state_id' => $r['state_id']]);

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
        self::authenticateRequest($r);
        Validators::isNumber($r['offset'], 'offset', false);
        Validators::isNumber($r['rowcount'], 'rowcount', false);

        // Defaults for offset and rowcount
        if (null == $r['offset']) {
            $r['offset'] = 0;
        }
        if (null == $r['rowcount']) {
            $r['rowcount'] = 100;
        }

        $startOfMonth = new DateTime('First day of this month');
        $result = SchoolsDAO::getRankByUsersAndProblemsWithAC($startOfMonth, $r['offset'], $r['rowcount']);

        return ['status' => 'ok', 'rank' => $result];
    }
}
