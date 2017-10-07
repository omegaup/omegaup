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
    public static function getAdmins(Request $r) {
        $variables = self::getVars($r);

        // Authenticate request
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r[$variables['alias']], $variables['alias']);

        try {
            $data = $variables['daoController']::getByAlias($r[$variables['alias']]);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::$variables['methodIsAdmin']($r['current_user_id'], $data)) {
            throw new ForbiddenAccessException();
        }

        $response = [];
        $response['admins'] = UserRolesDAO::$variables['methodGetAdmins']($data);
        $response['group_admins'] = GroupRolesDAO::$variables['methodGetAdmins']($data);
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
    public static function addAdmin(Request $r) {
        $variables = self::getVars($r);

        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::isStringNonEmpty($r[$variables['alias']], $variables['alias']);

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $data = $variables['daoController']::getByAlias($r[$variables['alias']]);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if ($variables['alias'] === 'problem_alias') {
            if (is_null($data)) {
                throw new NotFoundException('problemNotFound');
            }
        }

        // Only director is allowed to create problems in contest
        if (!Authorization::$variables['methodIsAdmin']($r['current_user_id'], $data)) {
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
    public static function removeAdmin(Request $r) {
        $variables = self::getVars($r);

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::isStringNonEmpty($r[$variables['alias']], $variables['alias']);

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $data = $variables['daoController']::getByAlias($r[$variables['alias']]);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::$variables['methodIsAdmin']($r['current_user_id'], $data)) {
            throw new ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!Authorization::$variables['methodIsAdmin']($user->user_id, $data)) {
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
    public static function addGroupAdmin(Request $r) {
        $variables = self::getVars($r);

        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::isStringNonEmpty($r[$variables['alias']], $variables['alias']);

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $data = $variables['daoController']::getByAlias($r[$variables['alias']]);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admins are allowed to modify contest
        if (!Authorization::$variables['methodIsAdmin']($r['current_user_id'], $data)) {
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
    public static function removeGroupAdmin(Request $r) {
        $variables = self::getVars($r);

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::isStringNonEmpty($r[$variables['alias']], $variables['alias']);

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $data = $variables['daoController']::getByAlias($r[$variables['alias']]);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::$variables['methodIsAdmin']($r['current_user_id'], $data)) {
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

    private static function getVars(Request $r) {
        if (isset($r['course_alias'])) {
            $alias = 'course_alias';
            $daoController = 'CoursesDAO';
            $methodIsAdmin = 'isCourseAdmin';
            $methodGetAdmins = 'getCourseAdmins';
        } elseif (isset($r['contest_alias'])) {
            $alias = 'contest_alias';
            $daoController = 'ContestsDAO';
            $methodIsAdmin = 'isContestAdmin';
            $methodGetAdmins = 'getContestAdmins';
        } elseif (isset($r['problem_alias'])) {
            $alias = 'problem_alias';
            $daoController = 'ProblemsDAO';
            $methodIsAdmin = 'isProblemAdmin';
            $methodGetAdmins = 'getProblemAdmins';
        }
        return [
            'alias' => $alias,
            'daoController' => $daoController,
            'methodIsAdmin' => $methodIsAdmin,
            'methodGetAdmins' => $methodGetAdmins,
        ];
    }
}
