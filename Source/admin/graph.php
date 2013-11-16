<?
session_start();

include("../inc/config.php");
include("../inc/tvswebsys.class.php");
include('../jpgraph/jpgraph.php');
include('../jpgraph/jpgraph_line.php');



if($_SESSION["logged"] == True)	{
	$tvswebsys	=	new TVSWEB_CONTROL($dbhost,$dbuser,$dbpass,$dbdb);
	$year	=	(int)($_REQUEST["year"]);
	$month	=	(int)($_REQUEST["month"]);
	if ( $year >= 2000 and $year <= 2200 and $month > 0 and $month < 13)	{
		switch($_REQUEST["type"])	{
			case "users":
				$data = $tvswebsys->BuildUserMonthReportData($year,$month);
				break;	
			case "devices":
				$data = $tvswebsys->BuildDeviceMonthReportData($year,$month);
				break;	
				
			case "connections":
				$data = $tvswebsys->BuildConnectionMonthReportData($year,$month);
				break;	
			default:
				exit();
		}
		$graph = $tvswebsys->BuildGraph($data["title"], $data);
	}	
}
