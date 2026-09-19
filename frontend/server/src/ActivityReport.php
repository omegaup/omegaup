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
     * @param array<int, int>|null $ipMapping
     *
     * @return list<ActivityEvent>
     */
    final public static function getActivityReport(array $events, ?array &$ipMapping = null): array {
        $events = array_map(fn ($event) => self::processData($event), $events);

        // Anonymize data.
        if (is_null($ipMapping)) {
            $ipMapping = [];
        }
        foreach ($events as &$entry) {
            if (is_null($entry['ip'])) {
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
     * @param list<array{username: string, classname: string, ip: int|null}> $events
     * @param array<int, int> $ipMapping
     * @return array{users: list<array{username: string, classname: string, ips: list<string>}>, origins: list<array{origin: string, usernames: list<array{username: string, classname: string}>}>}
     */
    final public static function getActivityReportDuplicates(array $events, array &$ipMapping): array {
        foreach ($events as &$entry) {
            if (is_null($entry['ip'])) {
                continue;
            }
            if (!isset($ipMapping[$entry['ip']])) {
                $ipMapping[$entry['ip']] = count($ipMapping);
            }
            $entry['ip_mapped'] = strval($ipMapping[$entry['ip']]);
        }

        // Group by user
        $userMapping = [];
        $classByUser = [];
        foreach ($events as $entry) {
            if (is_null($entry['ip'])) {
                continue;
            }
            $username = $entry['username'];
            $ipMapped = $entry['ip_mapped'];
            $userMapping[$username][] = $ipMapped;
            $classByUser[$username] = $entry['classname'];
        }

        $users = [];
        $sortedUsers = array_keys($userMapping);
        sort($sortedUsers);
        foreach ($sortedUsers as $username) {
            $ips = array_values(array_unique($userMapping[$username]));
            if (count($ips) <= 1) {
                continue;
            }
            sort($ips);
            $users[] = [
                'username' => $username,
                'classname' => $classByUser[$username],
                'ips' => $ips,
            ];
        }

        // Group by origin
        $originMapping = [];
        foreach ($events as $entry) {
            if (is_null($entry['ip'])) {
                continue;
            }
            $username = $entry['username'];
            $ipMapped = $entry['ip_mapped'];
            $originMapping[$ipMapped][] = $username;
        }

        $origins = [];
        $sortedOrigins = array_keys($originMapping);
        sort($sortedOrigins);
        foreach ($sortedOrigins as $origin) {
            $usernames = array_values(array_unique($originMapping[$origin]));
            if (count($usernames) <= 1) {
                continue;
            }
            sort($usernames);
            $origins[] = [
                'origin' => $origin,
                'usernames' => array_map(fn ($u) => [
                    'username' => $u,
                    'classname' => $classByUser[$u]
                ], $usernames),
            ];
        }

        return [
            'users' => $users,
            'origins' => $origins,
        ];
    }

    /**
     * @param array{alias: null|string, classname: string, clone_result: null|string, clone_token_payload: null|string, event_type: string, ip: int|null, name: null| string, time: \OmegaUp\Timestamp, username: string} $data
     * @return ActivityEvent
     */
    private static function processData(array $data): array {
        $event = ['name' => $data['event_type']];
        if ($data['event_type'] === 'submit') {
            if (!is_null($data['alias'])) {
                $event['problem'] = $data['alias'];
            }
        } elseif ($data['event_type'] === 'clone') {
            if (!is_null($data['alias'])) {
                $event['courseAlias'] = $data['alias'];
            }
            if (!is_null($data['name'])) {
                $event['courseName'] = $data['name'];
            }
            if (!is_null($data['clone_result'])) {
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
