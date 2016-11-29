<?php
if($_SERVER["argv"][2] == 1) {
	$config = file_get_contents("c:\\excelConfig.json");
} elseif($_SERVER["argv"][2] == 2) {
	$config = file_get_contents("c:\\excelConfig-vikingage.json");	
}

$config = json_decode($config, true);

// $fromPath = 'D:/facebook_td/branches/TD_Branch_001/release/v20130228/j7/app/configs/';
// $toPath = 'D:/facebootd/branches/TD_Branch_001/release/v20130228/j7/app/configs/';

//如果是普通配置文件，则替换平台
if($_SERVER["argv"][2] == 2) {
    $config['configName'] = 'commonConfig';
}

$fromPath = $toPath = sprintf($config['basePath'], $config['configName']);

//配置模板文件
// $fileConfig = array('items', 'tollgates');
$fileConfig = array($_SERVER["argv"][1]);

function fileCutting($fileConfig) {
	$errorLog = array();
	foreach ($fileConfig as $key) {
		if($key == 'tollgates' || $key == 'christmasToll') {
			$errorLog = array_merge($errorLog, cuttingWaves($key, 'en_us'));
		} elseif($key == 'items') {		
			$errorLog = array_merge($errorLog, cutting($key, 'en_us'));
			// cutting($key, 'zh_tw');
		}
	}

	if($errorLog) {
		echo PHP_EOL . PHP_EOL . "===========================>>>>ERROR MESSAGE================START" . PHP_EOL;
		echo implode(PHP_EOL, $errorLog);
		echo "===========================>>>>ERROR MESSAGE================END" . PHP_EOL. PHP_EOL. PHP_EOL;
	}	
}

function cutting($key, $lang) {
	global $fromPath, $toPath;
	$errorLog = array();
	$_fromPath = $fromPath . $key;
	$_toPath = $toPath . $key;
	if($lang == 'en_us') {
		$path = $_fromPath . '/en_us.php';
		$titleKey = $key . '_';//en_
	} else {
		$path = $_fromPath . '/zh_tw.php';
		$titleKey = $key . '_';//zh_
	}

	if(file_exists($path)) {
		include($path);
		$readconfig = $J7CONFIG[$key];
		foreach($readconfig as $configId => $valInfo) {
			$title = $titleKey . $configId;
			$setVal = array(
					$configId => $valInfo
				);
			/*$tmpData = "<?php\r\n " . "\$J7CONFIG['" . $title . "'] = " . preg_replace("/\n/", PHP_EOL,var_export($setVal, true)) . ';';*/
			$tmpData = "<?php\r\n " . "\$J7CONFIG['" . $title . "'] = " . var_export($setVal, true) . ';';
			file_put_contents($_toPath . '/' . $title . '.php', $tmpData);
			echo $title . " is OK!\r\n";
		} 
	}
	
	return $errorLog;
}

function cuttingWaves($key, $lang) {
	global $fromPath, $toPath;
	$_fromPath = $fromPath . 'tollgates';
	$_fromPath2 = $fromPath . 'waves';
	$_fromPath3 = $fromPath . 'subWave';
//	$_fromPath4 = $fromPath . 'christmasToll';//add 新加
	$_toPath = $toPath . 'wavesInfo';

	if($lang == 'en_us') {
		$path = $_fromPath . '/en_us.php';
		$path2 = $_fromPath2 . '/en_us.php';
		$path3 = $_fromPath3 . '/en_us.php';
//		$path4 = $_fromPath4 . '/en_us.php';
	} else {
		$path = $_fromPath . '/zh_tw.php';
		$path2 = $_fromPath2 . '/zh_tw.php';
		$path3 = $_fromPath3 . '/zh_tw.php';
//		$path4 = $_fromPath4 . '/zh_tw.php';
	}

	$errorLog = array();
	if(file_exists($path) && is_readable($path)) {
		include($path);
		include($path2);
		include($path3);
//		include($path4);
		$readconfig = $J7CONFIG[$key];
		foreach($readconfig as $configId => $tollInfo) {
			$setVal = array($configId => array());
			//查看tollgate的子波次
			if($tollInfo['wave']) {
				$waves = explode(',', $tollInfo['wave']);
				foreach ($waves as $subwaveId) {
					if(!$subwaveId || !$configId || !isset($J7CONFIG['waves'][$subwaveId])) {
						$errorLog[] = "key:{$key}, configId:{$configId}, subwaveId:{$subwaveId}" . PHP_EOL;	
					} else {
						$setVal[$configId][$subwaveId] = $J7CONFIG['waves'][$subwaveId];					
						//查看wave的子波次	
						if($J7CONFIG['waves'][$subwaveId]['subWaves']) {
							$waves2 = explode(',', $J7CONFIG['waves'][$subwaveId]['subWaves']);
							foreach ($waves2 as $subwaveId2) {
								$setVal[$configId][$subwaveId]['waveInfo'][$subwaveId2] = $J7CONFIG['subWave'][$subwaveId2];
							}
							
							unset($setVal[$configId][$subwaveId]['subWaves']);
						}
					}
				}
			}

			/*$tmpData = "<?php\r\n " . "\$J7CONFIG['wave_" . $configId . "'] = " . preg_replace("/\n/", PHP_EOL,var_export($setVal, true)) . ';';*/
			$tmpData = "<?php\r\n " . "\$J7CONFIG['wave_" . $configId . "'] = " . var_export($setVal, true) . ';';
			file_put_contents($_toPath . '/wave_' . $configId . '.php', $tmpData);
			echo $configId . " is OK!\r\n";
		}
	}

	return $errorLog;
}

//开始执行
fileCutting($fileConfig);