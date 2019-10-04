<?php
define('IMAGEFOLDER', "/files/");
define('IMAGEPATH', __DIR__ . IMAGEFOLDER);
define('APPURL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . '://'. $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . IMAGEFOLDER);

# ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- 

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

	if(!is_dir(IMAGEPATH)){ mkdir(IMAGEPATH, 0755);}

	createThumb($fName);

	echo "[\"". $fName ."\"]";
	exit;
}

# ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- 

function createThumb($fName) {
	if(!is_dir(IMAGEPATH . "thumbs")){ mkdir(IMAGEPATH . "thumbs", 0755);}
	
	# Create Thumb
	$fPath = IMAGEPATH . $fName;
	$fInfo = getimagesize($fPath);
	switch ($fInfo['mime']) {
		CASE "image/jpeg": $im = imagecreatefromjpeg($fPath); break;
		CASE "image/png": $im = imagecreatefrompng($fPath); break;
		CASE "image/gif": $im = imagecreatefromgif($fPath); break;
		DEFAULT: die('Original Not Supported');
	}

	$width = $fInfo[0];
	$height = $fInfo[1];

	$heightTo = 120;
	$ratio = $height/$width;
	$newwidth = $heightTo/$ratio;
	$newheight = $heightTo;

	$thumb = imagecreatetruecolor($newwidth, $newheight);
	//imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	imagecopyresampled($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	switch ($fInfo['mime']) {
		CASE "image/jpeg": imagejpeg($thumb, IMAGEPATH ."thumbs/". $fName); break;
		CASE "image/png": imagepng($thumb, IMAGEPATH ."thumbs/". $fName); break;
		CASE "image/gif": imagegif($thumb, IMAGEPATH ."thumbs/". $fName); break;
		DEFAULT: die('Converted Not Supported');
	}

	imagedestroy($im);
}

# ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- 

if (isset($_GET['mode']) && $_GET['mode'] == "bThumb") {
	echo "<pre>";
	echo "Build Thumbnail: Start\n"; flush(); ob_flush();

	foreach(glob(IMAGEPATH.'*.{jpg,png,gif}', GLOB_BRACE) as $filename){
		$fName = basename($filename);
		echo "Build Thumbnail: ". $fName; flush(); ob_flush();
		createThumb($fName);
		echo ": Complete\n"; flush(); ob_flush();
	}

	die('Build Thumbnail: Done');
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
	foreach(glob(IMAGEPATH.'*.{jpg,png,gif}', GLOB_BRACE) as $filename){
		$results[] = basename($filename);
	}
	header('Content-Type: application/json');
	rsort($results);
	echo json_encode($results);
	exit;
}