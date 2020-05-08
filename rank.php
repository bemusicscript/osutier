<?php

set_time_limit(0);
error_reporting(0);
ini_set("display_errors", "off");
require("init.php");
if(APP_NAME === "APP_NAME") die();

if(isset($_GET['id']) && isset($_GET['mode'])){
	if(endsWith($_GET['id'], "==")){
		$id = @htmlspecialchars(base64_decode($_GET['id']));
	}else{
		$id = @htmlspecialchars($_GET['id']);
	}
	$id = @get_user_lower(str_replace("%20", " ", $id));

	//$id = @htmlspecialchars($_GET['id']);
	switch($_GET['mode']){
		case "mania":
		case 3:
			$mode = 3;
			break;
		case "catch":
		case 2:
			$mode = 2;
			break;
		case "taiko":
		case 1:
			$mode = 1;
			break;
		default:
			$mode = (int)$_GET['mode'];
	}
	$info = @get_tier($id, $mode);
}else{
	header("Location: ./");
	exit;
}

if(!is_array($info)) $deadend = true;
$data = Array("id" => htmlspecialchars($id),
		"pp" => $info[0],
		"tier" => parse_tier($info[1]),
		"top" => $info[1],
		"type" => (int)$mode,
		"acc" => $info[3],
		"flag" => $info[4]);

// Image text output with spacing stuff
// http://stackoverflow.com/questions/6926613/php-imagettftext-letter-spacing
function imagettftextSp($image, $size, $angle, $x, $y, $color, $font, $text, $spacing = 0){
    if ($spacing == 0) { imagettftext($image, $size, $angle, $x, $y, $color, $font, $text); }else{
    	$temp_x = $x;
    	for ($i = 0; $i < strlen($text); $i++){
    		$bbox = imagettftext($image, $size, $angle, $temp_x, $y, $color, $font, $text[$i]);
    		$temp_x += $spacing + ($bbox[2] - $bbox[0]);
    	}
	}
}

// Image header
header("Content-Type:image/png");
// Set the enviroment variable for GD
putenv("GDFONTPATH=" . realpath('.'));

/* Image baseline */
$_img = imagecreatetruecolor(400, 119); // true color
$_img_bg = imagecolorallocate($_img, 0xff, 0xff, 0xff); // background
$_img_tx = imagecolorallocate($_img, 0x00, 0x00, 0x00); // text
$_img_flag_bd = imagecolorallocate($_img, 0xcc, 0xcc, 0xcc); // flag border
$_img_bd = imagecreatefrompng("./stub/border.png"); // border
$_img_font = "./stub/font/exo.ttf";
$_img_font_bold = "./stub/font/exob.ttf";

if($deadend || $data['pp'] == 0){
	$status = "fail";
	goto generate_failed;
}else{
	$status = "pass";
	goto generate_image;
}

generate_failed:

	$_img_tier = imagecreatefrompng("./stub/tier/unranked.png");
	imagecopy($_img, $_img_bd, 0, 0, 0, 0, 420, 119); // base border
	imagecopyresized($_img, $_img_tier, 15, 10, 0, 0, 100, 100, 192, 192); // tier

	// username
	if(strlen($data['id']) <= 10) {
		imagettftextSp($_img, 24, 0, 120, 52, $_img_tx, $_img_font_bold, $data['id'], -3);
	}else{
		imagettftextSp($_img, 20, 0, 120, 52, $_img_tx, $_img_font_bold, $data['id'], -2);
	}

	// display tier info
	imagettftextSp($_img, 10, 0, 120, 75, $_img_tx, $_img_font, "Unranked.", -3);
	imagettftextSp($_img, 10, 0, 120, 89, $_img_tx, $_img_font, "This user does not exist or did not play recently.", -3);

	goto cleanup;

generate_image:

	$_img_flag = imagecreatefrompng("./stub/flag/" . strtolower($data['flag']) . ".png"); // country flag
	$_tier = explode(" ", $data['tier']);
    switch($_tier[1]){
        case "II":
            $_tier[1] = "2";
            break;
        case "III":
            $_tier[1] = "3";
            break;
        case "IV":
            $_tier[1] = "4";
            break;
        case "I":
        default:
            $_tier[1] = "1";
    }
	$_img_tier = imagecreatefrompng("./stub/tier/". strtolower($_tier[0]) . "_" . $_tier[1] . ".png"); // tier mark

	imagecopy($_img, $_img_bd, 0, 0, 0, 0, 420, 119); // base border
	imagefilledrectangle($_img, 373, 10, 389, 21, $_img_flag_bd); // flag border
	imagecopyresized($_img, $_img_flag, 374, 11, 0, 0, 15, 10, 30, 20); // flag

	// game mode
	switch($mode){
		case 3:
			imagettftext($_img, 8, 0, 320, 20, $_img_tx, $_img_font, "osu!mania");
			break;
		case 2:
			imagettftext($_img, 8, 0, 324, 20, $_img_tx, $_img_font, "osu!catch");
			break;
		case 1:
			imagettftext($_img, 8, 0, 326, 20, $_img_tx, $_img_font, "osu!taiko");
			break;
		default:
			imagettftext($_img, 8, 0, 334, 20, $_img_tx, $_img_font, "osu!std");
			break;
	}

	imagecopyresized($_img, $_img_tier, 20, 0, 05, 0, 100, 100, 250, 250); // tier
	// username
	if(strlen($data['id']) <= 10) {
		imagettftextSp($_img, 24, 0, 130, 52, $_img_tx, $_img_font_bold, $data['id'], -3);
	}else{
		imagettftextSp($_img, 20, 0, 130, 52, $_img_tx, $_img_font_bold, $data['id'], -2);
	}

	// display tier info
	imagettftextSp($_img, 10, 0, 130, 75, $_img_tx, $_img_font, $data['tier'] . ", with " . $data['pp'] . "pp.", -3);
	imagettftextSp($_img, 10, 0, 130, 89, $_img_tx, $_img_font, "Top " . $data['top'] . "% with an accuracy of ".$data['acc']."%.", -3);

	goto cleanup;

cleanup:
	// print and free() leftovers.
	imagepng($_img);
	imagedestroy($_img);

	$f = fopen("log.tmp", "a+");
	@fwrite($f, "[". date("Y-m-d h:i:s") . "] " . $_SERVER['HTTP_CF_CONNECTING_IP'] . ": " . $id . " (" . $mode . ")\n");
	fclose($f);
?>
