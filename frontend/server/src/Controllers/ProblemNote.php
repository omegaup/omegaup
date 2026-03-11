<?php

namespace OmegaUp\Controllers;

/**
 * ProblemNoteController
 *
 * @psalm-type ProblemNoteItem=array{alias: string, title: string, note_text: string, problem_id: int}
 */
class ProblemNote extends \OmegaUp\Controllers\Controller {
    private const MAX_NOTE_LENGTH = 2000;

    /**
     * Save (create or update) a note for the current user on a problem
     *
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param string $note_text
     *
     * @return array{status: 'ok'}
     */
    public static function apiSave(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $noteText = $r->ensureString(
            'note_text',
            fn (string $text) => \OmegaUp\Validators::stringOfLengthInRange(
                $text,
                1,
                self::MAX_NOTE_LENGTH
            )
        );

        $targetProblem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($targetProblem) || is_null($targetProblem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $currentIdentityId = intval($r->identity->identity_id);

        \OmegaUp\DAO\ProblemNotes::upsert(
            new \OmegaUp\DAO\VO\ProblemNotes([
                'identity_id' => $currentIdentityId,
                'problem_id' => $targetProblem->problem_id,
                'note_text' => $noteText,
            ])
        );

        return ['status' => 'ok'];
    }

    /**
     * Delete a note for the current user on a problem
     *
     * @omegaup-request-param string $problem_alias
     *
     * @return array{status: 'ok'}
     */
    public static function apiDelete(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $targetProblem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($targetProblem) || is_null($targetProblem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $currentIdentityId = intval($r->identity->identity_id);
        $existingNote = \OmegaUp\DAO\ProblemNotes::getByPK(
            $currentIdentityId,
            $targetProblem->problem_id
        );
        if (is_null($existingNote)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'recordNotFound'
            );
        }

        \OmegaUp\DAO\ProblemNotes::delete($existingNote);

        return ['status' => 'ok'];
    }

    /**
     * Get a single note for the current user on a specific problem
     *
     * @omegaup-request-param string $problem_alias
     *
     * @return array{note_text: null|string}
     */
    public static function apiGet(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $targetProblem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($targetProblem) || is_null($targetProblem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $currentIdentityId = intval($r->identity->identity_id);
        $existingNote = \OmegaUp\DAO\ProblemNotes::getByPK(
            $currentIdentityId,
            $targetProblem->problem_id
        );

        return [
            'note_text' => !is_null($existingNote)
                ? $existingNote->note_text
                : null,
        ];
    }

    /**
     * Get all notes for the current user
     *
     * @return array{notes: list<ProblemNoteItem>, total: int}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $currentIdentityId = intval($r->identity->identity_id);
        $notes = \OmegaUp\DAO\ProblemNotes::getAllNotesForIdentity(
            $currentIdentityId
        );

        return [
            'notes' => $notes,
            'total' => count($notes),
        ];
    }
}
