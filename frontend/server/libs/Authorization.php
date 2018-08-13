<?php

/**
 * Authorization.php - Contains static function calls that return true if an identity is authorized to perform certain action.
 */

class Authorization {
    // Cache for the system admin privilege. This is used sort of frequently.
    private static $is_system_admin = null;

    // Cache for system group for quality reviewers.
    private static $quality_reviewer_group = null;

    // Cache for system group for course curators
    private static $course_curator_group = null;

    // Cache for system group for mentors
    private static $mentor_group = null;

    // Cache for system group for support team members
    private static $support_group = null;

    // Cache for system group identity creators
    private static $groupIdentityCreator = null;

    // Administrator for an ACL.
    const ADMIN_ROLE = 1;

    // Allowed to submit to a problemset.
    const CONTESTANT_ROLE = 2;

    // Problem reviewer.
    const REVIEWER_ROLE = 3;

    // Interviewer.
    const INTERVIEWER_ROLE = 4;

    // Mentor.
    const MENTOR_ROLE = 5;

    // Identity creator.
    const IDENTITY_CREATOR_ROLE = 7;

    // System-level ACL.
    const SYSTEM_ACL = 1;

    // Group for quality reviewers.
    const QUALITY_REVIEWER_GROUP_ALIAS = 'omegaup:quality-reviewer';

    // Group for course curators.
    const COURSE_CURATOR_GROUP_ALIAS = 'omegaup:course-curator';

    // Group for mentors.
    const MENTOR_GROUP_ALIAS = 'omegaup:mentor';

    // Group for support team members.
    const SUPPORT_GROUP_ALIAS = 'omegaup:support';

    // Group identities creators.
    const IDENTITY_CREATOR_GROUP_ALIAS = 'omegaup:group-identity-creator';

    public static function canViewRun($identity_id, Runs $run) {
        if (is_null($run) || !is_a($run, 'Runs')) {
            return false;
        }

        return (
            $run->identity_id === $identity_id ||
            Authorization::canEditRun($identity_id, $run)
        );
    }

    public static function canEditRun($identity_id, Runs $run) {
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
        if (!is_null($problemset) && Authorization::isAdmin($identity_id, $problemset)) {
            return true;
        }

        return Authorization::isProblemAdmin($identity_id, $problem);
    }

    public static function canViewClarification($identity_id, Clarifications $clarification) {
        if (is_null($clarification) || !is_a($clarification, 'Clarifications')) {
            return false;
        }

        if ($clarification->author_id === $identity_id) {
            return true;
        }

        $problemset = ProblemsetsDAO::getByPK($clarification->problemset_id);

        if (is_null($problemset)) {
            return false;
        }

        return Authorization::isAdmin($identity_id, $problemset);
    }

    public static function canEditClarification($identity_id, Clarifications $clarification) {
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

        return (self::isOwner($identity_id, $problem->acl_id)
                || Authorization::isAdmin($identity_id, $problemset));
    }

    /**
     * Returns whether the identity can edit the problem. Only problem admins and
     * reviewers can do so.
     */
    public static function canEditProblem($identity_id, Problems $problem) {
        if (is_null($problem) || !is_a($problem, 'Problems')) {
            return false;
        }
        return self::isProblemAdmin($identity_id, $problem) ||
            self::isQualityReviewer($identity_id) ||
            self::hasRole($identity_id, $problem->acl_id, Authorization::REVIEWER_ROLE);
    }

    public static function canViewEmail($identity_id) {
        return self::isMentor($identity_id);
    }

    public static function canCreateGroupIdentities($identity_id) {
        return self::isGroupIdentityCreator($identity_id);
    }

    public static function canViewCourse($identity_id, Courses $course, Groups $group) {
        if (!Authorization::isCourseAdmin($identity_id, $course) &&
            !Authorization::isGroupMember($identity_id, $group)) {
            return false;
        }

        return true;
    }

