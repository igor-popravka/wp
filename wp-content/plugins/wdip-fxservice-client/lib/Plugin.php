<?php

namespace WDIP\Plugin;

/**
 * @author: igor.popravka
 * @link https://www.upwork.com/freelancers/~010854a54a1811f970 Author Profile
 * Date: 09.11.2016
 * Time: 14:32
 */
class Plugin {
    /** todo: remove */
    const OPTIONS_GROUP = 'wdip-fxservice-client-group';
    /** todo: remove */
    const OPTIONS_PAGE = 'wdip-fxservice-client-page';
    /** todo: remove */
    const OPTIONS_NAME = 'wdip-fxservice-client-options';

    /** todo: remove */
    const SHORT_CODE_MYFXBOOK = 'myfxbook-client';
    /** todo: remove */
    const SHORT_CODE_FXBLUE = 'fxblue-client';

    /** todo: remove */
    const TYPE_MONTH_GROWTH = 'month-growth';
    /** todo: remove */
    const TYPE_TOTAL_GROWTH = 'total-growth';
    /** todo: remove */
    const TYPE_MONTHLY_GAIN_LOSS = 'monthly-gain-loss';
    /** todo: remove */
    const TYPE_CALCULATOR_FORM = 'calculator-form';
    /** todo: remove */
    const TYPE_MONTH_GROWTH_TABLE = 'month-growth-table';

    /** todo: remove */
    const TYPE_FXBLUE_CUMULATIVE_PIPS = 'cumulative-pips';
    /** todo: remove */
    const TYPE_FXBLUE_CUMULATIVE_RETURN = 'cumulative-return';
    /** todo: remove */
    const TYPE_FXBLUE_MONTHLY_RETURN = 'monthly-return';
    /** todo: remove */
    const TYPE_FXBLUE_ACCOUNT_STATS = 'account-stats';

    /** todo: remove */
    const ENV_DEV = 'dev';
    /** todo: remove */
    const ENV_LIVE = 'live';

    /** todo: remove */
    private static $session;
    /** todo: remove */
    private static $accounts = [];

    private static $instance;
    private static $version;
    private static $file = '';
    private static $dir = '';

