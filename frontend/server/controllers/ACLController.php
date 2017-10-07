<?php

/**
 * ACLController.php
 *
 * @author juan.pablo
 */
class ACLController extends Controller {
    /**
     * Returns all administrators for courses, contests
     * or problems
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function getAdmins(Request $r, $alias, $daoController, $methodIsAdmin, $methodGetAdmins) {
        // Authenticate request
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r[$alias], $alias);

        try {
            $data = $daoController::getByAlias($r[$alias]);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::$methodIsAdmin($r['current_user_id'], $data)) {
            throw new ForbiddenAccessException();
        }

        $response = [];
        $response['admins'] = UserRolesDAO::$methodGetAdmins($data);
        $response['group_admins'] = GroupRolesDAO::$methodGetAdmins($data);
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Adds an admin to a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function addAdmin(Request $r, $alias, $daoController, $methodIsAdmin) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::isStringNonEmpty($r[$alias], $alias);

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $data = $daoController::getByAlias($r[$alias]);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if ($alias === 'problem_alias') {
            if (is_null($data)) {
                throw new NotFoundException('problemNotFound');
            }
        }

        // Only director is allowed to create problems in contest
        if (!Authorization::$methodIsAdmin($r['current_user_id'], $data)) {
            throw new ForbiddenAccessException();
        }

        $user_role = new UserRoles();
        $user_role->acl_id = $data->acl_id;
        $user_role->user_id = $user->user_id;
        $user_role->role_id = Authorization::ADMIN_ROLE;

        // Save the contest to the DB
        try {
            UserRolesDAO::save($user_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Removes an admin from a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function removeAdmin(Request $r, $alias, $daoController, $methodIsAdmin) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::isStringNonEmpty($r[$alias], $alias);

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $data = $daoController::getByAlias($r[$alias]);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::$methodIsAdmin($r['current_user_id'], $data)) {
            throw new ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!Authorization::$methodIsAdmin($user->user_id, $data)) {
            throw new NotFoundException();
        }

        $user_role = new UserRoles();
        $user_role->acl_id = $data->acl_id;
        $user_role->user_id = $user->user_id;
        $user_role->role_id = Authorization::ADMIN_ROLE;

        // Delete the role
        try {
            UserRolesDAO::delete($user_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Adds an group admin to a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function addGroupAdmin(Request $r, $alias, $daoController, $methodIsAdmin) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::isStringNonEmpty($r[$alias], $alias);

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $data = $daoController::getByAlias($r[$alias]);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admins are allowed to modify contest
        if (!Authorization::$methodIsAdmin($r['current_user_id'], $data)) {
            throw new ForbiddenAccessException();
        }

        $group_role = new GroupRoles();
        $group_role->acl_id = $data->acl_id;
        $group_role->group_id = $group->group_id;
        $group_role->role_id = Authorization::ADMIN_ROLE;

        // Save the contest to the DB
        try {
            GroupRolesDAO::save($group_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function removeGroupAdmin(Request $r, $alias, $daoController, $methodIsAdmin) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::isStringNonEmpty($r[$alias], $alias);

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $data = $daoController::getByAlias($r[$alias]);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::$methodIsAdmin($r['current_user_id'], $data)) {
            throw new ForbiddenAccessException();
        }

        $group_role = new GroupRoles();
        $group_role->acl_id = $data->acl_id;
        $group_role->group_id = $group->group_id;
        $group_role->role_id = Authorization::ADMIN_ROLE;

        // Delete the role
        try {
            GroupRolesDAO::delete($group_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }
}
