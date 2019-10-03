<?php
define('IMAGEFOLDER', "/files/");
define('IMAGEPATH', __DIR__ . IMAGEFOLDER);
define('APPURL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . '://'. $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . IMAGEFOLDER);

function saveImg($url, $fType) {
	$ch = curl_init($url);
	$fName = date('YmdHis') .'.'. $fType;
	$fp = fopen(IMAGEPATH . $fName, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	//echo "Success"; 
	echo "[\"". $fName ."\"]";
	exit;
}

if (isset($_POST['mode']) && $_POST['mode'] == "SAVE" && isset($_POST['url']) && trim($_POST['url']) !== "") {
	$url = $_POST['url'];
	$fHeader = get_headers($url, 1);
	if (isset($fHeader['Content-Type']) && in_array($fHeader['Content-Type'], array('image/jpeg'))) {
		saveImg($url, "jpg");
	} else if (isset($fHeader['Content-Type']) && in_array($fHeader['Content-Type'], array('image/gif'))) {
		saveImg($url, "gif");
	} else if (isset($fHeader['Content-Type']) && in_array($fHeader['Content-Type'], array('image/png'))) {
		saveImg($url, "png");
	}
	
	echo "Fail";
	exit;
} else /*if (isset($_POST['mode']) && $_POST['mode'] == "LOAD")*/ {
	$results = array();
	foreach(glob(IMAGEPATH.'*') as $filename){
		$results[] = basename($filename);
	}
	header('Content-Type: application/json');
	rsort($results);
	echo json_encode($results);
	exit;
}