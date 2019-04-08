<?php

/**
 * ACLController.php
 *
 * @author juan.pablo
 */
class ACLController extends Controller {
    /**
     * Adds a user to an ACL with the specified role.
     *
     * @param $acl_id
     * @param $user_id
     * @param $role_id
     * @throws InvalidDatabaseOperationException
     */
    public static function addUser($acl_id, $user_id, $role_id = Authorization::ADMIN_ROLE) {
        try {
            UserRolesDAO::create(new UserRoles([
                'acl_id' => $acl_id,
                'user_id' => $user_id,
                'role_id' => $role_id,
            ]));
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Removes a user from an ACL with the specified role.
     *
     * @param $acl_id
     * @param $user_id
     * @param $role_id
     * @throws InvalidDatabaseOperationException
     */
    public static function removeUser($acl_id, $user_id, $role_id = Authorization::ADMIN_ROLE) {
        try {
            UserRolesDAO::delete(new UserRoles([
                'acl_id' => $acl_id,
                'user_id' => $user_id,
                'role_id' => $role_id,
            ]));
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Adds a group to an ACL with the specified role.
     *
     * @param $acl_id
     * @param $group_id
     * @param $role_id
     * @throws InvalidDatabaseOperationException
     */
    public static function addGroup($acl_id, $group_id, $role_id = Authorization::ADMIN_ROLE) {
        try {
            GroupRolesDAO::create(new GroupRoles([
                'acl_id' => $acl_id,
                'group_id' => $group_id,
                'role_id' => $role_id,
            ]));
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Removes a group from an ACL with the specified role.
     *
     * @param $acl_id
     * @param $group_id
     * @param $role_id
     * @throws InvalidDatabaseOperationException
     */
    public static function removeGroup($acl_id, $group_id, $role_id = Authorization::ADMIN_ROLE) {
        try {
            GroupRolesDAO::delete(new GroupRoles([
                'acl_id' => $acl_id,
                'group_id' => $group_id,
                'role_id' => $role_id,
            ]));
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }
}
