<?php
$state = urldecode($_GET["state"]);
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';

$path = str_replace("Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/redirect.php", "", $_SERVER['SCRIPT_NAME']);

$address = $_SERVER['HTTP_HOST'];

if (array_key_exists("code", $_GET)) {
	$str = $protocol . $address . $path . htmlspecialchars_decode($state) . '&code=' . $_GET["code"]
	       . '&state=' . $_GET["state"];
	header('Location: ' . $str);
} else {
	$str1 = $protocol . $address . $path . htmlspecialchars_decode($state);
	header('Location: ' . $str1);
}
