#!/usr/bin/python

import argparse
import getpass
import hashlib
import MySQLdb
import sys
import time
import urllib
import urllib2

parser = argparse.ArgumentParser(description='Replay a contest')

parser.add_argument('--user', type=str, help='MySQL username', required=True)
parser.add_argument('--database', type=str, help='MySQL database', required=True)
parser.add_argument('--password', type=str, help='MySQL password')
parser.add_argument('contest', type=str, help='Contest alias')

args = parser.parse_args()
password = args.password
if not password:
	password = getpass.getpass()

db = MySQLdb.connect(
	host='localhost',
	user=args.user,
	passwd=password,
	db=args.database
)
cur = db.cursor()

# Get contest ID
if cur.execute('SELECT contest_id FROM Contests WHERE alias = %s', args.contest) != 1:
	print>> sys.stderr, "Failed to load contest %s" % args.contest
	sys.exit(1)

contest_alias = args.contest
contest_id = cur.fetchone()[0]

# Create new contest
t = int(time.time())
new_alias = "%s_%d" % (contest_alias, t)
scoreboard_token = hashlib.md5(new_alias + '_scoreboard_admin').hexdigest()[:30]
cur.execute("""INSERT INTO Contests(
		title, description, start_time, finish_time, director_id, rerun_id, alias,
		feedback, penalty_time_start, penalty_calc_policy, scoreboard_url,
		scoreboard_url_admin
	) VALUES(%s, %s, %s, %s, 1, 0, %s, "yes", "none", "sum", %s, %s);""",
	(new_alias,
	"Replay of %s" % contest_alias,
	time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime(t)),
	time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime(t + 3600 * 5)),
	new_alias,
	hashlib.md5(new_alias + '_scoreboard').hexdigest()[:30],
	scoreboard_token))
new_id = cur.lastrowid

# Add old problems
cur.execute("""INSERT INTO Contest_Problems
	SELECT %s AS contest_id, problem_id, points, `order` FROM Contest_Problems
	WHERE contest_id = %s;""", (new_id, contest_id))
db.commit()

# Allow user to open the contest to see the shiny display
print>>sys.stderr,
	'http://localhost:8080/arena/%s/scoreboard/%s?ws=on' % (new_alias, scoreboard_token)
print>>sys.stderr, 'Press Enter to continue...',
raw_input()

# Replay all runs, one after the other
num_rows = cur.execute('SELECT * FROM Runs WHERE contest_id = %s;', (contest_id))
idx = 0
times = []
relevant_users = {}
t0_all = time.time()
for row in cur.fetchall():
	# Add user to Contest_Users if first time sending
	if row[1] not in relevant_users:
		relevant_users[row[1]] = True
		cur.execute(
			'INSERT INTO Contests_Users (user_id, contest_id) VALUES (%s, %s);',
			(row[1], new_id)
		)

	# Add run
	cur.execute("""INSERT INTO Runs (
		user_id, problem_id, contest_id, guid, language, status, veredict, runtime,
		memory, score, contest_score, ip, submit_delay, test, judged_by, time
	) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s);""",
	(row[1], row[2], new_id, hashlib.md5(new_alias + row[4]).hexdigest()) + row[5:13] + row[14:] + (time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime()),))
	run_id = cur.lastrowid
	db.commit()
	idx += 1
	print>>sys.stderr, '%2.3f%%\r' % (100.0 * idx / num_rows),

	# Force scoreboard regeneration
	t0 = time.time()
	response = urllib2.urlopen(
		'http://localhost/api/scoreboard/refresh/',
		urllib.urlencode({'token': 'secret', 'alias': new_alias, 'run': str(run_id)})
	).read()
	t1 = time.time()
	assert(response == '{"status":"ok"}')
	times.append(t1 - t0)
t1_all = time.time()

cur.close()
db.close()

# Print some stats
print t1_all - t0_all
print times
