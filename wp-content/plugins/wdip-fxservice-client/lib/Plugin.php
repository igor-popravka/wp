<?php

namespace WDIP\Plugin;

use WDIP\Plugin\Attributes\Attribute;
use WDIP\Plugin\Attributes\ShortCodeAttributes;
use WDIP\Plugin\Options\CalculatorForm;
use WDIP\Plugin\Options\MonthGrowth;
use WDIP\Plugin\Options\MonthGrowthTable;
use WDIP\Plugin\Options\MonthlyGainLoss;
use WDIP\Plugin\Options\TotalGrowth;

/**
 * @author: igor.popravka
 * @link https://www.upwork.com/freelancers/~010854a54a1811f970 Author Profile
 * Date: 09.11.2016
 * Time: 14:32
 */
class Plugin {
    const SHORT_CODE_MYFXBOOK = 'myfxbook-client';
    const SHORT_CODE_FXBLUE = 'fxblue-client';

    private static $instance;

    private function __construct() {
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function build($config) {
        Services::config()->parse($config);

        if (is_admin()) {
            add_action('admin_menu', $this->getCallback('initAdminMenu'));
            add_action('admin_init', $this->getCallback('initPluginSettings'));
            add_action('admin_enqueue_scripts', $this->getCallback('initAdminEnqueueScripts'));
            add_action('wp_ajax_nopriv_wdip-calculate-growth-data', $this->getCallback('ajaxCalculateGrowthData'));
            add_action('wp_ajax_wdip-calculate-growth-data', $this->getCallback('ajaxCalculateGrowthData'));
        } else {
            add_action('wp_enqueue_scripts', $this->getCallback('initEnqueueScripts'));
            add_shortcode(self::SHORT_CODE_MYFXBOOK, $this->getCallback('applyMyFXBookShortCode'));
            add_shortcode(self::SHORT_CODE_FXBLUE, $this->getCallback('applyFXBlueShortCode'));
        }

        register_deactivation_hook(Services::system()->getPluginFile(), $this->getCallback('onDeactivationSettings'));
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
            Services::viewer()->output('wdip-options-page');
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
        Services::viewer()->output('wdip-section-notify');
    }

    public function renderSectionField($args) {
        $data = new ObjectData($args);
        $options = get_option($data->options_name);
        $data->value = !empty($options[$data->label_for]) ? $options[$data->label_for] : $data->default_value;
        Services::viewer()->output('wdip-section-field', $data);
    }

    public function initAdminEnqueueScripts() {
        wp_enqueue_style('wdip-fxservice-addmin', Services::system()->getCssURL('wdip-fxservice-client'), null, $this->getVersion());
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

        if ($request->validate(['accountId', 'start', 'amount', 'fee'])) {
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
        wp_enqueue_script('highcharts', Services::system()->getJsURL('highcharts'));
        wp_enqueue_script('wdip-myfxbook-plagin', Services::system()->getJsURL('wdip-myfxbook.plagin'), [
            'jquery',
            'jquery-ui-slider',
            'highcharts'
        ], $this->getVersion());
        wp_enqueue_script('wdip-myfxbook-calculator', Services::system()->getJsURL('wdip-myfxbook.calculator'), [
            'jquery',
            'jquery-ui-dialog',
            'jquery-ui-datepicker',
            'jquery-ui-button',
            'highcharts'
        ], $this->getVersion());
        wp_enqueue_style('jquery-ui-slider-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_style('wdip-myfxbook-css', Services::system()->getCssURL('wdip-fxservice-client'), null, $this->getVersion());
        wp_enqueue_style('wdip-calculator-css', Services::system()->getCssURL('wdip-calculator'), null, $this->getVersion());
    }

    public function onDeactivationSettings() {
        unregister_setting(Services::config()->PLUGIN_SETTINGS['options_group'], Services::config()->PLUGIN_SETTINGS['options_name']);
        delete_option(Services::config()->PLUGIN_SETTINGS['options_name']);
    }

    /**
     * @param array $attr
     * @param string $content
     *
     * @return string
     */
    public function applyMyFXBookShortCode($attr = [], $content = '') {
        $attributes = new ShortCodeAttributes($attr);

        if ($attributes->isValid()) {
            $attributes->add(new Attribute('service-client', self::SHORT_CODE_MYFXBOOK));
            $content .= $this->renderShortCode($attributes);
        }

        return $content;
    }

    /**
     * @param array $attr
     * @param string $content
     *
     * @return string
     */
    public function applyFXBlueShortCode($attr = [], $content = null) {
        $attributes = new ShortCodeAttributes($attr);

        if ($attributes->isValid()) {
            $attributes->add(new Attribute('service-client', self::SHORT_CODE_FXBLUE));
            $content .= $this->renderShortCode($attributes);
        }

        return $content;
    }

    private function renderShortCode(ShortCodeAttributes $attributes) {
        $content = '';

        switch ($attributes->get('chart-type')) {
            case ShortCodeAttributes::CHART_TYPE_MONTH_GROWTH:
                $options = new MonthGrowth($attributes);
                $content = Services::viewer()->render('fxservice-chart', $options);
                break;
            case ShortCodeAttributes::CHART_TYPE_TOTAL_GROWTH:
                $options = new TotalGrowth($attributes);
                $content = Services::viewer()->render('fxservice-chart', $options);
                break;
            case ShortCodeAttributes::CHART_TYPE_MONTHLY_GAIN_LOSS:
                $options = new MonthlyGainLoss($attributes);
                $content = Services::viewer()->render('fxservice-chart', $options);
                break;
            case ShortCodeAttributes::CHART_TYPE_CALCULATOR_FORM:
                $options = new CalculatorForm($attributes);
                $content = Services::viewer()->render('calculator-form', $options);
                break;
            case ShortCodeAttributes::CHART_TYPE_MONTH_GROWTH_TABLE:
                $options = new MonthGrowthTable($attributes);
                $content = Viewer::instance()->render('month-growth-table', $options);
        }

        return $content;
    }

    public function getCallback($fun_name) {
        return [self::instance(), $fun_name];
    }

    public function getVersion() {
        $version = Services::cache()->get(Cache::CACHE_KEY_PLUGIN_VERSION, null);

        if (!isset($version)) {
            $content = file_get_contents(Services::system()->getFullPath('composer.json'));
            if ($composer = json_decode($content)) {
                $version = $composer->version;
                Services::cache()->set(Cache::CACHE_KEY_PLUGIN_VERSION, $version);
            }
        }

        return $version;
    }
}