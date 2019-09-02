<?php

/**
 * AuthorizationController
 */
class AuthorizationController extends \OmegaUp\Controllers\Controller {
    public static function apiProblem(\OmegaUp\Request $r) {
        // This is not supposed to be called by end-users, but by the
        // gitserver. Regular sessions cannot be used since they
        // expire, so use a pre-shared secret to authenticate that
        // grants admin-level privileges just for this call.
        if ($r['token'] !== OMEGAUP_GRADER_SECRET) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $resolvedIdentity = IdentityController::resolveIdentity($r['username']);

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $isAdmin = \OmegaUp\Authorization::isProblemAdmin($resolvedIdentity, $problem);
        $canEdit = $isAdmin || \OmegaUp\Authorization::canEditProblem($resolvedIdentity, $problem);
        return [
            'status' => 'ok',
            'has_solved' => \OmegaUp\DAO\Problems::isProblemSolved($problem, $resolvedIdentity->identity_id),
            'is_admin' => $isAdmin,
            'can_view' => $canEdit || \OmegaUp\DAO\Problems::isVisible($problem),
            'can_edit' => $canEdit,
        ];
    }
}
