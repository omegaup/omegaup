<?php
/*
	Question2Answer Markdown editor plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

class qa_markdown_viewer
{
	private $plugindir;

	public function load_module($directory, $urltoroot)
	{
		$this->plugindir = $directory;
	}

	public function calc_quality($content, $format)
	{
		return $format == 'markdown' ? 1.0 : 0.8;
	}

	public function get_html($content, $format, $options)
	{
		if (isset($options['blockwordspreg'])) {
			require_once QA_INCLUDE_DIR.'util/string.php';
			$content = qa_block_words_replace($content, $options['blockwordspreg']);
		}

		// include customized Markdown parser
		require_once 'MyMarkdown.php';
		$md = new MyMarkdown();
		$html = $md->parse($content);

		return qa_sanitize_html($html, @$options['linksnewwindow']);
	}

	public function get_text($content, $format, $options)
	{
		$viewer = qa_load_module('viewer', '');
		$text = $viewer->get_text($content, 'html', array());

		return $text;
	}
}
