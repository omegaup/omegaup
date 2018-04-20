<?php

class ActivityReport {
    final public static function getActivityReport($accesses, $submissions) {
        // Merge both logs.
        $result['events'] = [];
        $lenAccesses = count($accesses);
        $lenSubmissions = count($submissions);
        $iAccesses = 0;
        $iSubmissions = 0;

        while ($iAccesses < $lenAccesses && $iSubmissions < $lenSubmissions) {
            if ($accesses[$iAccesses]['time'] < $submissions[$iSubmissions]['time']) {
                array_push($result['events'], self::processAccess(
                    $accesses[$iAccesses++]
                ));
            } else {
                array_push($result['events'], self::processSubmission(
                    $submissions[$iSubmissions++]
                ));
            }
        }

        while ($iAccesses < $lenAccesses) {
            array_push($result['events'], self::processAccess(
                $accesses[$iAccesses++]
            ));
        }

        while ($iSubmissions < $lenSubmissions) {
            array_push($result['events'], self::processSubmission(
                $submissions[$iSubmissions++]
            ));
        }

        // Anonimize data.
        $ipMapping = [];
        foreach ($result['events'] as &$entry) {
            if (!array_key_exists($entry['ip'], $ipMapping)) {
                $ipMapping[$entry['ip']] = count($ipMapping);
            }
            $entry['ip'] = $ipMapping[$entry['ip']];
        }

        $result['status'] = 'ok';
        return $result;
    }

    private static function processAccess(&$access) {
        return [
            'username' => $access['username'],
            'classname' => $access['classname'],
            'time' => (int)$access['time'],
            'ip' => (int)$access['ip'],
            'event' => [
                'name' => 'open',
            ],
        ];
    }

    private static function processSubmission(&$submission) {
        return [
            'username' => $submission['username'],
            'classname' => $submission['classname'],
            'time' => (int)$submission['time'],
            'ip' => (int)$submission['ip'],
            'event' => [
                'name' => 'submit',
                'problem' => $submission['alias'],
            ],
        ];
    }
}
