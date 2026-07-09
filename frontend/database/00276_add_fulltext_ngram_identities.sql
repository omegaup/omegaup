-- Reemplaza el índice FULLTEXT por uno con parser ngram, que permite
-- encontrar subcadenas (no solo palabras completas) y elimina la
-- necesidad del LIKE '%...%' que no podía usar ningún índice
ALTER TABLE `Identities`
DROP INDEX `ft_user_username`,
ADD FULLTEXT INDEX `ft_user_username_ngram` (`username`, `name`) WITH PARSER ngram;
