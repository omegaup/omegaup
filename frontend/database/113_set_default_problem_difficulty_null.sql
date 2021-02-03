# We used to initialize problem difficulty as 0.
# Resetting to null and letting the cronjob update only
# the allowed problems will solve this.
UPDATE `Problems`
SET `difficulty` = NULL;
