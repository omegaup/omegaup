<?php
class Pager {
	public static function concat($params)
	{
		$str = "";
		$i = 0;
		$size = count($params);
		foreach ($params as $key => $val) {
			$str .= "$key=$val";
			if ($i + 1 < $size) {
				$str .= '&';
			}
			$i++;
		}

		return $str;
	}

	public static function paginate($base_url, $page, $adjacent, $total, $extra_params)
	{
		$total_pages = intval(ceil($total / PROBLEMS_PER_PAGE) + 1E-9);
		if ($page < 1 || $page > $total_pages) {
			$page = 1;
		}

		$expa = '';
		if (count($extra_params) > 0) {
			$expa = '&' . self::concat($extra_params);
		}

		$pager_links = array();
		$prev = array('label' => 'Previous', 'url' => '', 'class' => '');
		if ($page > $adjacent + 1) {
			$prev['url'] = $base_url . '?page=' . ($page - 1) . $expa;
		} else {
			$prev['url'] = '';
			$prev['class'] = 'disabled';
		}
		array_push($pager_links, $prev);

		if ($page > $adjacent + 1) {
			$first = array(
				'label' => '1',
				'url'   => $base_url . '?page=1' . $expa,
				'class' => ''
			);
			$period = array(
				'label' => '...',
				'url'	=> '',
				'class' => 'disabled'
			);
			array_push($pager_links, $first);
			array_push($pager_links, $period);
		}

		for ($i = max(1, $page - $adjacent); $i < $page; $i++) {
			$item = array(
				'label' => $i,
				'url'   => $base_url . '?page=' . $i . $expa,
				'class' => ''

			);
			array_push($pager_links, $item);
		}

		$current = array(
			'label' => $page,
			'url'   => '',
			'class' => 'active'
		);
		array_push($pager_links, $current);

		for ($i = $page + 1; $i <= min($total_pages, $page + $adjacent); $i++) {
			$item = array(
				'label' => $i,
				'url'   => $base_url . '?page=' . $i . $expa,
				'class' => ''
			);
			array_push($pager_links, $item);
		}

		if ($page + $adjacent < $total_pages) {
			$period = array(
				'label' => '...',
				'url'	=> '',
				'class' => 'disabled'
			);
			$last = array(
				'label' => $total_pages,
				'url'   => $base_url . '?page=' . $total_pages . $expa,
				'class' => ''
			);
			array_push($pager_links, $period);
			array_push($pager_links, $last);
		}

		$next = array('label' => 'Next', 'url' => '', 'class' => '');
		if ($page + $adjacent < $total_pages) {
			$next['url'] = $base_url . '?page=' . ($page + 1) . $expa;
		} else {
			$next['url'] = '';
			$next['class'] = 'disabled';
		}
		array_push($pager_links, $next);

		return $pager_links;
	}
}
?>

