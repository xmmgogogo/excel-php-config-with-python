<?php
$config = file_get_contents("c:\\excelConfig.json");
$config = json_decode($config, true);

//文件名
$filename = $_SERVER["argv"][1];
$filename2 = str_replace('.php', '.txt', $filename);

$data = file_get_contents('c:\\' . $filename);
$data = json_decode($data, true);
ksort($data);

$keydata = file_get_contents('c:\\' . $filename2);
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

$config['basePath'] = sprintf($config['basePath'], $config['configName']);
$namePath = $config['basePath'] . $name;

// echo $namePath;

/*$data = "<?php" . PHP_EOL . '$'. "J7CONFIG['" . $name . "'] = ".preg_replace("/\n/", PHP_EOL,var_export($data, true)). PHP_EOL . "?>";*/
$data = "<?php" . PHP_EOL . '$'. "J7CONFIG['" . $name . "'] = ".var_export($data, true). PHP_EOL . "?>";

if(!file_exists($namePath)){
	mkdir($namePath);
}

$lang = $config['lang'];
$moreLangDir = array('webLang.php', 'orders.php', 'newbieGuide.php', 'lang.php', 'feed.php');
if(in_array($filename, $moreLangDir)) {
	$r = file_put_contents($namePath . '\\' . $lang . '.php', $data);
	// copy($namePath . '\\' . $lang . '.php', $namePath . '\zh_tw.php');
} else {
	$r = file_put_contents($namePath . '\en_us.php', $data);
	copy($namePath . '\en_us.php', $namePath . '\zh_tw.php');
}

unlink('c:\\' . $filename);
unlink('c:\\' . $filename2);

if($r)
	echo "[$name] write ok!\r\n";
else
	echo "[$name] write faile!\r\n";