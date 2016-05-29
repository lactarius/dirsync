<?php

use DirSync\DirSync;
use DirSync\Util;

require_once __DIR__ . '/bootstrap.php';


$ds = new DirSync();



Util::vo($ds->getRootDir());
