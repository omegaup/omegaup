ALTER TABLE
    `Problems`
MODIFY COLUMN `visibility` ENUM('-10','-3','-2','-1','0','1','2','3') NOT NULL DEFAULT '2' COMMENT '-10 deleted, -3 private_banned, -2 public_banned, -1 private_warning, 0 private, 1 public_warning, 2 public, 3 promoted';