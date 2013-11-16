<?
session_start();

include("inc/config.php");
include("inc/tvswebsys.class.php");
include("inc/unifi.class.php");
include("inc/Mobile_Detect.php");


$unifiman	=	new UNIFI_CONTROL($unifiurl,$unifiuser,$unifipass,$maxusertime);
$tvswebsys	=	new TVSWEB_CONTROL($dbhost,$dbuser,$dbpass,$dbdb);
$detect		=	new Mobile_Detect();


if(isset($_GET["id"]))
	$_SESSION["macaddress"]	=	$_GET["id"];

if(isset($_GET["url"]))
	$_SESSION["url"]		=	$_GET["url"];

if(isset($_GET["ssid"]))
	$_SESSION["ssid"]		=	$_GET["ssid"];

if($detect->isMobile())	{
	$pagetpl	=	TVSWEB_CONTROL::LoadTemplate("mobile");
	$prefix		=	"mobile_";
}else if($detect->isTablet())	{
	$pagetpl	=	$tabletismobile?TVSWEB_CONTROL::LoadTemplate("mobile"):TVSWEB_CONTROL::LoadTemplate("tablet");
	$prefix		=	$tabletismobile?"mobile_":"tablet_";
}else{
	$pagetpl	=	$pcismobile?TVSWEB_CONTROL::LoadTemplate("mobile"):TVSWEB_CONTROL::LoadTemplate("desktop");
	$prefix		=	$pcismobile?"mobile_":"";
}
/*	Deteção de tipo de cliente */
/*	Tipo do Dispositivo
0 - Desktop 
1 - Android Phone 
2 - Android Tablet 
3 - iOS Phone 
4 - iOS Tablet 
5 - BlackBerry OS 
6 - Palm OS 
7 - Windows Phone/Mobile 
8 - Other
*/
if($detect->isMobile() || $detect->isTablet())	{
	if($detect->isAndroidOS())	
		$devtype = $detect->isTablet()?2:1;
	else if ($detect->isiOS())	
		$devtype = $detect->isTablet()?4:3;
	else if ($detect->isBlackBerryOS())
		$devtype = 5;
	else if ($detect->isPalmOS())
		$devtype = 6;
	else if ($detect->isWindowsMobileOS() || $detect->isWindowsPhoneOS())
		$devtype = 7;
	else
		$devtype = 8;
}else
	$devtype	= 0;

if(!empty($_REQUEST["nickname"]))
	$device_nickname = $_REQUEST["nickname"];
else{
	$d = $tvswebsys->GetDeviceNick(str_ireplace(":","",$_SESSION["macaddress"]));
	if(!empty($d))	
		$device_nickname = $d;
	else{
		switch($devtype)	{
			case 0: $device_nickname = "Meu Computador"; break;
			case 1: $device_nickname = "Meu Android"; break;
			case 2: $device_nickname = "Meu Android"; break;
			case 3: $device_nickname = "Meu iPhone"; break;
			case 4: $device_nickname = "Meu iPad"; break;
			case 5: $device_nickname = "Meu BlackBerry"; break;
			case 6: $device_nickname = "Meu Palm"; break;
			default: $device_nickname = "";
		}
	}
}

