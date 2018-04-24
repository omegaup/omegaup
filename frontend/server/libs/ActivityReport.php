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
                array_push($result['events'], self::processData(
                    $accesses[$iAccesses++]
                ));
            } else {
                array_push($result['events'], self::processData(
                    $submissions[$iSubmissions++],
                    true
                ));
            }
        }

        while ($iAccesses < $lenAccesses) {
            array_push($result['events'], self::processData(
                $accesses[$iAccesses++]
            ));
        }

        while ($iSubmissions < $lenSubmissions) {
            array_push($result['events'], self::processData(
                $submissions[$iSubmissions++],
                true
            ));
        }

        // Anonymize data.
        $ipMapping = [];
        foreach ($result['events'] as &$entry) {
            if (!isset($ipMapping[$entry['ip']]) || !array_key_exists($entry['ip'], $ipMapping)) {
                $ipMapping[$entry['ip']] = count($ipMapping);
            }
            $entry['ip'] = $ipMapping[$entry['ip']];
        }

        $result['status'] = 'ok';
        return $result;
    }

    private static function processData(&$data, $isSubmission = false) {
        return [
            'username' => $data['username'],
            'classname' => $data['classname'] ?? 'user-rank-unranked',
            'time' => (int)$data['time'],
            'ip' => (int)$data['ip'],
            'event' => $isSubmission ?
                [
                    'name' => 'submit',
                    'problem' => $data['alias'],
                ] :
                [
                    'name' => 'open'
                ],
        ];
    }
}
