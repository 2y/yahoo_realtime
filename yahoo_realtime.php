<?php
/*
$realtime = new YahooRealtime();
$realtime->setQuery('ラクスル -オマエ…ハタラク -ラクスルマガジン');
$item_list = $realtime->search();
var_dump($item_list);
*/

class YahooRealtime {
    const URL_SEARCH = 'http://realtime.search.yahoo.co.jp/search?p=';

    private $query;

    // {{{ analysis
    private static function analysis($html) {
        $item = [];
        preg_match('/data-time="([0-9]+)"/', $html, $match);
        $item['time'] = (int)$match[1];
        preg_match('/<h2>(.+)<\/h2>/s', $html, $match);
        $item['message'] = strip_tags($match[1]);
        preg_match('/<a href="(.+)" title="/', $html, $match);
        $item['url'] = htmlspecialchars_decode($match[1]);
        if (strpos($html, '<span class="ref ptn_3">Facebook</span>')) {
            $item['type']        = 'facebook';
            $item['is_facebook'] = true;
            $item['is_twitter']  = false;
        } elseif (strpos($html, '<span class="ref">Twitter</span>')) {
            $item['type']        = 'twitter';
            $item['is_facebook'] = false;
            $item['is_twitter']  = true;
        }
        preg_match('/class="nam" target="_blank">(.+)<\/a>/', $html, $match);
        $item['name'] = $match[1];
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
        $pos  = strpos($html, '<div class="cnt cf" data-time="');
        if ($pos === false) {
            return $list;
        }
        $html = substr($html, $pos);
        $pos  = strpos($html, '<div class="cnt cf" data-time="');
        while (strpos($html, '<div class="cnt cf" data-time="') !== false) {
            $pos  = strpos($html, '<!--/.cnt end-->');
            $item = substr($html, 0, $pos);
            $list[] = self::analysis($item);
            $html = substr($html, $pos + 17);
        }
        return $list;
    }
    // }}}
}
