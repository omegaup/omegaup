<?php

/**
 * ACLController.php
 *
 * @author juan.pablo
 */
class ACLController extends Controller {
    /**
     * Adds ACL to an admin
     *
     * @param $acl_id
     * @param $user_id
     * @param $role_id
     * @throws InvalidDatabaseOperationException
     */
    public static function addUser($acl_id, $user_id, $role_id = Authorization::ADMIN_ROLE) {
        $user_role = new UserRoles();
        $user_role->acl_id = $acl_id;
        $user_role->user_id = $user_id;
        $user_role->role_id = $role_id;

        // Save the ACL to the DB
        try {
            UserRolesDAO::save($user_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Removes ACL from a admin
     *
     * @param $acl_id
     * @param $user_id
     * @param $role_id
     * @throws InvalidDatabaseOperationException
     */
    public static function removeUser($acl_id, $user_id, $role_id = Authorization::ADMIN_ROLE) {
        $user_role = new UserRoles();
        $user_role->acl_id = $acl_id;
        $user_role->user_id = $user_id;
        $user_role->role_id = $role_id;

        // Delete the role
        try {
            UserRolesDAO::delete($user_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Adds ACL to a group admin
     *
     * @param $acl_id
     * @param $group_id
     * @param $role_id
     * @throws InvalidDatabaseOperationException
     */
    public static function addGroupUser($acl_id, $group_id, $role_id = Authorization::ADMIN_ROLE) {
        $group_role = new GroupRoles();
        $group_role->acl_id = $acl_id;
        $group_role->group_id = $group_id;
        $group_role->role_id = $role_id;

        // Save the ACL to the DB
        try {
            GroupRolesDAO::save($group_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Removes ACL from a group admin
     *
     * @param $acl_id
     * @param $group_id
     * @param $role_id
     * @throws InvalidDatabaseOperationException
     */
    public static function removeGroupUser($acl_id, $group_id, $role_id = Authorization::ADMIN_ROLE) {
        $group_role = new GroupRoles();
        $group_role->acl_id = $acl_id;
        $group_role->group_id = $group_id;
        $group_role->role_id = $role_id;

        // Delete the role
        try {
            GroupRolesDAO::delete($group_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }
}
