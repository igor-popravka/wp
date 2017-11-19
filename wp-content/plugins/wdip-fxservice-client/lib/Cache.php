<?php
namespace WDIP\Plugin;

/**
 * @author: igor.popravka
 * Date: 24.10.2017
 * Time: 11:42
 */
class Cache {
    const CACHE_KEY_MYFXBOOK_SESSION = 'KEY-MYFXBOOK-SESSION';
    const CACHE_KEY_MYFXBOOK_ACCOUNTS = 'KEY-MYFXBOOK-ACCOUNTS';
    const CACHE_KEY_MYFXBOOK_TOTAL_GAIN_DATA = 'KEY-MYFXBOOK-TOTAL-GAIN-DATA';
    const CACHE_KEY_MYFXBOOK_MONTHLY_GAIN_LOSS_DATA = 'KEY-MYFXBOOK-MONTHLY-GAIN-LOSS-DATA';
    const CACHE_KEY_MYFXBOOK_GROWTH_DATA = 'KEY-MYFXBOOK-GROWTH-DATA';
    const CACHE_KEY_FXBLUE_GROWTH_DATA = 'KEY-FXBLUE-GROWTH-DATA';
    const CACHE_KEY_FXBLUE_ACCOUNT_DATA = 'KEY-FXBLUE-ACCOUNT-DATA';
    const CACHE_KEY_FXBLUE_MONTHLY_GAIN_LOSS_DATA = 'KEY-FXBLUE-MONTHLY-GAIN-LOSS-DATA';
    const CACHE_KEY_PLUGIN_VERSION = 'KEY-PLUGIN-VERSION';

    private static $instance;

    private static $cache = [];

    private function __construct() {
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key, $default = null) {
        if ($this->has($key)) {
            return self::$cache[$this->key($key)];
        }
        return $default;
    }

    public function set($key, $value) {
        self::$cache[$this->key($key)] = $value;
    }

    public function has($key) {
        return isset(self::$cache[$this->key($key)]);
    }

    private function key($key) {
        if (is_array($key)) {
            $key = implode('-', $key);
        }
        return md5(strtoupper($key));
    }
}