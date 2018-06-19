<?php

/**
 *  ProblemOfTheWeekController
 *
 */
class ProblemOfTheWeekController extends Controller {
    const MAX_REQUEST_SIZE = 1000;

    /**
     * Returns the problem of the week with the latest date there is in the table.
     */
    public static function apiGetLastProblemOfTheWeek(Request $r) {
        $response = [];
        $total = 0;
        $response['results'] = self::getListOfProblemsOfTheWeek(/*offset=*/ 0, /*rowcount=*/ 1, $total)[0];
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Returns the last 'rowcount' problems of the week from newest to oldest.
     */
    public static function apiGetListOfProblemsOfTheWeek(Request $r) {
        $response = [];
        $total = 0;
        $response['results'] = self::getListOfProblemsOfTheWeek(intval($r['offset']), intval($r['rowcount']), $total);
        $response['status'] = 'ok';
        $response['total'] = $total;
        return $response;
    }

    private static function getListOfProblemsOfTheWeek($offset, $rowcount, &$total) {
        if ($rowcount > self::MAX_REQUEST_SIZE) {
            throw new InvalidDatabaseOperationException($e);
        }
        return ProblemOfTheWeekDAO::getListOfProblemsOfTheWeek($offset, $rowcount, $total);
    }
}
