<?php

namespace OmegaUp;

class ActivityReport {
    /**
     * @param array{username: string, ip: int, time: int, classname: string}[] $accesses
     * @param array{username: string, alias: string, ip: int, time: int, classname: string}[] $submissions
     *
     * @return array{username: string, ip: int, time: int, classname: string, alias?: string}[]
     */
    final public static function getActivityReport(
        array $accesses,
        array $submissions
    ) : array {
        // Merge both logs.
        /** @var array{username: string, ip: int, time: int, classname: string, alias?: string}[] */
        $events = [];
        $lenAccesses = count($accesses);
        $lenSubmissions = count($submissions);
        $iAccesses = 0;
        $iSubmissions = 0;

        while ($iAccesses < $lenAccesses && $iSubmissions < $lenSubmissions) {
            if ($accesses[$iAccesses]['time'] < $submissions[$iSubmissions]['time']) {
                array_push($events, self::processData(
                    $accesses[$iAccesses++]
                ));
            } else {
                array_push($events, self::processData(
                    $submissions[$iSubmissions++],
                    true
                ));
            }
        }

        while ($iAccesses < $lenAccesses) {
            array_push($events, self::processData(
                $accesses[$iAccesses++]
            ));
        }

        while ($iSubmissions < $lenSubmissions) {
            array_push($events, self::processData(
                $submissions[$iSubmissions++],
                true
            ));
        }

        // Anonymize data.
        /** @var array<int, int> */
        $ipMapping = [];
        foreach ($events as &$entry) {
            if (!isset($ipMapping[$entry['ip']]) || !array_key_exists($entry['ip'], $ipMapping)) {
                $ipMapping[$entry['ip']] = count($ipMapping);
            }
            $entry['ip'] = $ipMapping[$entry['ip']];
        }

        return $events;
    }

    /**
     * @param array{username: string, ip: int, time: int, classname: string, alias?: string} $data
     * @param bool $isSubmission
     * @return array{username: string, classname: string, time: int, ip: int, event: array{name: string, problem?: string}}
     */
    private static function processData(
        array $data,
        bool $isSubmission = false
    ) : array {
        return [
            'username' => $data['username'],
            'classname' => $data['classname'] ?? 'user-rank-unranked',
            'time' => intval($data['time']),
            'ip' => intval($data['ip']),
            'event' => $isSubmission ?
                [
                    'name' => 'submit',
                    'problem' => $data['alias'] ?? '',
                ] :
                [
                    'name' => 'open',
                ],
        ];
    }
}
