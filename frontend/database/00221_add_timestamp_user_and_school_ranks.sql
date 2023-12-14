-- User_Rank table
ALTER TABLE `User_Rank`
ADD COLUMN `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se actualiza el rank de usuario';

-- Schools table
ALTER TABLE `Schools`
ADD COLUMN `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se actualiza el rank de escuelas';
