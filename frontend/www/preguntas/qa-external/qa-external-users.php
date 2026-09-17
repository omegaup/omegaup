<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	File: qa-external-example/qa-external-users.php
	Description: Example of how to integrate with your own user database


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/


/*
	=========================================================================
	THIS FILE ALLOWS YOU TO INTEGRATE WITH AN EXISTING USER MANAGEMENT SYSTEM
	=========================================================================

	It is used if QA_EXTERNAL_USERS is set to true in qa-config.php.
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../');
	exit;
}


function ou_get_omegaup_root($include_port = false)
{
	$protocol = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
	if ($include_port) {
		return $protocol . $_SERVER['HTTP_HOST'];
	} else {
		return $protocol . explode(':', $_SERVER['HTTP_HOST'])[0];
	}
}


/**
 * ==========================================================================
 * YOU MUST MODIFY THIS FUNCTION *BEFORE* Q2A CREATES ITS DATABASE
 * ==========================================================================
 *
 * You should return the appropriate MySQL column type to use for the userid,
 * for smooth integration with your existing users. Allowed options are:
 *
 * SMALLINT, SMALLINT UNSIGNED, MEDIUMINT, MEDIUMINT UNSIGNED, INT, INT UNSIGNED,
 * BIGINT, BIGINT UNSIGNED or VARCHAR(x) where x is the maximum length.
 */
function qa_get_mysql_user_column_type()
{
	return 'VARCHAR(100)';
}


/**
 * ===========================================================================
 * YOU MUST MODIFY THIS FUNCTION, BUT CAN DO SO AFTER Q2A CREATES ITS DATABASE
 * ===========================================================================
 *
 * You should return an array containing URLs for the login, register and logout pages on
 * your site. These URLs will be used as appropriate within the Q2A site.
 *
 * You may return absolute or relative URLs for each page. If you do not want one of the links
 * to show, omit it from the array, or use null or an empty string.
 *
 * If you use absolute URLs, then return an array with the URLs in full (see example 1 below).
 *
 * If you use relative URLs, the URLs should start with $relative_url_prefix, followed by the
 * relative path from the root of the Q2A site to your login page. Like in example 2 below, if
 * the Q2A site is in a subdirectory, $relative_url_prefix.'../' refers to your site root.
 *
 * Now, about $redirect_back_to_url. Let's say a user is viewing a page on the Q2A site, and
 * clicks a link to the login URL that you returned from this function. After they log in using
 * the form on your main site, they want to automatically go back to the page on the Q2A site
 * where they came from. This can be done with an HTTP redirect, but how does your login page
 * know where to redirect the user to? The solution is $redirect_back_to_url, which is the URL
 * of the page on the Q2A site where you should send the user once they've successfully logged
 * in. To implement this, you can add $redirect_back_to_url as a parameter to the login URL
 * that you return from this function. Your login page can then read it in from this parameter,
 * and redirect the user back to the page after they've logged in. The same applies for your
 * register and logout pages. Note that the URL you are given in $redirect_back_to_url is
 * relative to the root of the Q2A site, so you may need to add something.
 */
function qa_get_login_links($relative_url_prefix, $redirect_back_to_url)
{
	return array(
		'login' => ou_get_omegaup_root(true) . '/login.php?redirect='.urlencode('/preguntas/'.$redirect_back_to_url),
		'register' => ou_get_omegaup_root(true) . '/login.php',
		'logout' => ou_get_omegaup_root(true) . '/logout.php?redirect='.urlencode('/preguntas/'.$redirect_back_to_url)
	);
}


/**
 * ===========================================================================
 * YOU MUST MODIFY THIS FUNCTION, BUT CAN DO SO AFTER Q2A CREATES ITS DATABASE
 * ===========================================================================
 *
 * qa_get_logged_in_user()
 *
 * You should check (using $_COOKIE, $_SESSION or whatever is appropriate) whether a user is
 * currently logged in. If not, return null. If so, return an array with the following elements:
 *
 * - userid: a user id appropriate for your response to qa_get_mysql_user_column_type()
 * - publicusername: a user description you are willing to show publicly, e.g. the username
 * - email: the logged in user's email address
 * - passsalt: (optional) password salt specific to this user, used for form security codes
 * - level: one of the QA_USER_LEVEL_* values below to denote the user's privileges:
 *
 * QA_USER_LEVEL_BASIC, QA_USER_LEVEL_EDITOR, QA_USER_LEVEL_ADMIN, QA_USER_LEVEL_SUPER
 *
 * To indicate that the user is blocked you can also add an element 'blocked' with the value true.
 * Blocked users are not allowed to perform any write actions such as voting or posting.
 *
 * The result of this function will be passed to your other function qa_get_logged_in_user_html()
 * so you may add any other elements to the returned array if they will be useful to you.
 *
 * Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
 * Q2A, you can also use the various qa_db_* functions to run queries.
 *
 * In order to access the admin interface of your Q2A site, ensure that the array element 'level'
 * contains QA_USER_LEVEL_ADMIN or QA_USER_LEVEL_SUPER when you are logged in.
 */
