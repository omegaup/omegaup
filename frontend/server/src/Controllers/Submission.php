<?php

 namespace OmegaUp\Controllers;

/**
 * SubmissionController
 */
class Submission extends \OmegaUp\Controllers\Controller {
    public static function getSource(string $guid): string {
        return \OmegaUp\Grader::GetInstance()->getSource($guid);
    }

    /**
     * Returns the latest submissions
     *
     * @return array{submissions: list<array{time: int, username: string, school_id: int, school_name: string, alias: string, title: string, language: string, verdict: string, runtime: int, memory: int}>, totalRows: int}
     */
    public static function apiLatestSubmissions(\OmegaUp\Request $r) {
        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);

        $offset = is_null($r['offset']) ? 1 : intval($r['offset']);
        $rowCount = is_null($r['rowcount']) ? 100 : intval($r['rowcount']);

        $identityId = null;
        if (!is_null($r['user'])) {
            \OmegaUp\Validators::validateValidUsername(
                $r['user'],
                'user'
            );
            $userIdentity = \OmegaUp\DAO\Identities::FindByUsername($r['user']);
            if (is_null($userIdentity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $identityId = $userIdentity->identity_id;
        }

        return \OmegaUp\DAO\Submissions::getLatestSubmissions(
            $offset,
            $rowCount,
            $identityId
        );
    }
}
