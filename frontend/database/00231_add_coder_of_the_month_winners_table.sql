-- Add table to store the winners of the coder of the month badge.
CREATE TABLE `Coder_Of_The_Month_Winners` (
  `coder_of_the_month_winners_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `comment` tinytext COMMENT 'Útil cuando se selecciona manualmente a un usuario que no obtuvo el primer lugar para dar una explicación breve de por qué se seleccionó.',
  `time` date NOT NULL DEFAULT '2000-01-01' COMMENT 'Primer día del mes en el que el usuario fue seleccionado. Ejemplo: 2024-08-01 para el coder que mas puntos obtuvo en julio de 2024.',
  `ranking` int NOT NULL COMMENT 'El lugar en el que el usuario estuvo durante ese mes. Útil cuando se selecciona manualmente a un usuario que no obtuvo el primer lugar.',
  `selected_by` int DEFAULT NULL COMMENT 'Id de la identidad que seleccionó al coder del mes manualmente.',
  `school_id` int DEFAULT NULL,
  `category` enum('all','female') NOT NULL DEFAULT 'all',
  `score` double NOT NULL DEFAULT '0.0',
  `problems_solved` int NOT NULL DEFAULT '0',
  `certificate_status` enum('uninitiated','queued','generated','retryable_error','fatal_error') NOT NULL DEFAULT 'uninitiated' COMMENT 'Estado de la petición de generar diplomas',
  PRIMARY KEY (`coder_of_the_month_winners_id`),
  KEY `coder_of_the_month_winners_id` (`coder_of_the_month_winners_id`),
  KEY `user_id` (`user_id`),
  KEY `selected_by` (`selected_by`),
  KEY `school_id` (`school_id`),
  KEY `rank_time_category` (`category`,`ranking`,`time`),
  KEY `time_category` (`category`,`time`),
  CONSTRAINT `fk_cotmws_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`),
  CONSTRAINT `fk_cotmwi_identity_id` FOREIGN KEY (`selected_by`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_cotmwu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guardar histórico de coders del mes por categoría de forma sencilla.';

-- Insert the winners of the coder of the month badge.
INSERT INTO `Coder_Of_The_Month_Winners` (
    `user_id`,
    `comment`,
    `time`,
    `ranking`,
    `selected_by`,
    `school_id`,
    `category`,
    `score`,
    `problems_solved`
)
SELECT
    cm.user_id,
    CASE
        WHEN cm.selected_by IS NULL THEN 'Ganador del mes seleccionado automáticamente'
        ELSE 'Ganador del mes seleccionado manualmente'
    END AS comment,
    cm.time,
    cm.ranking,
    cm.selected_by,
    i.current_identity_school_id AS school_id,
    cm.category,
    cm.score,
    cm.problems_solved
FROM
    Coder_Of_The_Month cm
INNER JOIN
    Users u ON u.user_id = cm.user_id
INNER JOIN
    Identities i ON i.identity_id = u.main_identity_id
LEFT JOIN
    User_Rank ur ON ur.user_id = cm.user_id
WHERE
    (cm.selected_by IS NOT NULL
    OR (
        cm.`ranking` = 1 AND
        NOT EXISTS (
            SELECT
                *
            FROM
                Coder_Of_The_Month
            WHERE
                time = cm.time AND
                selected_by IS NOT NULL
        )
    ))
ORDER BY
    cm.time ASC;