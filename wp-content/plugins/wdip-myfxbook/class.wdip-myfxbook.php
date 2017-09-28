<?php


/**
 * @author: igor.popravka
 * @link https://www.upwork.com/freelancers/~010854a54a1811f970 Author Profile
 * Date: 09.11.2016
 * Time: 14:32
 */
class WDIP_MyFXBook_Plugin {
    const OPTIONS_GROUP = 'wdip-myfxbook-group';
    const OPTIONS_PAGE = 'wdip-myfxbook-page';
    const OPTIONS_NAME = 'options_name';
    const SHORT_CODE_NAME = 'myfxbook';
    private static $dev = true;
    private static $instance;
    private static $session;
    private static $accounts = [];

    private function __construct() {
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function init() {
        if (is_admin()) {
            add_action('admin_menu', $this->getCallback('initAdminMenu'));
            add_action('admin_init', $this->getCallback('initSettings'));
            add_action('admin_enqueue_scripts', $this->getCallback('initAdminEnqueueScripts'));
            add_action('wp_ajax_nopriv_wdip-calculate', $this->getCallback('getCalculateResult'));
            add_action('wp_ajax_wdip-calculate', $this->getCallback('getCalculateResult'));
        } else {
            add_action('wp_enqueue_scripts', $this->getCallback('initEnqueueScripts'));
            add_shortcode(self::SHORT_CODE_NAME, $this->getCallback('applyShortCode'));
        }
    }

    public function initAdminMenu() {
        add_options_page(
            __('My FX Book Settings'),
            'MyFXBook',
            8,
            self::OPTIONS_PAGE,
            $this->getCallback('renderOptionsPage')
        );
    }

    public function applyShortCode($attr = [], $content = null) {
        if (!isset($attr['type']) || !isset($attr['id'])) return $content;
        ob_start();
        $ids = explode(',', $attr['id']);
        $attr['id'] = $ids[0];
        echo $content;
        try {

            switch ($attr['type']) {
                case 'get-daily-gain': //getdailyGain
                case 'get-data-daily': //getdataDaily
                case 'get-monthly-gain-loss':
                case 'get-all-yields':
                    static $counter = 0;
                    $id = md5("{$attr['type']}-{$attr['id']}-" . $counter++);
                    $options = [
                        'type' => $attr['type'],
                        'data' => [],
                        'title' => !empty($attr['title']) ? $attr['title'] : null,
                        'height' => !empty($attr['height']) ? $attr['height'] : null,
                        'width' => !empty($attr['width']) ? $attr['width'] : null,
                        'bgcolor' => !empty($attr['bgcolor']) ? $attr['bgcolor'] : null,
                        'gridcolor' => !empty($attr['gridcolor']) ? $attr['gridcolor'] : null,
                        'filter' => !empty($attr['filter']) ? $attr['filter'] : 0
                    ];
                    //$method = $this->getMethodByCode($attr['type']);
                    $method = 'getAllYields';
                    $finalArray = [];
                    $f_index = 0;
                    foreach ($ids as $key => $uid) {
                        //$arr[$key] = $this->$method($uid);
                        $finalArray = array_merge($finalArray, $this->getAllYields($uid));
                        if ($key == 0) { //assumption only 2 accounts are there
                            $f_index = count($finalArray);
                        }
                    }
                    $options['data'] = $finalArray;
                    if ($attr['type'] != 'get-monthly-gain-loss') {
                        for ($i = $f_index; $i < count($options['data']); $i++) {
                            $options['data'][$i]['y'] = $options['data'][$i - 1]['y'] + $options['data'][$i]['y'];
                        }
                    }
                    if (!empty($options['data'])) {
                        require __DIR__ . '/views/wdip-myfxbook-chart.php';
                    }
                    break;
                case 'get-calculator-form':
                    $method = $this->getMethodByCode($attr['type']);
                    $this->$method($attr);
            }


        } catch (\Exception $e) {
        }
        return ob_get_clean();
    }

    function pr($a) {
        echo "<hr><pre>";
        print_r($a);
        echo "</pre>";
    }

    public function initEnqueueScripts() {
        wp_enqueue_script('highcharts', plugins_url('/js/highcharts.js', __FILE__));
        wp_enqueue_script('wdip-myfxbook-chats', plugins_url('/js/wdip-myfxbook.chats.js', __FILE__), [
            'jquery',
            'jquery-ui-slider',
            'highcharts'
        ], null);
        wp_enqueue_script('wdip-myfxbook-calculator', plugins_url('/js/wdip-myfxbook.calculator.js', __FILE__), [
            'jquery',
            'jquery-ui-dialog',
            'jquery-ui-datepicker',
            'jquery-ui-button',
            'highcharts'
        ], null);
        wp_enqueue_style('jquery-ui-slider-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_style('wdip-myfxbook-css', plugins_url('/css/wdip-myfxbook.css', __FILE__));
        wp_enqueue_style('wdip-calculator-css', plugins_url('/css/wdip-calculator.css', __FILE__), null, null);
    }

    public function initAdminEnqueueScripts() {
        wp_enqueue_script('wdip-myfxbook', plugins_url('/js/wdip-myfxbook.admin.js', __FILE__), ['jquery'], null);
        wp_enqueue_style('wdip-myfxbook', plugins_url('/css/wdip-myfxbook.css', __FILE__));
    }

    public function renderOptionsPage() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
            <p>
            <div><span style="margin-right: 20px;">Author Name:</span><i>Igor P.</i></div>
            <div>
                <span style="margin-right: 20px;">Author Page:</span>
                <i><a href="https://www.upwork.com/freelancers/~010854a54a1811f970">https://www.upwork.com/freelancers/~010854a54a1811f970</a></i>
            </div>
            </p>
            <form action="options.php" method="post">
                <?php
                // output security fields
                settings_fields(self::OPTIONS_GROUP);
                // output setting sections and their fields
                do_settings_sections(self::OPTIONS_PAGE);
                // output save settings button
                submit_button('Save Settings');
                ?>
            </form>
            <?php if ($this->getSession()) { ?>
                <h1>SortCode Generator</h1>
                <div class="generation-fields">
                    <fieldset>
                        <legend>Attributes</legend>
                        <p>
                            <label for="account-list"><span>*</span> Account:</label>
                            <?php $this->renderAccountsList($this->getAccountsList()) ?>
                        </p>
                        <p>
                            <label for="type-list"><span>*</span> Type:</label>
                            <select name="type" id="type-list" class="attr-field">
                                <option value="get-daily-gain">Get Daily Gain</option>
                                <option value="get-data-daily">Get Data Daily</option>
                                <option value="get-monthly-gain-loss">Monthly Gain/Loss</option>
                                <option value="get-calculator-form">Calculator Form</option>
                            </select>
                        </p>
                        <p class="grope graph">
                            <label for="title">Title:</label>
                            <input name="title" id="title" type="text" value="" class="attr-field"/>
                        </p>
                        <p class="grope graph">
                            <label for="height">Height:</label>
                            <input name="height" id="height" type="text" value="" class="attr-field"/>
                        </p>
                        <p class="grope graph">
                            <label for="width">Width:</label>
                            <input name="width" id="width" type="text" value="" class="attr-field"/>
                        </p>
                        <p class="grope graph">
                            <label for="bgcolor">Background color:</label>
                            <input name="bgcolor" id="bgcolor" type="text" value="" class="attr-field"/>
                        </p>
                        <p class="grope graph">
                            <label for="gridcolor">Grid color:</label>
                            <input name="gridcolor" id="gridColor" type="text" value="" class="attr-field"/>
                        </p>
                        <p class="grope graph">
                            <label for="filter">Show custom filter:</label>
                            <input name="filter" id="filter" type="checkbox" value="1" checked class="attr-field"/>
                        </p>
                        <p class="grope calculate">
                            <label for="fee">Performance fee:</label>
                            <input name="fee" id="fee" type="text" value="" class="attr-field"/><br/>
                            <span class="description"
                                  style="margin-left: 155px;">Enter numbers (1-100) separated comma</span>
                        </p>
                    </fieldset>
                </div>
                <div class="generation-result">
                    <fieldset>
                        <legend>Result</legend>
                        <p>
                            <textarea id="result"></textarea>
                        </p>
                    </fieldset>
                </div>
                <div class="generation-action wp-core-ui">
                    <p>
                        <button class="button button-secondary">Generate</button>
                    <hr/>
                    </p>
                </div>
            <?php } ?>
        </div>
        <?php
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
        $name = $args['options_name'];
        $label_for = $args['label_for'];
        $options = get_option($name);
        $value = !empty($options[$label_for]) ? $options[$label_for] : (isset($args['default_value']) ? $args['default_value'] : '');
        ?>
        <input id="<?= esc_attr($label_for); ?>" type="<?= esc_attr($args['type']); ?>"
               name="<?= sprintf('%s[%s]', $name, esc_attr($label_for)); ?>"
               value="<?= $value; ?>"
        <div>
            <p class="description">
                <?= nl2br(__($args['description'], self::OPTIONS_PAGE)); ?>
            </p>
        </div>
        <?php
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
    private function httpRequest($action, array $params) {
        $url = sprintf('https://www.myfxbook.com/%s?%s', $action, build_query($params));
        $response = wp_remote_get($url);
        if (wp_remote_retrieve_response_code($response) == 200) {
            $response = json_decode(wp_remote_retrieve_body($response));
            if (!empty($response)) return $response;
        }
        return null;
    }


    private function getDailyGain($id, $start = null) {
        $acc_info = $this->getAccountInfo($id);
        $series = [];
        if (!empty($acc_info)) {
            if (self::$dev) {
                $result = $this->getDataFromJSON('getDailyGain');
            } else {
                $startDate = isset($start) ? $start : \DateTime::createFromFormat('m/d/Y H:i', $acc_info->firstTradeDate)->format('Y-m-d');
                $endDate = (new \DateTime())->format('Y-m-d');
                $result = $this->httpRequest('charts.json', [
                    'chartType' => 1,
                    'accountOid' => $id,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'showPips' => false,
                    'showChange' => true,
                    'showMain' => true
                ]);
            }

            if (isset($result->categories) && isset($result->series)) {
                $array_keys = array_map(function ($vl) {
                    $vl = preg_replace("/^([a-z]{3})\s(\d{2}),\s'(\d{2})$/i", "$2-$1-20$3", $vl);
                    return \DateTime::createFromFormat('d-M-Y', $vl)->format('m/d/Y');
                }, $result->categories);
                $array_values = [];
                foreach ($result->series as $item) {
                    if ($item->name == 'Growth') {
                        $array_values = $item->data;
                        break;
                    }
                }
                $chart_data = array_combine($array_keys, $array_values);
                $group_chart_data = [];
                foreach ($chart_data as $key => $val) {
                    $key = \DateTime::createFromFormat('m/d/Y', $key)->format('m/01/Y');
                    if (!isset($group_chart_data[$key])) {
                        $group_chart_data[$key] = [];
                    }
                    array_push($group_chart_data[$key], floatval($val));
                }
                foreach ($group_chart_data as $x => $y) {
                    $series[] = [
                        'x' => $x,
                        'y' => max($y)
                    ];
                }
            }
        }
        return $series;
    }

    private function getAllYields($id, $start = null) {
        $acc_info = $this->getAccountInfo($id);
        $series = $result = [];
        if (!empty($acc_info)) {
            if (self::$dev) {
                $result = $this->getDataFromJSON('getAllYields');
            } else {
                /*$startDate = isset($start) ? $start : \DateTime::createFromFormat('m/d/Y H:i', $acc_info->firstTradeDate)->format('Y-m-d');
                $endDate = (new \DateTime())->format('Y-m-d');
                $result = $this->httpRequest('charts.json', [
                    'chartType' => 1,
                    'accountOid' => $id,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'showPips' => false,
                    'showChange' => true,
                    'showMain' => true
                ]);*/
            }

            if (isset($result->categories) && isset($result->series)) {

                //Dec 21, '10
                /*$array_keys = array_map(function ($vl) {
                    $vl = preg_replace("/^([a-z]{3})\s(\d{2}),\s'(\d{2})$/i", "$2-$1-20$3", $vl);
                    return \DateTime::createFromFormat('d-M-Y', $vl)->format('m/d/Y');
                }, $result->categories);*/

                $result->categories = array_map(function ($vl) {
                    $date = \DateTime::createFromFormat("M d, 'y", $vl);
                    return ['Y'=>$date->format('Y'), 'M'=>$date->format('m'), 'D'=>$date->format('d')];
                }, $result->categories);

                $series = $result->series;
                $data = [];
                foreach ($result->series as $item) {
                    $item->data;
                    for($i= 0; count($item->data); $i++){
                        $item->data[$i] = [
                            'x'=>$result->categories[$i],
                            'y'=>$item->data[$i]
                        ];
                    }

                }


                /*$array_values = $array_keys = [];
                foreach ($result->series as $item) {
                    if ($item->name == 'Growth') {
                        $array_values = $item->data;
                        break;
                    }
                }
                $chart_data = array_combine($array_keys, $array_values);
                $group_chart_data = [];
                foreach ($chart_data as $key => $val) {
                    $key = \DateTime::createFromFormat('m/d/Y', $key)->format('m/01/Y');
                    if (!isset($group_chart_data[$key])) {
                        $group_chart_data[$key] = [];
                    }
                    array_push($group_chart_data[$key], floatval($val));
                }
                foreach ($group_chart_data as $x => $y) {
                    $series[] = [
                        'x' => $x,
                        'y' => max($y)
                    ];
                }*/
            }
        }
        return $result;
    }

    private function getDataDaily($id) {
        $acc_info = $this->getAccountInfo($id);
        $series = [];
        if (!empty($acc_info)) {
            $startDate = \DateTime::createFromFormat('m/d/Y H:i', $acc_info->firstTradeDate)->format('Y-m-d');
            $endDate = (new \DateTime())->format('Y-m-d');
            $result = $this->httpRequest('charts.json', [
                'chartType' => 1,
                'accountOid' => $id,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'showPips' => false,
                'showChange' => true,
                'showMain' => true
            ]);

            if (isset($result->categories) && isset($result->series)) {
                $array_keys = array_map(function ($vl) {
                    $vl = preg_replace("/^([a-z]{3})\s(\d{2}),\s'(\d{2})$/i", "$2-$1-20$3", $vl);
                    return \DateTime::createFromFormat('d-M-Y', $vl)->format('m/d/Y');
                }, $result->categories);
                $array_values = [];
                foreach ($result->series as $item) {
                    if ($item->name == 'Growth') {
                        $array_values = $item->data;
                        break;
                    }
                }

                $chart_data = array_combine($array_keys, $array_values);
                foreach ($chart_data as $x => $y) {
                    $series[] = [
                        'x' => $x,
                        'y' => $y
                    ];
                }
            }
        }
        return $series;
    }


    private function getMonthlyGainLoss($id) {
        $acc_info = $this->getAccountInfo($id);
        $series = $grouped_data = [];
        if (!empty($acc_info)) {
            $countYear = \DateTime::createFromFormat('m/d/Y H:i', $acc_info->firstTradeDate)->format('Y');
            $endYear = (new \DateTime())->format('Y');
            $endDate = (new \DateTime())->modify('last day of this month')->format('Y-m-d');
            while ($countYear <= $endYear) {
                $result = $this->httpRequest('charts.json', [
                    'chartType' => 3,
                    'monthType' => 0,
                    'accountOid' => $id,
                    'startDate' => "{$countYear}-01-01",
                    'endDate' => $endDate
                ]);
                if (isset($result->categories) && isset($result->series)) {
                    $keys = array_map(function ($val) {
                        $val = sprintf('01-%s', str_replace(' ', '-', $val));
                        return \DateTime::createFromFormat('d-M-Y', $val)->format('m/1/Y');
                    }, $result->categories);
                    $values = array_map(function ($item) {
                        return array_shift($item);
                    }, $result->series[0]->data);
                    $grouped_data = array_merge($grouped_data, array_combine($keys, $values));
                }
                $countYear++;
            }
            if (!empty($grouped_data)) {
                foreach ($grouped_data as $x => $y) {
                    $series[] = [
                        'x' => $x,
                        'y' => $y
                    ];
                }
            }
        }
        return $series;
    }


    private function getAccountInfo($id) {
        foreach ($this->getAccountsList() as $acc) {
            if ($acc->id == $id) {
                return $acc;
            }
        }
        return null;
    }


    private function getCalculatorForm($attr) {
        if ($this->getSession()) {
            $code = md5("get-calculator-form-{$attr['id']}-" . time());
            $chart_options = file_get_contents(__DIR__ . '/data/wdip-calculate.options.json');
            $chart_options = preg_replace("/[\r\n\s\t]/", '', $chart_options);
            $admin_url = admin_url('admin-ajax.php');
            require __DIR__ . '/views/wdip-calculator-form.php';
        }
    }

    public function getCalculateResult() {
        $fields = ['id' => null, 'start' => null, 'amount' => null, 'fee' => null];
        $series = [
            'categories' => [],
            'total_amount_data' => [],
            'fee_amount_data' => [],
            'gain_amount_data' => []
        ];
        $response = [
            'total_amount' => '$0.00',
            'fee_amount' => '$0.00',
            'gain_amount' => '$0.00',
            'series' => $series
        ];

        foreach (array_keys($fields) as $nm) {
            if (isset($_POST[$nm])) {
                $fields[$nm] = $_POST[$nm];
            } else {
                wp_send_json_success($response);
                return;
            }
        }
        $result = $this->getDailyGain($fields['id'], $fields['start']);
        if (!empty($result)) {
            $amount = floatval($fields['amount']);
            $fee = floatval($fields['fee']);
            $total_amount = $fee_amount = $gain = 0;
            foreach ($result as $item) {
                $name = \DateTime::createFromFormat('m/d/Y', $item['x'])->format('M, y');
                if (empty($series['categories'])) {
                    $gain = 0;
                } else {
                    $gain = round($amount * ($item['y'] / 100), 2);
                }
                $total_amount = ($amount + $gain);
                $fee_amount = round($gain * $fee, 2);
                $series['categories'][] = $name;
                $series['total_amount_data'][] = $total_amount;
                $series['gain_amount_data'][] = $gain;
                $series['fee_amount_data'][] = $fee_amount;
            }
            $response['total_amount'] = '$' . $total_amount;
            $response['fee_amount'] = '$' . $fee_amount;
            $response['gain_amount'] = '$' . $gain;
            $response['series'] = $series;
        }
        wp_send_json_success($response);
    }

    private function getSession($login = null, $password = null) {
        $options = get_option(self::OPTIONS_NAME);
        $login = isset($login) ? $login : (isset($options['login_field']) ? $options['login_field'] : null);
        $password = isset($password) ? $password : (isset($options['password_field']) ? $options['password_field'] : null);
        if (!isset(self::$session) && isset($login) && isset($password)) {
            $result = $this->httpRequest('api/login.json', [
                'email' => $login,
                'password' => $password
            ]);
            if (!empty($result->session)) {
                self::$session = $result->session;
            } else {
                self::$session = null;
            }
        }
        return self::$session;
    }

    private function getAccountsList() {
        if (empty(self::$accounts)) {
            if (self::$dev) {
                $result = $this->getDataFromJSON('getAccounts');
            } else {
                $result = $this->httpRequest('api/get-my-accounts.json', [
                    'session' => $this->getSession()
                ]);
            }

            if (isset($result->accounts)) {
                self::$accounts = $result->accounts;
            } else {
                self::$accounts = [];
            }
        }
        return self::$accounts;
    }

    private function getMethodByCode($code) {
        $units = explode('-', $code);
        if (!empty($units)) {
            array_walk($units, function (&$u, $key) {
                if ($key > 1) {
                    $u = ucfirst($u);
                }
            });
            return implode('', $units);
        }
        return '';
    }

    private function renderAccountsList($accounts) { ?>
        <select name="id" id="account-list" class="attr-field">
            <?php foreach ($accounts as $acc) { ?>
                <option value="<?= $acc->id; ?>"><?= $acc->name; ?> (<?= $acc->id; ?>)</option>
            <?php } ?>
        </select>
        <?php
    }

    private function getDataFromJSON($file) {
        $path = __DIR__ . "/dev/{$file}.json";
        $content = file_get_contents($path);
        return json_decode($content);
    }
}