function qa_get_logged_in_user()
{
	if (!isset($_COOKIE['ouat'])) {
		return null;
	}

	$options = array(
		'http' => array(
			'header' => 'Content-Type: application/x-www-form-urlencoded',
			'method' => 'POST',
			'content' => http_build_query(array('auth_token' => $_COOKIE['ouat'])),
		),
	);
	$url = ou_get_omegaup_root() . '/api/session/currentSession/';
	$context = stream_context_create($options);
	$json = file_get_contents($url, false, $context);
	if ($json === false) {
		return null;
	}

	$response = json_decode($json);
	if ($response === null || !$response->session->valid) {
		return null;
	}

	$admins = array(
		'lhchavez' => QA_USER_LEVEL_SUPER,
		'omegaup' => QA_USER_LEVEL_SUPER,
		'Lilia' => QA_USER_LEVEL_MODERATOR,
		'Denisse.Rosales' => QA_USER_LEVEL_ADMIN,
	);
	return array(
		'userid' => $response->session->user->username,
		'publicusername' => $response->session->user->username,
		'email' => $response->session->email,
		'level' => (array_key_exists($response->session->user->username, $admins) ?
			$admins[$response->session->user->username] : QA_USER_LEVEL_BASIC),
	);
}


/**
 * ===========================================================================
 * YOU MUST MODIFY THIS FUNCTION, BUT CAN DO SO AFTER Q2A CREATES ITS DATABASE
 * ===========================================================================
 *
 * qa_get_user_email($userid)
 *
 * Return the email address for user $userid, or null if you don't know it.
 *
 * Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
 * Q2A, you can also use the various qa_db_* functions to run queries.
 */
function qa_get_user_email($userid)
{
	$result = qa_db_read_one_assoc(qa_db_query_sub(
		'SELECT
			e.email
		FROM
			omegaup.Users u
		INNER JOIN
			omegaup.Identities i ON i.identity_id = u.main_identity_id
		INNER JOIN
			omegaup.Emails e ON e.email_id = u.main_email_id
		WHERE
			i.username = #;',
		$userid
	), true);

	if (!is_array($result))
		return null;

	return $result['email'];
}


/**
 * ===========================================================================
 * YOU MUST MODIFY THIS FUNCTION, BUT CAN DO SO AFTER Q2A CREATES ITS DATABASE
 * ===========================================================================
 *
 * qa_get_userids_from_public($publicusernames)
 *
 * You should take the array of public usernames in $publicusernames, and return an array which
 * maps valid usernames to internal user ids. For each element of this array, the username should be
 * in the key, with the corresponding user id in the value. If your usernames are case- or accent-
 * insensitive, keys should contain the usernames as stored, not necessarily as in $publicusernames.
 *
 * Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
 * Q2A, you can also use the various qa_db_* functions to run queries. If you access this database or
 * any other, try to use a single query instead of one per user.
 */
function qa_get_userids_from_public($publicusernames)
{
	$publictouserid = array();

	foreach ($publicusernames as $publicusername)
		$publictouserid[$publicusername] = $publicusername;

	return $publictouserid;
}


/**
 * ===========================================================================
 * YOU MUST MODIFY THIS FUNCTION, BUT CAN DO SO AFTER Q2A CREATES ITS DATABASE
 * ===========================================================================
 *
 * qa_get_public_from_userids($userids)
 *
 * This is exactly like qa_get_userids_from_public(), but works in the other direction.
 *
 * You should take the array of user identifiers in $userids, and return an array which maps valid
 * userids to public usernames. For each element of this array, the userid you were given should
 * be in the key, with the corresponding username in the value.
 *
 * Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
 * Q2A, you can also use the various qa_db_* functions to run queries. If you access this database or
 * any other, try to use a single query instead of one per user.
 */
function qa_get_public_from_userids($userids)
{
	$useridtopublic = array();

	foreach ($userids as $userid)
		$useridtopublic[$userid] = $userid;

	return $useridtopublic;
}


/**
 * ==========================================================================
 * YOU MAY MODIFY THIS FUNCTION, BUT THE DEFAULT BELOW WILL WORK OK
 * ==========================================================================
 *
 * qa_get_logged_in_user_html($logged_in_user, $relative_url_prefix)
 *
 * You should return HTML code which identifies the logged in user, to be displayed next to the
 * logout link on the Q2A pages. This HTML will only be shown to the logged in user themselves.
 * Note: the username MUST be escaped with htmlspecialchars() for general output, or urlencode()
 * for link URLs.
 *
 * $logged_in_user is the array that you returned from qa_get_logged_in_user(). Hopefully this
 * contains enough information to generate the HTML without another database query, but if not,
 * call qa_db_connection() to get the connection to the Q2A database.
 *
 * $relative_url_prefix is a relative URL to the root of the Q2A site, which may be useful if
 * you want to include a link that uses relative URLs. If the Q2A site is in a subdirectory of
 * your site, $relative_url_prefix.'../' refers to your site root (see example 1).
 *
 * If you don't know what to display for a user, you can leave the default below. This will
 * show the public username, linked to the Q2A profile page for the user.
 */
