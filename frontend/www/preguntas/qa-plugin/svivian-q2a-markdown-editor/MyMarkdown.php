<?php

class MyMarkdown extends \cebe\markdown\Markdown
{
	use \cebe\markdown\inline\UrlLinkTrait;

	/**
	 * Handle new lines
	 */
	protected function renderText($text)
	{
		return strtr($text[1], ["  \n" => "<br>\n", "\n" => "<br>\n"]);
	}

	/**
	 * Update headings to run from 2..6 to avoid multiple H1s on the page
	 */
	protected function renderHeadline($block)
	{
		$tag = 'h' . min($block['level'] + 1, 6);
		return "<$tag>" . $this->renderAbsy($block['content']) . "</$tag>\n";
	}
}
