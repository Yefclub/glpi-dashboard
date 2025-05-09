<?php

include ("../../../../inc/includes.php");
include ("../../../../inc/config.php");

global $DB;

error_reporting(E_ERROR | E_PARSE);

$ano = date("Y");
$month = date("Y-m");
$hoje = date("Y-m-d");
$thismonth = date("Y-m-01");
$thisyear = date("Y-01-01");

$yesterday = date('Y-m-d', strtotime('-1 days'));
$lastweek = date('Y-m-d', strtotime('-7 days'));
$last15 = date('Y-m-d', strtotime('-15 days'));
$lastmonth = date('Y-m-d', strtotime('-30 days'));
$last3month = date('Y-m-d', strtotime('-90 days'));
$last6month = date('Y-m-d', strtotime('-180 days'));

$datai_m2 = date('Y-m-d', strtotime('-90 days'));
$dataf = date('Y-m-d', strtotime('-365 days'));

switch ($sel_period) {
    case 0:
        $period = '';
        $periodp = '';
        $periody = '';
        $period_name = __('Total'); 
        break;
    case 1:
        $period = "AND glpi_tickets.date BETWEEN '".$thisyear." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$thisyear." 00:00:00' AND '".$hoje." 23:59:59'";
        $periody = "AND glpi_tickets.date BETWEEN '".$thisyear." 00:00:00' AND '".$yesterday." 23:59:59'";
		  $period_name = __('Current year','dashboard');
        break;
    case 2:
        $period = "AND glpi_tickets.date BETWEEN '".$thismonth." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$thismonth." 00:00:00' AND '".$hoje." 23:59:59'";
        $periody = "AND glpi_tickets.date BETWEEN '".$thismonth." 00:00:00' AND '".$yesterday." 23:59:59'";
        $period_name = __('Current month','dashboard');
        break;
    case 3:
        $period = "AND glpi_tickets.date BETWEEN '".$lastweek." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$lastweek." 00:00:00' AND '".$hoje." 23:59:59'";
        $periody = "AND glpi_tickets.date BETWEEN '".$lastweek." 00:00:00' AND '".$yesterday." 23:59:59'";
        $period_name = __('Last week');
        break;     
    case 4:
        $period = "AND glpi_tickets.date BETWEEN '".$last15." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$last15." 00:00:00' AND '".$hoje." 23:59:59'";
        $periody = "AND glpi_tickets.date BETWEEN '".$last15." 00:00:00' AND '".$yesterday." 23:59:59'";
        $period_name = __('Last 15 days','dashboard');
        break;    
    case 5:
        $period = "AND glpi_tickets.date BETWEEN '".$lastmonth." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$lastmonth." 00:00:00' AND '".$hoje." 23:59:59'";
        $periody = "AND glpi_tickets.date BETWEEN '".$lastmonth." 00:00:00' AND '".$yesterday." 23:59:59'";
        $period_name = __('Last 30 days','dashboard');
        break; 
    case 6:
        $period = "AND glpi_tickets.date BETWEEN '".$last3month." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$last3month." 00:00:00' AND '".$hoje." 23:59:59'";
        $periody = "AND glpi_tickets.date BETWEEN '".$last3month." 00:00:00' AND '".$yesterday." 23:59:59'";
        $period_name = __('Last 90 days','dashboard');
        break; 
    case 7:
        $period = "AND glpi_tickets.date BETWEEN '".$last6month." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$last6month." 00:00:00' AND '".$hoje." 23:59:59'";
        $periody = "AND glpi_tickets.date BETWEEN '".$last6month." 00:00:00' AND '".$yesterday." 23:59:59'";
        $period_name = __('Last 180 days','dashboard');
        break;                 
    default:
        $period = '';
        $periodp = '';
        $periody = '';
        $period_name = __('Total'); 
        break;              
}


switch (date("m")) {
    case "01": $mes = __('January','dashboard'); break;
    case "02": $mes = __('February','dashboard'); break;
    case "03": $mes = __('March','dashboard'); break;
    case "04": $mes = __('April','dashboard'); break;
    case "05": $mes = __('May','dashboard'); break;
    case "06": $mes = __('June','dashboard'); break;
    case "07": $mes = __('July','dashboard'); break;
    case "08": $mes = __('August','dashboard'); break;
    case "09": $mes = __('September','dashboard'); break;
    case "10": $mes = __('October','dashboard'); break;
    case "11": $mes = __('November','dashboard'); break;
    case "12": $mes = __('December','dashboard'); break;
}

