<?
include('../server/bootstrap.php');

$r = new Request($_REQUEST);

// This is not supposed to be called by end-users, but by the
// Grader service. Regular sessions cannot be used since they
// expire, so use a pre-shared secret to authenticate that
// grants admin-level privileges just for this call.
if ($r['token'] !== OMEGAUP_GRADER_SECRET) {
	header('HTTP/1.1 404 Not Found');
	die();
}

$id = 0;
// In case of emergency, pass the alias of the contest to expire.
if (isset($r['alias'])) {
	$contest = ContestsDAO::getByAlias($r['alias']);
	$id = $contest->getContestId();
} else {
	$id = $r['id'];
}

Scoreboard::RefreshScoreboardCache($id);
echo 'OK';
