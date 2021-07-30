<?php // ЮТФ-8

$levelArr['test'] = ['name' => 'Test', 'min' => 2, 'max' => 3,]; // 79
$levelArr['easy'] = ['name' => 'Easy', 'min' => 31, 'max' => 34,]; // 47
$levelArr['middle'] = ['name' => 'Middle', 'min' => 43, 'max' => 47,]; // 36
$levelArr['hard'] = ['name' => 'Hard', 'min' => 52, 'max' => 56,]; // 28
$levelArr['veryhard'] = ['name' => 'Impossible', 'min' => 61, 'max' => 62,]; // 19



function dbConnect($type = 'incore', $host = '', $user = '', $password = '', $base = '', $port = '3306', $socket = '', $charset = 'utf8mb4', $collate = 'utf8mb4_unicode_ci')
{
	$dbconn = mysqli_connect($host, $user, $password, $base, $port) or trigger_error(2, E_USER_ERROR);
	mysqli_query($dbconn, "set character_set_client='" . $charset . "'");
	mysqli_query($dbconn, "set character_set_results='" . $charset . "'");
	mysqli_query($dbconn, "set collation_connection='" . $collate . "'");
	if ($type == 'incore') $_SESSION['dbconn'] = $dbconn;
	else return $dbconn;
}



function dbQuery($query, $connect = '')
{
	if ($connect == '') $connect = $_SESSION['dbconn'];
	$result = mysqli_query($connect, $query) or exit(mysqli_error($connect));
	return $result;
}



function dbClose($connect = '')
{
	if ($connect == '') $connect = $_SESSION['dbconn'];
	mysqli_close($connect);
}



function queryEscape($query, $connect = '')
{
	if ($connect == '') $connect = $_SESSION['dbconn'];
	$result = mysqli_real_escape_string($connect, $query);
	return $result;
}



function getIP()
{
	$ip = '';
	if (getenv("HTTP_CLIENT_IP")) $ip = getenv("HTTP_CLIENT_IP");
	elseif (getenv("HTTP_X_FORWARDED_FOR")) $ip = getenv("HTTP_X_FORWARDED_FOR");
	else $ip = getenv("REMOTE_ADDR");
	return $ip;
}



function printr($string)
{
	echo '<pre>';
	print_r($string);
	echo '</pre>';
}



function string2sql($str, $connect = '')
{
	$str = trim($str);
	if (ini_get('magic_quotes_gpc')) $str = stripslashes($str);
	$str = queryEscape($str, $connect);
	return $str;
}



function getNumber()
{
	return (string)time() . (string)mt_rand(10000, 99999);
}



function checkGrid($grid)
{
	for ($row = 0; $row < 9; $row++) {
		for ($col = 0; $col < 9; $col++) {
			if ($grid[$row][$col] == 0) return False;
		}
	}
	// We have a complete grid!
	return true;
}



function solveGrid(&$grid)
{
	global $counter;
	// Find next empty cell
	global $grid;
	for ($i = 0; $i < 81; $i++) {
		$row = (int)floor($i / 9);
		$col = $i % 9;
		if ($grid[$row][$col] == 0) {
			for ($value = 1; $value < 10; $value++) {
				// Check that this value has not already be used on this row
				if (!in_array($value, $grid[$row], true)) {
					// Check that this value has not already be used on this column
					if ($value != $grid[0][$col] and $value != $grid[1][$col] and $value != $grid[2][$col] and $value != $grid[3][$col] and $value != $grid[4][$col] and $value != $grid[5][$col] and $value != $grid[6][$col] and $value != $grid[7][$col] and $value != $grid[8][$col]) {
						// Identify which of the 9 squares we are working on
						$square = [];
						if ($row < 3) {
							if ($col < 3) $square = makeGrid($grid, range(0, 2), range(0, 2)); // square=[grid[i][0:3] for i in range(0,3)];
							elseif ($col < 6) $square = makeGrid($grid, range(3, 5), range(0, 2)); // square=[grid[i][3:6] for i in range(0,3)];
							else $square = makeGrid($grid, range(6, 8), range(0, 2)); // square=[grid[i][6:9] for i in range(0,3)];
						} elseif ($row < 6) {
							if ($col < 3) $square = makeGrid($grid, range(0, 2), range(3, 5)); // square=[grid[i][0:3] for i in range(3,6)];
							elseif ($col < 6) $square = makeGrid($grid, range(3, 5), range(3, 5)); // square=[grid[i][3:6] for i in range(3,6)];
							else $square = makeGrid($grid, range(6, 8), range(3, 5)); // square=[grid[i][6:9] for i in range(3,6)];
						} else {
							if ($col < 3) $square = makeGrid($grid, range(0, 2), range(6, 8)); // square=[grid[i][0:3] for i in range(6,9)];
							elseif ($col < 6) $square = makeGrid($grid, range(3, 5), range(6, 8)); // square=[grid[i][3:6] for i in range(6,9)];
							else $square = makeGrid($grid, range(6, 8), range(6, 8)); // square=[grid[i][6:9] for i in range(6,9)];
						}
						// Check that this value has not already be used on this 3x3 square
						if (!in_array($value, $square, true)) {
							$grid[$row][$col] = $value;
							if (checkGrid($grid)) {
								$counter += 1;
								break;
							} else {
								if (solveGrid($grid)) return True;
							}
						}
					}
				}
			}
			break;
		}
	}
	$grid[$row][$col] = 0;
	return False;
}



