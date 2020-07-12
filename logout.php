<?php

require_once dirname(__FILE__).'/libs/config.php';

setcookie('rememberme', '', 0);
session_destroy();

header('Location: login.php?logged_out=1');
exit;