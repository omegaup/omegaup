-- Add ngram FULLTEXT index to Problems table for alias/title substring search
ALTER TABLE `Problems`
    ADD FULLTEXT `ft_alias_title_ngram` (`alias`, `title`) WITH PARSER ngram;
