<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/loader.php';

Tester\Environment::setup();
date_default_timezone_set( 'Europe/Prague' );
define( 'TMP_DIR', __DIR__ . '/tmp' );
