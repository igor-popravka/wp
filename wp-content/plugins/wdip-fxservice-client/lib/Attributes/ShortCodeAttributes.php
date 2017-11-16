<?php
namespace WDIP\Plugin\Attributes;


class ShortCodeAttributes extends AbstractCollectionAttributes {
    public function __construct(array $attributes) {
        $this->fromArray($attributes);
    }

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