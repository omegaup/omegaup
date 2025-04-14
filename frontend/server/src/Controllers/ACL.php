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
     * Returns all ACLs owned by the current user along with assigned roles for each.
     *
     * @param \OmegaUp\Request $r The request object containing user session information.
     * @throws \OmegaUp\Exceptions\NotFoundException If the user is not found.
     * @return array{acls: list<array{acl_id: int, type: string, alias: string, users: list<array{user_id: int|null, username: string, role_id: int|null, role_name: string, role_description: string}>>}
     */
    public static function apiUserOwnedAclReport(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $userId = $r->user->user_id;

        // Get ACLs owned by the user with alias, type, and users initialized
        $ownedAcls = \OmegaUp\DAO\ACLs::getUserOwnedAclTypesWithAliases(
            $userId
        );

        if (empty($ownedAcls)) {
            return ['acls' => []];
        }

        $aclMap = array_column($ownedAcls, null, 'acl_id');
        $aclIds = array_keys($aclMap);

        // Build role map (role_id => [name, description])
        $roleMap = [];
        foreach (\OmegaUp\DAO\Roles::getAll() as $role) {
            $roleMap[$role->role_id] = [
                'name' => $role->name,
                'description' => $role->description,
            ];
        }

        // Get all user roles for these ACLs
        $userRoles = \OmegaUp\DAO\UserRoles::getByAclIds($aclIds);

        // Get all usernames mapped by user_id
        $userIds = array_column($userRoles, 'user_id');
        $usernames = \OmegaUp\DAO\Identities::getUsernamesByUserIds(
            array_values(array_unique($userIds))
        );

        // Assign users to the corresponding ACLs
        foreach ($userRoles as $userRole) {
            $aclId = $userRole->acl_id;
            if (!isset($aclMap[$aclId])) {
                continue;
            }

            $aclMap[$aclId]['users'][] = [
                'user_id' => $userRole->user_id,
                'username' => $usernames[$userRole->user_id] ?? 'unknown',
                'role_id' => $userRole->role_id,
                'role_name' => $roleMap[$userRole->role_id]['name'] ?? 'Unknown',
                'role_description' => $roleMap[$userRole->role_id]['description'] ?? 'Unknown',
            ];
        }

        return [
            'acls' => array_values($aclMap),
        ];
    }
}
