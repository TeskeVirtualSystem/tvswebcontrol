<?

include("Statement_Result.class.php");

class TVSWEB_CONTROL	{

	//	Queries de adicionar
	private $q_adduser			=	"INSERT INTO users(username, password, name, email, receive_news, level) VALUES(?,UNHEX(SHA1(?)), ?, ?, ?, ?)";
	private $q_adddevice		=	"INSERT INTO devices(users_id, device_name, macaddress, createtime, type) VALUES(?, ?, ?, NOW(), ?)";
	private $q_addconnlog		=	"INSERT INTO connection_log(devices_id, timestamp, useragent, conn_max_date) VALUES(?,?,?,?)";

	//	Update Queries		
	private $q_updatedevice		=	"UPDATE devices SET device_name = ? WHERE users_id = ? and macaddress = ?";

	//	Queries de seleção
	private $q_checkuser		=	"SELECT username,password FROM users WHERE username = ? and password = UNHEX(SHA1(?))";
	private $q_getuser			=	"SELECT id,username,email,receive_news,level FROM users WHERE username = ?";
	private $q_getdevice		=	"SELECT * FROM devices WHERE users_id = ? AND macaddress = ? LIMIT 1";
	private $q_getdevices		=	"SELECT users.id, users.username, devices.* FROM users INNER JOIN devices ON devices.users_id = users.id WHERE users.username = ?";
	private $q_getlogs			=	"SELECT * FROM connection_log WHERE devices_id = ? ";
	private $q_getdevnick		=	"SELECT * FROM devices WHERE macaddress = ? ORDER BY id DESC LIMIT 1";
	private $q_getdevicebymac	=	"SELECT users.id, users.username, devices.*, devices.id as did FROM users INNER JOIN devices ON devices.users_id = users.id WHERE users.username = ? and devices.macaddress = ? LIMIT 1";
 
	private $q_repdevices		=	"SELECT * FROM `devices` WHERE createtime >= ? and createtime < ?";
	private $q_repusers			=	"SELECT * FROM `users` WHERE createtime >= ? and createtime < ?";
	private $q_repconn			=	"SELECT * FROM `connection_log` WHERE timestamp >= ? and timestamp < ?";

	private $q_getusers			=	"SELECT * FROM `users`";

	private $conn				= 	false;

	private $monthname			=	array("Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro");

	/*	Função construtora da classe	*/
	function __construct($host,$user,$pass,$db){
		$this->conn = new mysqli($host,$user,$pass,$db);
	}

	/*	Funções estáticas */
	static function DateTimeToMySQL($timestamp)	{
		return date("Y-m-d H:i:s", $timestamp);
	}
	static function MySQLToDateTime($mysqldatetime)	{
		return strtotime($mysqldatetime);
	}
	static function LoadTemplate($file)	{
		if(filesize("tpl/$file.html") > 0)	{
			$handle = fopen("tpl/$file.html", "r");
			$contents = fread($handle, filesize("tpl/$file.html"));
			fclose($handle);
			return $contents;
		}else
			return "";
	}
	/*	Funções de adicionar	*/
	function AddUser($username,$password,$name, $email,$receive_news,$level=0)	{
		$ud = $this->GetUserData($username);
		if ( count($ud) == 0 )	{
			$cmd	=	$this->conn->stmt_init();
			$cmd->prepare($this->q_adduser);
			$receive_news = $receive_news?1:0;
			$cmd->bind_param('ssssii', $username,$password,$name, $email,$receive_news,$level);
			$cmd->execute();
			$cmd->close();
			return true;
		}else
			return false; 
	}
	function AddDevice($userid, $device_name, $macaddress, $type=0)	{
			/*	Type:
				0 - Desktop
				1 - Android Phone
				2 - Android Tablet
				3 - iOS Phone
				4 - iOS Tablet
			*/
			$cmd	=	$this->conn->stmt_init();
			$cmd->prepare($this->q_adddevice);
			$cmd->bind_param('issi', $userid,$device_name,$macaddress,$type);
			$cmd->execute();
			$cmd->close();


			$cmd	=	$this->conn->stmt_init();
			$cmd->prepare($this->q_getdevice);
			$cmd->bind_param('is', $userid, $macaddress);
			$cmd->execute();
			$cmd->store_result();
			$result 	= 	new Statement_Result($cmd);
			if($cmd->num_rows() > 0)	{
				$cmd->fetch();
				$res	=	$result->Get_Array();
				$cmd->close();
				return $res["id"];
			}else{
				$cmd->close();
				return null;
			}	
	}
	function AddLog($device_id, $timestamp, $useragent, $conn_max_date)	{
			$timestamp 		= TVSWEB_CONTROL::DateTimeToMySQL($timestamp);
			$conn_max_date 	= TVSWEB_CONTROL::DateTimeToMySQL($conn_max_date);
			$cmd	=	$this->conn->stmt_init();
			$cmd->prepare($this->q_addconnlog);
			$cmd->bind_param('isss', $device_id, $timestamp, $useragent, $conn_max_date);
			$cmd->execute();
			$cmd->close();
			return true;
	}

