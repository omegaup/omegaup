-- Add ngram FULLTEXT index to Problems(alias, title)
ALTER TABLE `Problems`
    ADD FULLTEXT `ft_alias_title_ngram` (`alias`, `title`) WITH PARSER ngram;
