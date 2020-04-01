select
u2.user_id
from
Users as u2
inner join
(Select distinct user_id,sum(count)over (partition by user_id,time) as count,time
from (
(SELECT
    distinct u.user_id,
    year(c.last_updated) as time,
    count(c.contest_id) over (partition by year(c.last_updated)) as count
FROM
	Users AS u
INNER JOIN
	ACLs AS a ON a.owner_id = u.user_id
INNER JOIN
    Contests AS c ON c.acl_id = a.acl_id
WHERE
    year(c.last_updated) >= year(now())-2)
    union all
    (SELECT
	distinct u.user_id,
    year(p.creation_date) as time,
    count(p.problem_id) over (partition by year(p.creation_date)) as count
FROM
	Users AS u
INNER JOIN
	ACLs AS a ON a.owner_id = u.user_id
INNER JOIN
    Problems AS p ON p.acl_id = a.acl_id
WHERE
    year(p.creation_date) >= year(now())-2)
  union all
  (SELECT
	distinct u.user_id,
	year(s.time) as time,
	count(s.problem_id) over (partition by year(s.time)) as count
FROM
	Users AS u
INNER JOIN
	Submissions AS s ON s.identity_id = u.main_identity_id
INNER JOIN
	Runs AS r ON r.run_id = s.current_run_id
WHERE
    r.verdict = 'AC' AND year(s.time)>=year(now())-2)
) valores
group by valores.user_id,valores.count,valores.time
) as total on total.user_id=u2.user_id
group by u2.user_id
having
count(total.user_id)= 3;