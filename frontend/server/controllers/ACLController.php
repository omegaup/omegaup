<?php

/**
 * ACLController.php
 *
 * @author juan.pablo
 */
class ACLController extends \OmegaUp\Controllers\Controller {
    /**
     * Adds a user to an ACL with the specified role.
     *
     * @param $acl_id
     * @param $user_id
     * @param $role_id
     */
    public static function addUser($acl_id, $user_id, $role_id = \OmegaUp\Authorization::ADMIN_ROLE) {
        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'acl_id' => $acl_id,
            'user_id' => $user_id,
            'role_id' => $role_id,
        ]));
    }

    /**
     * Removes a user from an ACL with the specified role.
     *
     * @param $acl_id
     * @param $user_id
     * @param $role_id
     */
    public static function removeUser($acl_id, $user_id, $role_id = \OmegaUp\Authorization::ADMIN_ROLE) {
        \OmegaUp\DAO\UserRoles::delete(new \OmegaUp\DAO\VO\UserRoles([
            'acl_id' => $acl_id,
            'user_id' => $user_id,
            'role_id' => $role_id,
        ]));
    }

    /**
     * Adds a group to an ACL with the specified role.
     *
     * @param $acl_id
     * @param $group_id
     * @param $role_id
     */
    public static function addGroup($acl_id, $group_id, $role_id = \OmegaUp\Authorization::ADMIN_ROLE) {
        \OmegaUp\DAO\GroupRoles::create(new \OmegaUp\DAO\VO\GroupRoles([
            'acl_id' => $acl_id,
            'group_id' => $group_id,
            'role_id' => $role_id,
        ]));
    }

    /**
     * Removes a group from an ACL with the specified role.
     *
     * @param $acl_id
     * @param $group_id
     * @param $role_id
     */
    public static function removeGroup($acl_id, $group_id, $role_id = \OmegaUp\Authorization::ADMIN_ROLE) {
        \OmegaUp\DAO\GroupRoles::delete(new \OmegaUp\DAO\VO\GroupRoles([
            'acl_id' => $acl_id,
            'group_id' => $group_id,
            'role_id' => $role_id,
        ]));
    }
}
