<?php
namespace WDIP\Plugin;

/**
 * @author: igor.popravka
 * @link https://www.upwork.com/freelancers/~010854a54a1811f970 Author Profile
 * Date: 09.11.2016
 * Time: 14:32
 */
class MyFXBookClient {
    const OPTIONS_GROUP = 'wdip-myfxbook-client-group';
    const OPTIONS_PAGE = 'wdip-myfxbook-client-page';
    const OPTIONS_NAME = 'wdip-myfxbook-client-options';
    const SHORT_CODE_NAME = 'myfxbook-client';

    const TYPE_MONTH_GROWTH = 'month-growth';
    const TYPE_TOTAL_GROWTH = 'total-growth';
    const TYPE_MONTHLY_GAIN_LOSS = 'monthly-gain-loss';
    const TYPE_CALCULATOR_FORM = 'calculator-form';
    const TYPE_MONTH_GROWTH_TABLE = 'month-growth-table';

    const ENV_DEV = 'dev';
    const ENV_LIVE = 'live';

    private static $instance;
    private static $session;
    private static $accounts = [];
    private static $version;

    private function __construct() {
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function build() {
        if (is_admin()) {
            add_action('admin_menu', $this->getCallback('initAdminMenu'));
            add_action('admin_init', $this->getCallback('initSettings'));
            add_action('admin_enqueue_scripts', $this->getCallback('initAdminEnqueueScripts'));
            add_action('wp_ajax_nopriv_wdip-calculate-growth-data', $this->getCallback('ajaxCalculateGrowthData'));
            add_action('wp_ajax_wdip-calculate-growth-data', $this->getCallback('ajaxCalculateGrowthData'));
        } else {
            add_action('wp_enqueue_scripts', $this->getCallback('initEnqueueScripts'));
            add_shortcode(self::SHORT_CODE_NAME, $this->getCallback('applyShortCode'));
        }
        register_deactivation_hook(WDIP_PLUGIN, $this->getCallback('delSettings'));
    }

    public function initAdminMenu() {
        add_options_page(
            __('MyFXBook Client Settings'),
            'MyFXBook Client',
            8,
            self::OPTIONS_PAGE,
            $this->getCallback('renderOptionsPage')
        );
    }

    public function applyShortCode($attr = [], $content = null) {
        $attributes = new ShortCodeAttributes($attr);

        if ($attributes->has('accountid') && $attributes->has('charttype')) {
            switch ($attributes->charttype) {
                case self::TYPE_MONTH_GROWTH:
                    $options = new MonthGrowthOptions($attributes);
                    $content .= Viewer::instance()->render('myfxbook-chart', $options);
                    break;
                case self::TYPE_TOTAL_GROWTH:
                case self::TYPE_MONTHLY_GAIN_LOSS:
                    $options = new ChartOptions($attributes);
                    $content .= Viewer::instance()->render('myfxbook-chart', $options);
                    break;
                case self::TYPE_CALCULATOR_FORM:
                    $options = new ChartOptions($attributes);
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

    public function initEnqueueScripts() {
        wp_enqueue_script('highcharts', plugins_url('/media/js/highcharts.js', WDIP_PLUGIN));
        wp_enqueue_script('wdip-myfxbook-plagin', plugins_url('/media/js/wdip-myfxbook.plagin.js', WDIP_PLUGIN), [
            'jquery',
            'jquery-ui-slider',
            'highcharts'
        ], $this->getVersion());
        wp_enqueue_script('wdip-myfxbook-calculator', plugins_url('/media/js/wdip-myfxbook.calculator.js', WDIP_PLUGIN), [
            'jquery',
            'jquery-ui-dialog',
            'jquery-ui-datepicker',
            'jquery-ui-button',
            'highcharts'
        ], $this->getVersion());
        wp_enqueue_style('jquery-ui-slider-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_style('wdip-myfxbook-css', plugins_url('/media/css/wdip-myfxbook.css', WDIP_PLUGIN), null, $this->getVersion());
        wp_enqueue_style('wdip-calculator-css', plugins_url('/media/css/wdip-calculator.css', WDIP_PLUGIN), null, $this->getVersion());
    }

    public function initAdminEnqueueScripts() {
        wp_enqueue_style('wdip-myfxbook', plugins_url('/media/css/wdip-myfxbook.css', WDIP_PLUGIN), null, $this->getVersion());
    }

    public function renderOptionsPage() {
        if (current_user_can('manage_options')) {
            Viewer::instance()->output('options-page');
        }
    }

    public function initSettings() {
        register_setting(
            self::OPTIONS_GROUP,
            self::OPTIONS_NAME,
            $this->getCallback('validOptionsData')
        );
        $section_code = 'options_sections';
        /**
         * registration section
         */
        add_settings_section(
            $section_code,
            __('Account Registration Data', self::OPTIONS_PAGE),
            $this->getCallback('renderOptionsNotify'),
            self::OPTIONS_PAGE
        );
        add_settings_field(
            'login_field',
            __('Login', self::OPTIONS_PAGE),
            $this->getCallback('renderOptionsField'),
            self::OPTIONS_PAGE,
            $section_code,
            [
                'label_for' => 'login_field',
                'tag' => 'input',
                'type' => 'text',
                'description' => 'Enter your a login. It will be used only to authorization in API',
                'options_name' => self::OPTIONS_NAME
            ]
        );
        add_settings_field(
            'password_field',
            __('Password', self::OPTIONS_PAGE),
            $this->getCallback('renderOptionsField'),
            self::OPTIONS_PAGE,
            $section_code,
            [
                'label_for' => 'password_field',
                'tag' => 'input',
                'type' => 'password',
                'description' => 'Enter your a password. It will be used only to authorization in API',
                'options_name' => self::OPTIONS_NAME
            ]
        );
    }

    public function validOptionsData($options) {
        $session = $this->getSession($options['login_field'], $options['password_field']);
        if (empty($session)) {
            add_settings_error(
                'myfxbook-api-session-empty',
                'myfxbook-api-session-empty',
                __('Failed during authorization into <a href="https://www.myfxbook.com/api">https://www.myfxbook.com/api</a>', self::OPTIONS_PAGE)
            );
        }
        return $options;
    }

    public function renderOptionsNotify($args) {
        $notify = __('Please fill following information about account registration onto <a href="https://www.myfxbook.com">https://www.myfxbook.com</a>', self::OPTIONS_PAGE);
        ?>
        <p class="description"><?= $notify; ?></p>
        <?php
    }

    public function renderOptionsField($args) {
        $data = new MyFXBookData($args);
        $options = get_option($data->options_name);
        $data->value = !empty($options[$data->label_for]) ? $options[$data->label_for] : $data->default_value;
        Viewer::instance()->output('options-field', $data);
    }

    public function delSettings() {
        unregister_setting(self::OPTIONS_GROUP, self::OPTIONS_NAME);
        delete_option(self::OPTIONS_NAME);
    }

    public function getCallback($fun_name) {
        return [self::instance(), $fun_name];
    }

    /**
     * @param string $action
     * @param array $params
     * @return null|\stdClass
     */
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

    public function getAccountInfo($id) {
        foreach ($this->getAccounts() as $acc) {
            if ($acc->id == $id) {
                return $acc;
            }
        }
        return null;
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

    public function getDataFromJSON($file, $include_path = false) {
        $path = $include_path ? "{$file}.json" : WDIP_ROOT . "/{$file}.json";
        $content = file_get_contents($path, ($include_path ? FILE_USE_INCLUDE_PATH : null));

        return $content ? json_decode($content) : "";
    }

    public function getVersion() {
        if (!isset(self::$version)) {
            $plugin = $this->getDataFromJSON('composer');
            self::$version = $plugin->version;
        }

        return self::$version;
    }

    public function getEnvironment() {
        return MyFXBookConfig::instance()->environment;
    }
}