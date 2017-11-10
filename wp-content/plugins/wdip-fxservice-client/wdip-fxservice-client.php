<?php
/**
 * @Plugin Name: FX-Service Client
 * @Description: Builds Charts/Graphs/Tables using the data from API of the FX-Service Clients: [<a href="https://www.myfxbook.com/api">MYFXBook</a>] [<a href="https://www.fxblue.com">FXBlue Client</a>]
 * @Version:     2.1.1
 * @Author:      Igor Popravka
 * @Author URI:  https://www.upwork.com/freelancers/~010854a54a1811f970
 */

define("WDIP_ROOT", __DIR__);
define("WDIP_PLUGIN", __FILE__);

require WDIP_ROOT . '/vendor/autoload.php';

\WDIP\Plugin\MyFXBookClient::instance()->build();
