<?php

use DirSync\DirSync;
use DirSync\Util;

require_once __DIR__ . '/bootstrap.php';


$ds = new DirSync();

$ds->setRootDir( './holes' );

Util::vo($ds->getRootDir());
