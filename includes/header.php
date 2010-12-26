<?php

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n\t\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\">\n<head>\n<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n<title>";
if (isset ($ext_title)) echo $ext_title . " - ".sitename;
echo "</title>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"".relroot."/styles/reset.css\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"".relroot."/styles/default.css\" />\n";
if (is_array ($ext_css)) foreach ($ext_css as $css) echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".relroot."/styles/".$css."\" />\n";

echo "<script src=\"".relroot."/js/config.js\" type=\"text/javascript\"></script>\n";
echo "<script src=\"".relroot."/js/jquery.js\" type=\"text/javascript\"></script>\n";
if (is_array ($ext_js)) foreach ($ext_js as $js) echo "<script src=\"".$js."\" type=\"text/javascript\"></script>\n";

echo "</head>\n<body>\n<div id=\"container\">\n";

?>