if(isset($_REQUEST["action"]))	{
	switch($_REQUEST["action"])	{
		case "login":
			$user		=		$_POST["username"];
			$pass		=		$_POST["password"];
			$nick		=		$_POST["nickname"];
			$mac		=		$_POST["macaddress"];
			if($tvswebsys->CheckUser($user,$pass))	{
				$data		=	$tvswebsys->GetUserData($user);
				$devdata	=	$tvswebsys->GetDevice($user,$mac);
				$logtime	=	time();	
				if($devdata == null)	{
					$dev		=	$tvswebsys->AddDevice($data["id"], $nick, str_ireplace(":","",$mac), $devtype);	
					error_log($dev);			
					$tvswebsys->AddLog($dev, $logtime, $_SERVER["HTTP_USER_AGENT"], ($logtime + $maxusertime*60));
				}else{
					$tvswebsys->UpdateDevice($devdata["users_id"],$mac,$nick);
					$tvswebsys->AddLog($dev["did"], $logtime, $_SERVER["HTTP_USER_AGENT"], ($logtime + $maxusertime*60));	
				}
				$unifiman->Login();				
				$unifiman->AuthorizeClient($mac);
				$unifiman->Logout();
				//sleep(1);
				//header("Location: ".$defaultredir);
			}else
				$loginmsg	=	"<font color=\"RED\"><B>Erro de autentica&ccedil;&atilde;o:</B> Usu&aacute;rio ou Senha inv&aacute;lidos!</font>";
		break;
		case "register":
			$user		=	$_POST["username"];
			$pass		=	$_POST["password"];
			$pass2		=	$_POST["password2"];
			$name		=	$_POST["name"];
			$email		=	strtolower($_POST["email"]);
			$receive	=	$_POST["newsletter"]=="on";
			$nick		=	$_POST["nickname"];
			$mac		=	$_POST["macaddress"];
			if(!empty($user) && !empty($pass) && !empty($email) && !empty($name))	{
				if($pass != $pass2)
					$registermsg	=	"<font color=\"RED\"><B>Erro ao registrar:</B> As senhas n&atilde;o s&atilde;o iguais!</font>";
				else{
					if($tvswebsys->AddUser($user,$pass,$name,$email,$receive,0))	{
						$data		=	$tvswebsys->GetUserData($user);
						$devdata	=	$tvswebsys->GetDevice($user,$mac);
						$logtime	=	time();
						if($devdata == null)	{
							$dev		=	$tvswebsys->AddDevice($data["id"], $nick, str_ireplace(":","",$mac), $devtype);				
							$tvswebsys->AddLog($dev, $logtime, $_SERVER["HTTP_USER_AGENT"], ($logtime + $maxusertime*60));
						}else{
							$tvswebsys->UpdateDevice($devdata["users_id"],$mac,$nick);
							$tvswebsys->AddLog($dev["did"], $logtime, $_SERVER["HTTP_USER_AGENT"], ($logtime + $maxusertime*60));	
						}
						$unifiman->Login();				
						$unifiman->AuthorizeClient($mac);
						$unifiman->Logout();
						sleep(1);
						header("Location: ".$defaultredir);
						$registermsg	=	"<font color=\"GREEN\"><B>Voc&ecirc; foi registrado e seu login efetuado. Voc&ecirc; pode usar a internet agora.</B></font>";
					}else
						$registermsg	=	"<font color=\"RED\"><B>Erro ao registrar:</B> Um usu&aacute;rio </i>$user</i> j&aacute; foi registrado!</font>";
				}
			}else
				$registermsg	=	"<font color=\"RED\"><B>Erro ao registrar:</B> Preencha todos os campos!</font>";
	}
}



$replacetags	=	array(	
	"TITLE"			=>	$title,
	"COMPANY"		=>	$company,
	"MAC"			=>	$_SESSION["macaddress"],
	"REDIRURL"		=>	$_SESSION["url"],
	"SSID"			=>	$_SESSION["ssid"],
	"MSG"			=>	"",
	"USERNAME"		=>	$_REQUEST["username"],
	"DEVICE_NICK"	=>	$device_nickname,
	"NAME"			=>	$_REQUEST["name"],
	"EMAIL"			=>	$_REQUEST["email"],
	"LOGO"			=>	$logourl,
	"DEBUG"			=>  ""
);

$pagecont = $_REQUEST["m"];

switch($pagecont)	{
	case "login":
		$logintpl				=	TVSWEB_CONTROL::LoadTemplate($prefix."login");
		$page 					=	str_ireplace("{CONTENT}",$logintpl, $pagetpl);
		$replacetags["MSG"] 	=	$loginmsg;
		break;
	/*case "mydevices":
		$mydevicestpl	=	TVSWEB_CONTROL::LoadTemplate($prefix."mydevices");
		$page 			=	str_ireplace("{CONTENT}",$mydevicestpl, $pagetpl);
		//TODO
	*/
		break;
	case "register":
		$registertpl	=	TVSWEB_CONTROL::LoadTemplate($prefix."register");
		$page 			=	str_ireplace("{CONTENT}",$registertpl, $pagetpl);
		$replacetags["MSG"] 	=	$registermsg;
		break;
	default:
		header("Location: /guest/?m=login");
}
if($debug)
	$replacetags["DEBUG"] = "REQUEST: ".print_r($_REQUEST, true)."<BR>SESSION: ".print_r($_SESSION, true); 
else
	$replacetags["DEBUG"] = "";

foreach($replacetags as $tag => $val)	
	$page	=	str_ireplace("{".$tag."}",$val,$page);

echo $page;

