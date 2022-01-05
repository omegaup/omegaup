ALTER TABLE `User_Rank`
    ADD COLUMN `classname` varchar(50) DEFAULT NULL COMMENT 'Almacena la clase precalculada para no tener que determinarla en tiempo de ejecucion.';
