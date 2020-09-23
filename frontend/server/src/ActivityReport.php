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
     * @param list<array{alias: null|string, classname: string, event_type: string, ip: int, time: \OmegaUp\Timestamp, username: string}> $events
     *
     * @return list<ActivityEvent>
     */
    final public static function getActivityReport(array $events): array {
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
     * @param array{alias: null|string, classname: string, event_type: string, ip: int, time: \OmegaUp\Timestamp, username: string} $data
     * @return ActivityEvent
     */
    private static function processData(array $data): array {
        $event = ['name' => $data['event_type']];
        if ($data['event_type'] === 'submit') {
            if (!is_null($data['alias'])) {
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
