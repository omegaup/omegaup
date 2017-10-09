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
     * @throws InvalidDatabaseOperationException
     */
    public static function addACLAdmin($acl_id, $user_id) {
        $user_role = new UserRoles();
        $user_role->acl_id = $acl_id;
        $user_role->user_id = $user_id;
        $user_role->role_id = Authorization::ADMIN_ROLE;

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
     * @throws InvalidDatabaseOperationException
     */
    public static function removeACLAdmin($acl_id, $user_id) {
        $user_role = new UserRoles();
        $user_role->acl_id = $acl_id;
        $user_role->user_id = $user_id;
        $user_role->role_id = Authorization::ADMIN_ROLE;

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
     * @throws InvalidDatabaseOperationException
     */
    public static function addACLGroupAdmin($acl_id, $group_id) {
        $group_role = new GroupRoles();
        $group_role->acl_id = $acl_id;
        $group_role->group_id = $group_id;
        $group_role->role_id = Authorization::ADMIN_ROLE;

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
     * @throws InvalidDatabaseOperationException
     */
    public static function removeACLGroupAdmin($acl_id, $group_id) {
        $group_role = new GroupRoles();
        $group_role->acl_id = $acl_id;
        $group_role->group_id = $group_id;
        $group_role->role_id = Authorization::ADMIN_ROLE;

        // Delete the role
        try {
            GroupRolesDAO::delete($group_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }
}
