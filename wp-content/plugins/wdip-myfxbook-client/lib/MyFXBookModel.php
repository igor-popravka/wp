<?php
namespace WDIP\Plugin;

use WDIP\Plugin\MyFXBookClient as MFBClient;
use WDIP\Plugin\MyFXBookConfig as MFBConfig;

/**
 * @author: igor.popravka
 * Date: 24.10.2017
 * Time: 11:51
 */
class MyFXBookModel {

    public function getTotalGrowthData($account_id) {
        $key = md5("MYFXBOOK-TOTAL-GROWTH-DATA-{$account_id}");
        $result = RuntimeCache::instance()->getValue($key);

        if (empty($result) && ($account_info = $this->getAccountInfo($account_id))) {
            if (MFBClient::instance()->getEnvironment() == MFBClient::ENV_DEV) {
                $result = MFBClient::instance()->getDataFromJSON("myfxbook.get-daily-gain-{$account_id}", true);
            } else {
                $daily_gain = MFBConfig::instance()->SERIES->daily_gain;
                $result = MFBClient::instance()->httpRequest($daily_gain->url, [
                    'session' => MFBClient::instance()->getSession(),
                    'id' => $account_id,
                    'start' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y-m-d'),
                    'end' => (new \DateTime())->format('Y-m-d')
                ]);
            }

            RuntimeCache::instance()->setValue($key, $result);
        }

        return RuntimeCache::instance()->getValue($key, []);
    }

    public function getMonthlyGainLossData($account_id) {
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
                    $series_data = array_map(function($v){
                        return $v[0];
                    }, $result->series[0]->data);

                    $data[]  = [$result->categories, $series_data];
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
}