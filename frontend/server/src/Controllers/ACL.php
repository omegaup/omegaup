<?php

namespace OmegaUp\Controllers;

class ACL extends \OmegaUp\Controllers\Controller {
    /**
     * Adds a user to an ACL with the specified role.
     */
    public static function addUser(
        int $aclId,
        int $userId,
        int $roleId = \OmegaUp\Authorization::ADMIN_ROLE
    ): void {
        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'acl_id' => $aclId,
            'user_id' => $userId,
            'role_id' => $roleId,
        ]));
    }

    /**
     * Removes a user from an ACL with the specified role.
     */
    public static function removeUser(
        int $aclId,
        int $userId,
        int $roleId = \OmegaUp\Authorization::ADMIN_ROLE
    ): void {
        \OmegaUp\DAO\UserRoles::delete(new \OmegaUp\DAO\VO\UserRoles([
            'acl_id' => $aclId,
            'user_id' => $userId,
            'role_id' => $roleId,
        ]));
    }

    /**
     * Adds a group to an ACL with the specified role.
     */
    public static function addGroup(
        int $aclId,
        int $groupId,
        int $roleId = \OmegaUp\Authorization::ADMIN_ROLE
    ): void {
        \OmegaUp\DAO\GroupRoles::create(new \OmegaUp\DAO\VO\GroupRoles([
            'acl_id' => $aclId,
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]));
    }

    /**
     * Removes a group from an ACL with the specified role.
     */
    public static function removeGroup(
        int $aclId,
        int $groupId,
        int $roleId = \OmegaUp\Authorization::ADMIN_ROLE
    ): void {
        \OmegaUp\DAO\GroupRoles::delete(new \OmegaUp\DAO\VO\GroupRoles([
            'acl_id' => $aclId,
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]));
    }

    /**
     * Returns all ACLs owned by the current user and the roles assigned within those ACLs.
     *
     * @param \OmegaUp\Request $r The request object containing user session information.
     * @throws \OmegaUp\Exceptions\NotFoundException If the user is not found.
     * @return array{acls: list<array{acl_id: int, type: string, alias: ?string}>, roles: list<array{acl_id: int, acl_name: string, user_id: int, username: string, role_id: int, role_name: string, role_description: string}>}
    */
    public static function apiUserOwnedAclReport(\OmegaUp\Request $r): array {
        // Ensure user is authenticated
        $r->ensureMainUserIdentity();
        $user = $r->identity;
        if (is_null($user) || is_null($user->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
        }
        $userId = $user->identity_id;

        // Step 1: Fetch all ACLs owned by the user
        $allACLs = \OmegaUp\DAO\ACLs::getAll();
        $userACLs = [];
        foreach ($allACLs as $acl) {
            if ($acl->owner_id === $userId) {
                $aclData = self::getAclType($acl->acl_id);
                $userACLs[$acl->acl_id] = [
                    'acl_id' => $acl->acl_id,
                    'type' => $aclData['type'],
                    'alias' => $aclData['alias']
                ];
            }
        }

        if (empty($userACLs)) {
            return ['acls' => [], 'roles' => []];
        }

        // Step 2: Fetch role descriptions
        $roles = \OmegaUp\DAO\Roles::getAll();
        $roleMap = [];
        foreach ($roles as $role) {
            if (!is_null($role->role_id)) {
                $roleMap[$role->role_id] = [
                    'name' => $role->name,
                    'description' => $role->description,
                ];
            }
        }

        // Step 3: Fetch all UserRoles related to these ACLs
        $allUserRoles = \OmegaUp\DAO\UserRoles::getAll();
        $aclRoles = [];
        foreach ($allUserRoles as $userRole) {
            if (isset($userACLs[$userRole->acl_id])) {
                // Get user identity
                $identity = \OmegaUp\DAO\Identities::findByUserId(
                    $userRole->user_id
                );
                $username = $identity ? $identity->username : 'unknown';

                // Get role name & description
                $roleData = $roleMap[$userRole->role_id] ?? ['name' => 'Unknown', 'description' => 'Unknown role'];

                $aclRoles[] = [
                    'acl_id' => $userRole->acl_id,
                    'user_id' => $userRole->user_id,
                    'username' => $username,
                    'role_id' => $userRole->role_id,
                    'role_name' => $roleData['name'],
                    'role_description' => $roleData['description'],
                ];
            }
        }

        return [
            'acls' => array_values($userACLs),
            'roles' => $aclRoles
        ];
    }

    private static function getAclType(int $aclId): array {
        if ($contest = \OmegaUp\DAO\Contests::getByAclId($aclId)) {
            return ['type' => 'contest', 'alias' => $contest->alias ?? null];
        } elseif ($course = \OmegaUp\DAO\Courses::getByAclId($aclId)) {
            return ['type' => 'course', 'alias' => $course->alias ?? null];
        } elseif ($problem = \OmegaUp\DAO\Problems::getByAclId($aclId)) {
            return ['type' => 'problem', 'alias' => $problem->alias ?? null];
        } elseif ($group = \OmegaUp\DAO\Groups::getByAclId($aclId)) {
            return ['type' => 'group', 'alias' => $group->alias ?? null];
        }
        return ['type' => 'unknown', 'alias' => null];
    }
}
