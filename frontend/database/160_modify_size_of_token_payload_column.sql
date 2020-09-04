-- Course_Clone_Log
ALTER TABLE `Course_Clone_Log`
    MODIFY COLUMN `token_payload` varchar(220) NOT NULL
        COMMENT 'Claims del token usado para intentar clonar, independientemente de si fue exitoso o no.';
