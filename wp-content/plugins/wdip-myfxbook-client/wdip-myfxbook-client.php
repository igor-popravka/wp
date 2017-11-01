<?php
/**
 * @Plugin Name: MyFXBook Client
 * @Description: MyFXBook Client plugin, which builds charts/graphs using the data from  API <a href="https://www.myfxbook.com/api">https://www.myfxbook.com/api</a>
 * @Version:     2.0.9
 * @Author:      Igor Popravka
 * @Author URI:  https://www.upwork.com/freelancers/~010854a54a1811f970
 */

define("WDIP_ROOT", __DIR__);
define("WDIP_PLUGIN", __FILE__);

require WDIP_ROOT . '/vendor/autoload.php';

\WDIP\Plugin\MyFXBookClient::instance()->build();
