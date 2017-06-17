<?php

/**
 * Authorization.php - Contains static function calls that return true if a user is authorized to perform certain action.
 */

class Authorization {
    // Cache for the system admin privilege. This is used sort of frequently.
    private static $is_system_admin = null;

    // Cache for system group for quality reviewers.
    private static $quality_reviewer_group = null;

    // Administrator for an ACL.
    const ADMIN_ROLE = 1;

    // Allowed to submit to a problemset.
    const CONTESTANT_ROLE = 2;

    // Problem reviewer.
    const REVIEWER_ROLE = 3;

    // Interviewer.
    const INTERVIEWER_ROLE = 4;

    // System-level ACL.
    const SYSTEM_ACL = 1;

    // Group for quality reviewers.
    const QUALITY_REVIEWER_GROUP_ALIAS = 'omegaup:quality-reviewer';

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

    /**
     * Returns whether the user can edit the problem. Only problem admins and
     * reviewers can do so.
     */
    public static function canEditProblem($user_id, Problems $problem) {
        if (is_null($problem) || !is_a($problem, 'Problems')) {
            return false;
        }

        return Authorization::isProblemAdmin($user_id, $problem) ||
            self::hasRole($user_id, $problem->acl_id, Authorization::REVIEWER_ROLE);
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
            self::hasRole($user_id, $entity->acl_id, Authorization::ADMIN_ROLE);
    }

    public static function isContestAdmin($user_id, Contests $contest) {
        return self::isAdmin($user_id, $contest);
    }

    public static function isInterviewAdmin($user_id, Interviews $interview) {
        return self::isAdmin($user_id, $interview);
    }

    public static function isProblemAdmin($user_id, Problems $problem) {
        return self::isAdmin($user_id, $problem);
    }

    public static function hasRole($user_id, $acl_id, $role_id) {
        return GroupRolesDAO::hasRole($user_id, $acl_id, $role_id) ||
            UserRolesDAO::hasRole($user_id, $acl_id, $role_id);
    }

    public static function isSystemAdmin($user_id) {
        if (self::$is_system_admin == null) {
            self::$is_system_admin = Authorization::hasRole(
                $user_id,
                Authorization::SYSTEM_ACL,
                Authorization::ADMIN_ROLE
            );
        }
        return self::$is_system_admin;
    }

    public static function isQualityReviewer($user_id) {
        if (self::$quality_reviewer_group == null) {
            self::$quality_reviewer_group = GroupsDAO::findByAlias(
                Authorization::QUALITY_REVIEWER_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $user_id,
            self::$quality_reviewer_group
        );
    }

    public static function isGroupAdmin($user_id, Groups $group) {
        return self::isAdmin($user_id, $group);
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

    public static function clearCacheForTesting() {
        self::$is_system_admin = null;
        self::$quality_reviewer_group = null;
    }

    public static function canSubmitToProblemset($user_id, $problemset) {
        if (is_null($problemset)) {
            return false;
        }
        return self::isAdmin($user_id, $problemset) ||
               GroupRolesDAO::isContestant($user_id, $problemset->acl_id);
    }
}
