<?php

/**
 * Authorization.php - Contains static function calls that return true if a user is authorized to perform certain action.
 */
define('ADMIN_ROLE', '1');
define('CONTEST_ADMIN_ROLE', '2');
define('PROBLEM_ADMIN_ROLE', '3');

class Authorization {
    // Cache for the system admin privilege. This is used sort of frequently.
    private static $is_system_admin = null;

    public static function CanViewRun($user_id, Runs $run) {
        if (is_null($run) || !is_a($run, 'Runs')) {
            return false;
        }

        return (
            $run->user_id === $user_id ||
            Authorization::CanEditRun($user_id, $run)
        );
    }

    public static function CanEditRun($user_id, Runs $run) {
        if (is_null($run) || !is_a($run, 'Runs')) {
            return false;
        }

        try {
            $contest = ContestsDAO::getByPK($run->contest_id);
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

        if (!is_null($contest) && Authorization::IsContestAdmin($user_id, $contest)) {
            return true;
        }

        return Authorization::IsProblemAdmin($user_id, $problem);
    }

    public static function CanViewClarification($user_id, Clarifications $clarification) {
        if (is_null($clarification) || !is_a($clarification, 'Clarifications')) {
            return false;
        }

        if ($clarification->author_id === $user_id) {
            return true;
        }

        try {
            $contest = ContestsDAO::getByPK($clarification->contest_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($contest)) {
            return false;
        }

        return Authorization::IsContestAdmin($user_id, $contest);
    }

    public static function CanEditClarification($user_id, Clarifications $clarification) {
        if (is_null($clarification) || !is_a($clarification, 'Clarifications')) {
            return false;
        }

        try {
            $contest = ContestsDAO::getByPK($clarification->contest_id);
            $problem = ProblemsDAO::getByPK($clarification->problem_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($contest) || is_null($problem)) {
            return false;
        }

        return ($problem->author_id === $user_id
                || Authorization::IsContestAdmin($user_id, $contest));
    }

    public static function CanEditProblem($user_id, Problems $problem) {
        if (is_null($problem) || !is_a($problem, 'Problems')) {
            return false;
        }

        return Authorization::IsProblemAdmin($user_id, $problem);
    }

    public static function IsContestAdmin($user_id, Contests $contest) {
        if (is_null($contest) || !is_a($contest, 'Contests')) {
            return false;
        }

        if ($contest->director_id === $user_id) {
            return true;
        }

        return GroupRolesDAO::IsContestAdmin($user_id, $contest) ||
               UserRolesDAO::IsContestAdmin($user_id, $contest);
    }

    public static function IsProblemAdmin($user_id, Problems $problem) {
        if (is_null($problem)) {
            return false;
        }

        if ($problem->author_id === $user_id) {
            return true;
        }

        return GroupRolesDAO::IsProblemAdmin($user_id, $problem) ||
               UserRolesDAO::IsProblemAdmin($user_id, $problem);
    }

    public static function IsSystemAdmin($user_id) {
        if (self::$is_system_admin == null) {
            self::$is_system_admin =
                GroupRolesDAO::IsSystemAdmin($user_id) ||
                UserRolesDAO::IsSystemAdmin($user_id);
        }
        return self::$is_system_admin;
    }

    public static function IsGroupAdmin($user_id, Groups $group) {
        if (is_null($group)) {
            return false;
        }

        if ($group->owner_id === $user_id) {
            return true;
        }

        return Authorization::IsSystemAdmin($user_id);
    }

    /**
     * An admin is either the group owner or a member of the admin group.
     */
    public static function IsCourseAdmin($user_id, $course) {
        if ($course->id_owner == $user_id) {
            return true;
        }
        // TODO(pablo): Do group-based check once we're in the new ACL world.
        return false;
    }
}
