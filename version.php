<?php

define('APP_VERSION', value: '0.1.0-alpha');
define('APP_BUILD', trim(shell_exec('git rev-list --count HEAD')));
define('APP_TIMESTAMP', trim(shell_exec('git log -1 --format=%cd')));
define('APP_FULL_VERSION', APP_VERSION . '+' . APP_BUILD);

?>
