<?php
/**
 * @Plugin Name: MyFXBook Plugin
 * @Description: MyFXBook Plugin, which builds charts/graphs using the data from  API <a href="https://www.myfxbook.com/api">https://www.myfxbook.com/api</a>
 * @Version:     1.6.7
 * @Author:      Web Developer Igor P.
 * @Author URI:  https://www.upwork.com/freelancers/~010854a54a1811f970
 */

require __DIR__ . '/wdip-bootstrap.php';

use WDIP\Plugin\MyFXBook;

MyFXBook::instance()->build();
register_deactivation_hook(__FILE__, MyFXBook::instance()->getCallback('delSettings'));