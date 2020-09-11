<?php

namespace OmegaUp;

/**
 *
 * @psalm-type Event=array{name: string, problem?: string}
 * @psalm-type ActivityEvent=array{classname: string, event: Event, ip: int, time: \OmegaUp\Timestamp, username: string}
 *
 */
class ActivityReport {
    /**
     * @param list<array{alias?: string, classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}> $accesses
     * @param list<array{alias?: string, classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}> $submissions
     *
     * @return list<ActivityEvent>
     */
    final public static function getActivityReport(
        array $accesses,
        array $submissions
    ): array {
        $events = array_merge($accesses, $submissions);

        usort(
            $events,
            /**
             * @param array{alias?: string, classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string} $a
             * @param array{alias?: string, classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string} $b
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
     * @param array{alias?: string, classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string} $data
     * @return ActivityEvent
     */
    private static function processData(array $data): array {
        $event = ['name' => $data['eventType']];
        if ($data['eventType'] === 'submit') {
            if (isset($data['alias'])) {
                $event['problem'] = $data['alias'];
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
