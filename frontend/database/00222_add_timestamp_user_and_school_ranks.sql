-- User_Rank table
SET @dbname = DATABASE();
SET @tablename = "User_Rank";
SET @columnname = "timestamp";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se actualiza el rank de usuario';")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
