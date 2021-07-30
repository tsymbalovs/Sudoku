<?php // ЮТФ-8

// session_set_cookie_params(31622400, '/', '', '', true);

header("Content-Type: text/html; charset=utf-8");
ini_set('magic_quotes_gpc', '0');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
$_SESSION['time_start'] = microtime(true);
// $_SESSION['dctrueplace'] = true;

include 'config.php';
include $_SESSION['baseurl'] . 'functions.php';

dbConnect('incore', $conf['db']['host'], $conf['db']['user'], $conf['db']['password'], $conf['db']['base']);

$data = [];
$action = '';
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

$ip = getIP();

if ($action == 'step') {
	$number = filter_input(INPUT_GET, 'number', FILTER_SANITIZE_NUMBER_INT);
	$n = filter_input(INPUT_GET, 'n', FILTER_SANITIZE_NUMBER_INT);
	$digit = filter_input(INPUT_GET, 'digit', FILTER_SANITIZE_NUMBER_INT);

	unset($l, $game, $estr);
	$r = dbQuery("SELECT * FROM `" . $dbgames . "` WHERE `number`='" . $number . "'");
	while ($l = mysqli_fetch_assoc($r)) {
		$game = unserialize($l['game']);
		$user = $l;
	}

	if (empty($game)) {
		$data['error'] = 'usernotfound';
	} else {
		if ($game['ustr'][$n] != 0) {
			// nothing to do because digit taken
			$data['error'] = 'digittaken';
		} elseif ($user['estr'][$n] == $digit) {
			$game['ustr'][$n] = $digit;
			$game['emptyqty']--;
			$game['numqty'][$digit]--;
			dbQuery("UPDATE `" . $dbgames . "` SET `game`='" . serialize($game) . "' WHERE `number`='" . string2sql($number) . "';");
			$game['n'] = $n;
			$game['digit'] = $digit;
			$data['alert'] = 'stepright';
		} else {
			$game['mistakes'] = (int)$game['mistakes'] + 1;
			dbQuery("UPDATE `" . $dbgames . "` SET `game`='" . serialize($game) . "' WHERE `number`='" . string2sql($number) . "';");
			$game['n'] = $n;
			$game['digit'] = $digit;
			$data['alert'] = 'stepwrong';
			usleep(200000);
		}
		$data['game'] = $game;
		$data['game']['time'] = time() - strtotime($user['date']);
	}
}



if ($action == 'resume') {
	$number = filter_input(INPUT_GET, 'number', FILTER_SANITIZE_NUMBER_INT);

	unset($l);
	$r = dbQuery("SELECT * FROM `" . $dbgames . "` WHERE `number`='" . string2sql($number) . "'");
	while ($l = mysqli_fetch_assoc($r)) {
		$data['user']['number'] = $l['number'];
		// $data['user']['username'] = $l['username'];
		$data['user']['level'] = $l['level'];
		$data['user']['theme'] = $l['theme'];
		$data['game'] = unserialize($l['game']);
		$data['game']['time'] = time() - strtotime($l['date']);
		$data['game']['levelname'] = $levelArr[$l['level']]['name'];
	}

	if (empty($data['user'])) {
		$data['error'] = 'usernotfound';
	}
}



if ($action == 'start') {
	$error = [];

	$level = filter_input(INPUT_GET, 'level', FILTER_SANITIZE_SPECIAL_CHARS);
	if (empty($level)) $error[] = 'Level is no set';

	$theme = filter_input(INPUT_GET, 'theme', FILTER_SANITIZE_SPECIAL_CHARS);
	if (empty($theme)) $error[] = 'Theme is no set';

	if (empty($error) and isset($levelArr[$level])) {
		$emptydigs = mt_rand($levelArr[$level]['min'], $levelArr[$level]['max']);

		if ($level == 'veryhard') {
			$selectepmty = 55;
		} else {
			$selectepmty = $emptydigs;
		}
		// $username = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_SPECIAL_CHARS);

		unset($l, $gs);
		$r = dbQuery("SELECT * FROM `" . $dbcells . "` WHERE `empty`>=" . $selectepmty . " ORDER BY RAND() LIMIT 1;");
		while ($l = mysqli_fetch_assoc($r)) {
			$gs = json_decode($l['data'], true);
		}

		if (!isset($gs)) {
			die('No cells');
		} else {

			if ($level == 'veryhard') {
				while ($emptydigs > $gs['emptyqty']) {
					$r = mt_rand(0, 80);
					if ($gs['ustr'][$r] != '0') {
						$gs['emptyqty']++;
						$gs['numqty'][$gs['ustr'][$r]]++;
						$gs['ustr'][$r] = 0;
					}
				}
			} else {
				while ($emptydigs < $gs['emptyqty']) {
					$r = mt_rand(0, 80);
					if ($gs['ustr'][$r] == '0') {
						$gs['ustr'][$r] = $gs['estr'][$r];
						$gs['emptyqty']--;
						$gs['numqty'][$gs['estr'][$r]]--;
					}
				}
			}

			$game = [];
			$game['emptyqty'] = $gs['emptyqty'];
			$game['numqty'] = $gs['numqty'];
			$game['ustr'] = $gs['ustr'];
			$game['mistakes'] = 0;
			$estr = $gs['estr'];

			$number = getNumber();
			$date = date('Y-m-d H:i:s');

			dbQuery("INSERT INTO `" . $dbgames . "` (`ip`,`number`,`date`,`level`,`theme`,`estr`,`game`) VALUES ('" . string2sql($ip) . "','" . string2sql($number) . "','" . string2sql($date) . "','" . string2sql($level) . "','" . string2sql($theme) . "','" . $estr . "','" . serialize($game) . "');");

			$data['user']['number'] = $number;
			$data['user']['level'] = $level;
			$data['user']['theme'] = $theme;
			$data['game'] = $game;
			$data['game']['levelname'] = $levelArr[$level]['name'];
		}
	} else {
		$data['error'] = $error;
	}
}


if (!empty($data)) {
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json');
	echo json_encode($data);
}


clearstatcache();
dbClose();
