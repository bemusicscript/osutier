<?php

define(APP_NAME, "osu!tier");
define(APP_URL, "https://osu.stypr.com/tier/");
define(APP_KEY, ""): // OSU API KEY

if($_SERVER['HTTP_HOST'] !== "gaming.harold.kim") {
	header("Location: https://gaming.harold.kim/tier/");
	exit;
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);

    return $length === 0 ||
    (substr($haystack, -$length) === $needle);
}

/* https://github.com/ppy/osu-api/wiki */
function get_user_lower($username){
	// ignore case sensitive API calls.
	$cache = "./cache/username_list";
	foreach(file($cache) as $line) {
		$_line = @explode("||", $line);
		if($_line[0] == $username){
            if($_list[1] == ""){
                 return trim($username);
            }
			return trim($_line[1]);
		}
	}

	$d = fopen($cache, "a+");
	$f = curl_init("https://osu.ppy.sh/u/" . $username);
	curl_setopt($f, CURLOPT_POST, true);
	curl_setopt($f, CURLOPT_POSTFIELDS, http_build_query($p));
	curl_setopt($f, CURLOPT_RETURNTRANSFER, true);
	$r = curl_exec($f);
	$u = explode("'s", explode("<title>",$r)[1])[0];
	if((strtolower($u) === $username)||
	(strpos($u, "User not found!") === false && strpos($u, "CloudFlare") === false && strpos($u, "Request Not") === false) && $u != ""){
		fwrite($d, "$username||$u\n");
		fclose($d);
		return $u;
	}else{
		fwrite($d, "$username||$username\n");
		return $username;
	}
}

/* notused#
function generate_filename($username, $type=0){
	return "./cache/" . md5($username . $type . date("Y-m-d")) . ".png";
} */

// 0 = osu, 1 = taiko, 2 = ctb, 3 = mania
function get_user($username, $type=0){
    // osu.ppy.sh/api/get_user
    $is_cache = false;
    $cache = "./cache/username_list_api_" . date("Y-m-d");
    foreach(file($cache) as $line) {
        $_line = @explode("||", $line);
        if($_line[0] == $username && $_line[1] == $type){
            $r = trim($_line[2]);
            $is_cache = true;
            goto decode_routine;
        }
    }

    $p = array('k' => APP_KEY, 'u' => $username, 'm' => $type, 'type' => 'string');
    $f = curl_init("https://osu.ppy.sh/api/get_user");

    curl_setopt($f, CURLOPT_POST, true);
    curl_setopt($f, CURLOPT_POSTFIELDS, http_build_query($p));
    curl_setopt($f, CURLOPT_RETURNTRANSFER, true);

    $r = curl_exec($f);
    if(!$is_cache){
         $_f = fopen($cache, "a+");
         fwrite($_f, "$username||$type||$r\n");
         fclose($_f);
    }
decode_routine:
    $r = json_decode($r);

    if($r[0]->pp_rank && strtolower($r[0]->username) === strtolower($username)){
        $k = $r[0];
    }else{
        $k = -1;
    }
    curl_close($f);
    return $k;
}

// http://www.op.gg/statistics/tier/
function get_tier($username, $type=2){
	// get user and check if exists
	$base = get_user($username, $type);
	$base_accuracy = sprintf("%.2f", $base->accuracy);
	$base_country = $base->country;
	$base_playcount = $base->playcount;
	$base_pp = $base->pp_raw;
	$base = $base->pp_rank;

	if(!$base) return Array(0, "unranked");
	if($base_playcount <= 50) return Array(0, "low_playcount");
	// get user base -- from peppy's pp rank

	// 170813 fix.
	// 0 = osu, 1 = taiko, 2 = ctb, 3 = mania
	// $peppy is the lowermost rank available in the user rank.
	switch($type){
		case 3:
			$peppy = (get_user('dekobokonnbi', 3)->pp_rank);
		case 2:
			$peppy = (get_user('Rchetype', 2)->pp_rank);
		case 1:
			$peppy = (get_user('Guilty night', 1)->pp_rank);
		case 0:
			$peppy = (get_user('wolfsword11041', 0)->pp_rank);
		default:
			$peppy = (get_user('peppy', $type)->pp_rank);
	}
	// peppy is not accurate; 8pp is still to big.
	// https://osu.ppy.sh/u/wolfsword11041, 0pp std
	// https://osu.ppy.sh/u/rchetype#_general, 0pp ctb
    //if($_SERVER['REMOTE_ADDR'] === "45.32.26.197"){ die($peppy); }
	$tier = ($base / (float)$peppy) * (float)100;
	$tier = sprintf("%.f",$tier);
	if($tier >= 100 || !$tier){ return Array(0, "unranked"); }else{ return Array($base_pp, $tier, $base, $base_accuracy, strtolower($base_country)); }
}

function parse_tier($result){
	if($result == 0) return "Unranked";
	if($result <= 0.01) return "Challenger I";
    if($result <= 0.03) return "Grandmaster I";
	if($result <= 0.09) return "Master I";
	if($result <= 0.26) return "Diamond I";
	if($result <= 0.60) return "Diamond II";
	if($result <= 1.37) return "Diamond III";
	if($result <= 3.80) return "Diamond IV";
	if($result <= 5.17) return "Platinum I";
	if($result <= 7.07) return "Platinum II";
	if($result <= 9.63) return "Platinum III";
	if($result <= 15.7) return "Platinum IV";
	if($result <= 19.7) return "Gold I";
	if($result <= 26.1) return "Gold II";
	if($result <= 33) return "Gold III";
	if($result <= 45) return "Gold IV";
	if($result <= 53) return "Silver I";
	if($result <= 62) return "Silver II";
	if($result <= 71) return "Silver III";
	if($result <= 80) return "Silver IV";
	if($result <= 86) return "Bronze I";
    if($result <= 91) return "Bronze II";
	if($result <= 95) return "Bronze III";
	if($result <= 98) return "Bronze IV";
	if($result <= 99) return "Iron I";
	return "Iron IV";
}


// Packer for JavaScript

?>
