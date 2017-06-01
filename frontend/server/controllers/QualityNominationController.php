<?php
require_once 'libs/dao/QualityNominations.dao.php';

class QualityNominationController extends Controller {
    /**
     * Creates a new QualityNomination
     *
     * @param Request $r
     * @return array
     * @throws DuplicatedEntryInDatabaseException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiCreate(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Validate request
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');
        Validators::isInEnum($r['nomination'], 'nomination', ['promotion', 'demotion']);
        Validators::isStringNonEmpty($r['contents'], 'contents');

        $contents = json_decode($r['contents'], true /*assoc*/);
        if (!is_array($contents)
            || (!isset($contents['rationale']) || !is_string($contents['rationale']) || empty($contents['rationale']))
        ) {
            throw new InvalidParameterException('parameterInvalid', 'contents');
        }

        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        if ($r['nomination'] == 'promotion') {
            // When a problem is being nominated for promotion, the user
            // nominating it must have already solved it.
            if (!ProblemsDAO::isProblemSolved($problem, $r['current_user'])) {
                throw new PreconditionFailedException('qualityNominationMustHaveSolvedProblem');
            }
            if ((!isset($contents['statement']) || !is_string($contents['statement']) || empty($contents['statement']))
                || (!isset($contents['source']) || !is_string($contents['source']) || empty($contents['source']))
                || (!isset($contents['tags']) || !is_array($contents['tags']))
            ) {
                throw new InvalidParameterException('parameterInvalid', 'contents');
            }
        } elseif ($r['nomination'] == 'demotion') {
            if (!isset($contents['reason']) || !in_array($contents['reason'], ['duplicate', 'offensive'])) {
                throw new InvalidParameterException('parameterInvalid', 'contents');
            }
            // Duplicate reports need more validation.
            if ($contents['reason'] == 'duplicate') {
                if (!isset($contents['original']) || !is_string($contents['original']) || empty($contents['original'])) {
                    throw new InvalidParameterException('parameterInvalid', 'contents');
                }
                $original = ProblemsDAO::getByAlias($contents['original']);
                if (is_null($original)) {
                    throw new NotFoundException('problemNotFound');
                }
            }
        }

        // Create object
        QualityNominationsDAO::save(new QualityNominations([
            'user_id' => $r['current_user_id'],
            'problem_id' => $problem->problem_id,
            'nomination' => $r['nomination'],
            'contents' => json_encode($contents), // re-encoding it for normalization.
            'status' => 'open',
        ]));

        return ['status' => 'ok'];
    }
}
