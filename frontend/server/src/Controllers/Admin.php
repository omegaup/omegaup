<?php

 namespace OmegaUp\Controllers;

class Admin extends \OmegaUp\Controllers\Controller {
    /**
     * Get stats for an overall platform report.
     *
     * @return array{report: array{acceptedSubmissions: int, activeSchools: int, activeUsers: array<string, int>, courses: int, omiCourse: array{attemptedUsers: int, completedUsers: int, passedUsers: int}}}
     *
     * @omegaup-request-param int|null $end_time
     * @omegaup-request-param int|null $start_time
     */
    public static function apiPlatformReportStats(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateOptionalNumber(
            $r['start_time'],
            'start_time'
        );
        \OmegaUp\Validators::validateOptionalNumber($r['end_time'], 'end_time');

        $startTime = empty($r['start_time']) ?
            strtotime('first day of this January') :
            intval($r['start_time']);
        $endTime = empty($r['end_time']) ?
            \OmegaUp\Time::get() :
            intval($r['end_time']);

        return [
            'report' => [
                'activeUsers' => array_merge(...array_map(
                    /**
                     * @param array{gender: string, users: int} $row
                     * @return array<string, int>
                     */
                    fn (array $row) => [$row['gender'] => $row['users']],
                    \OmegaUp\DAO\Identities::countActiveUsersByGender(
                        $startTime,
                        $endTime
                    )
                )),
                'acceptedSubmissions' => \OmegaUp\DAO\Submissions::countAcceptedSubmissions(
                    $startTime,
                    $endTime
                ),
                'activeSchools' => \OmegaUp\DAO\Schools::countActiveSchools(
                    $startTime,
                    $endTime
                ),
                'courses' => \OmegaUp\DAO\Courses::countCourses(
                    $startTime,
                    $endTime
                ),
                'omiCourse' => [
                    'attemptedUsers' => \OmegaUp\DAO\Courses::countAttemptedIdentities(
                        'Curso-OMI',
                        $startTime,
                        $endTime
                    ),
                    'passedUsers' => \OmegaUp\DAO\Courses::countCompletedIdentities(
                        'Curso-OMI',
                        0.7,
                        $startTime,
                        $endTime
                    ),
                    'completedUsers' => \OmegaUp\DAO\Courses::countCompletedIdentities(
                        'Curso-OMI',
                        1.0,
                        $startTime,
                        $endTime
                    ),
                ],
            ],
        ];
    }

    /**
     * Get a report listing users by profile, their assigned ACLs, and ACL types.
     *
     * @return array{report: array<int, array{username: string, roles_count: int, roles: array<int, array{name: string, description: string, acl_id: int, acl_type: string, alias: ?string}>}>}
     *
     * @omegaup-request-param mixed $unused
     */
    public static function apiUserProfileReport(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();

        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Fetch role descriptions
        $roles = \OmegaUp\DAO\Roles::getAll();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role->role_id] = [
                'name' => $role->name,
                'description' => $role->description,
            ];
        }

        // Fetch user-role mappings with ACL details
        $userRoles = \OmegaUp\DAO\UserRoles::getAll();
        $userMap = [];  // Store users grouped by username

        foreach ($userRoles as $userRole) {
            self::addUserRole(
                $userMap,
                $userRole->user_id,
                $userRole->role_id,
                $userRole->acl_id,
                $roleMap
            );
        }

        // Fetch owners from ACLs table and add them as "Owner"
        $aclOwners = \OmegaUp\DAO\ACLs::getAll();
        foreach ($aclOwners as $aclOwner) {
            self::addUserRole(
                $userMap,
                $aclOwner->owner_id,
                null,
                $aclOwner->acl_id,
                $roleMap,
                true
            );
        }

        return ['report' => array_values($userMap)];
    }

    /**
     * Adds a user role entry to the user map.
     */
    private static function addUserRole(
        array &$userMap,
        int $userId,
        ?int $roleId,
        int $aclId,
        array $roleMap,
        bool $isOwner = false
    ): void {
        // Get ACL Type & Alias
        $aclData = self::getAclType($aclId);
        $aclType = $aclData['type'];
        $alias = $aclData['alias'];

        // Get username from identity
        $mainIdentity = \OmegaUp\DAO\Users::getByPK($userId);
        if (is_null($mainIdentity)) {
            return; // Skip if user does not exist
        }
        $username = \OmegaUp\DAO\Identities::getByPK(
            $mainIdentity->main_identity_id
        )->username;

        // Ensure user exists in userMap
        if (!isset($userMap[$username])) {
            $userMap[$username] = [
                'username' => $username,
                'roles_count' => 0,
                'roles' => []
            ];
        }

        // Append role details
        $userMap[$username]['roles'][] = [
            'name' => $isOwner ? 'Owner' : $roleMap[$roleId]['name'],
            'description' => $isOwner ? 'Owner of this ACL' : $roleMap[$roleId]['description'],
            'acl_id' => $aclId,
            'acl_type' => $aclType,
            'alias' => $alias, // Include alias
        ];

        // Update role count
        $userMap[$username]['roles_count'] = count(
            $userMap[$username]['roles']
        );
    }

    /**
     * Determine the ACL type and alias based on related tables.
     *
     * @param int $aclId
     * @return array{type: string, alias: ?string}
     */
    private static function getAclType(int $aclId): array {
        if ($contest = \OmegaUp\DAO\Contests::getByAclId($aclId)) {
            return ['type' => 'contest', 'alias' => $contest->alias];
        } elseif ($course = \OmegaUp\DAO\Courses::getByAclId($aclId)) {
            return ['type' => 'course', 'alias' => $course->alias];
        } elseif ($problem = \OmegaUp\DAO\Problems::getByAclId($aclId)) {
            return ['type' => 'problem', 'alias' => $problem->alias];
        } elseif ($group = \OmegaUp\DAO\Groups::getByAclId($aclId)) {
            return ['type' => 'group', 'alias' => $group->alias];
        }
        return ['type' => 'unknown', 'alias' => null];
    }
}
