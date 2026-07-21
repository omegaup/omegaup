-- Drop old fulltext index on Problems(alias, title) after adding the ngram index
ALTER TABLE `Problems`
    DROP INDEX `ft_alias_title`;