	/*	Funções de seleção	*/
	function CheckUser($username,$password)	{
		if( empty($username) || empty($password))
			return false;
		else{
			$cmd	=	$this->conn->stmt_init();
			$cmd->prepare($this->q_checkuser);
			$cmd->bind_param('ss', $username,$password);
			$cmd->execute();
			$cmd->store_result();
			$ret	=	$cmd->num_rows() > 0;
			$cmd->close();
			return $ret;
		}	
	}
	function GetUserData($username)	{
		$cmd	=	$this->conn->stmt_init();
		$cmd->prepare($this->q_getuser);
		$cmd->bind_param('s', $username);
		$cmd->execute();
		$cmd->store_result();
		$result 	= 	new Statement_Result($cmd);
		if($cmd->num_rows() > 0)	{
			$cmd->fetch();
			$res	=	$result->Get_Array();
			$cmd->close();
			return $res;
		}else{
			$cmd->close();
			return array();
		}	
	}
	function GetDevices($username)	{
		$cmd	=	$this->conn->stmt_init();
		$cmd->prepare($this->q_getdevices);
		$cmd->bind_param('s', $username);
		$cmd->execute();
		$cmd->store_result();
		$result 	= 	new Statement_Result($cmd);
		if($cmd->num_rows() > 0)	{
			$cmd->fetch();
			$res	=	$result->Get_Array();
			$cmd->close();
			return $res;
		}else{
			$cmd->close();
			return array();
		}	
	}
	function GetDeviceNick($macaddress)	{
		if(empty($macaddress))
			return "";
		else{
			$cmd	=	$this->conn->stmt_init();
			$cmd->prepare($this->q_getdevnick);
			$cmd->bind_param('s', $macaddress);
			$cmd->execute();
			$cmd->store_result();
			$result 	= 	new Statement_Result($cmd);
			if($cmd->num_rows() > 0)	{
				$cmd->fetch();
				$res	=	$result->Get_Array();
				$cmd->close();
				return $res["device_name"];
			}else{
				$cmd->close();
				return "";
			}	
		}
	}
	function GetDevice($user,$macaddress)	{
		//$q_getdevice
		if(empty($macaddress) or empty($user))
			return "";
		else{
			$cmd	=	$this->conn->stmt_init();
			$cmd->prepare($this->q_getdevicebymac);
			$cmd->bind_param('ss', $user, $macaddress);
			$cmd->execute();
			$cmd->store_result();
			$result 	= 	new Statement_Result($cmd);
			if($cmd->num_rows() > 0)	{
				$cmd->fetch();
				$res	=	$result->Get_Array();
				$cmd->close();
				return $res;
			}else{
				$cmd->close();
				return null;
			}	
		}
	}
	function GetLogs($deviceid)	{
		$cmd	=	$this->conn->stmt_init();
		$cmd->prepare($this->q_getlogs);
		$cmd->bind_param('i', $deviceid);
		$cmd->execute();
		$cmd->store_result();
		$result 	= 	new Statement_Result($cmd);
		if($cmd->num_rows() > 0)	{
			$cmd->fetch();
			$res	=	$result->Get_Array();
			$cmd->close();
			return $res;
		}else{
			$cmd->close();
			return array();
		}	
	}

	function GetReportData($data,$mindate,$maxdate)	{
		$cmd	=	$this->conn->stmt_init();
		$mindate = TVSWEB_CONTROL::DateTimeToMySQL($mindate); 
		$maxdate = TVSWEB_CONTROL::DateTimeToMySQL($maxdate);
		switch($data)	{
			case "devices":	$cmd->prepare($this->q_repdevices); break;
			case "users":	$cmd->prepare($this->q_repusers); break;
			case "conn":	$cmd->prepare($this->q_repconn); break;
			default:		return False;
		}
		$cmd->bind_param('ss', $mindate,$maxdate);
		$cmd->execute();
		$cmd->store_result();
		$result 	= 	new Statement_Result($cmd);
		$results	=	array();
		if($cmd->num_rows() > 0)	{
			while($cmd->fetch())	{
				$x = $result->Get_Array();
				$x = unserialize(serialize($x));
				if(isset($x["password"]))
					unset($x["password"]);
				if(isset($x["createtime"]))
					$x["createtime"] =  TVSWEB_CONTROL::MySQLToDateTime($x["createtime"]);
				if(isset($x["timestamp"]))
					$x["timestamp"] =  TVSWEB_CONTROL::MySQLToDateTime($x["timestamp"]);
				$results[] = $x;
			}
			$cmd->close();
			return $results;
		}else{
			$cmd->close();
			return array();
		}	
	}
	function BuildEmailList($recv=False)	{
		$cmd	=	$this->conn->stmt_init();
		$cmd->prepare($this->q_getusers);
		$cmd->execute();
		$cmd->store_result();
		$result 	= 	new Statement_Result($cmd);
		$maildata	=	"";
		if($cmd->num_rows() > 0)	{
			while($cmd->fetch())	{
				$x = $result->Get_Array();
				if( ($recv && $x["receive_news"]==1) | !$recv)	{
					$name		=	htmlentities($x["name"]);
					$email		=	$x["email"];
					$receivenews	=	$x["receive_news"]==1?"Sim":"N&atilde;o";
					$date		=	$x["createtime"];
					$username		=	htmlentities($x["username"]);
					$maildata	.=	"<li data-role=\"list-divider\">$name ($username)<span class=\"ui-li-count\">3</span></li>
								<li> Email: $email </li>
								<li> Deseja receber emails: $receivenews </li>
								<li> Data de cria&ccedil;&atilde;o da conta: $date </li>";	
				}			
			}
			$cmd->close();
			return $maildata;
		}else{
			$cmd->close();
			return "";
		}	

	
	}
	/*	Funções de atualização	*/
	function UpdateDevice($userid,$mac,$nick)	{
		$cmd	=	$this->conn->stmt_init();
		$cmd->prepare($this->q_updatedevice);
		$cmd->bind_param('sis', $nick,$userid,$mac);
		$cmd->execute();
		$cmd->close();		
	}

