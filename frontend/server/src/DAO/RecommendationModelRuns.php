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
     * Returns the most recent training runs, newest first. `created_at` only
     * keeps seconds, so the id breaks the tie between two runs recorded within
     * the same second.
     *
     * @return list<\OmegaUp\DAO\VO\RecommendationModelRuns>
     */
    public static function getRecent(int $limit = 20): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\RecommendationModelRuns::FIELD_NAMES,
            'Recommendation_Model_Runs'
        );
        $sql = "SELECT {$fields}
                FROM Recommendation_Model_Runs
                ORDER BY created_at DESC, model_run_id DESC
                LIMIT ?;";
        /** @var list<array{created_at: \OmegaUp\Timestamp, cron_run_id: int|null, dataset_size: int, followup_decay: float, map_score: float, model_run_id: int, num_followups: int, output_path: null|string, published: bool, rng_seed: int|null, skip_reason: null|string, train_fraction: float}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$limit]
        );
        $modelRuns = [];
        foreach ($rs as $row) {
            $modelRuns[] = new \OmegaUp\DAO\VO\RecommendationModelRuns($row);
        }
        return $modelRuns;
    }
}
