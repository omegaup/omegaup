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
        $identity = $r->identity;

        if (is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $userId = $identity->identity_id;

        // Step 1: Get all ACLs owned by the user
        $allACLs = \OmegaUp\DAO\ACLs::getAll();
        $aclTypes = \OmegaUp\DAO\ACLs::getAclTypesWithAliases();
        $userACLs = [];

        foreach ($allACLs as $acl) {
            if (
                !isset($acl->acl_id, $acl->owner_id) ||
                $acl->owner_id !== $userId ||
                !isset($aclTypes[$acl->acl_id])
            ) {
                continue;
            }

            $typeInfo = $aclTypes[$acl->acl_id];
            $userACLs[$acl->acl_id] = [
                'acl_id' => $acl->acl_id,
                'type' => $typeInfo['type'],
                'alias' => $typeInfo['alias'],
            ];
        }

        if (empty($userACLs)) {
            return ['acls' => [], 'roles' => []];
        }

        // Step 2: Map roles
        $roles = \OmegaUp\DAO\Roles::getAll();
        $roleMap = [];
        foreach ($roles as $role) {
            if (isset($role->role_id, $role->name, $role->description)) {
                $roleMap[$role->role_id] = [
                    'name' => $role->name,
                    'description' => $role->description,
                ];
            }
        }

        // Step 3: Fetch all UserRoles related to owned ACLs
        $allUserRoles = \OmegaUp\DAO\UserRoles::getAll();
        $filteredRoles = [];
        $userIdsToFetch = [];

        foreach ($allUserRoles as $userRole) {
            if (
                !isset(
                    $userRole->acl_id,
                    $userRole->user_id,
                    $userRole->role_id
                ) ||
                !isset($userACLs[$userRole->acl_id])
            ) {
                continue;
            }

            $filteredRoles[] = $userRole;
            $userIdsToFetch[] = $userRole->user_id;
        }

        $usernames = \OmegaUp\DAO\Identities::getUsernamesByUserIds(
            array_values(array_unique($userIdsToFetch))
        );

        $aclRoles = [];
        foreach ($filteredRoles as $userRole) {
            $username = $usernames[$userRole->user_id] ?? 'unknown';
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

        return [
            'acls' => array_values($userACLs),
            'roles' => $aclRoles,
        ];
    }
}