    public static function isAdmin($identity_id, $entity) {
        if (is_null($entity)) {
            return false;
        }
        return self::isOwner($identity_id, $entity->acl_id) ||
            self::hasRole($identity_id, $entity->acl_id, Authorization::ADMIN_ROLE);
    }

    public static function isContestAdmin($identity_id, Contests $contest) {
        return self::isAdmin($identity_id, $contest);
    }

    public static function isInterviewAdmin($identity_id, Interviews $interview) {
        return self::isAdmin($identity_id, $interview);
    }

    public static function isProblemAdmin($identity_id, Problems $problem) {
        return self::isAdmin($identity_id, $problem);
    }

    public static function hasRole($identity_id, $acl_id, $role_id) {
        return GroupRolesDAO::hasRole($identity_id, $acl_id, $role_id) ||
            UserRolesDAO::hasRole($identity_id, $acl_id, $role_id);
    }

    public static function isSystemAdmin($identity_id) {
        if (self::$is_system_admin == null) {
            self::$is_system_admin = Authorization::hasRole(
                $identity_id,
                Authorization::SYSTEM_ACL,
                Authorization::ADMIN_ROLE
            );
        }
        return self::$is_system_admin;
    }

    public static function isQualityReviewer($identity_id) {
        if (self::$quality_reviewer_group == null) {
            self::$quality_reviewer_group = GroupsDAO::findByAlias(
                Authorization::QUALITY_REVIEWER_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identity_id,
            self::$quality_reviewer_group
        );
    }

    public static function isMentor($identity_id) {
        if (self::$mentor_group == null) {
            self::$mentor_group = GroupsDAO::findByAlias(
                Authorization::MENTOR_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identity_id,
            self::$mentor_group
        );
    }

    public static function isGroupIdentityCreator($identityId) {
        if (self::$groupIdentityCreator == null) {
            self::$groupIdentityCreator = GroupsDAO::findByAlias(
                Authorization::IDENTITY_CREATOR_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identityId,
            self::$groupIdentityCreator
        );
    }

    public static function isSupportTeamMember($identity_id) {
        if (self::$support_group == null) {
            self::$support_group = GroupsDAO::findByAlias(
                Authorization::SUPPORT_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identity_id,
            self::$support_group
        );
    }

    public static function isGroupAdmin($identity_id, Groups $group) {
        return self::isAdmin($identity_id, $group);
    }

    private static function isOwner($identity_id, $acl_id) {
        // TODO: Remove this when ACL is migrated
        $owner_id = ACLsDAO::getACLIdentityByPK($acl_id);
        return $owner_id == $identity_id;
    }

    /**
     * An admin is either the group owner or a member of the admin group.
     */
    public static function isCourseAdmin($identity_id, Courses $course) {
        return self::isAdmin($identity_id, $course);
    }

    public static function isGroupMember($identity_id, Groups $group) {
        if (is_null($identity_id) || is_null($group)) {
            return false;
        }

        if (Authorization::isSystemAdmin($identity_id)) {
            return true;
        }
        $groupUsers = GroupsIdentitiesDAO::getByPK($group->group_id, $identity_id);

        return !empty($groupUsers);
    }

    public static function isCourseCurator($identity_id) {
        if (self::$course_curator_group == null) {
            self::$course_curator_group = GroupsDAO::findByAlias(
                Authorization::COURSE_CURATOR_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identity_id,
            self::$course_curator_group
        );
    }

    public static function clearCacheForTesting() {
        self::$is_system_admin = null;
        self::$quality_reviewer_group = null;
        self::$mentor_group = null;
        self::$support_group = null;
        self::$groupIdentityCreator = null;
    }

    public static function canSubmitToProblemset($identity_id, $problemset) {
        if (is_null($problemset)) {
            return false;
        }
        return self::isAdmin($identity_id, $problemset) ||
               GroupRolesDAO::isContestant($identity_id, $problemset->acl_id);
    }

    public static function canCreatePublicCourse($identity_id) {
        return self::isCourseCurator($identity_id);
    }
}
