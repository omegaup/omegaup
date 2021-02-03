<?php

namespace OmegaUp;

/**
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 */
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
     * @param int $rows The number of rows to show per page.
     * @param int $pageSize The total number of rows available.
     * @param int $current  The page we want to show, the 'c' in the figure.
     * @param int $adjacent Number of items before and after the current page,
     * the 'a' in the figure.
     * @param array<string, string[]|string> $params Additional key => value
     * parameters to append to the item's URL.
     *
     * @return list<array{class: string, label: string, page: int}> The
     * information for each item of the pager.
     */
    public static function paginate(
        int $rows,
        int $pageSize,
        int $current,
        int $adjacent,
        array $params
    ): array {
        $pages = intval(($rows + $pageSize - 1) / $pageSize);
        if ($current < 1 || $current > $pages) {
            $current = 1;
        }

        /** @var list<array{class: string, label: string, page: int}> */
        $items = [];
        $prev = ['label' => '«', 'class' => '', 'page' => 0];
        if ($current > 1) {
            $prev['page'] = ($current - 1);
        } else {
            $prev['class'] = 'disabled';
        }
        $items[] = $prev;

        if ($current > $adjacent + 1) {
            $items[] = ['label' => '1', 'class' => '', 'page' => 1];
            $items[] = ['label' => '...', 'class' => 'disabled', 'page' => 0];
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
            $items[] = [
                'label' => strval($i),
                'class' => ($i == $current) ? 'active' : '',
                'page' => $i,
            ];
        }

        if ($current + $adjacent < $pages) {
            $items[] = ['label' => '...', 'class' => 'disabled', 'page' => 0];
            $items[] = [
                'label' => strval($pages),
                'class' => '',
                'page' => $pages,
            ];
        }

        $next = ['label' => '»', 'class' => '', 'page' => 0];
        if ($current < $pages) {
            $next['page'] = ($current + 1);
        } else {
            $next['class'] = 'disabled';
        }
        $items[] = $next;

        return $items;
    }

    /**
     * The function gets all the items of paginator function and it adds the
     * base url to each one.
     *
     * @param int $rows     The number of rows to show per page.
     * @param int $pageSize The total number of rows available.
     * @param int $current  The page we want to show, the 'c' in the figure.
     * @param string $url   The base URL that each item will point to.
     * @param int $adjacent Number of items before and after the current page,
     * the 'a' in the figure.
     * @param array<string, string[]|string> $params Additional key => value
     * parameters to append to the item's URL.
     *
     * @return list<PageItem> The information for each item of the pager.
     */
    public static function paginateWithUrl(
        int $rows,
        int $pageSize,
        int $current,
        string $url,
        int $adjacent,
        array $params
    ) {
        $pagerItems = self::paginate(
            $rows,
            $pageSize,
            $current,
            $adjacent,
            $params
        );
        $query = '';
        if (!empty($params)) {
            $query = '&' . self::buildQueryString($params);
        }
        foreach ($pagerItems as &$item) {
            if ($item['page'] === 0) {
                continue;
            }
            $item['url'] = "{$url}?page={$item['page']}{$query}";
        }

        return $pagerItems;
    }
}
