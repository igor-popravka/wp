<?php
/**
 * @Plugin Name: FX-Service Client
 * @Description: Builds Charts/Graphs/Tables using the data from API of the FX-Service Clients: [<a href="https://www.myfxbook.com/api">MYFXBook</a>] [<a href="https://www.fxblue.com">FXBlue Client</a>]
 * @Version:     2.2.10
 * @Author:      Igor Popravka
 * @Author URI:  https://www.upwork.com/freelancers/~010854a54a1811f970
 */

require __DIR__ . '/vendor/autoload.php';

use WDIP\Plugin\Services;

Services::system()->registerPlugin(__FILE__);

Services::plugin()->build('wdip-plugin-config.ini');