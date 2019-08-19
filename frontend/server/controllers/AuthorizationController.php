<?php

/**
 * AuthorizationController
 */
class AuthorizationController extends Controller {
    public static function apiProblem(Request $r) {
        // This is not supposed to be called by end-users, but by the
        // gitserver. Regular sessions cannot be used since they
        // expire, so use a pre-shared secret to authenticate that
        // grants admin-level privileges just for this call.
        if ($r['token'] !== OMEGAUP_GRADER_SECRET) {
            throw new ForbiddenAccessException();
        }

        $resolvedIdentity = IdentityController::resolveIdentity($r['username']);

        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        $isAdmin = Authorization::isProblemAdmin($resolvedIdentity, $problem);
        $canEdit = $isAdmin || Authorization::canEditProblem($resolvedIdentity, $problem);
        return [
            'status' => 'ok',
            'has_solved' => ProblemsDAO::isProblemSolved($problem, $resolvedIdentity->identity_id),
            'is_admin' => $isAdmin,
            'can_view' => $canEdit || ProblemsDAO::isVisible($problem),
            'can_edit' => $canEdit,
        ];
    }
}
