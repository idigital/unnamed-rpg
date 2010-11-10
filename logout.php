<?php

include ('includes/notextinc.php');

session_destroy();
header ('Location: '.relroot);

?>