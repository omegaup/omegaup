CREATE TABLE `Users_Experiments` (
  `user_id` int(11) NOT NULL,
  `experiment` varchar(256) NOT NULL,
  KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guarda los experimentos habilitados para un usuario.';

ALTER TABLE `Users_Experiments`
  ADD CONSTRAINT `fk_ueu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
