<?php
/*
	Question2Answer Markdown editor plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

require_once QA_INCLUDE_DIR.'qa-app-posts.php';

class qa_markdown_events
{
	private $directory;
	private $urltoroot;
	private $convopt = 'markdown_comment';

	public function load_module($directory, $urltoroot)
	{
		$this->directory = $directory;
		$this->urltoroot = $urltoroot;
	}

	public function process_event($event, $userid, $handle, $cookieid, $params)
	{
		// check we have the correct event and the option is set
		if ($event != 'a_to_c')
			return;
		if (!qa_opt($this->convopt))
			return;

		 qa_post_set_content($params['postid'], null, null, '', null, null, null, qa_get_logged_in_userid());
	}
}
