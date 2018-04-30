<?php

namespace WDIP\Plugin;

class HTTP {
    const RESPONSE_TYPE_RAW = 0;
    const RESPONSE_TYPE_JSON = 1;

    private static $instance;

    private function __construct() {
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function get($url, $response_type = self::RESPONSE_TYPE_JSON) {
        $response = wp_remote_get($url, ['sslverify' => false]);

        if (wp_remote_retrieve_response_code($response) == 200) {
            if ($response_type == self::RESPONSE_TYPE_JSON) {
                return json_decode(wp_remote_retrieve_body($response));
            } else {
                return wp_remote_retrieve_body($response);
            }
        }

        return null;
    }

    public function buildQuery($host, $action = '', $params = []) {
        return sprintf('%s%s%s',
            $host,
            (!empty($action) ? "/{$action}" : ''),
            (!empty($params) ? '?' . build_query($params) : '')
        );
    }
}