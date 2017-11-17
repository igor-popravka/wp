<?php
namespace WDIP\Plugin;

use WDIP\Plugin\FXServiceClient as MFBClient;
use WDIP\Plugin\MyFXBookConfig as MFBConfig;

/**
 * @author: igor.popravka
 * Date: 24.10.2017
 * Time: 11:51
 */
class Model {
    private static $instance;

    private function __construct() {
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function getGrowthData($account_id, $basic = 0) {
        $key = md5("MYFXBOOK-GROWTH-DATA-{$account_id}");
        $result = RuntimeCache::instance()->getValue($key);

        if (empty($result) && ($account_info = $this->getAccountInfo($account_id))) {
            $data = [];

            if (MFBClient::instance()->getEnvironment() == MFBClient::ENV_DEV) {
                $result = MFBClient::instance()->getDataFromJSON("myfxbook.get-daily-gain-{$account_id}", true);
            } else {
                $config = MFBConfig::instance()->MYFXBOOK_API->daily_gain;
                $result = MFBClient::instance()->httpRequest($config->url, [
                    'session' => MFBClient::instance()->getSession(),
                    'id' => $account_id,
                    'start' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y-m-d'),
                    'end' => (new \DateTime())->format('Y-m-d')
                ]);
            }

            if (!$result->error) {
                $data = array_map(function ($dt) use ($basic) {
                    return [
                        //\DateTime::createFromFormat("m/d/Y", $dt[0]->date)->getTimestamp(),
                        $dt[0]->date,
                        $dt[0]->value + $basic
                    ];
                }, $result->dailyGain);
            }

            RuntimeCache::instance()->setValue($key, $data);
        }

        return RuntimeCache::instance()->getValue($key, []);
    }

    public function getGainLossData($account_id) {
        $key = md5("MYFXBOOK-MONTHLY-GAIN-LOSS-DATA-{$account_id}");
        $result = RuntimeCache::instance()->getValue($key);

        if (empty($result) && $account_info = $this->getAccountInfo($account_id)) {
            $monthly_gain_los = MFBConfig::instance()->SERIES->monthly_gain_los;
            $startYear = intval(\DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y'));
            $endYear = intval(\DateTime::createFromFormat('m/d/Y H:i', $account_info->lastUpdateDate)->format('Y'));
            $data = [];

            while ($startYear <= $endYear) {
                $result = MFBClient::instance()->httpRequest($monthly_gain_los->url, [
                    'chartType' => 3,
                    'monthType' => 0,
                    'accountOid' => $account_id,
                    'startDate' => "{$startYear}-01-01",
                    'endDate' => (new \DateTime())->format('Y-m-d')
                ]);

                if (isset($result->categories) && isset($result->series)) {
                    $series_data = array_map(function ($v) {
                        return $v[0];
                    }, $result->series[0]->data);

                    $data[] = [$result->categories, $series_data];
                }

                $startYear++;
            }

            RuntimeCache::instance()->setValue($key, $data);
        }

        return RuntimeCache::instance()->getValue($key, []);
    }

    public function getAccountInfo($account_id) {
        $key = md5("MYFXBOOK-ACCOUNTS");
        $accounts = RuntimeCache::instance()->getValue($key);

        if (empty($accounts)) {
            if (MFBClient::instance()->getEnvironment() == MFBClient::ENV_DEV) {
                $result = MFBClient::instance()->getDataFromJSON("myfxbook.get-my-accounts", true);
            } else {
                $result = MFBClient::instance()->httpRequest('api/get-my-accounts.json', [
                    'session' => MFBClient::instance()->getSession()
                ]);
            }

            if (!$result->error) {
                $accounts = $result->accounts;
                RuntimeCache::instance()->setValue($key, $accounts);
            }
        }

        if (RuntimeCache::instance()->isSetKey($key)) {
            foreach (RuntimeCache::instance()->getValue($key) as $acc) {
                if ($acc->id == $account_id) {
                    return $acc;
                }
            }
        }

        throw new \Exception("Account {$account_id} didn't found in MyFxBook accounts.");
    }

    public function getTotalGainData($account_id) {
        $key = md5("MYFXBOOK-TOTAL-GAIN-DATA-{$account_id}");
        $result = RuntimeCache::instance()->getValue($key);

        if (!isset($result) && $account_info = $this->getAccountInfo($account_id)) {
            $value = null;
            $config = MFBConfig::instance()->MYFXBOOK_API->gain;
            $result = MFBClient::instance()->httpRequest($config->url, [
                'session' => MFBClient::instance()->getSession(),
                'id' => $account_id,
                'start' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y-m-d'),
                'end' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->lastUpdateDate)->format('Y-m-d')
            ]);

            if (!$result->error) {
                $value = $result->value;
            }

            RuntimeCache::instance()->setValue($key, $value);
        }

        return RuntimeCache::instance()->getValue($key, 0);
    }

    public function getFXBlueChartData($chart) {
        $key = md5("FXBLUE-DATA-{$chart}");
        $result = RuntimeCache::instance()->getValue($key);

        if (!isset($result)) {
            $value = null;
            $result = $this->getClient()->httpGET(
                $this->getClient()->prepareURL('https://www.fxblue.com/fxbluechart.aspx'),
                [
                    'c' => $chart,
                    'id' => 'binaforexquest'
                ],
                false
            );

            $result = preg_replace('/[\s\t\r\n]+/', '', $result);

            if (preg_match("/data\.addRows\(\[(?:\['Start',0\],)?(.+)\]\);/", $result, $match) > 0) {
                $value = json_decode("[{$match[1]}]");
            }

            RuntimeCache::instance()->setValue($key, $value);
        }

        return RuntimeCache::instance()->getValue($key, []);
    }

    public function getFXBlueAccountStatData(){
        $key = md5("FXBLUE-DATA-ACCOUNT-STAT");
        $result = RuntimeCache::instance()->getValue($key);

        if (!isset($result)) {
            $value = null;
            $result = $this->getClient()->httpGET(
                $this->getClient()->prepareURL('https://www.fxblue.com/fxbluechart.aspx'),
                [
                    'c' => 'ch_accountstats',
                    'id' => 'binaforexquest'
                ],
                false
            );

            $result = preg_replace('/[\s\t\r\n]+/', '', $result);

            if (preg_match("/document\.ChartData=(\{.+\});/", $result, $match) > 0) {
                $value = json_decode($match[1]);
            }

            RuntimeCache::instance()->setValue($key, $value);
        }

        return RuntimeCache::instance()->getValue($key, new \stdClass());
    }

    public function getClient() {
        return MFBClient::instance();
    }

    public function getMyFXBookSession($login, $password){
        $cache_key = 'MY-FX-BOOK-SESSION';
        $session = Services::cache()->getValue($cache_key, null);

        if (!isset($session)) {
            $query = Services::http()->buildQuery(Services::config()->MYFXBOOK_API['url'], 'api/login.json', [
                'email' => $login,
                'password' => $password
            ]);

            $result = Services::http()->get($query);

            if (!$result->error) {
                $session = $result->session;
                Services::cache()->setValue($cache_key, $session);
            }
        }

        return $session;
    }
}