<?php
namespace WDIP\Plugin\Attributes;


class ShortCodeAttributes extends AbstractCollectionAttributes {
    const CHART_TYPE_MONTH_GROWTH = 'month-growth';
    const CHART_TYPE_TOTAL_GROWTH = 'total-growth';
    const CHART_TYPE_MONTHLY_GAIN_LOSS = 'monthly-gain-loss';
    const CHART_TYPE_CALCULATOR_FORM = 'calculator-form';
    const CHART_TYPE_MONTH_GROWTH_TABLE = 'month-growth-table';
    
    protected function getAttrConfig() {
        return [
            'account-id' => ['required' => true, 'default' => '', 'type' => Attribute::TYPE_LIST],
            'chart-type' => ['required' => true, 'default' => '', 'type' => Attribute::TYPE_STRING],
            'background-color' => ['required' => false, 'default' => '#FFFFFF', 'type' => Attribute::TYPE_STRING],
            'grid-line-color' => ['required' => false, 'default' => '#465D86', 'type' => Attribute::TYPE_STRING],
            'title' => ['required' => false, 'default' => '', 'type' => Attribute::TYPE_STRING],
            'fee' => ['required' => false, 'default' => '', 'type' => Attribute::TYPE_LIST],
        ];
    }
}