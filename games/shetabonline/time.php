<?php

require '_calendar.php';
require '_utilities.php';

send('ok', calendar::get_now_int());
?>