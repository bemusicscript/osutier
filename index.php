<?php

require("init.php");
if(APP_NAME === "APP_NAME") die();

?>
<!doctype html>
<html>
<head>
	<link rel="shortcut icon" href="//s.ppy.sh/favicon.ico" type="image/vnd.microsoft.icon"/>
	<link rel="icon" href="//s.ppy.sh/favicon.ico" type="image/vnd.microsoft.icon"/>
	<title>osu!tier r180103</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Exo+2:400,300">
	<link rel="stylesheet" href="./static/static.css">
	<meta name="keywords" content="osu, tier, rank">
	<meta name="description" content="Check your osu! account tier.">
	<meta property="og:url" content="https://osu.stypr.com/tier/" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="osu!tier" />
	<meta property="og:description" content="Check your osu! account tier." />
	<meta property="og:image" content="https://osu.stypr.com/tier/images/challenger_I.png" />
	<meta name="author" content="stypr">
	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<link rel="canonical" href="<?php echo $_SERVER['REQUEST_URI']; ?>">
</head>
<body>
	<header class="masthead">
		<div class="container centered">
			<a style="float:left;" href="//osu.stypr.com/tier/" class="masthead-logo">
				<span class="mega-octicon centered">osu!tier</span>
			</a>
		</div>
	</header>
	<div class="container" id="search_tab">
		<div class="columns">
			<div class="row column centered">
				<hr>
				<center><form onsubmit="return search()">
					<input class="form-control" required placeholder="Username (eg. Cookiezi, Fast)" id="id" name="id" <?php if($_GET['id']){?>value="<?php echo htmlspecialchars($_GET['id']); ?>"<?php }?>>
					<select class="form-select" required name="type">
						<option value=0<?php if(!$_GET['type']){ ?> selected<?php }?>>osu!</option>
						<option value=1<?php if($_GET['type']==1||$_GET['type']=="taiko"){?> selected<?php }?>>osu!taiko</option>
						<option value=2<?php if($_GET['type']==2||$_GET['type']=="catch"){?> selected<?php }?>>osu!ctb</option>
						<option value=3<?php if($_GET['type']==3||$_GET['type']=="mania"){?> selected<?php }?>>osu!mania</option>
					</select>
					<button class="btn btn-danger" type="submit" id="submit">&#8981;</button>
				</form></center>
			</div>
		</div>
	</div>
	<hr>
	<div class="container">
		<div class="row column centered result" id="result">
			<center><div id="tier_stat"></div>
			<div class="share_link" style="display:none;">
				<br>
				<table><tr><td style="padding-bottom:5pt;"><font size=1>Share Link: <a href="#" id="permalink"></a></font></td><td id="twitter_button"></td></tr></table>
			</div>
		</div>
	</div>
	<hr>
	<div class="container">
		<center>
			<p class="foot">
				This service is <b>purely experiemental</b>. Made for fun purposes!<br>
				Performance Points are calculated and tier is given based on the <a href="http://www.op.gg/statistics/tier/">LOL KR Graph</a>.
				<br><br><br>
				Made by <a href="//osu.ppy.sh/u/Fast">Fast</a>, Inspired by <a href="//twitter.com/bmsplayer">xert*</a>.<br><a href="//twitter.com/stereotype32">Tweet</a> me or <a href="https://osu.ppy.sh/forum/p/5372608">Post</a> for improvement requests.
				<br><br>
				<b>The calculation is extremely slow as of now.<br>
				It might take a long time on the first calculation.</b>
				<br><br><br>
				<h5><a href="https://harold.kim/donate/">Donate!</a></h5>
            </p>
        </center>
    </div>
    <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
    <script src="./static/js/jquery.js"></script>