switch (date("w")) {
    case "0": $dia = __('Sunday','dashboard'); break;    
    case "1": $dia = __('Monday','dashboard'); break;
    case "2": $dia = __('Tuesday','dashboard'); break;
    case "3": $dia = __('Wednesday','dashboard'); break;
    case "4": $dia = __('Thursday','dashboard'); break;
    case "5": $dia = __('Friday','dashboard'); break;
    case "6": $dia = __('Saturday','dashboard'); break;  
}

// time period for metrics
$sql_met = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'metric' AND users_id = ".$_SESSION['glpiID']."";
$result_met = $DB->query($sql_met);
$sel_period = $DB->result($result_met,0,'value');

// entity
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$_SESSION['glpiID']."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

if($sel_ent != -1 && $sel_ent != '') {			
	$entidade = "AND glpi_tickets.entities_id IN (".$sel_ent.")";
	$ent_problem =  "AND glpi_problems.entities_id IN (".$sel_ent.")";
}

if($sel_ent == '') {
	
	$entities = $_SESSION['glpiactiveentities'];
	$ent = implode(",",$entities);
	
	if($ent != '') {
		$entidade = "AND glpi_tickets.entities_id IN (".$ent.")";
		$ent_problem =  "AND glpi_problems.entities_id IN (".$ent.")";
	}
	else {
		$entidade = "";
		$ent_problem =  "";
	}
}


//group name
if($_SESSION['glpiactive_entity'] != '') {
	
	$sql_e = "SELECT name FROM glpi_groups WHERE id = ".$id_grp."";
	$result_e = $DB->query($sql_e);
	$grp_name = $DB->result($result_e,0,'name');
	$actent = __('Group').": ". $grp_name;
}

elseif($_SESSION['glpiactive_entity'] == 0) {
	$actent = __('Root entity');
}

else {
	$actent = 'GLPI '.$CFG_GLPI['version'];
}	

//chamados ano
$sql_ano =	"SELECT COUNT(glpi_tickets.id) as total 
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.is_deleted = 0
AND DATE_FORMAT( glpi_tickets.date, '%Y' ) IN (".$ano.")
$entidade ";

$result_ano = $DB->query($sql_ano);
$total_ano = $DB->fetchAssoc($result_ano);
  

//chamados mes
$sql_mes =	"SELECT COUNT(glpi_tickets.id) as total 
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.is_deleted = '0'
AND glpi_tickets.date LIKE '$month%'        
$period
$entidade ";

$result_mes = $DB->query($sql_mes);
$total_mes = $DB->fetchAssoc($result_mes);

  
//ticktes by month
$querym = "
SELECT DISTINCT DATE_FORMAT( glpi_tickets.date, '%b-%y' ) AS month_l, COUNT( glpi_tickets.id ) AS nb, DATE_FORMAT( glpi_tickets.date, '%y-%m' ) AS MONTH
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.is_deleted = '0'
$period
$entidade
GROUP BY MONTH
ORDER BY MONTH ASC ";

$resultm = $DB->query($querym) or die('erro');

$arr_grfm = array();
while ($row_result = $DB->fetchAssoc($resultm))		
{ 
	$v_row_result = $row_result['month_l'];
	$arr_grfm[$v_row_result] = $row_result['nb'];			
} 
	
$grfm = array_keys($arr_grfm) ;
$quantm = array_values($arr_grfm) ;

$grfm2 = implode("','",$grfm);
$grfm3 = "'$grfm2'";
$quantm2 = implode(',',$quantm);

$opened = array_sum($quantm);


//Today Ticktes
$sql_hoje =	"SELECT COUNT(glpi_tickets.id) as total        
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.date like '$hoje%'      
AND glpi_tickets.is_deleted = '0'
".$entidade." ";

$result_hoje = $DB->query($sql_hoje);
$today_tickets = $DB->result($result_hoje,0,'total');

//Ticktes by day
$queryd = "
SELECT DISTINCT DATE_FORMAT(glpi_tickets.date, '%b-%d') as day_l,  COUNT(glpi_tickets.id) as nb, DATE_FORMAT(glpi_tickets.date, '%Y-%m-%d') as day
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.is_deleted = '0'
AND glpi_tickets.date BETWEEN '".$lastmonth." 00:00:00' AND '".$hoje." 23:59:59'
$entidade
GROUP BY day
ORDER BY day";

$resultd = $DB->query($queryd) or die('erro_day');

$arr_day = array();
$arr_days = array();

while ($row_result = $DB->fetchAssoc($resultd))		
{ 
	$v_row_result = $row_result['day_l'];
	$arr_day[$v_row_result] = $row_result['nb'];			
} 
	
