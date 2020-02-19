<?php
// Forked from https://gist.github.com/1809044
// Available from https://gist.github.com/nichtich/5290675#file-deploy-php
// Edited by Jake Hobbs
$TITLE   = 'Git Deployment Hamster';
$VERSION = '0.11';
include '/home/ubuntu/php-config/deploy_secret.php';
echo <<<EOT
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>$TITLE</title>
</head>
<body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
<pre>
  o-o    $TITLE
 /\\"/\   v$VERSION
(`=*=')
 ^---^`-.
EOT;
// Check whether client is allowed to trigger an update
$allowed_ips = array(
	'192.30.252.','191.30.253.','191.30.254.','191.30.255.','185.199.108.','185.199.109.','185.199.110.','185.199.111.','140.82.112.','140.82.113.','140.82.114.','140.82.115.','140.82.116.','140.82.117.','140.82.118.','140.82.119.','140.82.120.','140.82.121.','140.82.122.','140.82.123.','140.82.124.','140.82.125.','140.82.126.','140.82.127.' // GitHub
);
$allowed = false;
$ip = $_SERVER['REMOTE_ADDR'];

foreach ($allowed_ips as $allow) {
    if (stripos($ip, $allow) !== false) {
        $allowed = true;
        break;
    }
}
if (!$allowed) {
		header('HTTP/1.1 403 Forbidden');
	 	echo "<span style=\"color: #ff0000\">Sorry, no hamster - better convince your parents!</span>\n";
		echo "<span style=\"color: #ff0000\">Your IP: " . $_SERVER['REMOTE_ADDR'] . "</span>\n";
	  echo "</pre>\n</body>\n</html>";
	  exit;
}

// also check if secret from github is correct
if ($deploySecret !== NULL) {
	if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
		throw new \Exception("HTTP header 'X-Hub-Signature' is missing.");
	} elseif (!extension_loaded('hash')) {
		throw new \Exception("Missing 'hash' extension to check the secret code validity.");
	}
	list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');
	if (!in_array($algo, hash_algos(), TRUE)) {
		throw new \Exception("Hash algorithm '$algo' is not supported.");
	}
	$rawPost = file_get_contents('php://input');
	if (!hash_equals($hash, hash_hmac($algo, $rawPost, $deploySecret))) {
		throw new \Exception('Hook secret does not match.');
	}
}

flush();
// Actually run the update
$commands = array(
	'echo $PWD',
	'whoami',
	'git pull',
	'git status',
	'git submodule sync',
	'git submodule update',
	'git submodule status',
);
$output = "\n";
$log = "####### ".date('Y-m-d H:i:s'). " #######\n";
foreach($commands AS $command){
    // Run it
    $tmp = shell_exec("$command 2>&1");
    // Output
    $output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
    $output .= htmlentities(trim($tmp)) . "\n";
    $log  .= "\$ $command\n".trim($tmp)."\n";
}
$log .= "\n";
file_put_contents ('deploy.log',$log,FILE_APPEND);
echo $output;
?>
</pre>
</body>
</html>
