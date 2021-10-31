-- Modify Contests table, added default_show_all_contestants_in_scoreboard column
ALTER TABLE `Contests`
    ADD COLUMN `default_show_all_contestants_in_scoreboard` tinyint(1) DEFAULT 0
        COMMENT 'Bandera que indica si en el scoreboard se mostrarán todos los concursantes por defecto.';

