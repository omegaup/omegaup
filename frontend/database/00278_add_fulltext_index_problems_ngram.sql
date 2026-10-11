-- Replace old fulltext index on Problems(alias, title) with the ngram index
ALTER TABLE `Problems`
    DROP INDEX `ft_alias_title`,
    ADD FULLTEXT `ft_alias_title_ngram` (`alias`, `title`) WITH PARSER ngram;
