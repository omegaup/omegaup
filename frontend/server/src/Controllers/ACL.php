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
     * @return array{acls: list<array{acl_id: int, type: string, alias: string}>, roles: list<array{acl_id: int|null, user_id: int|null, username: string, role_id: int|null, role_name: string, role_description: string}>}
     */
    public static function apiUserOwnedAclReport(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $userId = $r->user->user_id;

        // Get ACLs owned by the user with alias and type
        $ownedAcls = \OmegaUp\DAO\ACLs::getUserOwnedAclTypesWithAliases(
            $userId
        );

        if (empty($ownedAcls)) {
            return ['acls' => [], 'roles' => []];
        }

        $aclIds = array_column($ownedAcls, 'acl_id');
        $aclMap = array_column($ownedAcls, null, 'acl_id');

        // Build role map
        $roleMap = [];
        foreach (\OmegaUp\DAO\Roles::getAll() as $role) {
            $roleMap[$role->role_id] = [
                'name' => $role->name,
                'description' => $role->description,
            ];
        }

        // Get all user roles for these ACLs
        $userRoles = \OmegaUp\DAO\UserRoles::getByAclIds($aclIds);

        // Map user IDs to usernames
        $userIds = array_column($userRoles, 'user_id');
        $usernames = \OmegaUp\DAO\Identities::getUsernamesByUserIds(
            array_values(array_unique($userIds))
        );

        // Build roles array
        $aclRoles = array_map(fn($userRole) => [
            'acl_id' => $userRole->acl_id,
            'user_id' => $userRole->user_id,
            'username' => $usernames[$userRole->user_id] ?? 'unknown',
            'role_id' => $userRole->role_id,
            'role_name' => $roleMap[$userRole->role_id]['name'] ?? 'Unknown',
            'role_description' => $roleMap[$userRole->role_id]['description'] ?? 'Unknown',
        ], $userRoles);

        return [
            'acls' => array_values($aclMap),
            'roles' => $aclRoles,
        ];
    }
}
