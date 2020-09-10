<?php

namespace OmegaUp;

/**
 *
 * @psalm-type Event=array{courseAlias?: string, courseName?: string, name: string, problem?: string, result?: string}
 * @psalm-type ActivityEvent=array{classname: string, event: Event, ip: int, time: \OmegaUp\Timestamp, username: string}
 *
 */
class ActivityReport {
    /**
     * @param list<array{alias?: string, classname: string, eventType: string, ip: int, name?: string, result?: string, time: \OmegaUp\Timestamp, token_payload?: string, username: string}> $accesses
     * @param list<array{alias?: string, classname: string, eventType: string, ip: int, name?: string, result?: string, time: \OmegaUp\Timestamp, token_payload?: string, username: string}> $submissions
     *
     * @return list<ActivityEvent>
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
                $events[] = self::processData($accesses[$iAccesses++]);
            } else {
                $events[] = self::processData($submissions[$iSubmissions++]);
            }
        }

        while ($iAccesses < $lenAccesses) {
            $events[] = self::processData($accesses[$iAccesses++]);
        }

        while ($iSubmissions < $lenSubmissions) {
            $events[] = self::processData($submissions[$iSubmissions++]);
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
     * @param list<array{alias?: string, classname: string, eventType: string, ip: int, name?: string, result?: string, time: \OmegaUp\Timestamp, token_payload?: string, username: string}> $accesses
     * @param list<array{alias?: string, classname: string, eventType: string, ip: int, name?: string, result?: string, time: \OmegaUp\Timestamp, token_payload?: string, username: string}> $submissions
     * @param list<array{alias?: string, classname: string, eventType: string, ip: int, name?: string, result?: string, time: \OmegaUp\Timestamp, token_payload?: string, username: string}> $cloneAttempts
     *
     * @return list<ActivityEvent>
     */
    final public static function getCourseActivityReport(
        array $accesses,
        array $submissions,
        array $cloneAttempts
    ): array {
        $events = array_merge($accesses, $submissions, $cloneAttempts);

        usort(
            $events,
            /**
             * @param array{alias?: string, classname: string, eventType: string, ip: int, name?: string, result?: string, time: \OmegaUp\Timestamp, token_payload?: string, username: string} $a
             * @param array{alias?: string, classname: string, eventType: string, ip: int, name?: string, result?: string, time: \OmegaUp\Timestamp, token_payload?: string, username: string} $b
             */
            fn (array $a, array $b) => $a['time'] <=> $b['time']
        );
        $events = array_map(fn ($event) => self::processData($event), $events);

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
     * @param array{alias?: string, classname: string, eventType: string, ip: int, name?: string, result?: string, time: \OmegaUp\Timestamp, token_payload?: string, username: string} $data
     * @return ActivityEvent
     */
    private static function processData(array $data): array {
        $event = ['name' => $data['eventType']];
        if ($data['eventType'] === 'submit') {
            if (isset($data['alias'])) {
                $event['problem'] = $data['alias'];
            }
        } elseif ($data['eventType'] === 'clone') {
            if (isset($data['alias'])) {
                $event['courseAlias'] = $data['alias'];
            }
            if (isset($data['name'])) {
                $event['courseName'] = $data['name'];
            }
            if (isset($data['result'])) {
                $event['result'] = $data['result'];
            }
        }
        return [
            'username' => $data['username'],
            'classname' => $data['classname'],
            'time' => $data['time'],
            'ip' => $data['ip'],
            'event' => $event,
        ];
    }
}
