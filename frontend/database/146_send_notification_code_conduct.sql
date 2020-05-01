/*Notify users about Code of Conduct*/
INSERT INTO `Notifications` (`user_id`, `contents`)
SELECT
  `Users`.`user_id`,
  CASE
    WHEN `language_id` = 2 THEN "{\"type\": \"general_notification\", \"message\": \"We have incorporated a Code of Conduct in omegaUp, we invite you to read it.\", \"url\": \"https://blog.omegaup.com/codigo-de-conducta-en-omegaup/\"}"
    WHEN `language_id` = 3 THEN "{\"type\": \"general_notification\", \"message\": \"Incorporamos um Código de Conduta no omegaUp. Convidamos você a lê-lo neste link.\", \"url\": \"https://blog.omegaup.com/codigo-de-conducta-en-omegaup/\"}"
    ELSE "{\"type\": \"general_notification\", \"message\": \"Hemos incorporado un Código de Conducta en omegaUp, te invitamos a leerlo en esta liga.\", \"url\": \"https://blog.omegaup.com/codigo-de-conducta-en-omegaup/\"}"
    END
FROM `Users` INNER JOIN `Identities` ON `Users`.`main_identity_id` = `Identities`.`identity_id`;