<?php
namespace WDIP\Plugin\Options;

use WDIP\Plugin\ObjectData;

/**
 * @property $uid
 * @property $chartType
 * @property $title
 * @property $chartHeight
 * @property $chartWidth
 * @property $accountId
 * @property $backgroundColor
 * @property $gridLineColor
 */
abstract class AbstractOptions extends ObjectData {
    private static $count = 0;

    /**
     * @param array $data
     * @return void
     */
    abstract protected function generate(array $data);

    /**
     * @return array
     */
    abstract protected function getData();

    public function __construct(ObjectData $data = null) {
        parent::__construct($data);
        $this->init();
    }

    private function init() {
        $uid_string = sprintf("%s-%s-%d-%d", get_called_class(), $this->chartType, time(), self::$count++);
        $this->uid = md5($uid_string);

        $this->generate($this->getData());
    }
}