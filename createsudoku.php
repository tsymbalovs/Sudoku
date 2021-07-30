<?php // ЮТФ-8

header("Content-Type: text/html; charset=utf-8");
ini_set('magic_quotes_gpc', '0');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include 'config.php';

include $_SESSION['baseurl'] . 'functions.php';

dbConnect('incore', $conf['db']['host'], $conf['db']['user'], $conf['db']['password'], $conf['db']['base']);

function fsize($value, $digs = 2)
{
	if ($value > 999999999) $exitvalue = round(($value / 1073741824), $digs) . ' Гб';
	elseif ($value > 999999) $exitvalue = round(($value / 1048576), $digs) . ' Мб';
	elseif ($value > 999) $exitvalue = round(($value / 1024), $digs) . ' Кб';
	else $exitvalue = $value . ' б';
	return $exitvalue;
}

function redir($gourl = '?')
{
	header("location: " . $gourl);
	exit();
}



$action = filter_input(INPUT_GET, 'action');



if ($action == 'creategrids') {
	$num = filter_input(INPUT_POST, 'num');
	$codetime = microtime(true);

	for ($i = 0; $i < $num; $i++) {
		unset($grid);
		$grid = [
			[0, 0, 0, 0, 0, 0, 0, 0, 0,],
			[0, 0, 0, 0, 0, 0, 0, 0, 0,],
			[0, 0, 0, 0, 0, 0, 0, 0, 0,],
			[0, 0, 0, 0, 0, 0, 0, 0, 0,],
			[0, 0, 0, 0, 0, 0, 0, 0, 0,],
			[0, 0, 0, 0, 0, 0, 0, 0, 0,],
			[0, 0, 0, 0, 0, 0, 0, 0, 0,],
			[0, 0, 0, 0, 0, 0, 0, 0, 0,],
			[0, 0, 0, 0, 0, 0, 0, 0, 0,],
		];
		fillGrid($grid);
		dbQuery("INSERT INTO `" . $dbgrids . "` (`grid`) VALUES ('" . json_encode($grid) . "');");
	}
	$time = round(microtime(true) - $codetime, 4);
	$_SESSION['alerts'][] = $num . ' grids added for ' . $time;
	redir('?');
}



if ($action == 'createcells') {
	$codetime = microtime(true);
	$num = 0;

	unset($l, $cells);
	$r = dbQuery("SELECT `gid` FROM `" . $dbcells . "`");
	while ($l = mysqli_fetch_assoc($r)) {
		$cells[] = $l['gid'];
	}

	unset($l);
	$r = dbQuery("SELECT * FROM `" . $dbgrids . "`");
	while ($l = mysqli_fetch_assoc($r)) {
		if (!in_array($l['gid'], $cells)) {
			dbQuery("INSERT INTO `" . $dbcells . "` (`gid`) VALUES (" . $l['gid'] . ");");
			$grid = json_decode($l['grid']);
			$numqty = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0,];
			$emptyqty = 0;
			$attempts = 5;
			while ($attempts > 0) {
				$row = rand(0, 8); // random_int (PHP 7)
				$col = rand(0, 8); // random_int (PHP 7)
				while ($grid[$row][$col] == 0) {
					$row = rand(0, 8); // random_int (PHP 7)
					$col = rand(0, 8); // random_int (PHP 7)
				}
				$backup = $grid[$row][$col];
				$grid[$row][$col] = 0;
				$emptyqty++;
				$numqty[$backup]++;
				$copyGrid = $grid;
				$counter = 0;
				solveGrid($copyGrid);
				if ($counter != 1) {
					$grid[$row][$col] = $backup;
					$emptyqty--;
					$numqty[$backup]--;
					$attempts -= 1;
				}
			}
			$game = [];
			$game['estr'] = makeEstr(json_decode($l['grid']));
			$game['ustr'] = makeEstr($grid);
			$game['numqty'] = $numqty;
			$game['emptyqty'] = $emptyqty;
			dbQuery("UPDATE `" . $dbcells . "` SET `empty`=" . $emptyqty . ",`data`='" . json_encode($game) . "' WHERE `gid`=" . $l['gid'] . ";");
			$num++;
		}
	}

	$time = round(microtime(true) - $codetime, 4);
	$_SESSION['alerts'][] = $num . ' cells added for ' . $time;
	redir('?');
}


?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
	<title>Create Sudoku</title>
</head>

<body>
	<h1>Create Sudoku</h1>

	<?php
	if (isset($_SESSION['alerts'])) {
		foreach ($_SESSION['alerts'] as $v) {
			echo '<div class="alert alert-success" role="alert">' . $v . '</div>';
		}
		unset($_SESSION['alerts']);
	}
	?>

	<div class="card">
		<div class="card-body">
			<h2>Create Grids</h2>
			<form action="?action=creategrids" method="POST" class="form form-inline">
				Quantity <input type="number" name="num" class="form-control" value="1000"> <button type="submit" class="btn btn-primary">Create</button>
			</form>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<h2>Create Cells</h2>
			<form action="?action=createcells" method="POST" class="form form-inline">
				<button type="submit" class="btn btn-primary">Create</button>
			</form>
		</div>
	</div>

</body>

</html>
<?php

clearstatcache();
dbClose();