$grfd = array_keys($arr_day) ;
$quantd = array_values($arr_day) ;

$grfd2 = implode("','",$grfd);
$grfd3 = "'$grfd2'";
$quantd2 = implode(',',$quantd);

$bydays = array_sum($quantd);


//resolution time
$query2 = "
SELECT count( glpi_tickets.id ) AS chamados , DATEDIFF( glpi_tickets.solvedate, date ) AS days
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.solvedate IS NOT NULL
AND glpi_tickets.is_deleted = 0
$period
$entidade
GROUP BY days ";
		
$result2 = $DB->query($query2) or die('erro');

$arr_grf2 = array();

while ($row_result = $DB->fetchAssoc($result2))		
{ 
	$v_row_result = $row_result['days'];
	$arr_grf2[$v_row_result] = $row_result['chamados'];			
} 
	
$grf2 = array_keys($arr_grf2);
$quant2 = array_values($arr_grf2);

$conta = count($arr_grf2);

for($i=0; $i < 7; $i++) {

	if($quant2[$i] != 0) {
		$till[$i] = $quant2[$i];
	}
	else {
		$till[$i] = 0;
	}	
	
	$arr_days[] += $till[$i];
}

$res_days = 0;

// SLA time
$sql_cham = 
"SELECT glpi_tickets.id AS id, glpi_tickets.name AS descr, glpi_tickets.date AS date, glpi_tickets.solvedate as solvedate, 
glpi_tickets.status, glpi_tickets.time_to_resolve AS duedate, sla_waiting_duration AS slawait, glpi_tickets.type,
FROM_UNIXTIME( UNIX_TIMESTAMP( `glpi_tickets`.`solvedate` ) , '%Y-%m' ) AS date_unix, AVG( glpi_tickets.solve_delay_stat ) AS time
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.is_deleted = 0
$period
$entidade
GROUP BY id
ORDER BY id DESC ";

$result_cham = $DB->query($sql_cham);
$conta_cons = $DB->numrows($result_cham);

// Count overdue tickets
$v = 0;
while($row = $DB->fetchAssoc($result_cham)){

	if($row['solvedate'] > $row['duedate']) {
		$v = $v+1;
	}	

	else {	
		
		if(!isset($row['solvedate']) AND $hoje > $row['duedate']) {
			$v = $v+1;
		}
	}	
} 

// Count within tickets
$w = $conta_cons - $v;
$gauge_val = round(($w*100)/$conta_cons,1);

//STATUS
$query_sta = "
SELECT COUNT(glpi_tickets.id) as tick, glpi_tickets.status as stat
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.is_deleted = 0
AND glpi_tickets.status NOT IN (5,6)       
$period      
$entidade 
GROUP BY glpi_tickets.status
ORDER BY tick DESC ";
		
$result_sta = $DB->query($query_sta) or die('erro_stat');

$arr_sta = array();
while ($row_result = $DB->fetchAssoc($result_sta))		
{ 
   $v_row_result = Ticket::getStatus($row_result['stat']);
   $arr_sta[$v_row_result] = $row_result['tick'];			
} 
	
$grf_sta = array_keys($arr_sta);
$quant_sta = array_values($arr_sta);

$conta = count($arr_sta);

$sta_labels = implode(',',$grf_sta);
$sta_values = implode(',',$quant_sta);


//Tickets type
//incident - request
$query_type = "SELECT count(glpi_tickets.id) AS quant, glpi_tickets.type
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.is_deleted = 0
$period
$entidade
GROUP BY glpi_tickets.type
ORDER BY glpi_tickets.type ASC"; 

$res_type = $DB->query($query_type);

$inc = $DB->result($res_type,0,'quant');
$req = $DB->result($res_type,1,'quant');

$inc_label = $DB->result($res_type,0,'type');
$req_label = $DB->result($res_type,1,'type');

if($inc_label == "1") {
	$inc_label = __('Incident');
	$req_label = __('Request');
}
else {
	$inc_label = __('Request');	
	$req_label = __('Incident');
}

//problems	
$query_prob = "SELECT count(glpi_problems.id) AS quant
	FROM `glpi_groups_problems`, glpi_problems, glpi_groups
	WHERE glpi_groups_problems.`groups_id` = ".$id_grp."
	AND glpi_groups_problems.`groups_id` = glpi_groups.id
	AND glpi_groups_problems.`problems_id` = glpi_problems.id
	AND glpi_problems.is_deleted = 0
	$periodp
	$ent_problem ";