<script>
function search() {
    var type = encodeURIComponent(document.forms[0].type.value);
    var username = encodeURIComponent(document.forms[0].id.value);
    var b64 = false;
    if(encodeURIComponent(document.forms[0].id.value).includes("%20")) {
        username = btoa(username);
        while(!username.endsWith("==")){
          username += "=";
        }
        b64 = true;
    }
    switch (type) {
        case "1":
        case "taiko":
            type = "taiko";
            d_type = "osu!taiko";
            break;
        case "2":
        case "catch":
            type = "catch";
            d_type = "osu!catch";
            break;
        case "3":
        case "mania":
            type = "mania";
            d_type = "osu!mania";
            break;
        default:
            type = "osu!";
            d_type = "osu!std";
            break
    }
    $(".share_link").hide();
    $("#tier_stat").html("<img src='./rank.php?id=" + username + "&mode=" + type + "'>");
    $("#result").show();
    $("#permalink").attr("href", "#");
    $("#permalink").text("https://osu.stypr.com/tier/?id=" + decodeURIComponent(username) + "&type=" + type);
    document.getElementById("permalink").addEventListener("click", function() {
        copyTextToClipboard($("#permalink").text());
        alert("Copied to clipboard!")
    }, false);
    $("#twitter_button").html('&nbsp;&nbsp;&nbsp; <a href="https://twitter.com/share?text=' + encodeURIComponent('Check out my tier in osu!') + '&url=https://osu.stypr.com/tier/%3fid=' + username + '%26type=' + type + '" id="twitter_share" class="twitter-share-button" data-show-count="false">Tweet</a>');
    twttr.widgets.load();
    $(".share_link").show();
    $("#result").show();
    return false
}

function copyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.style.position = 'fixed';
    textArea.style.top = 0;
    textArea.style.left = 0;
    textArea.style.width = '2em';
    textArea.style.height = '2em';
    textArea.style.padding = 0;
    textArea.style.border = 'none';
    textArea.style.outline = 'none';
    textArea.style.boxShadow = 'none';
    textArea.style.background = 'transparent';
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    try {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful'
    } catch (err) {}
    document.body.removeChild(textArea)
}
</script>

<!--	<script>eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(c/a))+String.fromCharCode(c%a+161)};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\[\xa1-\xff]+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp(e(c),'g'),k[c])}}return p}('» Ú(){¨ ¢=º(¥.½[0].¢.´);¨ ¬=º(¥.½[0].®.´);Ù(¢){¦"1":¦"¹":¢="¹";©="¤!¹";¯;¦"2":¦"«":¢="«";©="¤!«";¯;¦"3":¦"·":¢="·";©="¤!·";¯;×:¢="¤!";©="¤!Ý";¯}$(".¾").ã();$("#á").¿("<à Þ=\'./ß.Õ?®="+¬+"&Ò="+¢+"\'>");$("#¼").ª();$("#­").Ê("Á","#");$("#­").§("µ://¤.À.²/³/?®="+Ì(¬)+"&¢="+¢);¥.É("­").Ç("È",»(){Å($("#­").§());Ô("Í Ó Ñ!")},±);$("#Ð").¿(\'&¸;&¸;&¸; <a Á="µ://Ã.²/Â?§=\'+º(\'å ú û ³ ù ¤!\')+\'&ø=µ://¤.À.²/³/%¢¢=\'+¬+\'%¢¡=\'+¢+\'" ®="õ" ô="Ã-Â-é" è-ª-æ="±">ç</a>\');ì.í.ò();$(".¾").ª();$("#¼").ª();î ±}» Å(§){¨ ¡=¥.ï("ð");¡.£.ñ=\'ó\';¡.£.ë=0;¡.£.ê=0;¡.£.ÿ=\'Ä\';¡.£.þ=\'Ä\';¡.£.¢£=0;¡.£.ý=\'°\';¡.£.ü=\'°\';¡.£.÷=\'°\';¡.£.ö=\'¢¤\';¡.´=§;¥.Æ.ä(¡);¡.Ï();Î{¨ ¶=¥.Ë(\'â\');¨ Ü=¶?\'¶\':\'Ö\'}«(Ø){}¥.Æ.Û(¡)}',95,99,'textArea|type|style|osu|document|case|text|var|d_type|show|catch|username|permalink|id|break|none|false|com|tier|value|https|successful|mania|nbsp|taiko|encodeURIComponent|function|result|forms|share_link|html|stypr|href|share|twitter|2em|copyTextToClipboard|body|addEventListener|click|getElementById|attr|execCommand|decodeURIComponent|Copied|try|select|twitter_button|clipboard|mode|to|alert|php|unsuccessful|default|err|switch|search|removeChild|msg|std|src|rank|img|tier_stat|copy|hide|appendChild|Check|count|Tweet|data|button|left|top|twttr|widgets|return|createElement|textarea|position|load|fixed|class|twitter_share|background|boxShadow|url|in|out|my|outline|border|height|width|26type|3fid|padding|transparent'.split('|'),0,{}));<?php if(isset($_GET['id']) && isset($_GET['type'])){?>search();$("#search_tab").hide();<?php } ?></script>
--></body>
</html>
