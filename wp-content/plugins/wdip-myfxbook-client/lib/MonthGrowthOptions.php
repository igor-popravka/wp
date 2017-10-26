<?php
namespace WDIP\Plugin;

/**
 * @property $series
 * @property $monthtickinterval
 */
class MonthGrowthOptions extends MyFXBookOptions {
    protected function generate() {
        $this->monthtickinterval = 1000 * 3600 * 24 * 30;

        $series_data = [];
        $basic = 0;
        foreach ($this->accountid as $id) {
            $series_data = array_merge($series_data, $this->getModel()->getGrowthData($id, $basic));
            $basic = $series_data[count($series_data) - 1][1];
        }

        $group_data = [];
        foreach ($series_data as $data) {
            $date = \DateTime::createFromFormat("m/d/Y", $data[0]);

            $group_name = $date->format('Ym');
            if (!isset($group_data[$group_name])) {
                $group_data[$group_name] = [0, 0];
            }

            $uts = $date->getTimestamp() * 1000;
            if ($group_data[$group_name][0] <= $uts) {
                $group_data[$group_name][0] = $uts;
                $group_data[$group_name][1] = $data[1];
            }
        }

        $this->series = [[
            'name' => 'Quest',
            'data' => array_values($group_data),
            'color' => 'rgba(124, 181, 236, 0.7)',
            'negativeColor' => 'rgba(255, 79, 79, 0.7)'
        ]];
    }
}