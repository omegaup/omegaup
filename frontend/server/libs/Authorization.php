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

    public static function canViewSubmission(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Submissions $submission
    ) : bool {
        return (
            $submission->identity_id === $identity->identity_id  ||
            Authorization::canEditSubmission($identity, $submission)
        );
    }

    public static function canEditSubmission(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Submissions $submission
    ) : bool {
        $problem = ProblemsDAO::getByPK($submission->problem_id);
        if (is_null($problem)) {
            return false;
        }

        if ($problem->deprecated) {
            throw new PreconditionFailedException('problemDeprecated');
        }

        if (!is_null($submission->problemset_id)) {
            $problemset = ProblemsetsDAO::getByPK($submission->problemset_id);
            if (!is_null($problemset) && Authorization::isAdmin(
                $identity,
                $problemset
            )) {
                return true;
            }
        }

        return Authorization::isProblemAdmin($identity, $problem);
    }

    public static function canViewClarification(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Clarifications $clarification
    ) : bool {
        // TODO Temporary until isAdmin function is fixed
        $identity_id = $identity->identity_id;
        if ($clarification->author_id === $identity_id) {
            return true;
        }

        $problemset = ProblemsetsDAO::getByPK($clarification->problemset_id);
        if (is_null($problemset)) {
            return false;
        }

        return Authorization::isAdmin($identity, $problemset);
    }

    public static function canEditClarification(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Clarifications $clarification
    ) : bool {
        $problemset = ProblemsetsDAO::getByPK($clarification->problemset_id);
        if (is_null($problemset)) {
            return false;
        }

        $problem = ProblemsDAO::getByPK($clarification->problem_id);
        if (is_null($problem)) {
            return false;
        }

        return (self::isOwner($identity, $problem->acl_id)
                || Authorization::isAdmin($identity, $problemset));
    }

    /**
     * Returns whether the identity can edit the problem. Only problem admins and
     * reviewers can do so.
     */
    public static function canEditProblem(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ) : bool {
        return self::isProblemAdmin($identity, $problem) ||
            self::isQualityReviewer($identity) ||
            self::hasRole(
                $identity,
                $problem->acl_id,
                Authorization::REVIEWER_ROLE
            );
    }

    /**
     * Returns whether the identity can view the problem solution. Only problem
     * admins and identities that have solved the problem can do so.
     */
    public static function canViewProblemSolution(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ) : bool {
        if (is_null($identity->identity_id)) {
            return false;
        }
        return Authorization::canEditProblem($identity, $problem) ||
            ProblemsDAO::isProblemSolved($problem, $identity->identity_id) ||
            ProblemsForfeitedDAO::isProblemForfeited($problem, $identity);
    }

    public static function canViewEmail(\OmegaUp\DAO\VO\Identities $identity) : bool {
        return self::isMentor($identity);
    }

    public static function canCreateGroupIdentities(\OmegaUp\DAO\VO\Identities $identity) : bool {
        return self::isGroupIdentityCreator($identity);
    }

    public static function canViewCourse(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group
    ) : bool {
        if (!Authorization::isCourseAdmin($identity, $course) &&
            !Authorization::isGroupMember($identity, $group)
        ) {
            return false;
        }

        return true;
    }

    public static function isAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        Object $entity
    ) : bool {
        if (is_null($entity) || is_null($identity->user_id)) {
            return false;
        }
        return self::isOwner($identity, $entity->acl_id) ||
            self::hasRole(
                $identity,
                $entity->acl_id,
                Authorization::ADMIN_ROLE
            );
    }

    public static function isContestAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Contests $contest
    ) : bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $contest);
    }

    public static function isInterviewAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Interviews $interview
    ) : bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $interview);
    }

    public static function isProblemAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ) : bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $problem);
    }

    public static function hasRole(
        \OmegaUp\DAO\VO\Identities $identity,
        int $acl_id,
        int $role_id
    ) : bool {
        return GroupRolesDAO::hasRole($identity->identity_id, $acl_id, $role_id) ||
            UserRolesDAO::hasRole($identity->identity_id, $acl_id, $role_id);
    }

    public static function isSystemAdmin(\OmegaUp\DAO\VO\Identities $identity) : bool {
        if (self::$is_system_admin == null) {
            self::$is_system_admin = Authorization::hasRole(
                $identity,
                Authorization::SYSTEM_ACL,
                Authorization::ADMIN_ROLE
            );
        }
        return self::$is_system_admin;
    }

    public static function isQualityReviewer(\OmegaUp\DAO\VO\Identities $identity) : bool {
        if (self::$quality_reviewer_group == null) {
            self::$quality_reviewer_group = GroupsDAO::findByAlias(
                Authorization::QUALITY_REVIEWER_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identity,
            self::$quality_reviewer_group
        );
    }

    public static function isMentor(\OmegaUp\DAO\VO\Identities $identity) : bool {
        if (self::$mentor_group == null) {
            self::$mentor_group = GroupsDAO::findByAlias(
                Authorization::MENTOR_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identity,
            self::$mentor_group
        );
    }

    /**
     * Only last two days of the month mentor is available to choose
     * the coder of the month
     * @return Array
     */
    public static function canChooseCoder(int $currentTimestamp) : bool {
        $today = date('Y-m-d', $currentTimestamp);
        $lastDayOfMonth = date('t', $currentTimestamp);
        $availableDateToChooseCoder = [];
        $availableDateToChooseCoder[] = date('Y-m-', $currentTimestamp) . $lastDayOfMonth;
        $availableDateToChooseCoder[] = date('Y-m-', $currentTimestamp) . ($lastDayOfMonth - 1);
        return in_array($today, $availableDateToChooseCoder);
    }

    public static function isGroupIdentityCreator(\OmegaUp\DAO\VO\Identities $identity) : bool {
        if (self::$groupIdentityCreator == null) {
            self::$groupIdentityCreator = GroupsDAO::findByAlias(
                Authorization::IDENTITY_CREATOR_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identity,
            self::$groupIdentityCreator
        );
    }

    public static function isSupportTeamMember(\OmegaUp\DAO\VO\Identities $identity) : bool {
        if (self::$support_group == null) {
            self::$support_group = GroupsDAO::findByAlias(
                Authorization::SUPPORT_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identity,
            self::$support_group
        );
    }

    public static function isGroupAdmin(\OmegaUp\DAO\VO\Identities $identity, \OmegaUp\DAO\VO\Groups $group) : bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $group);
    }

    private static function isOwner(\OmegaUp\DAO\VO\Identities $identity, int $aclId) : bool {
        $acl = ACLsDAO::getByPK($aclId);
        return $acl->owner_id == $identity->user_id;
    }

    /**
     * An admin is either the group owner or a member of the admin group.
     */
    public static function isCourseAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course
    ) : bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $course);
    }

    private static function isGroupMember(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Groups $group
    ) : bool {
        if (Authorization::isSystemAdmin($identity)) {
            return true;
        }
        $groupUsers = GroupsIdentitiesDAO::getByPK(
            $group->group_id,
            $identity->identity_id
        );
        return !empty($groupUsers);
    }

    public static function isCourseCurator(\OmegaUp\DAO\VO\Identities $identity) : bool {
        if (self::$course_curator_group == null) {
            self::$course_curator_group = GroupsDAO::findByAlias(
                Authorization::COURSE_CURATOR_GROUP_ALIAS
            );
        }
        return Authorization::isGroupMember(
            $identity,
            self::$course_curator_group
        );
    }

    public static function clearCacheForTesting() : void {
        self::$is_system_admin = null;
        self::$quality_reviewer_group = null;
        self::$mentor_group = null;
        self::$support_group = null;
        self::$groupIdentityCreator = null;
    }

    public static function canSubmitToProblemset(
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Problemsets $problemset
    ) : bool {
        if (is_null($problemset)) {
            return false;
        }
        return self::isAdmin($identity, $problemset) ||
               GroupRolesDAO::isContestant(
                   $identity->identity_id,
                   $problemset->acl_id
               );
    }

    public static function canCreatePublicCourse(\OmegaUp\DAO\VO\Identities $identity) : bool {
        return self::isCourseCurator($identity);
    }
}
