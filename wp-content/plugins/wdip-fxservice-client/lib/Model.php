<?php
namespace WDIP\Plugin;

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

    public function getMyFXBookGrowthData($account_id, $basic = 0) {
        $result = Services::cache()->get([Cache::CACHE_KEY_MYFXBOOK_GROWTH_DATA, $account_id, $basic], null);

        if (!isset($result)) {
            $account_info = $this->getMyFXBookAccountInfo($account_id);

            $query = Services::http()->buildQuery(
                Services::config()->FXSERVICE_API['myfxbook_url'],
                'api/get-daily-gain.json',
                [
                    'session' => $this->getMyFXBookSession(),
                    'id' => $account_id,
                    'start' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y-m-d'),
                    'end' => (new \DateTime())->format('Y-m-d')
                ]
            );

            $result = Services::http()->get($query);

            if (!$result->error) {
                $result = array_map(function ($dt) use ($basic) {
                    return [
                        $dt[0]->date,
                        $dt[0]->value + $basic
                    ];
                }, $result->dailyGain);

                Services::cache()->set([Cache::CACHE_KEY_MYFXBOOK_GROWTH_DATA, $account_id, $basic], $result);
            }
        }

        return $result;
    }

    public function getMyFXBookMonthlyGainLossData($account_id) {
        $result = Services::cache()->get([Cache::CACHE_KEY_MYFXBOOK_MONTHLY_GAIN_LOSS_DATA, $account_id], null);

        if (!isset($result)) {
            $account_info = $this->getMyFXBookAccountInfo($account_id);
            $startYear = intval(\DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y'));
            $endYear = intval(\DateTime::createFromFormat('m/d/Y H:i', $account_info->lastUpdateDate)->format('Y'));
            $data = [];

            while ($startYear <= $endYear) {
                $query = Services::http()->buildQuery(
                    Services::config()->FXSERVICE_API['myfxbook_url'],
                    'charts.json',
                    [
                        'chartType' => 3,
                        'monthType' => 0,
                        'accountOid' => $account_id,
                        'startDate' => "{$startYear}-01-01",
                        'endDate' => (new \DateTime())->format('Y-m-d')
                    ]
                );
                $result = Services::http()->get($query);

                if (isset($result->categories) && isset($result->series)) {
                    $series_data = array_map(function ($v) {
                        return $v[0];
                    }, $result->series[0]->data);

                    $data[] = [$result->categories, $series_data];
                }

                $startYear++;
            }

            $result = !empty($data) ? $data : null;
            Services::cache()->set([Cache::CACHE_KEY_MYFXBOOK_MONTHLY_GAIN_LOSS_DATA, $account_id], $result);
        }

        return $result;
    }

    public function getMyFXBookAccountInfo($account_id) {
        if ($accounts = $this->getMyFXBookAccounts()) {
            foreach ($accounts as $acc) {
                if ($acc->id == $account_id) {
                    return $acc;
                }
            }
        }

        throw new \Exception("Account {$account_id} didn't found in MyFxBook accounts.");
    }

    public function getMyFXBookTotalAccountGain($account_id) {
        $result = Services::cache()->get([Cache::CACHE_KEY_MYFXBOOK_TOTAL_GAIN_DATA, $account_id], null);

        if (!isset($result)) {
            $account_info = $this->getMyFXBookAccountInfo($account_id);

            $query = Services::http()->buildQuery(
                Services::config()->FXSERVICE_API['myfxbook_url'],
                'api/get-gain.json',
                [
                    'session' => $this->getMyFXBookSession(),
                    'id' => $account_id,
                    'start' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y-m-d'),
                    'end' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->lastUpdateDate)->format('Y-m-d')
                ]
            );

            $result = Services::http()->get($query);

            if (!$result->error) {
                $result = $result->value;
                Services::cache()->set([Cache::CACHE_KEY_MYFXBOOK_TOTAL_GAIN_DATA, $account_id], $result);
            }
        }

        return $result;
    }

    public function getFXBlueGrowthData($account_id) {
        $result = Services::cache()->get([Cache::CACHE_KEY_FXBLUE_GROWTH_DATA, $account_id], null);

        if (!isset($result)) {
            $query = Services::http()->buildQuery(
                Services::config()->FXSERVICE_API['fxblue_url'],
                'fxbluechart.aspx',
                [
                    'c' => 'ch_cumulativereturn',
                    'id' => $account_id
                ]
            );

            if ($result = Services::http()->get($query, HTTP::RESPONSE_TYPE_RAW)) {
                $result = preg_replace('/[\s\t\r\n]+/', '', $result);

                if (preg_match("/data\.addRows\(\[(?:\['Start',0\],)?(.+)\]\);/", $result, $match) > 0) {
                    $result = json_decode("[{$match[1]}]");
                    Services::cache()->set([Cache::CACHE_KEY_FXBLUE_GROWTH_DATA, $account_id], $result);
                }
            }
        }

        return $result;
    }

    public function getFXBlueMonthlyGainLossData ($account_id) {
        $result = Services::cache()->get([Cache::CACHE_KEY_FXBLUE_MONTHLY_GAIN_LOSS_DATA, $account_id], null);

        if (!isset($result)) {
            $query = Services::http()->buildQuery(
                Services::config()->FXSERVICE_API['fxblue_url'],
                'fxbluechart.aspx',
                [
                    'c' => 'ch_cumulativereturn',
                    'id' => $account_id
                ]
            );

            if ($result = Services::http()->get($query, HTTP::RESPONSE_TYPE_RAW)) {
                $result = preg_replace('/[\s\t\r\n]+/', '', $result);

                if (preg_match("/data\.addRows\(\[(?:\['Start',0\],)?(.+)\]\);/", $result, $match) > 0) {
                    $result = json_decode("[{$match[1]}]");
                    Services::cache()->set([Cache::CACHE_KEY_FXBLUE_MONTHLY_GAIN_LOSS_DATA, $account_id], $result);
                }
            }
        }

        return $result;
    }

    public function getFXBlueAccountData($account_id) {
        $result = Services::cache()->get([Cache::CACHE_KEY_FXBLUE_ACCOUNT_DATA, $account_id], null);

        if (!isset($result)) {
            $query = Services::http()->buildQuery(
                Services::config()->FXSERVICE_API['fxblue_url'],
                'fxbluechart.aspx',
                [
                    'c' => 'ch_accountstats',
                    'id' => $account_id
                ]
            );

            if ($result = Services::http()->get($query, HTTP::RESPONSE_TYPE_RAW)) {
                $result = preg_replace('/[\s\t\r\n]+/', '', $result);

                if (preg_match("/document\.ChartData=(\{.+\});/", $result, $match) > 0) {
                    $result = json_decode($match[1]);
                    Services::cache()->set([Cache::CACHE_KEY_FXBLUE_ACCOUNT_DATA, $account_id], $result);
                }
            }
        }

        return $result;
    }

    public function getMyFXBookSession($login = null, $password = null) {
        $session = null;
        if (isset($login) && isset($password)) {
            $query = Services::http()->buildQuery(
                Services::config()->FXSERVICE_API['myfxbook_url'],
                'api/login.json',
                [
                    'email' => $login,
                    'password' => $password
                ]
            );

            $result = Services::http()->get($query);

            if (!$result->error) {
                $session = $result->session;
                Services::cache()->set(Cache::CACHE_KEY_MYFXBOOK_SESSION, $session);
            }
        } else if (!($session = Services::cache()->get(Cache::CACHE_KEY_MYFXBOOK_SESSION, null))) {
            $options = get_option(Plugin::getOptionName());
            $login = isset($options['login_field']) ? $options['login_field'] : null;
            $password = isset($options['password_field']) ? $options['password_field'] : null;

            $query = Services::http()->buildQuery(
                Services::config()->FXSERVICE_API['myfxbook_url'],
                'api/login.json',
                [
                    'email' => $login,
                    'password' => $password
                ]
            );

            $result = Services::http()->get($query);

            if (!$result->error) {
                $session = $result->session;
                Services::cache()->set(Cache::CACHE_KEY_MYFXBOOK_SESSION, $session);
            }
        }

        return $session;
    }

    public function getMyFXBookAccounts() {
        $accounts = Services::cache()->get(Cache::CACHE_KEY_MYFXBOOK_ACCOUNTS, null);

        if (!isset($accounts)) {
            $query = Services::http()->buildQuery(
                Services::config()->FXSERVICE_API['myfxbook_url'],
                'api/get-my-accounts.json',
                ['session' => $this->getMyFXBookSession()]
            );

            $result = Services::http()->get($query);

            if (!$result->error) {
                $accounts = $result->accounts;
                Services::cache()->set(Cache::CACHE_KEY_MYFXBOOK_ACCOUNTS, $accounts);
            }
        }
        return $accounts;
    }
}