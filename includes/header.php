<?php

echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"utf-8\"><title>";
if (isset ($ext_title)) echo $ext_title . " - ".sitename;
echo "</title><link rel=\"stylesheet\" href=\"".relroot."/styles/reset.css\" /><link rel=\"stylesheet\" href=\"".relroot."/styles/default.css\" />";
if (isset ($ext_css) && is_array ($ext_css)) foreach ($ext_css as $css) echo "<link rel=\"stylesheet\" href=\"".relroot."/styles/".$css."\" />";

echo "<script src=\"".relroot."/js/config.js\"></script>";
echo "<script src=\"".relroot."/js/jquery.js\"></script>";
if (isset ($ext_js) && is_array ($ext_js)) foreach ($ext_js as $js) echo "<script src=\"".$js."\"></script>";

echo "<link rel=\"shortcut icon\" href=\"".relroot."/favicon.ico\" type=\"image/x-icon\" />";

echo "</head><body><div id=\"container\">";

?>