    private function __construct() {
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function build($plugin_file, $config_file) {
        //todo: move pathinfo to ObjectDta
        self::$file = $plugin_file;
        self::$dir = pathinfo($plugin_file, PATHINFO_DIRNAME);

        Services::config()->parse($config_file);

        if (is_admin()) {
            add_action('admin_menu', $this->getCallback('initAdminMenu'));
            add_action('admin_init', $this->getCallback('initPluginSettings'));
            add_action('admin_enqueue_scripts', $this->getCallback('initAdminEnqueueScripts'));
            add_action('wp_ajax_nopriv_wdip-calculate-growth-data', $this->getCallback('ajaxCalculateGrowthData'));
            add_action('wp_ajax_wdip-calculate-growth-data', $this->getCallback('ajaxCalculateGrowthData'));
        } else {
            add_action('wp_enqueue_scripts', $this->getCallback('initEnqueueScripts'));

            foreach (Services::config()->SHORT_CODES as $code) {
                add_shortcode($code['name'], $this->getCallback($code['callback']));
            }
        }

        register_deactivation_hook($this->getFile(), $this->getCallback('onDeactivationSettings'));
    }

    public function getFile() {
        return self::$file;
    }

    public function getDir() {
        return self::$dir;
    }

    public function initAdminMenu() {
        add_options_page(
            __(Services::config()->OPTIONS_PAGE['page_title']),
            Services::config()->OPTIONS_PAGE['menu_title'],
            Services::config()->OPTIONS_PAGE['page_capability'],
            Services::config()->OPTIONS_PAGE['menu_slug'],
            $this->getCallback('renderOptionsPage')
        );
    }

    public function renderOptionsPage() {
        if (current_user_can('manage_options')) {
            Services::viewer()->output('options-page');
        }
    }

    public function initPluginSettings() {
        /** registration setting */
        register_setting(
            Services::config()->PLUGIN_SETTINGS['options_group'],
            Services::config()->PLUGIN_SETTINGS['options_name'],
            $this->getCallback('onBeforeSaveSettings')
        );

        /** registration section */
        $section = Services::config()->PLUGIN_SETTINGS['section_code'];
        $page = Services::config()->OPTIONS_PAGE['menu_slug'];

        add_settings_section(
            $section,
            __('Account Registration Data', $page),
            $this->getCallback('renderSectionNotify'),
            $page
        );

        /** registration fields */
        add_settings_field(
            'login_field',
            __('Login', $page),
            $this->getCallback('renderSectionField'),
            $page,
            $section,
            [
                'label_for' => 'login_field',
                'tag' => 'input',
                'type' => 'text',
                'description' => 'Enter your a login. It will be used only to authorization in API',
                'options_name' => Services::config()->PLUGIN_SETTINGS['options_name'],
                'options_page' => $page
            ]
        );
        add_settings_field(
            'password_field',
            __('Password', $page),
            $this->getCallback('renderSectionField'),
            $page,
            $section,
            [
                'label_for' => 'password_field',
                'tag' => 'input',
                'type' => 'password',
                'description' => 'Enter your a password. It will be used only to authorization in API',
                'options_name' => Services::config()->PLUGIN_SETTINGS['options_name'],
                'options_page' => $page
            ]
        );
    }

    public function onBeforeSaveSettings($options) {
        $session = Services::model()->getMyFXBookSession($options['login_field'], $options['password_field']);

        if (empty($session)) {
            add_settings_error(
                Services::config()->PLUGIN_SETTINGS['error']['session_empty']['code'],
                Services::config()->PLUGIN_SETTINGS['error']['session_empty']['code'],
                __(Services::config()->PLUGIN_SETTINGS['error']['session_empty']['message'], Services::config()->OPTIONS_PAGE['menu_slug'])
            );
        }

        return $options;
    }

    public function renderSectionNotify($args) {
        Services::viewer()->output('section-notify');
    }

    public function renderSectionField($args) {
        $data = new ObjectData($args);
        $options = get_option($data->options_name);
        $data->value = !empty($options[$data->label_for]) ? $options[$data->label_for] : $data->default_value;
        Services::viewer()->output('section-field', $data);
    }

    public function initAdminEnqueueScripts() {
        wp_enqueue_style('wdip-fxservice-addmin', plugins_url('/media/css/wdip-fxservice-client.css', $this->getFile()), null, $this->getVersion());
    }

    public function ajaxCalculateGrowthData() {
        $request = new Request();
        $response = [
            'total_amount' => '$0.00',
            'fee_amount' => '$0.00',
            'gain_amount' => '$0.00',
            'series' => [
                'categories' => [],
                'total_amount_data' => [],
                'fee_amount_data' => [],
                'gain_amount_data' => []
            ]
        ];

        if ($request->validate(['id', 'start', 'amount', 'fee'])) {
            $daily_gain = MyFXBookConfig::instance()->SERIES->daily_gain;
            $dailyGainData = [];
            foreach ($request->id as $id) {
                $result = $this->httpRequest($daily_gain->url, [
                    'session' => $this->getSession(),
                    'id' => $id,
                    'start' => $request->start,
                    'end' => (new \DateTime())->format('Y-m-d')
                ]);
                if (!$result->error) {
                    if (empty($dailyGainData)) {
                        $start_value = 0;
                    } else {
                        $start_value = $dailyGainData[count($dailyGainData) - 1][1];
                    }

                    foreach ($result->dailyGain as $data) {
                        $dailyGainData[] = [
                            \DateTime::createFromFormat('m/d/Y', $data[0]->date)->format('M, y'),
                            $start_value + $data[0]->value
                        ];
                    }
                }
            }

            if (!empty($dailyGainData)) {
                $amount = floatval($request->amount);
                $fee = floatval($request->fee);
                $total_amount = $fee_amount = $gain = 0;
                foreach ($dailyGainData as $item) {
                    $name = $item[0];
                    if (empty($response['series']['categories'])) {
                        $gain = 0;
                    } else {
                        $gain = round($amount * ($item[1] / 100), 2);
                    }
                    $total_amount = ($amount + $gain);
                    $fee_amount = round($gain * $fee, 2);
                    $response['series']['categories'][] = $name;
                    $response['series']['total_amount_data'][] = $total_amount;
                    $response['series']['gain_amount_data'][] = $gain;
                    $response['series']['fee_amount_data'][] = $fee_amount;
                }
                $response['total_amount'] = '$' . $total_amount;
                $response['fee_amount'] = '$' . $fee_amount;
                $response['gain_amount'] = '$' . $gain;
            }
            wp_send_json_success($response);
        } else {
            wp_send_json_success($response);
        }
    }

    public function initEnqueueScripts() {
        wp_enqueue_script('highcharts', plugins_url('/media/js/highcharts.js', $this->getFile()));
        wp_enqueue_script('wdip-myfxbook-plagin', plugins_url('/media/js/wdip-myfxbook.plagin.js', $this->getFile()), [
            'jquery',
            'jquery-ui-slider',
            'highcharts'
        ], $this->getVersion());
        wp_enqueue_script('wdip-myfxbook-calculator', plugins_url('/media/js/wdip-myfxbook.calculator.js', $this->getFile()), [
            'jquery',
            'jquery-ui-dialog',
            'jquery-ui-datepicker',
            'jquery-ui-button',
            'highcharts'
        ], $this->getVersion());
        wp_enqueue_style('jquery-ui-slider-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_style('wdip-myfxbook-css', plugins_url('/media/css/wdip-fxservice-client.css', $this->getFile()), null, $this->getVersion());
        wp_enqueue_style('wdip-calculator-css', plugins_url('/media/css/wdip-calculator.css', $this->getFile()), null, $this->getVersion());
    }

    public function onDeactivationSettings() {
        unregister_setting(Services::config()->PLUGIN_SETTINGS['options_group'], Services::config()->PLUGIN_SETTINGS['options_name']);
        delete_option(Services::config()->PLUGIN_SETTINGS['options_name']);
    }

    /** todo: refactoring */
    public function applyMyFXBookShortCode($attr = [], $content = null) {
        $attributes = new ShortCodeAttributes($attr);

        if ($attributes->has('accountid') && $attributes->has('charttype')) {
            switch ($attributes->charttype) {
                case self::TYPE_MONTH_GROWTH:
                    $options = new MonthGrowthOptions($attributes);
                    $content .= Viewer::instance()->render('myfxbook-chart', $options);
                    break;
                case self::TYPE_TOTAL_GROWTH:
                    $options = new TotalGrowthOptions($attributes);
                    $content .= Viewer::instance()->render('myfxbook-chart', $options);
                    break;
                case self::TYPE_MONTHLY_GAIN_LOSS:
                    $options = new MonthlyGainLossOptions($attributes);
                    $content .= Viewer::instance()->render('myfxbook-chart', $options);
                    break;
                case self::TYPE_CALCULATOR_FORM:
                    $options = new CalculatorFormOptions($attributes);
                    $content .= Viewer::instance()->render('calculator-form', $options);
                    break;
                case self::TYPE_MONTH_GROWTH_TABLE:
                    $options = new TableOptions($attributes);
                    $content .= Viewer::instance()->render('quest-monthly-table', $options);
                    break;
            }
        }

        return $content;
    }

    /** todo: refactoring */
    public function applyFXBlueShortCode($attr = [], $content = null) {
        $attributes = new ShortCodeAttributes($attr);

        if ($attributes->has('charttype')) {
            switch ($attributes->charttype) {
                case self::TYPE_FXBLUE_MONTHLY_RETURN:
                    $attributes->charttype = sprintf('ch_%s', str_replace('-', '', $attributes->charttype));
                    $options = new FXBlueColumnChartOptions($attributes);
                    $content .= Viewer::instance()->render('myfxbook-chart', $options);
                    break;
                case self::TYPE_FXBLUE_CUMULATIVE_PIPS:
                case self::TYPE_FXBLUE_CUMULATIVE_RETURN:
                    $attributes->charttype = sprintf('ch_%s', str_replace('-', '', $attributes->charttype));

                    $options = new FXBlueLineChartOptions($attributes);
                    $content .= Viewer::instance()->render('myfxbook-chart', $options);
                    break;
                case self::TYPE_FXBLUE_ACCOUNT_STATS:
                    $options = new FXBlueAccountStatTableOptions($attributes);
                    $content .= Viewer::instance()->render('fxblue-acc-stat-table', $options);
                    break;
            }
        }

        return $content;
    }

    public function getCallback($fun_name) {
        return [self::instance(), $fun_name];
    }

    /** todo: should remove */
    public function httpRequest($action, array $params) {
        $url = sprintf('%s/%s?%s',
            MyFXBookConfig::instance()->HTTP->myfxbook->url,
            $action,
            build_query($params)
        );
        $response = wp_remote_get($url);
        if (wp_remote_retrieve_response_code($response) == 200) {
            $response = json_decode(wp_remote_retrieve_body($response));
            if (!empty($response)) return $response;
        }
        return null;
    }

    /** todo: should remove */
    public function httpGET($url, $params = [], $toJSON = true) {
        if (!empty($params)) {
            $url .= '?' . build_query($params);
        }

        $response = wp_remote_get($url);

        if (wp_remote_retrieve_response_code($response) == 200) {
            if ($toJSON) {
                return json_decode(wp_remote_retrieve_body($response));
            } else {
                return wp_remote_retrieve_body($response);
            }
        }

        return null;
    }

    /** todo: should remove */
    public function prepareURL($host, $action = '') {
        return sprintf('%s%s', $host, (!empty($action) ? "/{$action}" : ''));
    }

    /** todo: move to MODEL */
    public function getAccountInfo($id) {
        foreach ($this->getAccounts() as $acc) {
            if ($acc->id == $id) {
                return $acc;
            }
        }
        return null;
    }

    /** todo: should remove */
    public function getSession($login = null, $password = null) {
        $options = get_option(self::OPTIONS_NAME);
        $login = isset($login) ? $login : (isset($options['login_field']) ? $options['login_field'] : null);
        $password = isset($password) ? $password : (isset($options['password_field']) ? $options['password_field'] : null);
        if (!isset(self::$session) && isset($login) && isset($password)) {
            if ($this->getEnvironment() == self::ENV_DEV) {
                $result = $this->getDataFromJSON("myfxbook.login", true);
            } else {
                $result = $this->httpRequest('api/login.json', [
                    'email' => $login,
                    'password' => $password
                ]);
            }

            if (!$result->error) {
                self::$session = $result->session;
            }
        }
        return self::$session;
    }

    /** todo: move to MODEL */
    private function getAccounts() {
        if (empty(self::$accounts)) {
            if ($this->getEnvironment() == self::ENV_DEV) {
                $result = $this->getDataFromJSON("myfxbook.get-my-accounts", true);
            } else {
                $result = $this->httpRequest('api/get-my-accounts.json', [
                    'session' => $this->getSession()
                ]);
            }

            if (!$result->error) {
                self::$accounts = $result->accounts;
            }
        }
        return self::$accounts;
    }

    /** todo: refactoring */
    public function getDataFromJSON($file, $include_path = false) {
        $path = $include_path ? "{$file}.json" : WDIP_ROOT . "/{$file}.json";
        $content = file_get_contents($path, ($include_path ? FILE_USE_INCLUDE_PATH : null));

        return $content ? json_decode($content) : "";
    }

    /** todo: should init in build */
    public function getVersion() {
        if (!isset(self::$version)) {
            $plugin = $this->getDataFromJSON('composer');
            self::$version = $plugin->version;
        }

        return self::$version;
    }

    /** todo: remove */
    public function getEnvironment() {
        //return MyFXBookConfig::instance()->environment;
    }
}