<?php
/**
 * 处理字符串
 * @param $value 传入字符串
 * @return array
 */
function stringType($value) {
    //[1,2,3,4,5,6,7,8] | [name:1;age:2]
    preg_match('/^\[(.*)\]$/', $value, $match);
    if($match) {
        if(strrpos($match[1], ',') !== false) { //这种可以替换其他格式
            $value = explode(',', $match[1]);
        } elseif(strrpos($match[1], ':') !== false) {
            $value = multiSerialToArray($match[1]);
        } else {
            $value = array();
        }
    } else {
        $value = multiSerialToArray($value);
    }

    return $value;
}

/**
 * 将字符串转换为数组
 * @param $serailStr 传入字符串
 * @param string $mainSplit 主分隔
 * @param string $subSplit 单元分隔
 * @return array
 */
function multiSerialToArray($serailStr, $mainSplit = ';', $subSplit = ':') {
    if ($serailStr && strpos($serailStr, $subSplit) !== false) {
        $arrResult = array();
        $arrRand = explode($mainSplit, $serailStr);
        foreach ($arrRand as $item) {
            if ($item) {
                $arrItem = explode($subSplit, $item);
                $arrResult[$arrItem[0]] = stringType($arrItem[1]);
            }
        }
        return $arrResult;
    } else {
        return $serailStr;
    }
}

//处理不同类型的配置表
function actFormatByType($value, $type) {
    $data = '';
    switch ($type) {
        case 1://标准a:1;b:2
            $data = stringType($value);
            break;
        case 2:
            break;

        default:
            $data = $value;
            break;
    }

    return $data;
}

//文件名
$filename = $_SERVER["argv"][1];
$filename2 = str_replace('.php', '.txt', $filename);

if($_SERVER["argv"][2] == 1) {
	$config = file_get_contents("c:\\excelConfig.json");
} elseif($_SERVER["argv"][2] == 2) {
	$config = file_get_contents("c:\\excelConfig-vikingage.json");	
}

$config = json_decode($config, true);

$keydata = file_get_contents($config['savePath'] . $filename2);
$keydata = json_decode($keydata, true);

$data = file_get_contents($config['savePath'] . $filename);
$data = json_decode($data, true);
// ksort($data);

$name = str_replace('.php', '', $filename);

//如果是普通配置文件，则替换平台
if(!in_array($name, array('gameSetconfig'))) {
    $config['configName'] = 'commonConfig';
}

$config['basePath'] = sprintf($config['basePath'], $config['configName']);
$namePath = $config['basePath'] . $name;

// echo $namePath;die;
// var_dump($data);die;

$tmp = array();
foreach($keydata as $vv){
    if(isset($data[$vv])) {
        $save = $data[$vv];
        if ($save[0]) {
            if($save[1]) {
                //特殊处理
                $tmp[$vv] = actFormatByType($save[0], $save[1]);
            } else {
                $tmp[$vv] = $save[0];
            }
        }
    }
}

unset($data);

//写入文件
$data = "<?php" . PHP_EOL . '$'. "J7CONFIG['" . $name . "'] = ".var_export($tmp, true). PHP_EOL . "?>";

if(!file_exists($namePath)){
    mkdir($namePath);
}

$lang = $config['lang'];
$moreLangDir = array('webLang.php', 'orders.php', 'newbieGuide.php', 'lang.php', 'feed.php');
if(in_array($filename, $moreLangDir)) {
    $r = file_put_contents($namePath . '\\' . $lang . '.php', $data);
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