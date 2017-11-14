<?php
namespace WDIP\Plugin\Options;

use WDIP\Plugin\FXServiceModel;
use WDIP\Plugin\FXServiceData;

/**
 * @property $uid
 * @property $charttype
 * @property $title
 * @property $chartheight
 * @property $chartwidth
 * @property $accountid
 * @property $backgroundcolor
 * @property $gridlinecolor
 */
abstract class AbstractOptions extends FXServiceData {
    private static $count = 0;
    private $model;

    /**
     * @param array $data
     * @return void
     */
    abstract protected function generate(array $data);

    /**
     * @return array
     */
    abstract protected function getData();

    public function __construct(FXServiceData $data = null) {
        parent::__construct($data);
        $this->init();
    }

    private function init() {
        $this->model = new FXServiceModel();

        $uid_string = sprintf("%s-%s-%d-%d", get_called_class(), $this->charttype, time(), self::$count++);
        $this->uid = md5($uid_string);

        $this->generate($this->getData());
    }

    /**
     * @return FXServiceModel
     */
    public function getModel() {
        return $this->model;
    }
}