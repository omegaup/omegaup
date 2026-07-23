<?php

namespace OmegaUp\DAO;

/**
 * RecommendationModelRuns Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\RecommendationModelRuns}.
 */
class RecommendationModelRuns extends \OmegaUp\DAO\Base\RecommendationModelRuns {
    /**
     * Returns the most recent training run that was actually published. Used as
     * the baseline the quality guardrail compares the new model against.
     */
    public static function getLatestPublished(
    ): ?\OmegaUp\DAO\VO\RecommendationModelRuns {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\RecommendationModelRuns::FIELD_NAMES,
            'Recommendation_Model_Runs'
        );
        $sql = "SELECT {$fields}
                FROM Recommendation_Model_Runs
                WHERE published = 1
                ORDER BY created_at DESC
                LIMIT 1;";
        /** @var array{cron_run_id: int|null, created_at: \OmegaUp\Timestamp, dataset_size: int|null, followup_decay: float|null, map_score: float|null, model_run_id: int, num_followups: int|null, output_path: null|string, published: bool, skip_reason: null|string, train_fraction: float|null}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, []);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\RecommendationModelRuns($row);
    }

    /**
     * Returns the most recent training runs, newest first, for the admin view
     * of how the model quality has moved over time.
     *
     * @return list<\OmegaUp\DAO\VO\RecommendationModelRuns>
     */
    public static function getRecent(
        int $limit = 20
    ): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\RecommendationModelRuns::FIELD_NAMES,
            'Recommendation_Model_Runs'
        );
        $sql = "SELECT {$fields}
                FROM Recommendation_Model_Runs
                ORDER BY created_at DESC
                LIMIT ?;";
        /** @var list<array{cron_run_id: int|null, created_at: \OmegaUp\Timestamp, dataset_size: int|null, followup_decay: float|null, map_score: float|null, model_run_id: int, num_followups: int|null, output_path: null|string, published: bool, skip_reason: null|string, train_fraction: float|null}> */
        $rows = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$limit]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[] = new \OmegaUp\DAO\VO\RecommendationModelRuns($row);
        }
        return $result;
    }
}
