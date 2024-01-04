<?php

/**
 * Plugin Name: ZEnMo MiniOrange Ory Keto hook
 * Plugin URI: miniorange-keto-hook
 * Description: sync roles from Ory Keto after login in WordPress
 * Version: 0.0.1
 * Author: Erik van Velzen
 * Author URI: https://www.zenmo.com
 * License: MIT/Expat
 */

use Zenmo\ZenmoRoleSync;

require_once __DIR__ . '/vendor/autoload.php';

ZenmoRoleSync::setup();