$res_prob = $DB->query($query_prob);

$prob = $DB->result($res_prob,0,'quant');
$prob_label = __('Problem');

$types = array();

$array_type[0] = $req;
$array_type[1] = $inc;
$array_type[2] = $prob;

$types = implode(",",$array_type);

$array_label[0] = $req_label;
$array_label[1] = $inc_label;
$array_label[2] = $prob_label;

$rag_labels = implode(",",$array_label);


//Satisfaction
	$query_sat =
	"SELECT avg( `glpi_ticketsatisfactions`.satisfaction ) AS media 
	FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups, `glpi_ticketsatisfactions`
	WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
	AND glpi_groups_tickets.`groups_id` = glpi_groups.id
	AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
	AND glpi_tickets.is_deleted = 0
	AND `glpi_ticketsatisfactions`.tickets_id = glpi_tickets.id
	AND glpi_ticketsatisfactions.satisfaction <> 'NULL'
	$period
	$entidade ";

	$result_sat = $DB->query($query_sat) or die('erro_sat');
	$sat = $DB->result($result_sat,0,'media');
	$satisf = round(($sat*100/5),1);


	//count by status
	$query_stat = "
	SELECT 
	SUM(case when glpi_tickets.status = 1 then 1 else 0 end) AS new,
	SUM(case when glpi_tickets.status = 2 then 1 else 0 end) AS assig,
	SUM(case when glpi_tickets.status = 3 then 1 else 0 end) AS plan,
	SUM(case when glpi_tickets.status = 4 then 1 else 0 end) AS pend,
	SUM(case when glpi_tickets.status = 5 then 1 else 0 end) AS solve,
	SUM(case when glpi_tickets.status = 6 then 1 else 0 end) AS close
	FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
	WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
	AND glpi_groups_tickets.`groups_id` = glpi_groups.id
	AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
	AND glpi_tickets.is_deleted = 0
	$period
	$entidade ";
	
	$result_stat = $DB->query($query_stat);
	
	$new = $DB->result($result_stat,0,'new');
	$assig = $DB->result($result_stat,0,'assig');
	$plan = $DB->result($result_stat,0,'plan');
	$pend = $DB->result($result_stat,0,'pend');
	$solved = $DB->result($result_stat,0,'solve');
	$closed = $DB->result($result_stat,0,'close'); 
	
	$assigned = $assig + $plan;
	$total = $new + $assig + $plan + $pend + $solved;
	
	
	//by status yesterday
	$query_staty = "
	SELECT 
	SUM(case when glpi_tickets.status = 1 then 1 else 0 end) AS new,
	SUM(case when glpi_tickets.status = 2 then 1 else 0 end) AS assig,
	SUM(case when glpi_tickets.status = 3 then 1 else 0 end) AS plan,
	SUM(case when glpi_tickets.status = 4 then 1 else 0 end) AS pend,
	SUM(case when glpi_tickets.status = 5 then 1 else 0 end) AS solve,
	SUM(case when glpi_tickets.status = 6 then 1 else 0 end) AS close
	FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
	WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
	AND glpi_groups_tickets.`groups_id` = glpi_groups.id
	AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
	AND glpi_tickets.is_deleted = 0
	$periody	
	$entidade ";
	
	 
	$result_staty = $DB->query($query_staty);
	
	$newy = $DB->result($result_staty,0,'new');
	$assigy = $DB->result($result_staty,0,'assig');
	$plany = $DB->result($result_staty,0,'plan');
	$pendy = $DB->result($result_staty,0,'pend');
	$solvedy = $DB->result($result_staty,0,'solve');
	$closedy = $DB->result($result_staty,0,'close'); 
	
	$assignedy = $assigy + $plany;
	$totaly = $newy + $assigy + $plany + $pendy + $solvedy;

	
	function percent($data,$datay) {
		
		if($datay != 0 || $datay != '') {
	
			$diff = $data/$datay;		
			
			if($diff > 1 && $diff != 0) {
				$perc = ($diff-1)*100;	
				echo "<span style='color:#66ce39 !important; '>". round($perc,1) ." %</span>" ;					
			}
			
			elseif($diff < 1 && $diff != 0) {
				$perc = (1-$diff)*100;
				echo "<span style='color:#f23c25 !important;'>". round($perc,1) ." %</span>" ;
			}		
	
			elseif($diff == 1 ) {
				$perc = 0;
				echo "<span>". round($perc,1) ." %</span>" ;
			}
				
			else {return 0;}
		}
		else {return 0;}
	}
	
?>