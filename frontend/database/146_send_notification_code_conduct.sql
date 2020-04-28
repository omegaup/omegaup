/*Notify users about Code of Conduct*/
INSERT INTO `Notifications` (`user_id`, `contents`)
SELECT 
  `Users`.`user_id`,  
  CASE 
    WHEN `language_id` = 2 THEN "{\"type\": \"general_notification\", \"message\": \"We have incorporated a Code of Conduct in omegaUp, we invite you to read it <a href='https://blog.omegaup.com/codigo-de-conducta-en-omegaup/'>here</a>.\"}"
    WHEN `language_id` = 3 THEN "{\"type\": \"general_notification\", \"message\": \"Incorporamos um Código de Conduta no omegaUp. Convidamos você a lê-lo neste <a href='https://blog.omegaup.com/codigo-de-conducta-en-omegaup/'>link</a>.\"}" 
    ELSE "{\"type\": \"general_notification\", \"message\": \"Hemos incorporado un Código de Conducta en omegaUp, te invitamos a leerlo en esta <a href='https://blog.omegaup.com/codigo-de-conducta-en-omegaup/'>liga</a>.\"}"
    END
FROM `Users` INNER JOIN `Identities` ON `Users`.`main_identity_id` = `Identities`.`identity_id`;