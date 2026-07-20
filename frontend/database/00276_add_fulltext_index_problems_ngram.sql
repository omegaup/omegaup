-- Replace old fulltext index with ngram fulltext index on Problems(alias, title)
ALTER TABLE `Problems`
    DROP INDEX `ft_alias_title`,
    ADD FULLTEXT `ft_alias_title_ngram` (`alias`, `title`) WITH PARSER ngram;