function qa_get_logged_in_user_html($logged_in_user, $relative_url_prefix)
{

	$publicusername = $logged_in_user['publicusername'];

	return '<a href="' . qa_path_html('user/' . $publicusername) . '" class="qa-user-link">' . htmlspecialchars($publicusername) . '</a>';
}


/**
 * ==========================================================================
 * YOU MAY MODIFY THIS FUNCTION, BUT THE DEFAULT BELOW WILL WORK OK
 * ==========================================================================
 *
 * qa_get_users_html($userids, $should_include_link, $relative_url_prefix)
 *
 * You should return an array of HTML to display for each user in $userids. For each element of
 * this array, the userid should be in the key, with the corresponding HTML in the value.
 * Note: the username MUST be escaped with htmlspecialchars() for general output, or urlencode()
 * for link URLs.
 *
 * Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
 * Q2A, you can also use the various qa_db_* functions to run queries. If you access this database or
 * any other, try to use a single query instead of one per user.
 *
 * If $should_include_link is true, the HTML may include links to user profile pages.
 * If $should_include_link is false, links should not be included in the HTML.
 *
 * $relative_url_prefix is a relative URL to the root of the Q2A site, which may be useful if
 * you want to include links that uses relative URLs. If the Q2A site is in a subdirectory of
 * your site, $relative_url_prefix.'../' refers to your site root (see example 1).
 *
 * If you don't know what to display for a user, you can leave the default below. This will
 * show the public username, linked to the Q2A profile page for each user.
 */
function qa_get_users_html($userids, $should_include_link, $relative_url_prefix)
{
	$useridtopublic = qa_get_public_from_userids($userids);

	$usershtml = array();

	foreach ($userids as $userid) {
		$publicusername = $useridtopublic[$userid];

		$usershtml[$userid] = htmlspecialchars($publicusername);

		if ($should_include_link)
			$usershtml[$userid] = '<a href="' . qa_path_html('user/' . $publicusername) . '" class="qa-user-link">' . $usershtml[$userid] . '</a>';
	}

	return $usershtml;
}


/**
 * ==========================================================================
 * YOU MAY MODIFY THIS FUNCTION, BUT THE DEFAULT BELOW WILL WORK OK
 * ==========================================================================
 *
 * qa_avatar_html_from_userid($userid, $size, $padding)
 *
 * You should return some HTML for displaying the avatar of $userid on the page.
 * If you do not wish to show an avatar for this user, return null.
 *
 * $size contains the maximum width and height of the avatar to be displayed, in pixels.
 *
 * If $padding is true, the HTML you return should render to a square of $size x $size pixels,
 * even if the avatar is not square. This can be achieved using CSS padding - see function
 * qa_get_avatar_blob_html(...) in qa-app-format.php for an example. If $padding is false,
 * the HTML can render to anything which would fit inside a square of $size x $size pixels.
 *
 * Note that this function may be called many times to render an individual page, so it is not
 * a good idea to perform a database query each time it is called. Instead, you can use the fact
 * that before qa_avatar_html_from_userid(...) is called, qa_get_users_html(...) will have been
 * called with all the relevant users in the array $userids. So you can pull out the information
 * you need in qa_get_users_html(...) and cache it in a global variable, for use in this function.
 */
function qa_avatar_html_from_userid($userid, $size, $padding)
{
	$email = qa_get_user_email($userid);
	if ($email == null) {
		return null;
	}

	$hash = md5(strtolower(trim($email)));
	return '<img src="https://www.gravatar.com/avatar/' . $hash . '.jpg?s=32" width="32" height="32" class="qa-avatar-image" alt="" />';
}


/**
 * ==========================================================================
 * YOU MAY MODIFY THIS FUNCTION, BUT THE DEFAULT BELOW WILL WORK OK
 * ==========================================================================
 *
 * qa_user_report_action($userid, $action)
 *
 * Informs you about an action by user $userid that modified the database, such as posting,
 * voting, etc... If you wish, you may use this to log user activity or monitor for abuse.
 *
 * Call qa_db_connection() to get the connection to the Q2A database. If your database is shared with
 * Q2A, you can also use the various qa_db_* functions to run queries.
 *
 * $action will be a string (such as 'q_edit') describing the action. These strings will match the
 * first $event parameter passed to the process_event(...) function in event modules. In fact, you might
 * be better off just using a plugin with an event module instead, since you'll get more information.
 *
 * FYI, you can get the IP address of the user from qa_remote_ip_address().
 */
function qa_user_report_action($userid, $action)
{
	// Do nothing by default
}
