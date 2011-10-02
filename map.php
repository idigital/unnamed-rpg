<?php

/**
* This is the page where the user moves around the world. The world is displayed in a grid of images
* representing what's in that space. The world should be pretty massive, and so we don't want to be
* showing the entire world to the player; only a small range of sight.
*/

define ('LOGIN', true);
define ('FORCE_PHASE', true);
require_once ('includes/notextinc.php');

$ext_title = "The World";
$ext_css[] = "map.css";
$ext_js[] = relroot."/js/map.js";
require_once ('includes/header.php');

include ('fight_scripts/stat_bar.php');

echo "<div id=\"map\"><div>\n<div id=\"map_table\">\n";
echo "</div>\n</div>\n";

echo "</div>\n";

echo "<div id=\"map_right\">\n";

echo "<img style=\"width:120px; height:120px; border: 0px;\" usemap=\"#navmap\" src=\"".relroot."/images/map_ui/navarrows.gif\" />\n";
echo <<<MAPAREA
<map name="navmap" id="navmap">
  <area alt="Northwest" href="javascript:generateMap ('nw')" coords="6,6,39,6,39,11,11,39,6,39" shape="poly" />
  <area alt="North" href="javascript:generateMap ('north')" coords="60,0,82,22,82,26,36,26,36,21,59,0" shape="poly" />
  <area alt="Northeast" href="javascript:generateMap ('ne')" coords="112,7,112,39,107,39,79,11,79,7" shape="poly" />
  <area alt="West" href="javascript:generateMap ('west')" coords="0,59,21,38,26,38,26,83,21,83,0,61" shape="poly" />
  <area alt="East" href="javascript:generateMap ('east')" coords="119,61,99,83,93,83,93,37,98,37,119,57" shape="poly" />
  <area alt="Southwest" href="javascript:generateMap ('sw')" coords="6,113,6,80,11,80,39,108,39,113" shape="poly" />
  <area alt="South" href="javascript:generateMap ('south')" coords="59,119,37,98,37,93,83,93,83,97,61,119" shape="poly" />
  <area alt="Southeast" href="javascript:generateMap ('se')" coords="113,113,80,113,80,109,107,81,113,81" shape="poly" />
</map>
MAPAREA;

echo "<div id=\"mapnav\">\n";
echo "</div>\n";

echo "</div>\n";


require_once ('includes/footer.php');
?>