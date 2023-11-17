<?php

#$realtime = new YahooRealtime();
#$realtime->setQuery('BitStar OR ビットスター OR @bitstar_tokyo');
#$item_list = $realtime->search();
#var_dump($item_list);


class YahooRealtime {
    const URL_SEARCH = 'https://search.yahoo.co.jp/realtime/search?p=';
    const DELIMITER1 = '<time class="Tweet_time__';
    const DELIMITER2 = '</time>';

    private $query;

    // {{{ analysis
    private static function analysis($html) {
        $item = [];
	preg_match('/<a href="(.+[0-9]+)\?utm_source=yjrealtime/', $html, $match);
	$item['url'] = $match[1];
	preg_match('/([0-9]+)$/', $item['url'], $match);
	$item['time'] = $match[1];
        return $item;
    }
    // }}}

    // {{{ setQuery
    public function setQuery($query) {
        $this->query = $query;
    }
    // }}}
    // {{{ search
    public function search() {
        $html = file_get_contents(self::URL_SEARCH . urlencode($this->query));
	$list = [];
	$pos  = strpos($html, self::DELIMITER1);
        if ($pos === false) {
            return $list;
        }
        $html = substr($html, $pos + strlen(self::DELIMITER1));
        while (strpos($html, self::DELIMITER1) !== false) {
            $pos  = strpos($html, self::DELIMITER2);
	    $item = substr($html, 0, $pos);
            $list[] = self::analysis($item);
            $html = substr($html, strpos($html, self::DELIMITER1) + strlen(self::DELIMITER1));
	}
        return $list;
    }
    // }}}
}
