<?php
	$url = 'https://api.fda.gov/food/enforcement.json?api_key=dkjmH4qrI5pMYoj8hN0SCR8mhESAPGg8XxBH169b&search=report_date:[20040101+TO+20141231]&limit=100';
	$data = array('key1' => 'value1', 'key2' => 'value2');

	$options = array(
		'http' => array(
			'header'  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n", 
			'method'  => 'GET',
			'content' => http_build_query($data),
		),
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	$bigArr = array();
	$resArr = array();
	$datArr = array();
	$vals = array();
	$bigArr = json_decode($result,true,20);

	$resArr = $bigArr['results'];

	for($x = 0; $x < count($resArr); $x++) {
		$datArr = $resArr[$x];
		$names = array_keys($datArr);
		$cols = '';
		$val = '';
		foreach ($names as $name) {
			$cols .= str_replace('@','',$name) . ' varchar(500), ';
			if ($name == 'openfda') {
				$val .= '|';
			} else {
				$val .=  $datArr[$name] . '|';
			}
		}
		$end = strrpos($val, '|');
		$val = substr($val, 0, $end);
		$vals[$x] = $val;
	}
	$end = strrpos($cols, ',');
	$cols = substr($cols, 0, $end);

	$link = mysqli_connect("54.152.245.25","4840w","4840w","4840w"); 
	if (!$link) { 
		die('Could not connect to MySQL: ' . mysql_error()); 
	} 
	echo 'Connection OK<br>'; 
	$sql = "drop table fda_food_recall_tmp";
	$result = mysqli_query($link, $sql) or die("Error in DDL " . mysqli_error($link));
	echo "temp table dropped<br><br>";

	$hdrDDL = 'create table fda_food_recall_tmp (' . $cols . ')';
	$sql = $hdrDDL; 
	$result = mysqli_query($link, $sql) or die("Error in DDL " . mysqli_error($link));
	echo $hdrDDL . ' executed - table created.<br><br>';

	for($x = 0; $x < count($vals); $x++) {
		$row = str_replace('\'','',$vals[$x]); // clean internal apostrophes
		$row = str_replace('|','\',\'',$row); // change the pipes to single-quote-comma combos for the insert statement
		$row = 'insert into fda_food_recall_tmp values (\'' . $row . '\')';
		$sql = $row; 
		$result = mysqli_query($link, $sql) or die("Error in DDL " . mysqli_error($link));
		echo $row . ' inserted.<br><br>';
	}

	mysqli_close($link); 
	
?>	
