<?php
class Pager {
	/**
	 * Returns a concatenation of key => value parameters ready to use in a URL.
	 */
	public static function buildQueryString($dict) {
		$params = array();
		$str = '';
		foreach ($dict as $key => $val) {
			$str .= "$key=$val";
			$params[] = urlencode($key) . '=' . urlencode($val);
		}

		return implode('&', $params);
	}

	/**
	 * Returns an array with all the information needed to create a pager bar.
	 * -------------------------------------------------------------------------------------
	 * | « | ... | c - a | ... | c - 2 | c - 1 | c | c + 1 | c + 2 | ... | c + a | ... | » |
	 * -------------------------------------------------------------------------------------
	 *                                           ^
	 *                                           |
	 *                                      current page
	 *
	 * @param int $rows	The total number of rows to show.
	 * @param int $current	The page we want to show, the 'c' in the figure.
	 * @param string $url	The base URL that each item will point to.
	 * @param int $adjacent	Number of items before and after the current page, the 'a' in the figure.
	 * @param array $params	Additional key => value parameters to append to the item's URL.
	 * @return array $items	The information for each item of the pager.
	 */
	public static function paginate($rows, $current, $url, $adjacent, $params) {
		$pages = intval(($rows + PROBLEMS_PER_PAGE - 1) / PROBLEMS_PER_PAGE);
		if ($current < 1 || $current > $pages) {
			$current = 1;
		}

		$query = '';
		if (count($params) > 0) {
			$query = '&' . self::buildQueryString($params);
		}

		$items = array();
		$prev = array('label' => '«', 'url' => '', 'class' => '');
		if ($current > 1) {
			$prev['url'] = $url . '?page=' . ($current - 1) . $query;
		} else {
			$prev['url'] = '';
			$prev['class'] = 'disabled';
		}
		array_push($items, $prev);

		if ($current > $adjacent + 1) {
			$first = array(
				'label' => '1',
				'url'   => $url . '?page=1' . $query,
				'class' => ''
			);
			$period = array(
				'label' => '...',
				'url'	=> '',
				'class' => 'disabled'
			);
			array_push($items, $first);
			array_push($items, $period);
		}

		for ($i = max(1, $current - $adjacent); $i <= min($pages, $current + $adjacent); $i++) {
			$item = array(
				'label' => $i,
				'url'   => $url . '?page=' . $i . $query,
				'class' => ($i == $current) ? 'active' : ''

			);
			array_push($items, $item);
		}

		if ($current + $adjacent < $pages) {
			$period = array(
				'label' => '...',
				'url'	=> '',
				'class' => 'disabled'
			);
			$last = array(
				'label' => $pages,
				'url'   => $url . '?page=' . $pages . $query,
				'class' => ''
			);
			array_push($items, $period);
			array_push($items, $last);
		}

		$next = array('label' => '»', 'url' => '', 'class' => '');
		if ($current < $pages) {
			$next['url'] = $url . '?page=' . ($current + 1) . $query;
		} else {
			$next['url'] = '';
			$next['class'] = 'disabled';
		}
		array_push($items, $next);

		return $items;
	}
}
?>

