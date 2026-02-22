<?php

namespace OmegaUp;

/**
 *
 * @psalm-type Event=array{courseAlias?: string, courseName?: string, name: string, problem?: string, cloneResult?: string}
 * @psalm-type ActivityEvent=array{classname: string, event: Event, ip: int|null, time: \OmegaUp\Timestamp, username: string}
 *
 */
class ActivityReport {
    /**
     * @param list<array{alias: null|string, classname: string, clone_result: null|string, clone_token_payload: null|string, event_type: string, ip: int|null, name: null| string, time: \OmegaUp\Timestamp, username: string}> $events
     *
     * @return list<ActivityEvent>
     */
    final public static function getActivityReport(array $events): array {
        $events = array_map(fn ($event) => self::processData($event), $events);

        // Anonymize data.
        /** @var array<int, int> */
        $ipMapping = [];
        foreach ($events as &$entry) {
            if ($entry['ip'] === null) {
                continue;
            }
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
     * @param array{alias: null|string, classname: string, clone_result: null|string, clone_token_payload: null|string, event_type: string, ip: int|null, name: null| string, time: \OmegaUp\Timestamp, username: string} $data
     * @return ActivityEvent
     */
    private static function processData(array $data): array {
        $event = ['name' => $data['event_type']];
        if ($data['event_type'] === 'submit') {
            if ($data['alias'] !== null) {
                $event['problem'] = $data['alias'];
            }
        } elseif ($data['event_type'] === 'clone') {
            if ($data['alias'] !== null) {
                $event['courseAlias'] = $data['alias'];
            }
            if ($data['name'] !== null) {
                $event['courseName'] = $data['name'];
            }
            if ($data['clone_result'] !== null) {
                $event['cloneResult'] = $data['clone_result'];
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
