<?php

namespace OmegaUp;

class ActivityReport {
    /**
     * @param list<array{alias?: string, classname?: string, ip: int, time: \OmegaUp\Timestamp, username: string}> $accesses
     * @param list<array{alias?: string, classname?: string, ip: int, time: \OmegaUp\Timestamp, username: string}> $submissions
     *
     * @return list<array{username: string, ip: int, time: \OmegaUp\Timestamp, classname?: string, alias?: string}>
     */
    final public static function getActivityReport(
        array $accesses,
        array $submissions
    ): array {
        // Merge both logs.
        $events = [];
        $lenAccesses = count($accesses);
        $lenSubmissions = count($submissions);
        $iAccesses = 0;
        $iSubmissions = 0;

        while ($iAccesses < $lenAccesses && $iSubmissions < $lenSubmissions) {
            if ($accesses[$iAccesses]['time'] < $submissions[$iSubmissions]['time']) {
                $events[] = self::processData(
                    $accesses[$iAccesses++]
                );
            } else {
                $events[] = self::processData(
                    $submissions[$iSubmissions++],
                    true
                );
            }
        }

        while ($iAccesses < $lenAccesses) {
            $events[] = self::processData(
                $accesses[$iAccesses++]
            );
        }

        while ($iSubmissions < $lenSubmissions) {
            $events[] = self::processData(
                $submissions[$iSubmissions++],
                true
            );
        }

        // Anonymize data.
        /** @var array<int, int> */
        $ipMapping = [];
        foreach ($events as &$entry) {
            if (
                !isset($ipMapping[$entry['ip']]) ||
                !array_key_exists($entry['ip'], $ipMapping)
            ) {
                $ipMapping[$entry['ip']] = count($ipMapping);
            }
            $entry['ip'] = $ipMapping[$entry['ip']];
        }

        return $events;
    }

    /**
     * @param array{username: string, ip: int, time: \OmegaUp\Timestamp, classname?: string, alias?: string} $data
     * @param bool $isSubmission
     * @return array{username: string, classname?: string, time: \OmegaUp\Timestamp, ip: int, event: array{name: string, problem?: string}}
     */
    private static function processData(
        array $data,
        bool $isSubmission = false
    ): array {
        return [
            'username' => $data['username'],
            'classname' => $data['classname'] ?? 'user-rank-unranked',
            'time' => $data['time'],
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
