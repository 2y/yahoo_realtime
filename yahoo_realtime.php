<?php

#$realtime = new YahooRealtime();
#$realtime->setQuery('BitStar OR ビットスター OR @bitstar_tokyo');
#$item_list = $realtime->search();
#var_dump($item_list);


class YahooRealtime {
    const URL_SEARCH = 'https://search.yahoo.co.jp/realtime/search?p=';

    private $query;

    // {{{ analysis
    private static function analysis($html) {
        $item = [];
        preg_match('/<p class="Tweet_body(.+)<\/p>/', $html, $match);
        $item['message'] = strip_tags($match[0]);
        preg_match('/<time class="Tweet_time__[0-9a-zA-Z]+"><a href="(.+)" target=/', $html, $match);
        $item['url'] = substr($match[1], 0, strpos($match[1], '?'));
        preg_match('/status\/([0-9]+)/', $item['url'], $match);
        $item['time'] = (int)$match[1];
        preg_match('/<span class="Tweet_authorName__[0-9a-zA-Z]+">(.+)<\/span>/', $html, $match);
        $item['name'] = strip_tags($match[0]);
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
        $pos  = strpos($html, '<div class="Tweet_TweetContainer');
        if ($pos === false) {
            #echo self::URL_SEARCH . urlencode($this->query);
            #exit('dead');
            return $list;
        }
        $html = substr($html, $pos);
        #echo substr($html, 0, 2000);exit;
        while (strpos($html, '<div class="Tweet_TweetContainer', 10) !== false) {
            $pos  = strpos($html, '<div class="Tweet_TweetContainer', 10);
            $item = substr($html, 0, $pos);
            $item = self::analysis($item);
            $list[$item['time']] = $item;
            $html = substr($html, $pos);
        }
        ksort($item);
        return $list;
    }
    // }}}
}