function fillGrid(&$grid)
{
	// global $grid;
	// Find next empty cell
	for ($i = 0; $i < 81; $i++) {
		$row = (int)floor($i / 9);
		$col = $i % 9;
		if ($grid[$row][$col] == 0) {
			$numberList = [1, 2, 3, 4, 5, 6, 7, 8, 9];
			shuffle($numberList);
			foreach ($numberList as $value) {
				// Check that this value has not already be used on this row
				if (!in_array($value, $grid[$row], true)) {
					// Check that this value has not already be used on this column
					if ($value != $grid[0][$col] and $value != $grid[1][$col] and $value != $grid[2][$col] and $value != $grid[3][$col] and $value != $grid[4][$col] and $value != $grid[5][$col] and $value != $grid[6][$col] and $value != $grid[7][$col] and $value != $grid[8][$col]) {
						// Identify which of the 9 squares we are working on
						$square = [];
						if ($row < 3) {
							if ($col < 3) $square = makeGrid($grid, range(0, 2), range(0, 2)); // square=[grid[i][0:3] for i in range(0,3)];
							elseif ($col < 6) $square = makeGrid($grid, range(3, 5), range(0, 2)); // square=[grid[i][3:6] for i in range(0,3)];
							else $square = makeGrid($grid, range(6, 8), range(0, 2)); // square=[grid[i][6:9] for i in range(0,3)];
						} elseif ($row < 6) {
							if ($col < 3) $square = makeGrid($grid, range(0, 2), range(3, 5)); // square=[grid[i][0:3] for i in range(3,6)];
							elseif ($col < 6) $square = makeGrid($grid, range(3, 5), range(3, 5)); // square=[grid[i][3:6] for i in range(3,6)];
							else $square = makeGrid($grid, range(6, 8), range(3, 5)); // square=[grid[i][6:9] for i in range(3,6)];
						} else {
							if ($col < 3) $square = makeGrid($grid, range(0, 2), range(6, 8)); // square=[grid[i][0:3] for i in range(6,9)];
							elseif ($col < 6) $square = makeGrid($grid, range(3, 5), range(6, 8)); // square=[grid[i][3:6] for i in range(6,9)];
							else $square = makeGrid($grid, range(6, 8), range(6, 8)); // square=[grid[i][6:9] for i in range(6,9)];
						}
						// Check that this value has not already be used on this 3x3 square
						if (!in_array($value, $square, true)) {
							$grid[$row][$col] = $value;
							if (checkGrid($grid)) {
								return True;
							} else {
								if (fillGrid($grid)) return True;
							}
						}
					}
				}
			}
			break;
		}
	}
	$grid[$row][$col] = 0;
	return False;
}



function showGrid($grid)
{
	echo '<br>';
	foreach ($grid as $r) echo implode(' , ', $r) . '<br>';
	echo '<br>';
}



function makeGrid($grid, $f1, $f2)
{
	// global $grid;
	$square = [];
	foreach ($f2 as $x) {
		foreach ($f1 as $y) {
			$square[] = $grid[$x][$y];
		}
	}
	return $square;
}



function makeEstr($grid)
{
	$str = '';
	foreach ($grid as $r) $str .= implode('', $r);
	return $str;
}
