<?php
namespace WDIP\Plugin;

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
abstract class MyFXBookOptions extends MyFXBookData {
    private static $count = 0;
    private $model;

    abstract protected function generate();

    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->init();
    }

    private function init(){
        $this->model = new MyFXBookModel();

        $uid_string = sprintf("%s-%s-%d-%d", get_called_class(), $this->charttype, time(), self::$count++);
        $this->uid = md5($uid_string);
    }

    /**
     * @return MyFXBookModel
     */
    public function getModel(){
        return $this->model;
    }
}