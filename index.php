<?php

define ('LOGIN', 1);
define ('FORCE_PHASE', true);

require_once ('includes/notextinc.php');
$ext_title = "Homepage";
include_once ('includes/header.php');

echo "<p>Maybe you want <a href=\"".relroot."/map.php\">the map page</a>?</p>\n";

include_once ('includes/footer.php');
?>