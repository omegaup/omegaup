<?php

namespace OmegaUp;

class Pager {
    /**
     * Returns a concatenation of key => value parameters ready to use in a URL.
     *
     * @param array<string, string[]|string> $dict
     */
    public static function buildQueryString($dict): string {
        $params = [];
        foreach ($dict as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $item) {
                    $params[] = urlencode($key) . '[]=' . urlencode($item);
                }
            } else {
                $params[] = urlencode($key) . '=' . urlencode($val);
            }
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
     * @param int $rows The total number of rows to show.
     * @param int $current  The page we want to show, the 'c' in the figure.
     * @param string $url   The base URL that each item will point to.
     * @param int $adjacent Number of items before and after the current page,
     * the 'a' in the figure.
     * @param array<string, string[]|string> $params Additional key => value
     * parameters to append to the item's URL.
     *
     * @return array{label: string, url: string, class: string}[] The
     * information for each item of the pager.
     */
    public static function paginate(
        int $rows,
        int $current,
        string $url,
        int $adjacent,
        array $params
    ): array {
        $pages = intval(($rows + PROBLEMS_PER_PAGE - 1) / PROBLEMS_PER_PAGE);
        if ($current < 1 || $current > $pages) {
            $current = 1;
        }

        $query = '';
        if (!empty($params)) {
            $query = '&' . self::buildQueryString($params);
        }

        /** @var array{label: string, url: string, class: string}[] */
        $items = [];
        $prev = ['label' => '«', 'url' => '', 'class' => ''];
        if ($current > 1) {
            $prev['url'] = $url . '?page=' . ($current - 1) . $query;
        } else {
            $prev['url'] = '';
            $prev['class'] = 'disabled';
        }
        array_push($items, $prev);

        if ($current > $adjacent + 1) {
            array_push(
                $items,
                [
                    'label' => '1',
                    'url'   => "{$url}?page=1{$query}",
                    'class' => '',
                ]
            );
            array_push(
                $items,
                [
                    'label' => '...',
                    'url'   => '',
                    'class' => 'disabled',
                ]
            );
        }

        for (
            $i = max(
                1,
                $current - $adjacent
            ); $i <= min(
                $pages,
                $current + $adjacent
            ); $i++
        ) {
            array_push(
                $items,
                [
                    'label' => strval($i),
                    'url'   => "{$url}?page={$i}{$query}",
                    'class' => ($i == $current) ? 'active' : '',
                ]
            );
        }

        if ($current + $adjacent < $pages) {
            array_push(
                $items,
                [
                    'label' => '...',
                    'url'   => '',
                    'class' => 'disabled',
                ]
            );
            array_push(
                $items,
                [
                    'label' => strval($pages),
                    'url'   => "{$url}?page={$pages}{$query}",
                    'class' => '',
                ]
            );
        }

        $next = ['label' => '»', 'url' => '', 'class' => ''];
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