	/*	Funções extendidas */

	function random_color(){
		mt_srand((double)microtime()*1000000);
		$c = '';
		while(strlen($c)<6){
			$c .= sprintf("%02X", mt_rand(0, 255));
		}
		return "#".$c;
	}

	function BuildUserMonthReportData($year,$month)	{
		$startdate 		=	strtotime($year."-".$month."-01");
		$enddate			=	strtotime($year."-".($month+1)."-01");
		$maxdays			=	date("t", strtotime($startdate));
		$userreport 		=	$this->GetReportData("users",$startdate,$enddate);
		$reps			=	array();
		for($d=0;$d<$maxdays;$d++)
			$reps[$d] = 0;

		foreach($userreport as $report)	{
				$day	=	date("j", $report["createtime"]);
				$reps[$day-1] += 1; 
		}
		return array("data" => $reps, "legend" => "Usuários", "title" => "Usuários novos em ".$this->monthname[$month]);
	}

	function BuildDeviceMonthReportData($year,$month)	{
		$startdate 		=	strtotime($year."-".$month."-01");
		$enddate			=	strtotime($year."-".($month+1)."-01");
		$maxdays			=	date("t", strtotime($startdate));
		$devreport 		=	$this->GetReportData("devices",$startdate,$enddate);
		$reps			=	array();
		for($d=0;$d<$maxdays;$d++)
			$reps[$d] = 0;
		foreach($devreport as $report)	{
				$day	=	date("j", $report["createtime"]);
				$reps[$day-1] += 1; 
		}
		return array("data" => $reps, "legend" => "Dispositivos", "title" => "Dispositivos novos em ".$this->monthname[$month]);
	}

	function BuildConnectionMonthReportData($year,$month)	{
		$startdate 		=	strtotime($year."-".$month."-01");
		$enddate			=	strtotime($year."-".($month+1)."-01");
		$maxdays			=	date("t", strtotime($startdate));
		$connreport 		=	$this->GetReportData("conn",$startdate,$enddate);
		$reps			=	array();
		for($d=0;$d<$maxdays;$d++)
			$reps[$d] = 0;
		foreach($connreport as $report)	{
				$day	=	date("j", $report["timestamp"]);
				$reps[$day-1] += 1; 
		}
		return array("data" => $reps, "legend" => "Conexões", "title" => "Conexões de ".$this->monthname[$month]);
	}
	

	function BuildGraph($title, $plotdata, $graphbg = "", $width=640, $height=480)	{
		$graph = new Graph($width,$height);
		$graph->SetScale("textlin");
	
		$theme_class=new UniversalTheme;
	
		$graph->SetTheme($theme_class);
	
		$graph->title->Set($title);
		$graph->SetBox(false);
		
		$graph->yaxis->HideZeroLabel();
		$graph->yaxis->HideLine(false);
		$graph->yaxis->HideTicks(false,false);
		$graph->ygrid->SetFill(false);
		$graph->SetMarginColor('#F1F1F1'); 
		$graph->SetFrame(true,'#F1F1F1',1);
		if(!empty($graphbg))
			$graph->SetBackgroundImage($graphbg,BGIMG_FILLFRAME);
	
		$graph->xgrid->Show();
		$graph->xgrid->SetLineStyle("solid");
		$graph->xgrid->SetColor('#A3A3A3');
		$p = new LinePlot($plotdata["data"]);
		$graph->add($p);
		$p->SetColor($this->random_color());
		$p->SetLegend($plotdata["legend"]);
		$graph->legend->SetFrameWeight(1);
	
		$graph->Stroke();
	}
}

