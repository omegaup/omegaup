'''Shared constants for cron jobs.

These mirror the values defined in frontend/server/src/Authorization.php
(SYSTEM_ACL, ADMIN_ROLE) and frontend/server/src/ProblemParams.php
(PROBLEM_VISIBILITY_PUBLIC), plus the historical QualityNomination cutoff.
'''

# acl_id of the system ACL (Authorization::SYSTEM_ACL).
SYSTEM_ACL = 1

# role_id of the site admin role (Authorization::ADMIN_ROLE).
ADMIN_ROLE = 1

# QualityNomination id from which the question-change format applies.
QUALITYNOMINATION_QUESTION_CHANGE_ID = 18663

# Lowest `Problems`.`visibility` the catalog offers to a normal user
# (ProblemParams::VISIBILITY_PUBLIC), the threshold DAO\Problems uses when
# it lists problems. The column comment is stale: banned is -2, not -1.
PROBLEM_VISIBILITY_PUBLIC = 2
