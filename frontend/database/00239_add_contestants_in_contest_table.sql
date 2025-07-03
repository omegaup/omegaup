-- Add index in Problemsets table for contest_id and problemset_id
CREATE INDEX idx_pp_contest_problemset ON Problemsets (contest_id, problemset_id);

-- Add column contestants into the Contests table
ALTER TABLE `Contests`
  ADD COLUMN `contestants` INT NOT NULL DEFAULT 0,
  ADD INDEX `idx_archived_recommended_endcheck` (`archived`, `recommended`, `finish_time`, `start_time`),
  ADD INDEX `idx_admission_archived_recommended_endcheck` (`admission_mode`, `archived`, `recommended`, `finish_time`, `start_time`);

-- Calculate the number of participants in each contest
UPDATE Contests c
SET contestants = (
  SELECT COUNT(*) FROM (
    SELECT
      i.identity_id
    FROM
      (
        SELECT
          raw_identities.identity_id
        FROM
          (
            SELECT
              pi.identity_id
            FROM
              Problemset_Identities pi
            WHERE
              pi.problemset_id = c.problemset_id

            UNION

            SELECT
              gi.identity_id
            FROM
              Group_Roles gr
            INNER JOIN
              Groups_Identities gi
            ON
              gi.group_id = gr.group_id
            WHERE
              gr.acl_id = c.acl_id AND gr.role_id = 2 -- contestant role
          ) AS raw_identities
        GROUP BY
          raw_identities.identity_id
      ) AS ri
    INNER JOIN
      Identities i ON i.identity_id = ri.identity_id
    WHERE
      (
        i.user_id NOT IN (
          SELECT ur.user_id FROM User_Roles ur WHERE ur.acl_id IN (c.acl_id, 1) AND ur.role_id = 1
        )
        AND i.identity_id NOT IN (
          SELECT
            gi.identity_id
          FROM
            Group_Roles gr
          INNER JOIN
            Groups_Identities gi ON gi.group_id = gr.group_id
          WHERE
            gr.acl_id IN (c.acl_id, 1) AND gr.role_id = 1  -- administrator role
        )
        AND i.user_id != (SELECT a.owner_id FROM ACLs a WHERE a.acl_id = c.acl_id)
        OR i.user_id IS NULL
      )
  ) AS participants
);
