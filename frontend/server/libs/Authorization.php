<?php

/**
 * Authorization.php - Contains static function calls that return true if a user is authorized to perform certain action.
 */

class Authorization {
    // Cache for the system admin privilege. This is used sort of frequently.
    private static $is_system_admin = null;

    // Administrator for an ACL.
    const ADMIN_ROLE = 1;

    // Interviewer.
    const INTERVIEWER_ROLE = 4;

    // System-level ACL.
    const SYSTEM_ACL = 1;

    public static function canViewRun($user_id, Runs $run) {
        if (is_null($run) || !is_a($run, 'Runs')) {
            return false;
        }

        return (
            $run->user_id === $user_id ||
            Authorization::canEditRun($user_id, $run)
        );
    }

    public static function canEditRun($user_id, Runs $run) {
        if (is_null($run) || !is_a($run, 'Runs')) {
            return false;
        }

        try {
            $problem = ProblemsDAO::getByPK($run->problem_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($problem)) {
            return false;
        }

        if ($problem->deprecated) {
            throw new PreconditionFailedException('problemDeprecated');
        }

        $problemset = ProblemsetsDAO::getByPK($run->problemset_id);
        if (!is_null($problemset) && Authorization::isAdmin($user_id, $problemset)) {
            return true;
        }

        return Authorization::isProblemAdmin($user_id, $problem);
    }

    public static function canViewClarification($user_id, Clarifications $clarification) {
        if (is_null($clarification) || !is_a($clarification, 'Clarifications')) {
            return false;
        }

        if ($clarification->author_id === $user_id) {
            return true;
        }

        $problemset = ProblemsetsDAO::getByPK($clarification->problemset_id);

        if (is_null($problemset)) {
            return false;
        }

        return Authorization::isAdmin($user_id, $problemset);
    }

    public static function canEditClarification($user_id, Clarifications $clarification) {
        if (is_null($clarification) || !is_a($clarification, 'Clarifications')) {
            return false;
        }

        $problemset = ProblemsetsDAO::getByPK($clarification->problemset_id);
        try {
            $problem = ProblemsDAO::getByPK($clarification->problem_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($problemset) || is_null($problem)) {
            return false;
        }

        return (self::isOwner($user_id, $problem->acl_id)
                || Authorization::isAdmin($user_id, $problemset));
    }

    public static function canEditProblem($user_id, Problems $problem) {
        if (is_null($problem) || !is_a($problem, 'Problems')) {
            return false;
        }

        return Authorization::isProblemAdmin($user_id, $problem);
    }

    public static function canViewCourse($user_id, Courses $course, Groups $group) {
        if (!Authorization::isCourseAdmin($user_id, $course) &&
            !Authorization::isGroupMember($user_id, $group)) {
            return false;
        }

        return true;
    }

    public static function isAdmin($user_id, $entity) {
        if (is_null($entity)) {
            return false;
        }
        return self::isOwner($user_id, $entity->acl_id) ||
            UserRolesDAO::isAdmin($user_id, $entity->acl_id) ||
            GroupRolesDAO::isAdmin($user_id, $entity->acl_id);
    }

    public static function isContestAdmin($user_id, Contests $contest) {
        return self::isAdmin($user_id, $contest);
    }

    public static function isInterviewAdmin($user_id, Interviews $interview) {
        return self::isAdmin($user_id, $interview);
    }

    public static function isProblemAdmin($user_id, Problems $problem) {
        if (is_null($problem)) {
            return false;
        }

        if (self::isOwner($user_id, $problem->acl_id)) {
            return true;
        }

        return GroupRolesDAO::isAdmin($user_id, $problem->acl_id) ||
               UserRolesDAO::isAdmin($user_id, $problem->acl_id);
    }

    public static function isSystemAdmin($user_id) {
        if (self::$is_system_admin == null) {
            self::$is_system_admin =
                GroupRolesDAO::isSystemAdmin($user_id) ||
                UserRolesDAO::isSystemAdmin($user_id);
        }
        return self::$is_system_admin;
    }

    public static function isGroupAdmin($user_id, Groups $group) {
        if (is_null($group)) {
            return false;
        }

        if ($group->owner_id === $user_id) {
            return true;
        }

        return Authorization::isSystemAdmin($user_id);
    }

    private static function isOwner($user_id, $acl_id) {
        $acl = ACLsDAO::getByPK($acl_id);
        return $acl->owner_id == $user_id;
    }

    /**
     * An admin is either the group owner or a member of the admin group.
     */
    public static function isCourseAdmin($user_id, Courses $course) {
        return self::isAdmin($user_id, $course);
    }

    public static function isGroupMember($user_id, Groups $group) {
        if (is_null($group)) {
            return false;
        }

        if (Authorization::isSystemAdmin($user_id)) {
            return true;
        }

        $groupUsers = GroupsUsersDAO::search(new GroupsUsers([
            'user_id' => $user_id,
            'group_id' => $group->group_id
        ]));

        return !is_null($groupUsers) && count($groupUsers) > 0;
    }

    public static function clearSystemAdminCache() {
        self::$is_system_admin = null;
    }
}
