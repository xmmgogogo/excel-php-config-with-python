<?php
if(count($_SERVER["argv"]) < 3) {
	echo "参数错误，请重新输入!";die;
}

if($_SERVER["argv"][2] == 1) {
	$config = file_get_contents("c:\\excelConfig.json");
} elseif($_SERVER["argv"][2] == 2) {
	$config = file_get_contents("c:\\excelConfig-vikingage.json");	
}

$config = json_decode($config, true);

//文件名
$filename = $_SERVER["argv"][1];
$filename2 = str_replace('.php', '.txt', $filename);

$data = file_get_contents($config['savePath'] . $filename);
$data = json_decode($data, true);
ksort($data);

$keydata = file_get_contents($config['savePath'] . $filename2);
$keydata = json_decode($keydata, true);

foreach($data as $kk => $vv){
	//去掉excel配置空健的字段 null
	if(strpos($kk, "null") === false) {
		$tmp = array();
		foreach($keydata as $key){
			$tmp[$key] = $vv[$key];	
		}
		
		$data[$kk] = $tmp;
		unset($tmp);
	} else {
		unset($data[$kk]);
	}
}

$name = str_replace('.php', '', $filename);
//如果是普通配置文件，则替换平台
if(!in_array($name, array('gameSetconfig', 'competitionProgress', 'orders', 'worldBoss', 'unlock')) && $_SERVER["argv"][2] == 2) {
    $config['configName'] = 'commonConfig';
}

$config['basePath'] = sprintf($config['basePath'], $config['configName']);
$namePath = $config['basePath'] . $name;

// echo $namePath;

/*$data = "<?php" . PHP_EOL . '$'. "J7CONFIG['" . $name . "'] = ".preg_replace("/\n/", PHP_EOL,var_export($data, true)). PHP_EOL . "?>";*/
if ($filename != 'tollgates.php') $data = "<?php" . PHP_EOL . '$'. "J7CONFIG['" . $name . "'] = ".var_export($data, true). PHP_EOL . "?>";

if(!file_exists($namePath)){
	mkdir($namePath);
}

$lang = $config['lang'];
$moreLangDir = array('webLang.php', 'lang.php', 'feed.php');
if(in_array($filename, $moreLangDir)) {
	$r = file_put_contents($namePath . '\\' . $lang . '.php', $data);
	// copy($namePath . '\\' . $lang . '.php', $namePath . '\zh_tw.php');
} elseif ($filename == 'tollgates.php') {
	if($lang == 'en') {
		$lang = 'en_us';
	}
	$basePath = substr($namePath, 0, -10);

	//特殊处理飞龙
	require ($basePath . "\\waves\\" . $lang . ".php");
	require ($basePath . "\\subWave\\" . $lang . ".php");
	require ($basePath . "\\monsters\\" . $lang . ".php");

	foreach ($data as $key => $value) {
		$wave = explode(',', $value['wave']);
		$minDuration = 0;//最短的关卡时段
		$maxDuration = 0;//最长的关卡时段
		$waitTime = array();
		foreach ($wave as $waveKey => $waveValue) {
			$subWaves = explode(',', $J7CONFIG['waves'][$waveValue]['subWaves']);
			$subWavesArr = array();
			foreach ($subWaves as $subWaveKey => $subWaveValue) {
				$monsterTypeId = $J7CONFIG['subWave'][$subWaveValue]['monsterTypeId'];
				$type = $J7CONFIG['monsters'][$monsterTypeId]['type'];
				if ($type == 2) {
					$data[$key]['flyMonster'] = 1;
				}
				$times = $J7CONFIG['subWave'][$subWaveValue]['times'];
				$interval = $J7CONFIG['subWave'][$subWaveValue]['interval'];
				$startTime = $J7CONFIG['subWave'][$subWaveValue]['startTime'];
				$calculationTime = ($times-1) * $interval + $startTime;
				$subWavesArr[] = $calculationTime;
			}
			$minDuration += max($subWavesArr);
			$waitTime[] = $J7CONFIG['waves'][$waveValue]['waitTime'];
		}
		array_shift($waitTime);
		$waitTime = array_sum($waitTime) * 1000;
		$maxDuration = $minDuration + $waitTime;

		$data[$key]['minDuration'] = $minDuration;
		$data[$key]['maxDuration'] = $maxDuration;
	}

	$data = "<?php" . PHP_EOL . '$'. "J7CONFIG['" . $name . "'] = ".var_export($data, true). PHP_EOL . "?>";
	$r = file_put_contents($namePath . '\en_us.php', $data);
	copy($namePath . '\en_us.php', $namePath . '\zh_tw.php');
} else {
	$r = file_put_contents($namePath . '\en_us.php', $data);
	copy($namePath . '\en_us.php', $namePath . '\zh_tw.php');
}

unlink($config['savePath'] . $filename);
unlink($config['savePath'] . $filename2);

if($r)
	echo "[$name] write ok!\r\n";
else
	echo "[$name] write faile!\r\n";