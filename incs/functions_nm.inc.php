<?php
/*
7/16/10 Initial Release for no internet operation - created from FIP
8/17/10 gettext => captions
8/21/10 function do_login moved to login.inc.php
8/25/10 logout() and login() relocated to LIP
*/

error_reporting(E_ALL);


//	{						-- dummy
@session_start();
require_once('istest.inc.php');
require_once('mysql.inc.php');
require_once("phpcoord.php");				// UTM converter	
require_once("usng.inc.php");				// USNG converter 9/12/08
//require_once($_SESSION['fmp']);			// added 12/19/07
require_once("browser.inc.php");			// added 1/23/10

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/7/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

define ('NOT_STR', '*not*');
define ('NA_STR', '*na*');
define ('ADM_STR', 'Admin');
define ('SUPR_STR', 'Super');				// added 6/16/08

//$GLOBALS['mysql_prefix'] 			= $mysql_prefix;
/* constants - do NOT change */
$GLOBALS['STATUS_RESERVED'] 		= 0;		// 10/24/08
$GLOBALS['STATUS_CLOSED'] 			= 1;
$GLOBALS['STATUS_OPEN']   			= 2;
$GLOBALS['STATUS_SCHEDULED']   		= 3;
$GLOBALS['NOTIFY_ACTION'] 			= 'Added Action/Patient';
$GLOBALS['NOTIFY_TICKET'] 			= 'Ticket Update';
$GLOBALS['ACTION_DESCRIPTION']		= 1;
$GLOBALS['ACTION_OPEN'] 			= 2;
$GLOBALS['ACTION_CLOSE'] 			= 3;
$GLOBALS['PATIENT_OPEN'] 			= 4;
$GLOBALS['PATIENT_CLOSE'] 			= 5;

$GLOBALS['NOTIFY_TICKET_CHG'] 		= 0;		// 10/22/08
$GLOBALS['NOTIFY_ACTION_CHG'] 		= 1;
$GLOBALS['NOTIFY_PERSON_CHG'] 		= 2;

//$GLOBALS['ACTION_OWNER'] 			= 4;
//$GLOBALS['ACTION_PROBLEMSTART'] 	= 5;
//$GLOBALS['ACTION_PROBLEMEND'] 	= 6;
//$GLOBALS['ACTION_AFFECTED'] 		= 7;
//$GLOBALS['ACTION_SCOPE'] 			= 8;
//$GLOBALS['ACTION_SEVERITY']		= 9;

$GLOBALS['ACTION_COMMENT']			= 10;
$GLOBALS['SEVERITY_NORMAL'] 		= 0;
$GLOBALS['SEVERITY_MEDIUM'] 		= 1;
$GLOBALS['SEVERITY_HIGH'] 			= 2;

$GLOBALS['LEVEL_SUPER'] 			= 0;		// 6/9/08
$GLOBALS['LEVEL_ADMINISTRATOR']		= 1;
$GLOBALS['LEVEL_USER'] 				= 2;
$GLOBALS['LEVEL_GUEST'] 			= 3;
$GLOBALS['LEVEL_MEMBER'] 			= 4;		// 12/15/08	
$GLOBALS['LEVEL_UNIT'] 				= 5;		// 7/8/09

$GLOBALS['LOG_SIGN_IN']				= 1;
$GLOBALS['LOG_SIGN_OUT']			= 2;
$GLOBALS['LOG_COMMENT']				= 3;		// misc comment
$GLOBALS['LOG_INCIDENT_OPEN']		=10;
$GLOBALS['LOG_INCIDENT_CLOSE']		=11;
$GLOBALS['LOG_INCIDENT_CHANGE']		=12;
$GLOBALS['LOG_ACTION_ADD']			=13;
$GLOBALS['LOG_PATIENT_ADD']			=14;
$GLOBALS['LOG_INCIDENT_DELETE']		=15;		// added 6/4/08 
$GLOBALS['LOG_ACTION_DELETE']		=16;		// 8/7/08
$GLOBALS['LOG_PATIENT_DELETE']		=17;
$GLOBALS['LOG_UNIT_STATUS']			=20;
$GLOBALS['LOG_UNIT_COMPLETE']		=21;		// 	run complete
$GLOBALS['LOG_UNIT_CHANGE']			=22;

$GLOBALS['LOG_CALL_DISP']			=30;		// 1/20/09
$GLOBALS['LOG_CALL_RESP']			=31;
$GLOBALS['LOG_CALL_ONSCN']			=32;
$GLOBALS['LOG_CALL_CLR']			=33;
$GLOBALS['LOG_CALL_RESET']			=34;		// 7/7/09

$GLOBALS['LOG_CALL_REC_FAC_SET']	=35;		// 9/29/09
$GLOBALS['LOG_CALL_REC_FAC_CHANGE']	=36;		// 9/29/09
$GLOBALS['LOG_CALL_REC_FAC_UNSET']	=37;		// 9/29/09
$GLOBALS['LOG_CALL_REC_FAC_CLEAR']	=38;		// 9/29/09

$GLOBALS['LOG_FACILITY_ADD']		=40;		// 9/22/09
$GLOBALS['LOG_FACILITY_CHANGE']		=41;		// 9/22/09

$GLOBALS['LOG_FACILITY_INCIDENT_OPEN']	=42;		// 9/29/09
$GLOBALS['LOG_FACILITY_INCIDENT_CLOSE']	=43;		// 9/29/09
$GLOBALS['LOG_FACILITY_INCIDENT_CHANGE']=44;		// 9/29/09

$GLOBALS['LOG_CALL_U2FENR']			=45;		// 9/29/09
$GLOBALS['LOG_CALL_U2FARR']			=46;		// 9/29/09

$GLOBALS['LOG_FACILITY_DISP']		=47;		// 9/22/09
$GLOBALS['LOG_FACILITY_RESP']		=48;		// 9/22/09
$GLOBALS['LOG_FACILITY_ONSCN']		=49;		// 9/22/09
$GLOBALS['LOG_FACILITY_CLR']		=50;		// 9/22/09
$GLOBALS['LOG_FACILITY_RESET']		=51;		// 9/22/09

$GLOBALS['icons'] = array("black.png", "blue.png", "green.png", "red.png", "white.png", "yellow.png", "gray.png", "lt_blue.png", "orange.png");
$GLOBALS['sm_icons']	= array("sm_black.png", "sm_blue.png", "sm_green.png", "sm_red.png", "sm_white.png", "sm_yellow.png", "sm_gray.png", "sm_lt_blue.png", "sm_orange.png");
$GLOBALS['fac_icons'] = array("square_red.png", "square_black.png", "square_white.png", "square_yellow.png", "square_blue.png", "square_green.png", "shield_red.png", "shield_grey.png", "shield_green.png", "shield_blue.png", "shield_orange.png");

$GLOBALS['SESSION_TIME_LIMIT']		= 60*480;		// minutes of inactivity before logout is forced - 1/18/10
$GLOBALS['TOLERANCE']				= 180*60;		// seconds of deviation from UTC before remotes sources considered 	erroneous - 3/25/09

$GLOBALS['TRACK_APRS']			=1;     	// 7/8/09
$GLOBALS['TRACK_INSTAM']		=2;       
$GLOBALS['TRACK_GTRACK']		=3;   
$GLOBALS['TRACK_LOCATEA']		=4;      
$GLOBALS['TRACK_GLAT']			=5;     

$GLOBALS['UNIT_TYPES_BG']	= array("#000000", "#5A59FF", "#63DB63", "#FF3C4A", "#FFFFFF", "#F7F363", "#C6C3C6", "#00FFFF");	// keyed to unit_types - 2/8/10
$GLOBALS['UNIT_TYPES_TEXT']	= array("#FFFFFF", "#FFFFFF", "#000000", "#000000", "#000000", "#000000", "#000000", "#000000");	// 2/8/10

$GLOBALS['FACY_TYPES_BG']	= array("#E72429", "#000000", "#E7E3E7", "#E7E321", "#5269BD", "#52BE52", "#C60000", "#7B7D7B", "#005D00", "#1000EF");	// keyed to fac_types - 2/8/10
$GLOBALS['FACY_TYPES_TEXT']	= array("#000000", "#FFFFFF", "#000000", "#FFFFFF", "#FFFFFF", "#000000", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF");	// 2/8/10


$evenodd = array ("even", "odd", "heading");	// class names for alternating table row css colors

/* connect to mysql database */

if (!mysql_connect($GLOBALS['mysql_host'], $GLOBALS['mysql_user'], $GLOBALS['mysql_passwd'])) {
	die ("Connection attempt to MySQL failed - correction required in order to continue.");
	}

if (!mysql_select_db($GLOBALS['mysql_db'])) {
	print "Connection attempt to database failed. Please run <a href=\"install.php\">install.php</a> with valid  database configuration information.";
	exit();
	}

/* check for mysql tables, if non-existent, point to install.php */
$failed = 0;
if (!mysql_table_exists("$GLOBALS[mysql_prefix]user")) 		{ print "MySQL table '$GLOBALS[mysql_prefix]user' is missing<BR />"; $failed = 1; 	}
if ($failed) {
	print "One or more database tables is missing.  Please run <a href=\"install.php\">install.php</a> with valid database configuration information.";
	exit();
	}

$expiry = expires();		// note global

require_once ('login.inc.php');				// 8/21/10

function remove_nls($instr) {                // 10/20/09
	$nls = array("\r\n", "\n", "\r");        // note order
	return str_replace($nls, " ", $instr);
	}        // end function

function mysql_table_exists($table) {/* check if mysql table exists */
	$query = "SELECT COUNT(*) FROM `$table`";
	$result = mysql_query($query);
	$num_rows = @mysql_num_rows($result);
	if($num_rows)
		return TRUE;
	else
		return FALSE;
	}

function get_issue_date($id){
	$result = mysql_query("SELECT date FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'");
	$row = mysql_fetch_assoc($result);
	print $row[date];
	}

function check_for_rows($query) {		/* check sql query for returning rows, courtesy of Micah Snyder */
	if($sql = mysql_query($query)) {
		if(mysql_num_rows($sql) !== 0)
			return mysql_num_rows($sql);
		else
			return false;
		}
	else
		return false;
	}

//	} {		-- dummy

function get_disps($tick_id, $resp_id) {				// 7/4/10
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
		WHERE `ticket_id`='$tick_id' AND `responder_id` = '$resp_id'
		AND ((`dispatched` IS NOT NULL) 	AND (DATE_FORMAT(`dispatched`,'%y') != '00'))
		AND ((`responding` IS NULL) 		OR (DATE_FORMAT(`responding`,'%y') = '00'))
		AND ((`on_scene` IS NULL) 			OR (DATE_FORMAT(`on_scene`,'%y') = '00'))
		AND ((`clear` IS NULL) 				OR (DATE_FORMAT(`clear`,'%y') = '00'))
		ORDER BY `id` DESC LIMIT 1 
		 ");		// 6/25/10
	if (mysql_affected_rows()>0) {
		$row = mysql_fetch_assoc($result);
		return "dispatched " . substr ($row['dispatched'] ,11 ,5 );
		}
	
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
		WHERE `ticket_id`='$tick_id' AND `responder_id` = '$resp_id'
		AND ((`responding` IS NOT NULL) 	AND (DATE_FORMAT(`responding`,'%y') != '00'))
		AND ((`on_scene` IS NULL) 			OR (DATE_FORMAT(`on_scene`,'%y') = '00'))
		AND ((`clear` IS NULL) 				OR (DATE_FORMAT(`clear`,'%y') = '00'))
		ORDER BY `id` DESC LIMIT 1 
		");		// 6/25/10
	if (mysql_affected_rows()>0) {
		$row = mysql_fetch_assoc($result);
		return "responding " . substr ($row['responding'] ,11 ,5 );
		}
	
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
		WHERE `ticket_id`='$tick_id'  AND `responder_id` = '$resp_id'
		AND ((`on_scene` IS NOT NULL) 	AND (DATE_FORMAT(`dispatched`,'%y') != '00'))
		AND (`clear` IS NULL 				OR DATE_FORMAT(`clear`,'%y') = '00')	
		ORDER BY `id` DESC LIMIT 1 
		");		
	if (mysql_affected_rows()>0) {
		$row = mysql_fetch_assoc($result);
		return "on_scene " . substr ($row['on_scene'] ,11 ,5 );
		}
		return "???? ";
	}

function show_assigns($which, $id) {				// 08/8/5, 4/30/10
	global $evenodd;

	$which_ar = array ("ticket_id", "responder_id");
	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.*, UNIX_TIMESTAMP(as_of) AS as_of, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket`,
	`$GLOBALS[mysql_prefix]responder`.`name` AS `u_name`,
	`$GLOBALS[mysql_prefix]user`.`user` AS `by_name`,
	CONCAT_WS(' ',`$GLOBALS[mysql_prefix]responder`.`street`,`$GLOBALS[mysql_prefix]responder`.`city`,`$GLOBALS[mysql_prefix]responder`.`state`) AS `addr`
	FROM `$GLOBALS[mysql_prefix]assigns` 
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` 	ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`
	LEFT JOIN `$GLOBALS[mysql_prefix]responder` ON `$GLOBALS[mysql_prefix]assigns`.`responder_id`=`$GLOBALS[mysql_prefix]responder`.`id`	
	LEFT JOIN `$GLOBALS[mysql_prefix]user` 		ON `$GLOBALS[mysql_prefix]assigns`.`user_id`=`$GLOBALS[mysql_prefix]user`.`id`	
	WHERE `$GLOBALS[mysql_prefix]assigns`.`{$which_ar[$which]}` = $id
	ORDER BY `as_of` DESC";		//	07/05/10 Made responder table explicit.

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$i = 0;	
	$print = "";
	if (mysql_affected_rows()>0) {
		$print .= "\n<BR /><TABLE ALIGN='center' BORDER=0 width='100%'>";
		$print .= "\n<TR><TD ALIGN='center' COLSPAN=99><B>Active/Recent Dispatches</B> (" . mysql_affected_rows() . ")</TD>";
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$i++;
			$strike = $strikend = "";
			if (is_date($row['clear'])) {		
				$strike = "<STRIKE>"; $strikend = "</STRIKE>";		// strikethrough on closed assigns
				}
			
			$print .="\n\t<TR CLASS= '" . $evenodd[($i+1)%2] . "'>";
//			$print .= "<TD>" . $strike . $row['id']	. 	$strikend . "</TD>";
			if ($which == 1) {															// showing incidents? - 4/30/10
				$onclick = " onClick = 'open_tick_window ({$row['ticket_id']})'";
//				$print .= "<TD>" . $strike . shorten($row['ticket'], 20)	. 	$strikend . "</TD>";
				$print .= "<TD TITLE='{$row['ticket']} - {$row['addr']}' {$onclick}>" . $strike . shorten($row['ticket'], 10) . " - " . shorten($row['addr'], 20)	. 	$strikend . "</TD>";
				}
			else {
//				$print .= "<TD>" . $strike . shorten($row['u_name']). 	$strikend . "</TD>";
				$print .= "<TD TITLE='" . $row['u_name']. "'>" . $strike . shorten($row['u_name'], 20)	. 	$strikend . "</TD>";
				}
			$print .= "<TD>" . $strike . format_date($row['as_of'])	. $strikend . "</TD>";
			$print .= "<TD>" . $strike . $row['by_name'] 	. 	$strikend . "</TD>";
			$print .= "</TR>";
			}				// end while($row...)
		$print .= "</TABLE>\n";			
		}				// end if (mysql_ ...)
	return $print;
	
	}			// end function get_assigns()


function OLD_show_assigns($which, $id) {				// 08/8/5, 4/30/10
	global $evenodd;

	$which_ar = array ("ticket_id", "responder_id");
	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.*, UNIX_TIMESTAMP(as_of) AS as_of, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket`,
	`$GLOBALS[mysql_prefix]responder`.`name` AS `u_name`,
	`$GLOBALS[mysql_prefix]user`.`user` AS `by_name`,
	CONCAT_WS(' ',`street`,`city`,`state`) AS `addr`
	FROM `$GLOBALS[mysql_prefix]assigns` 
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` 	ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`
	LEFT JOIN `$GLOBALS[mysql_prefix]responder` ON `$GLOBALS[mysql_prefix]assigns`.`responder_id`=`$GLOBALS[mysql_prefix]responder`.`id`	
	LEFT JOIN `$GLOBALS[mysql_prefix]user` 		ON `$GLOBALS[mysql_prefix]assigns`.`user_id`=`$GLOBALS[mysql_prefix]user`.`id`	
	WHERE `$GLOBALS[mysql_prefix]assigns`.`{$which_ar[$which]}` = $id
	ORDER BY `as_of` DESC";
	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$i = 0;	
	$print = "";
	if (mysql_affected_rows()>0) {
		$print .= "\n<BR /><TABLE ALIGN='center' BORDER=0 width='100%'>";
		$print .= "\n<TR><TD ALIGN='center' COLSPAN=99><B>Active/Recent Dispatches</B> (" . mysql_affected_rows() . ")</TD>";
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$i++;
			$strike = $strikend = "";
			if (is_date($row['clear'])) {		
				$strike = "<STRIKE>"; $strikend = "</STRIKE>";		// strikethrough on closed assigns
				}
			
			$print .="\n\t<TR CLASS= '" . $evenodd[($i+1)%2] . "'>";
			if ($which == 1) {															// showing incidents? - 4/30/10
				$onclick = " onClick = 'open_tick_window ({$row['ticket_id']})'";
				$print .= "<TD TITLE='{$row['ticket']} - {$row['addr']}' {$onclick}>" . $strike . shorten($row['ticket'], 10) . " - " . shorten($row['addr'], 20)	. 	$strikend . "</TD>";
				}
			else {
				$print .= "<TD TITLE='" . $row['u_name']. "'>" . $strike . shorten($row['u_name'], 20)	. 	$strikend . "</TD>";
				}
			$print .= "<TD>" . $strike . format_date($row['as_of'])	. $strikend . "</TD>";
			$print .= "<TD>" . $strike . $row['by_name'] 	. 	$strikend . "</TD>";
			$print .= "</TR>";
			}				// end while($row...)
		$print .= "</TABLE>\n";			
		}				// end if (mysql_ ...)
	return $print;
	
	}			// end function get_assigns()


function show_actions ($the_id, $theSort="date", $links, $display) {			/* list actions and patient data belonging to ticket */
	if ($display) {
		$evenodd = array ("even", "odd");		// class names for display table row colors
		}
	else {
		$evenodd = array ("plain", "plain");	// print
		}
	$query = "SELECT `id`, `name` FROM `$GLOBALS[mysql_prefix]responder`";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$responderlist = array();
	$responderlist[0] = "NA";	
	while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$responderlist[$act_row['id']] = $act_row['name'];
		}
	$print = "<TABLE BORDER='0' ID='patients' width=" . max(320, intval($_SESSION['scr_width']* 0.4)) . ">";
																	/* list patients */
	$query = "SELECT *,UNIX_TIMESTAMP(date) AS `date`,UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]patient` WHERE `ticket_id`='$the_id' ORDER BY `date`";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$caption = "Patient: &nbsp;&nbsp;";
	$actr=0;
	while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$print .= "<TR CLASS='" . $evenodd[$actr%2] . "' WIDTH='100%'><TD VALIGN='top' NOWRAP CLASS='td_label'>" . $caption . "</TD>";
		$print .= "<TD NOWRAP>" . $act_row['name'] . "</TD><TD NOWRAP>". format_date($act_row['updated']) . "</TD>";
		$print .= "<TD NOWRAP> by <B>".get_owner($act_row['user'])."</B>";
		
		$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'] ? "*" : "-")."</TD><TD>" . nl2br($act_row['description']) . "</TD>";
		if ($links) {
			$print .= "<TD>&nbsp;[<A HREF='patient.php?ticket_id=$the_id&id=" . $act_row['id'] . "&action=edit'>edit</A>|
				<A HREF='patient.php?id=" . $act_row['id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</TD></TR>\n";	
				}
		$caption = "";				// once only
		$actr++;
		}
																	/* list actions */
	$query = "SELECT *,UNIX_TIMESTAMP(date) AS `date`,UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id`='$the_id' ORDER BY `date`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if ((mysql_affected_rows() + $actr)==0) { 				// 8/6/08
		return "";
		}				
	else {
		$caption = "Actions: &nbsp;&nbsp;";
		$pctr=0;
		while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
			$print .= "<TR CLASS='" . $evenodd[$pctr%2] . "' WIDTH='100%'><TD VALIGN='top' NOWRAP CLASS='td_label'>$caption</TD>";
			$responders = explode (" ", trim($act_row['responder']));	// space-separated list to array
			$sep = $respstring = "";
			for ($i=0 ;$i< count($responders);$i++) {				// build string of responder names
				if (array_key_exists($responders[$i], $responderlist)) {
					$respstring .= $sep . "&bull; " . $responderlist[$responders[$i]];
					$sep = "<BR />";
					}
				}
			
			$print .= "<TD NOWRAP>" . $respstring . "</TD><TD NOWRAP>".format_date($act_row['updated']) ."</TD>";
			$print .= "<TD NOWRAP>by <B>".get_owner($act_row['user'])."</B> ";
			$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'])? '*' : '-';
			$print .= "</TD><TD WIDTH='100%'>" . nl2br($act_row['description']) . "</TD>";
			if ($links) {
				$print .= "<TD><NOBR>&nbsp;[<A HREF='action.php?ticket_id=$the_id&id=" . $act_row['id'] . "&action=edit'>edit</A>|
					<A HREF='action.php?id=" . $act_row['id'] . "&ticket_id=$the_id&action=delete'>delete</A>]</NOBR></TD></TR>\n";	
				}
			$caption = "";
			$pctr++;
			}				// end if/else (...)
		$print .= "</TABLE>\n";
		return $print;
		}				// end else
	}			// end function show_actions

// } { -- dummy

function show_log ($theid, $show_cfs=FALSE) {								// 11/20/09
	global $evenodd ;	// class names for alternating table row colors
	require_once('./incs/log_codes.inc.php'); 								// 3/25/10
		
	$query = "
		SELECT *, UNIX_TIMESTAMP(`when`) AS `when`,
		t.scope AS `tickname`,
		`r`.`name` AS `unitname`,
		`s`.`status_val` AS `theinfo`,
		`u`.`user` AS `thename` 
		FROM `$GLOBALS[mysql_prefix]log`
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]log.ticket_id = t.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` r ON ($GLOBALS[mysql_prefix]log.responder_id = r.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` s ON ($GLOBALS[mysql_prefix]log.info = s.id)
		LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON ($GLOBALS[mysql_prefix]log.who = u.id)
		WHERE `$GLOBALS[mysql_prefix]log`.`ticket_id` = $theid
		";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$i = 0;
	$print = "<TABLE ALIGN='left' CELLSPACING = 1 WIDTH='100%'>";

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		if ($i==0) {				// 11/20/09
			$print .= "<TR CLASS='even'><TD TITLE = \"{$row['tickname']}\" COLSPAN=99 ALIGN='center'><B> Log: <I>". shorten($row['tickname'], 32) . "</I></B></TD></TR>";
			$cfs_head = ($show_cfs)? "<TD ALIGN='center'>CFS</TD>" : ""  ;
			$print .= "<TR CLASS='odd'><TD ALIGN='left'>Code</TD>" . $cfs_head . "<TD ALIGN='left'>Unit</TD><TD ALIGN='left'>Status</TD><TD ALIGN='left'>When</TD><TD ALIGN='left'>By</TD><TD ALIGN='left'>From</TD></TR>";
			}
	
		$print .= "<TR CLASS='" . $evenodd[$i%2] . "'>" .				// 11/20/09
			"<TD TITLE =\"{$types[$row['code']]}\">". shorten($types[$row['code']], 20) . "</TD>"; // 
		if ($show_cfs) {
			$print .= "<TD TITLE =\"{$row['tickname']}\">". shorten($row['tickname'], 16) . "</TD>";	// 2009-11-07 22:37:41 - substr($row['when'], 11, 5)
			}
		$print .= 
			"<TD TITLE =\"{$row['unitname']}\">". 	shorten($row['unitname'], 16) . "</TD>".
			"<TD TITLE =\"{$row['theinfo']}\">". 	shorten($row['theinfo'], 16) . "</TD>".
			"<TD TITLE =\"" . format_date($row['when']) . "\">". date ("H:i", $row['when']) . "</TD>".
			"<TD TITLE =\"{$row['thename']}\">". 	shorten($row['thename'], 8) . "</TD>".
			"<TD TITLE =\"{$row['from']}\">". 		substr($row['from'], -4) . "</TD>".
			"</TR>";
			$i++;
		}
	$print .= "</TABLE>";
	return $print;
	}		// end function get_log ()
//	} -- dummy
function set_ticket_status($status,$id){				/* alter ticket status */
	$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET status='$status' WHERE ID='$id'LIMIT 1";
	$result = mysql_query($query) or do_error("set_ticket_status(s:$status, id:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	}

function format_date($date){							/* format date to defined type */ 
	if (good_date($date)) {	
		return date(get_variable("date_format"),$date);}	//return date(get_variable("date_format"),strtotime($date));
	else {return "TBD";}
	}				// end function format_date($date)
	
function good_date($date) {		// 
	return (is_string ($date) && ((strlen($date)==10)));
	}

function format_sb_date($date){							/* format sidebar date */ 
	if (is_string ($date) && strlen($date)==10) {	
		return date("M-d H:i",$date);}	//return date(get_variable("date_format"),strtotime($date));
	else {return "TBD";}
	}				// end function format_date($date)

function good_date_time($date) {						//  2/15/09
	return (is_string ($date) && (strlen($date)==19) && (!($date=="0000-00-00 00:00:00")));
	}

function format_date_time($date){		// mySql format to settings spec - 2/15/09
	return (good_date_time($date))? date(get_variable("date_format"),mysql2timestamp($date))  : "TBD";
	}				// end function format_date()
	
function get_status($status){							/* return status text from code */
	switch($status)	{
		case 1: return 'Closed';
			break;
		case 2: return 'Open';
			break;
		case 3: return 'Scheduled';
			break;
		default: return 'Status error';
		}
	}

function get_owner($id){								/* get owner name from id */
	$result	= mysql_query("SELECT user FROM `$GLOBALS[mysql_prefix]user` WHERE `id`='$id' LIMIT 1") or do_error("get_owner(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row	= stripslashes_deep(mysql_fetch_assoc($result));
	return (mysql_affected_rows()==0 )? "unk?" : $row['user'];
//	return $row['user'];
	}

function get_severity($severity){			/* return severity string from value */
	switch($severity) {
		case $GLOBALS['SEVERITY_NORMAL']: 	return "normal"; break;
		case $GLOBALS['SEVERITY_MEDIUM']: 	return "medium"; break;
		case $GLOBALS['SEVERITY_HIGH']: 	return "high"; break;
		default: 							return "Severity error"; break;
		}
	}

function get_responder($id){			/* return responder-type string from value */
	$result	= mysql_query("SELECT `name` FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$id' LIMIT 1") or do_error("get_responder(i:$id)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$temprow	= stripslashes_deep(mysql_fetch_assoc($result));
	return $temprow['name'];
	}

function strip_html($html_string) {						/* strip HTML tags/special characters and fix custom ones to prevent bad HTML, CrossSiteScripting etc */
	$html_string =strip_tags(htmlspecialchars($html_string));	//strip all "real" html and convert special characters first
	
	if (!get_variable('allow_custom_tags')){
		//$html_string = str_replace('\[|\]', '', $html_string);
		//$html_string = str_replace('[b]', '', $html_string);
		//$html_string = str_replace('[/b]', '', $html_string);
		//$html_string = str_replace('[i]', '', $html_string);
		//$html_string = str_replace('[/i]', '', $html_string);
		return $html_string;
		}
	
	$html_string = str_replace('[b]', '<b>', $html_string);	//fix bolds
	$html_string = str_replace('[/b]', '</b>', $html_string);
	
	$html_string = str_replace('[i]', '<i>',$html_string);	//fix italics
	$html_string = str_replace('[/i]', '</i>', $html_string);
	
	return $html_string;
	}

$variables = array();
function get_variable($which){								/* get variable from db settings table, returns FALSE if absent  */
	global $variables;
	if (empty($variables)) {
		$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]settings`") or do_error("get_variable(n:$name)::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$name = $row['name']; $value=$row['value'] ;
			$variables[$name] = $value;
			}
		}
	return (array_key_exists($which, $variables))? $variables[$which] : FALSE ;
//	return $variables[$which];
	}
	
function do_error($err_function,$err,$custom_err='',$file='',$line=''){/* raise an error event */
	print "<FONT CLASS=\"warn\">An error occured in function '<B>$err_function</B>': '<B>$err</B>'<BR />";
	if ($file OR $line) print "Error occured in '$file' at line '$line'<BR />";
	if ($custom_err != '') print "Additional info: '<B>$custom_err</B>'<BR />";
	print '<BR />Check your MySQL connection and if the problem persist, contact the <A HREF="help.php?q=credits">author</A>.<BR />';
	die('<B>Execution stopped.</B></FONT>');
	}

function add_header($ticket_id, $no_edit = FALSE) {		// 11/27/09, 3/30/10
	$win_height =  get_variable('map_height') + 240;
	$win_width = get_variable('map_width') + 80;
	print "<BR /><SPAN STYLE = 'margin-left:40px'><NOBR><FONT SIZE='2'>This Call: ";	
	print "<A HREF='#' onClick = \"var popWindow = window.open('incident_popup.php?id=$ticket_id', 'PopWindow', 'resizable=1, scrollbars, height={$win_height}, width={$win_width}, left=50,top=50,screenX=50,screenY=50'); popWindow.focus();\">Popup</A> |"; // 7/3/10

	if (is_administrator() || is_super()){
		if (!($no_edit)) {
			print "<A HREF='edit_nm.php?id=$ticket_id'>Edit </A> | ";
			}
//		print "<A HREF='edit_nm.php?id=$ticket_id&delete=1'>Delete </A> | ";
		if (!is_closed($ticket_id)) {
			print "<A HREF='action.php?ticket_id=$ticket_id'>Add Action</A> | ";
			print "<A HREF='patient.php?ticket_id=$ticket_id'>Add Patient</A> | ";
			}
		print "<A HREF='config.php?func=notify&id=$ticket_id'>Notify</A> | ";
		}
	print "<A HREF='main.php?print=true&id=$ticket_id'>Print </A> | ";
	if (!is_guest()) {				// 2/1/10
		print "<A HREF='#' onClick = \"var mailWindow = window.open('mail.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=300, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\">E-mail </A> |"; // 2/1/10
		print "<A HREF='#' onClick = \"var mailWindow = window.open('add_note.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\"> Add note </A>"; // 10/8/08
		if (!is_closed($ticket_id)) {		// 10/5/09
			print "  | <A HREF='#' onClick = \"var mailWindow = window.open('close_in.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\"> Close incident </A> ";  // 8/20/09
			}
		print " | <A HREF='routes_nm.php?ticket_id=$ticket_id'> Dispatch Unit</A>";		// 3/30/10
		}
	print "</FONT></NOBR></SPAN><BR />";
	}				// function add_header()

function is_closed($id){/* is ticket closed? */
	return check_for_rows("SELECT id,status FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id' AND status='$GLOBALS[STATUS_CLOSED]'");
	}

function is_super(){				// added 6/9/08
	return ($_SESSION['level'] == $GLOBALS['LEVEL_SUPER']);		// 5/11/10
	}
function is_administrator(){/* is user admin or super? */
	return (($_SESSION['level'] == $GLOBALS['LEVEL_ADMINISTRATOR']) || ($_SESSION['level'] == $GLOBALS['LEVEL_SUPER']));		// 5/11/10
	}

function is_guest(){/* is user guest? */
	return (($_SESSION['level'] == $GLOBALS['LEVEL_GUEST']) || ($_SESSION['level'] == $GLOBALS['LEVEL_MEMBER']));				// 6/25/10
	}

function is_member(){				/* is user member? */
	return (($_SESSION['level'] == $GLOBALS['LEVEL_MEMBER']));				// 7/2/10
	}

function is_user(){					/* is user operator/dispatcher? */
	return ($_SESSION['level'] == $GLOBALS['LEVEL_USER']);		// 5/11/10
	}

function is_unit(){					/* is user unit? */			
	return ($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']);						// 7/12/10
	}

function may_email() {
	return (!(is_guest()) || (is_member() || is_unit())) ;						// members, units  allowed
	}
																	/* print date and time in dropdown menus */ 
function generate_date_dropdown($date_suffix,$default_date=0, $disabled=FALSE) {			// 'extra allows 'disabled'
	$dis_str = ($disabled)? " disabled" : "" ;
	$td = array ("E" => "5", "C" => "6", "M" => "7", "W" => "8");							// hours west of GMT
	$deltam = intval(get_variable('delta_mins'));													// align server clock minutes
	$local = (time() - (intval(get_variable('delta_mins'))*60));
	
	if ($default_date)	{	//default to current date/time if no values are given
		$year  		= date('Y',$default_date);
		$month 		= date('m',$default_date);
		$day   		= date('d',$default_date);
		$minute		= date('i',$default_date);
		$meridiem	= date('a',$default_date);
		if (get_variable('military_time')==1) 	$hour = date('H',$default_date);
		else 									$hour = date('h',$default_date);;
		}
	else {
		$year 		= date('Y', $local);
		$month 		= date('m', $local);
		$day 		= date('d', $local);
		$minute		= date('i', $local);
		$meridiem	= date('a', $local);
		if (get_variable('military_time')==1) 	$hour = date('H', $local);
		else 									$hour = date('h', $local);
		}
		
	$locale = get_variable('locale');				// Added use of Locale switch for Date entry pulldown to change display for locale 08/07/09
	switch($locale) { 
		case "0":
			print "<SELECT name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				print "<OPTION VALUE='$i'";
				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
				
			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				print "<OPTION VALUE='$i'";
				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			
			print "</SELECT>\n&nbsp;<SELECT name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				print "<OPTION VALUE=\"$i\"";
				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";
		
			print "\n<!-- default:$default_date,$year-$month-$day $hour:$minute -->\n";
			break;
	
		case "1":
			print "<SELECT name='frm_day_$date_suffix' $dis_str>";
			for($i = 1; $i < 32; $i++){
				print "<OPTION VALUE=\"$i\"";
				$day == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
	
			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_month_$date_suffix' $dis_str>";
			for($i = 1; $i < 13; $i++){
				print "<OPTION VALUE='$i'";
				$month == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}

			print "</SELECT>";
			print "&nbsp;<SELECT name='frm_year_$date_suffix' $dis_str>";
			for($i = date("Y")-1; $i < date("Y")+1; $i++){
				print "<OPTION VALUE='$i'";
				$year == $i ? print " SELECTED>$i</OPTION>" : print ">$i</OPTION>";
				}
			print "</SELECT>\n&nbsp;&nbsp;";
		
			print "\n<!-- default:$default_date,$year-$month-$day $hour:$minute -->\n";
			break;
																						// 8/10/09
		default:
		    print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				
		}

	
	print "\n<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_hour_$date_suffix' VALUE='$hour' $dis_str>:";
	print "\n<INPUT TYPE='text' SIZE='2' MAXLENGTH='2' NAME='frm_minute_$date_suffix' VALUE='$minute' $dis_str>";
	$show_ampm = (!get_variable('military_time')==1);
	if ($show_ampm){	//put am/pm optionlist if not military time
		print "\n<SELECT NAME='frm_meridiem_$date_suffix' $dis_str><OPTION value='am'";
		if ($meridiem == 'am') print ' selected';
		print ">am</OPTION><OPTION value='pm'";
		if ($meridiem == 'pm') print ' selected';
		print ">pm</OPTION></SELECT>";
		}
	}		// end function generate_date_dropdown(

function report_action($action_type,$ticket_id,$value1='',$value2=''){/* insert reporting actions */
	if (!get_variable('reporting')) return;
	
	switch($action_type)	{
		case $GLOBALS[ACTION_OPEN]: 	$description = "Ticket Opened"; break;
		case $GLOBALS[ACTION_CLOSED]: 	$description = "Ticket Closed"; break;
		case $GLOBALS[PATIENT_OPEN]: 	$description = "Patient Item Opened"; break;
		case $GLOBALS[PATIENT_CLOSED]: 	$description = "Patient Item Closed"; break;
		default: 						$description = "[unknown report value: $action_type]";
		}
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]action` (date,ticket_id,action_type,description,user) VALUES('{$now}','{$ticket_id}','{$action_type}','{$description}','{$_SESSION['user_id']}')";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	}

function dumpp($variable) {
	echo "\n<PRE>";				// pretty it a bit
	var_dump(debug_backtrace());
	var_dump($variable) ;
	echo "</PRE>\n";
	}
	
function dump($variable) {
	echo "\n//<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "//</PRE>\n";
	}

function shorten($instring, $limit) {
	return (strlen($instring) > $limit)? substr($instring, 0, $limit-4) . ".." : $instring ;	// &#133
	}

function format_phone ($instr) {
	$temp = trim($instr);
	return  (!empty($temp))? "(" . substr ($instr, 0,3) . ") " . substr ($instr,3, 3) . "-" . substr ($instr,6, 4): "";
	}
	
function highlight($term, $string) {		// highlights search term
	$replace = "<SPAN CLASS='found'>" .$term . "</SPAN>";
	if (function_exists('str_ireplace')) {
		return str_ireplace ($term,  $replace, $string); 
		}
	else {
		return str_replace ($term,  $replace, $string); 
		}
	}

function stripslashes_deep($value) {
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);
    return $value;
	}
function trim_deep($value) {	
    $value = is_array($value) ?
                array_map('trim_deep', $value) :
                trim($value);
    return $value;
	}
function mysql_real_escape_string_deep($value) {
    $value = is_array($value) ?
                array_map('mysql_real_escape_string_deep', $value) :
                mysql_real_escape_string($value);
    return $value;
	}
function nl2brr($text) {
    return preg_replace("/\r\n|\n|\r/", "<BR />", $text);
	}

function get_level_text ($level) {
	switch ($level) {
		case $GLOBALS['LEVEL_SUPER'] 			: return "Super"; break;
		case $GLOBALS['LEVEL_ADMINISTRATOR'] 	: return "Admin"; break;
		case $GLOBALS['LEVEL_USER'] 			: return "Operator"; break;
		case $GLOBALS['LEVEL_GUEST'] 			: return "Guest"; break;
		case $GLOBALS['LEVEL_MEMBER'] 			: return "Member"; break;			// 3/3/09
		case $GLOBALS['LEVEL_UNIT'] 			: return "Unit"; break;				// 7/12/10
		default 								: return "level error"; break;
		}
	}		//end function
	
function mysql_format_date($indate="") {			// returns MySQL-format date given argument timestamp or default now
	if (empty($indate)) {$indate = time();}
	return @date("Y-m-d H:i:s", $indate);
	}

function is_date($DateEntry) {						// returns true for valid non-zero date	
	$Date_Array = explode('-',$DateEntry);			// "2007-00-00 00:00:00"
	if (count($Date_Array)!=3) 									return FALSE;
	if((strlen($Date_Array[0])!=4)|| ($Date_Array[0]=="0000")) 	return FALSE;
	else {return TRUE;}	
	}		// end function Is_Date()

// function toUTM($coordsIn, $from = "") {							// UTM converter - assume comma separator
	// $temp = explode(",", $coordsIn);
	// $coords = new LatLng(trim($temp[0]), trim($temp[1]));	
	// $utm = $coords->toUTMRef();
	// $temp = $utm->toString();
	// $temp1 = explode (" ", $temp);					// parse by space
	// $temp2 = explode (".", $temp1[1]);				// parse by period
	// $temp3 = explode (".", $temp1[2]);
	// return $temp1[0] . " " . $temp2[0] . " " . $temp3[0];
	// }				// end function toUTM ()
	
function get_type($id) {				// returns incident type given its id
	if ($id == 0) {return "TBD";}		// 1/11/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` WHERE `id`= $id LIMIT 1";
	$result_type = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_type = stripslashes_deep(mysql_fetch_assoc($result_type));
//	unset ($result_type);
	return (isset($row_type['type']))? $row_type['type']: "?";		// 8/12/09
	}

function output_csv($data, $filename = false){
	$csv = array();
	foreach($data as $row){
		$csv[] = implode(', ', $row);
		}
	$csv = sprintf('%s', implode("\n", $csv));

	if ( !$filename ){
		return $csv;
		}

	// Dumping output straight out to browser.

//	header('Content-Type: application/csv');
//	header('Content-Disposition: attachment; filename=' . $filename);
//	echo $csv;
//	exit;
	}

function mysql2timestamp($m) {				// 1/26/09
	return mktime(substr($m,11,2),substr($m,14,2),substr($m,17,2),substr($m,5,2),substr($m,8,2),substr($m,0,4));
	}

function do_log($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) {		// generic log table writer - 5/31/08, 10/6/09
	@session_start();							// 4/4/10
	$who = (array_key_exists ('user_id', $_SESSION ))? $_SESSION['user_id']: 0;
	$from = $_SERVER['REMOTE_ADDR'];
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$query = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]log` (`who`,`from`,`when`,`code`,`ticket_id`,`responder_id`,`info`, `facility`, `rec_facility`, `mileage`)  
		VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
				quote_smart(trim($who)),
				quote_smart(trim($from)),
				quote_smart(trim($now)),
				quote_smart(trim($code)),
				quote_smart(trim($ticket_id)),
				quote_smart(trim($responder_id)),
				quote_smart(trim($info)),
				quote_smart(trim($facility_id)),
				quote_smart(trim($rec_facility_id)),
				quote_smart(trim($mileage)));

	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	unset($result);		// 3/12/09
	}

/*
9/29 quotes line 355 
11/02 corrections to list and show ticket to handle newlines in Description and Comments fields.
11/03 added function do_onload () frame jump prevention
11/06 revised function get_variable to return FALSE if argument is absent
11/9 added map under image
11/30 added function do_log()
12/15 revised log schema for consistency across codes
*/

// =====================================================================================

	function set_sess_exp() {						// updates session-expires time in user record
		@session_start();							// 4/4/10

		global $expiry;
		$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET `expires` = '{$expiry}' WHERE `id`='{$_SESSION['user_id']}' LIMIT 1";		// note no 'delta'
		$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);
		}

	function expired() {			// returns TRUE/FALSE state of login time_out
		if(empty($_SESSION)) {return TRUE;}		// $_SESSION = array(); ??

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` ='{$_SESSION['user_id']}' LIMIT 1";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_affected_rows()==1) {
			$row = stripslashes_deep(mysql_fetch_array($result)); 
			$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
			if ($row['expires'] > $now) {
				return FALSE;			// NOT expired
				}
			else {
				return TRUE;		// expired
				}
			}		// end mysql_affected_rows() ==1
		else {
			dump (__LINE__ . " ?????????");		// ERROR ??????????????
			return TRUE;		// expired
			}
		}			// end expired()

function get_sess_key($line="") {
	if(!(isset($_SESSION['id']))) return FALSE;
	return $_SESSION['id'];
	}

function totime($string){			// given a MySQL-format date/time, returns the unix equivalent
	return mktime(substr($string, 11 , 2),  substr($string, 14 , 2), substr($string, 17 , 2),  substr($string, 5 , 2),  substr($string, 8 , 2),  substr($string, 0 , 4));
	}

function LessExtension($strName) {
	$ext = strrchr($strName, '.');	
	return ($ext)? substr($strName, 0, -strlen($ext)):$strName  ;
	}		// end function LessExtension()


function xml2php($xml) {
	$fils = 0;
	$tab = false;
	$array = array();
	foreach($xml->children() as $key => $value) 	{   
		$child = xml2php($value);
		foreach($node->attributes() as $ak=>$av) {		// To deal with the attributes
			$child[$ak] = (string)$av;
			}
		if($tab==false && in_array($key,array_keys($array))) {		// Let's see if the new child is not in the array
			$tmp = $array[$key];									// If this element is already in the array
			$array[$key] = NULL;									//   we will create an indexed array
			$array[$key][] = $tmp;
			$array[$key][] = $child;
			$tab = true;
			}
		elseif($tab == true) {
			$array[$key][] = $child;			//Add an element in an existing array
			}
		else {			//Add a simple element
			$array[$key] = $child;
			}
		$fils++;	   
	  	}
	if($fils==0) {
		return (string)$xml;
		}
	return $array;
	}

function get_stuff($in_file) {				// return file contents as string
	return file_get_contents($in_file);;
	}				// end function get_stuff()
	
function get_ext($filename) {				// return extension in lower-case
	$exts = explode(".", $filename) ;	// 8/2/09
	return strtolower($exts[count($exts)-1]);
	}

function ezDate($d) {
	$temp = strtotime(str_replace("-","/",$d));
	$ts = time() - $temp;
	if (($ts < 0) || ($ts > 315360000)) {return FALSE;}							// sanity check
	
	if($ts>31536000) $val = round($ts/31536000,0).' year';
	else if($ts>2419200) $val = round($ts/2419200,0).' month';
	else if($ts>604800) $val = round($ts/604800,0).' week';
	else if($ts>86400) $val = round($ts/86400,0).' day';
	else if($ts>3600) $val = round($ts/3600,0).' hour';
	else if($ts>60) $val = round($ts/60,0).' minute';
  	else $val = $ts.' second';
	if(!($val==1)) $val .= 's';
	$val .= " ago";
	return $val;
	}

function mail_it ($to_str, $text, $ticket_id, $text_sel=1, $txt_only = FALSE) {				// 10/6/08, 10/15/08,  2/18/09, 3/7/09
	global $istest;

/*
Subject		A
Inciden		B  Title
Priorit		C  Priorit
Nature		D  Nature
Written		E  Written
Updated		F  As of
Reporte		G  By
Phone: 		H  Phone: 
Status:		I  Status:
Address		J  Location
Descrip		K  Descrip
Disposi		L  Disposi
Start/end	M
Map: " 		N  Map: " 
Actions		O
Patients	P
Host		Q
911 contact	R				// 6/26/10
*/

	switch ($text_sel) {		// 7/7/09
		case 1:
		   	$match_str = strtoupper(get_variable("msg_text_1"));				// note case
		   	break;
		case 2:
		   	$match_str = strtoupper(get_variable("msg_text_2"));
		   	break;
		case 3:
		   	$match_str = strtoupper(get_variable("msg_text_3"));
		   	break;
		}
    if (empty($match_str)) {$match_str = implode ("", range("A", "R"));}        // empty get all	

	require_once("cell_addrs.inc.php");			// 10/22/08
	$cell_addrs = array( "vtext.com", "messaging.sprintpcs.com", "txt.att.net", "vmobl.com", "myboostmobile.com");		// 10/5/08
	if ($istest) {array_push($cell_addrs, "gmail.com");};
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`='$ticket_id' LIMIT 1";
	$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$t_row = stripslashes_deep(mysql_fetch_array($ticket_result));
	$eol = "\n";

	$message="";
	$_end = (good_date_time($t_row['problemend']))?  "  End:" . $t_row['problemend'] : "" ;		// 
	
	for ($i = 0;$i< strlen($match_str); $i++) {
		if(!($match_str[$i]==" ")) {
			switch ($match_str[$i]) {
				case "A":
				    break;
				case "B":
					$message .= "Incident: " . $t_row['scope'] . $eol;
				    break;
				case "C":
					$message .= "Priority: " . get_severity($t_row['severity']) . $eol;
				    break;
				case "D":
					$message .= "Nature: " . get_type($t_row['in_types_id']) . $eol;
				    break;
				case "J":
					$str = "";
					$str .= (empty($t_row['street']))? 	""  : $t_row['street'] . " " ;
					$str .= (empty($t_row['city']))? 	""  : $t_row['city'] . " " ;
					$str .= (empty($t_row['state']))? 	""  : $t_row['state'];
					$message .= empty($str) ? "" : "Addr: " . $str . $eol;
				    break;
				case "K":
					$message .= (empty($t_row['description']))?  "": "Descr: ". wordwrap($t_row['description']).$eol;
				    break;
				case "G":
					$message .= "Reported by: " . $t_row['contact'] . $eol;
				    break;
				case "H":
					$message .= (empty($t_row['phone']))?  "": "Phone: " . format_phone ($t_row['phone']) . $eol;
					break;
				case "E":
					$message .= (empty($t_row['date']))? "":  "Written: " . format_date_time($t_row['date']) . $eol;
				    break;
				case "F":
					$message .= "Updated: " . format_date_time($t_row['updated']) . $eol;
				    break;
				case "I":
					$message .= "Status: ".get_status($t_row['status']).$eol;
				    break;
				case "L":
					$message .= (empty($t_row['comments']))? "": "Disp: ".wordwrap($t_row['comments']).$eol;
				    break;
				case "M":
					$message .= "Run Start: " . format_date_time($t_row['problemstart']). $_end .$eol;
				    break;
				case "N":
					$usng = LLtoUSNG($t_row['lat'], $t_row['lng']);
					$message .= "Map: " . $t_row['lat'] . " " . $t_row['lng'] . ", " . $usng . "\n";
				    break;
			
				case "P":															
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE ticket_id='$ticket_id'";
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_affected_rows()>0) {
						$message .= "\nPatient:\n";
						while($pat_row = stripslashes_deep(mysql_fetch_array($result))){
							$message .= $pat_row['name'] . ", " . $pat_row['updated']  . "- ". wordwrap($pat_row['description'], 70)."\n";
							}
						}
					unset ($result);
				    break;
			
				case "O":
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id`='$ticket_id'";		// 10/16/08
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	// 3/22/09
					if (mysql_affected_rows()>0) {
						$message .= "\nActions:\n";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						while($act_row = stripslashes_deep(mysql_fetch_array($result))) {
							$message .= $act_row['updated'] . " - ".wordwrap($act_row['description'], 70)."\n";
							}
						}	
					unset ($result);
				    break;
			
				case "Q":
					$message .= "Tickets host: ".get_variable('host').$eol;
				    break;
					
				case "R":							// 6/26/10
					$message .= (empty($t_row['nine_one_one']))?  "": "911: ". wordwrap($t_row['nine_one_one']).$eol;
				    break;

				default:
				    $message = "Match string error:" . $match_str[$i]. " " . $match_str . $eol ;

				}		// end switch ()
			}		// end if(!($match_...))
		}		// end for ($i...)

	$message = str_replace("\n.", "\n..", $message);					// see manual re mail win platform peculiarities
	
	$subject = (strpos ($match_str, "A" ))? $subject = $text . $t_row['scope'] . " (#" .$t_row['id'] . ")": "";

	if ($txt_only) {
		return $subject . "\n" . $message;		// 2/16/09
		}
	else {
		do_send ($to_str,  $subject, $message);
		}
	}				// end function mail_it ()
// ________________________________________________________

function smtp ($my_to, $my_subject, $my_message, $my_params, $my_from) { // 7/5/10 - per Kurt Jack
	require_once 'lib/swift_required.php';
	
	// $params = "outgoing.verizon.net/587/none/ashore4/********/ashore4@verizon.net";
	//                       0         1    2      3      4           5
	
	$conn_ary = explode ("/",  $my_params);   
	// dump($conn_ary) ;
	$transport = Swift_SmtpTransport::newInstance($conn_ary[0] , $conn_ary[1] , $conn_ary[2])
	  ->setUsername($conn_ary[3])
	  ->setPassword($conn_ary[4])
	  ;
	
	$mailer = Swift_Mailer::newInstance($transport); // instantiate using  created Transport
	$temp_ar = explode("@", $my_to); // extract name portion - 7/8/09
	$the_from = (isset($conn_ary[5]))? $conn_ary[5]: $my_from;
	$the_from_ar = explode("@", $my_from);	// to extract user portion
											// Create a message
	$message = Swift_Message::newInstance($my_subject)
	  ->setFrom(array($the_from => $the_from_ar[0]))
	  ->setTo(array($my_to , $my_to => trim($temp_ar[0])))
	  ->setBody($my_message)
	  ;
	//    ->setTo(array('receiver@domain.org', 'other@domain.org' => 'Names'))
	$result = $mailer->send($message); //Send the message
	
	} // end function smtp


function old_smtp ($my_to, $my_subject, $my_message, $my_params, $my_from) {				// 7/7/09
	require_once 'lib/swift_required.php';

// $params = "outgoing.verizon.net/587/ashore4/********/ashore4@verizon.net";
//				   0				1	   2	  3			4	

	$conn_ary = explode ("/",  $my_params);
//	dump($conn_ary) ;
	$transport = Swift_SmtpTransport::newInstance($conn_ary[0] , $conn_ary[1])
	  ->setUsername($conn_ary[2])
	  ->setPassword($conn_ary[3])
	  ;
	
	$mailer = Swift_Mailer::newInstance($transport);		// instantiate using  created Transport	
	$temp_ar = explode("@", $my_to);						// extract name portion - 7/8/09
	$the_from = (isset($conn_ary[4]))? $conn_ary[4]: $my_from;
	$the_from_ar = explode("@", $my_from);					// to extract user portion
															// Create a message
	$message = Swift_Message::newInstance($my_subject)
	  ->setFrom(array($the_from => $the_from_ar[0]))
	  ->setTo(array($my_to , $my_to => trim($temp_ar[0])))
	  ->setBody($my_message)
	  ;
	//    ->setTo(array('receiver@domain.org', 'other@domain.org' => 'Names'))
	$result = $mailer->send($message);						//Send the message
	
	}		// end function smtp

function do_send ($to_str, $subject_str, $text_str ) {						// 7/7/09
	global $istest;
	$sleep = 4;																// seconds delay between text messages
	
	function stripLabels($sText){
		$labels = array("Incident:", "Priority:", "Nature:", "Addr:", "Descr:", "Reported by:", "Phone:", "Written:", "Updated:", "Status:", "Disp:", "Run Start:", "Map:", "Patient:", "Actions:", "Tickets host:"); // 5/9/10
		for ($x = 0; $x < count($labels); $x++) {
			$sText = str_replace($labels[$x] , '', $sText);
			}
		return $sText;
		}

	$to_array = explode ("|",$to_str );										// pipe-delimited string  - 10/17/08
	require_once("cell_addrs.inc.php");										// 10/22/08
	$cell_addrs = array("vtext.com", "messaging.sprintpcs.com", "txt.att.net", "vmobl.com", "myboostmobile.com");		// 10/5/08
	if ($istest) {array_push($cell_addrs, "gmail.com");};

	$host = get_variable('host');
	$temp = get_variable('email_reply_to');	
//	$reply_to = (empty($temp))? "": "'Reply-To: '". $temp ."\r\n" ; 
	$reply_to = (empty($temp))? "": "'Reply-To: ". $temp ."'\r\n" ;		// 2/18/10
	
	$temp = get_variable('email_from');												// 6/24/09
	if (empty($temp)) {
		$from_str = "Tickets_CAD" .'@' .$host ;
		}
	else {	
		$temp_ar = explode("@", $temp);
		if (count($temp_ar)==2) {
			$from_str = $temp;		// OK
			}
		else {
			$from_str = $temp_ar[0] . "@" . $host ;
			}
		}
		
//	$from = (empty($temp))?  "Tickets_CAD" : $temp;
	
	$headers = 'From:' .$from_str  . "\r\n" .
	    $reply_to .
	    'X-Mailer: PHP/' . phpversion();

	$to_sep = $cell_sep = "";
	$tostr = $tocellstr = "";
	for ($i = 0; $i< count($to_array); $i++) {
		$temp =  explode ( "@", $to_array[$i]);
		if (in_array(trim(strtolower($temp[1])), $cell_addrs))  {				// cell addr?
			$tocellstr .= $cell_sep . stripslashes($to_array[$i]);				// yes
			$cell_sep = ",";
			}
		else {																	// no
			$tostr .= $to_sep . stripslashes($to_array[$i]);
			$to_sep = ",";														// comma separated addr string
			}
		}				// end for ($i = ...)

	$caption="";
	$smtp = trim(get_variable('smtp_acct'));									// 7/7/09
	if (strlen($tostr)>0) {	
		if (strlen($smtp)==0) {
			@mail($tostr, $subject_str, $text_str, $headers);
			}
		else {
			smtp ($tostr, $subject_str, $text_str, $smtp, $from_str);						// ($my_to, $my_subject, $my_message, $my_params)
			}
		$caption = "Email sent";
		}
	if (strlen($tocellstr)>0) {
		$lgth = 140;
		$ix = 0;
		$i = 1;
		$cell_text_str = stripLabels($text_str);								// strip labels 5/10/10
		while (substr($cell_text_str, $ix , $lgth )) {								// chunk to $lgth-length strings
			$subject_ex = $subject_str . "/part " . $i . "/";					// 10/21/08
			if (strlen($smtp)==0) {			
				mail($tocellstr, $subject_ex, substr ($cell_text_str, $ix , $lgth ), $headers);
				}
			else {
				smtp ($tocellstr, $subject_ex, substr ($cell_text_str, $ix , $lgth ), $smtp, $from_str);	// ($my_to, $my_subject, $my_message, $my_params, $my_from)
				}
			if($i>1) {sleep ($sleep);}								// 10/17/08
			$ix+=$lgth;
			$i++;
			}
		$caption .= " - Cell mail sent";
		}
	return $caption;
	}					// end function do send ()
	
function is_email($email){		   //  validate email, code courtesy of Jerrett Taylor - 10/8/08, 7/2/10
	if(!preg_match( "/^" .
	"[a-zA-Z0-9]+([_\\.-][a-zA-Z0-9]+)*" .		//user
	"@" .
	"([a-zA-Z0-9]+([\.-][a-zA-Z0-9]+)*)+" .   	//domain
	"\\.[a-zA-Z]{2,}" .							//sld, tld
	"$/", $email, $regs)) {
			return FALSE;
			}
		else {
			return TRUE;
			}
		}							  // end function is_email()
				

function notify_user($ticket_id,$action_id) {								// 10/20/08
	if (get_variable('allow_notify') != '1') return FALSE;						//should we notify?
	
	$fields = array();
	$fields[$GLOBALS['NOTIFY_TICKET_CHG']] = "on_ticket";
	$fields[$GLOBALS['NOTIFY_ACTION_CHG']] = "on_action";
	$fields[$GLOBALS['NOTIFY_PERSON_CHG']] = "on_patient";
	
	$addrs = array();															// 
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]notify` WHERE (`ticket_id`='$ticket_id' OR `ticket_id`=0)  AND `" .$fields[$action_id] ."` = '1'";	// all notifies for given ticket - or any ticket 10/22/08
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
		if (is_email($row['email_address'])) {
			array_push($addrs, $row['email_address']); // save for emailing
			}		
		}
	return (empty($addrs))? FALSE: $addrs;
	}


function snap($source, $stuff = "") {																// 10/18/08 , 3/5/09 - debug tool
	global $snap_table;				// defined in istest.inc.php
	if (mysql_table_exists($snap_table)) {
		$query	= "DELETE FROM `$snap_table` WHERE `when`< (NOW() - INTERVAL 1 DAY)"; 		// first remove old
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	
		$query = sprintf("INSERT INTO `$snap_table` (`source`,`stuff`)  
			VALUES(%s,%s)",
				quote_smart_deep(trim($source)),
				quote_smart_deep(trim($stuff)));
	
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		unset($result);
		}
	else {
//		dump(__LINE__);	
		}
	}		// end function snap()
	
function isFloat($n){														// 1/23/09
    return ( $n == strval(floatval($n)) )? true : false;
	}
	
function quote_smart($value) {												// 1/28/09
	if (get_magic_quotes_gpc()) {		// Stripslashes
		$value = stripslashes($value);
		}
	if (!is_int($value)) {			// Quote if not a number or a numeric string
		$value = "'" . mysql_real_escape_string($value) . "'";
		}
	return $value;
	}

function quote_smart_deep($value) {		// recursive array-capable version of the above 
    $value = is_array($value) ? array_map('quote_smart_deep', $value) : quote_smart($value);
    return $value;
	}

function db_insert($table, $fieldset){				// 2/4/09
	return 'INSERT INTO ' . $table . '(' . implode(',', array_keys($fieldset)) . ') VALUES (' . implode(',', array_values($fieldset)) . ')';
	}
function db_delete($table, $where = ''){
	return 'DELETE FROM ' . $table . ($where ? ' WHERE ' . $where : '');
	}
function db_update($table, $fieldset, $where = ''){
	$set = array();
	foreach($fieldset as $field=>$value) $set[] = $field . '=' . $value;
	return 'UPDATE ' . $table . ' SET ' . implode(',', $set) . ($where ? ' WHERE ' . $where : '');
	}

function my_is_float($n){									// 5/4/09
    return ( $n == strval(floatval($n)) && (!($n==0)) )? true : false;
	}

function my_is_int($n){										// 3/25/09
    return ( $n == strval(intval($n)) )? true : false;
	}

function my_date_diff($d1, $d2){		// end, start timestamp integers in, returns string - 5/13/10
	if ($d1 < $d2){	//check higher timestamp and switch if neccessary
		$temp = $d2;
		$d2 = $d1;
		$d1 = $temp;
		}
	else {
		$temp = $d1; //temp can be used for day count if required
		}
	$d1 = date_parse(date("Y-m-d H:i:s",$d1));
	$d2 = date_parse(date("Y-m-d H:i:s",$d2));
	if ($d1['second'] >= $d2['second']){	//seconds
		$diff['second'] = $d1['second'] - $d2['second'];
		}
	else {
		$d1['minute']--;
		$diff['second'] = 60-$d2['second']+$d1['second'];
		}
	if ($d1['minute'] >= $d2['minute']){	//minutes
		$diff['minute'] = $d1['minute'] - $d2['minute'];
		}
	else {
		$d1['hour']--;
		$diff['minute'] = 60-$d2['minute']+$d1['minute'];
		}
	if ($d1['hour'] >= $d2['hour']){	//hours
		$diff['hour'] = $d1['hour'] - $d2['hour'];
		}
	else {
		$d1['day']--;
		$diff['hour'] = 24-$d2['hour']+$d1['hour'];
		}
	if ($d1['day'] >= $d2['day']){	//days
		$diff['day'] = $d1['day'] - $d2['day'];
		}
	else {
		$d1['month']--;
		$diff['day'] = date("t",$temp)-$d2['day']+$d1['day'];
		}
	if ($d1['month'] >= $d2['month']){	//months
		$diff['month'] = $d1['month'] - $d2['month'];
		}
	else {
		$d1['year']--;
		$diff['month'] = 12-$d2['month']+$d1['month'];
		}
	$diff['year'] = $d1['year'] - $d2['year'];	//years

	$out_str = ""; 
	$plural = ($diff['year'] == 1)? "": "s";								// needless elegance
	$out_str .= empty($diff['year'])? "" : "{$diff['year']} yr{$plural}, ";

	$plural = ($diff['month'] == 1)? "": "s";
	$out_str .= empty($diff['month'])? "" : "{$diff['month']} mo{$plural}, ";

	$plural = ($diff['day'] == 1)? "": "s";
	$out_str .= empty($diff['day'])? "" : "{$diff['day']} day{$plural}, ";

	$plural = ($diff['hour'] == 1)? "": "s";
	$out_str .= empty($diff['hour'])? "" : "{$diff['hour']} hr{$plural}, ";

	$plural = ($diff['minute'] == 1)? "": "s";
	$out_str .= empty($diff['minute'])? "" : "{$diff['minute']} min{$plural}";

	return  $out_str;
	}

function expires() {
	$now = time() - (intval(intval(get_variable('delta_mins')))*60); 				// 6/17/10
	return mysql_format_date($now + $GLOBALS['SESSION_TIME_LIMIT']);
	}

function get_status_sel($unit_in, $status_val_in, $tbl_in) {					// returns select list as click-able string - 2/6/10

	switch ($tbl_in) {
		case ("u") :
			$tablename = "responder";
			$link_field = "un_status_id";
			$status_table = "un_status";
			$status_field = "status_val";
			break;
		case ("f") :
			$tablename = "facilities";
			$link_field = "status_id";
			$status_table = "fac_status";
			$status_field = "status_val";
			break;
		default:
			print "ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ";	
			}

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$tablename}`, `$GLOBALS[mysql_prefix]{$status_table}` WHERE `$GLOBALS[mysql_prefix]{$tablename}`.`id` = $unit_in 
		AND `$GLOBALS[mysql_prefix]{$status_table}`.`id` = `$GLOBALS[mysql_prefix]{$tablename}`.`{$link_field}` LIMIT 1" ;	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()==0) {				// 2/7/10
		$init_bg_color = "transparent";
		$init_txt_color = "black";	
		}
	else {
		$row = stripslashes_deep(mysql_fetch_assoc($result)); 
		$init_bg_color = $row['bg_color'];
		$init_txt_color = $row['text_color'];
		}

	$guest = is_guest();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$status_table}` ORDER BY `group` ASC, `sort` ASC, `{$status_field}` ASC";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$dis = ($guest)? " DISABLED": "";								// 9/17/08
	$the_grp = strval(rand());			//  force initial OPTGROUP value
	$i = 0;
	$outstr = "\t\t<SELECT CLASS='sit' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color};' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; do_sel_update({$unit_in}, this.value)' >";	// 12/19/09, 1/1/10
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		if ($the_grp != $row['group']) {
			$outstr .= ($i == 0)? "": "\t</OPTGROUP>";
			$the_grp = $row['group'];
			$outstr .= "\t\t<OPTGROUP LABEL='$the_grp'>";
			}
		$sel = ($row['id']==$status_val_in)? " SELECTED": "";
		$outstr .= "\t\t\t<OPTION VALUE=" . $row['id'] . $sel ." STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'  onMouseover = 'style.backgroundColor = this.backgroundColor;'>$row[$status_field] </OPTION>";		
		$i++;
		}		// end while()
	$outstr .= "\t\t</OPTGROUP>\t\t</SELECT>";
	return $outstr;
	}

function get_units_legend() {		// returns string as centered span - 2/8/10
	$query = "SELECT DISTINCT `type`, `icon`,  `$GLOBALS[mysql_prefix]unit_types`.`name` AS `mytype` FROM `$GLOBALS[mysql_prefix]responder` 
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` ON `$GLOBALS[mysql_prefix]unit_types`.`id` = `$GLOBALS[mysql_prefix]responder`.`type` ORDER BY `mytype`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$out_str = "<SPAN CLASS = 'odd' ALIGN = 'center'><SPAN CLASS = 'even' ALIGN = 'center'> Units: </SPAN>&nbsp;";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];	
		$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];		
		$out_str .= "<SPAN STYLE='background-color:{$the_bg_color}; opacity: .7; color:{$the_text_color}'> {$row['mytype']}</SPAN>&nbsp;";
		}
	return $out_str .= "</SPAN>";	
	}										// end function get_units_legend()

function get_facilities_legend() {		// returns string as centered row - 2/8/10
	$query = "SELECT DISTINCT `type`, `icon`,  `$GLOBALS[mysql_prefix]fac_types`.`name` AS `mytype` FROM `$GLOBALS[mysql_prefix]facilities` 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]fac_types`.`id` = `$GLOBALS[mysql_prefix]facilities`.`type` ORDER BY `mytype`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$out_str = "<TR><TD COLSPAN='99' ALIGN='center'><SPAN CLASS='even'><SPAN CLASS='odd' ALIGN = 'right'>----------</SPAN>";

	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
		$the_bg_color = 	$GLOBALS['FACY_TYPES_BG'][$row['icon']];	
		$the_text_color = 	$GLOBALS['FACY_TYPES_TEXT'][$row['icon']];		
		$out_str .= "<SPAN>&nbsp;</SPAN>";
		}
	return $out_str .= "</SPAN></TD></TR>";	
	}										// end function get_facilities_legend()

function is_phone ($instr) {		// 3/13/10
	if(get_variable("locale")==0){
		return ((strlen(trim($instr))==9) && (is_numeric($instr))) ;
		}
	else { 
		return (is_numeric($instr));
		}
	}
function get_unit_status_legend() {		// returns string as div - 3/21/10
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `status_val`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$out_str = "<DIV><SPAN CLASS = 'even' ALIGN = 'center'> Status legend: </SPAN>&nbsp;";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$out_str .= "<SPAN STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']}'>&nbsp;{$row['status_val']}&nbsp;</SPAN>&nbsp;";
		}
	return $out_str .= "</DIV>";	
	}										// end function get_unit_status_legend()

function get_un_div_height ($in_max) {				//	compute pixels min 260, max .5 x screen height - 2/8/10
	$min = 80 ;
	$max = round($in_max * $_SESSION['scr_height']);
	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";
	$result_unit = mysql_query($query) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	unset ($result_unit);

	$required = mysql_affected_rows() * 23;		// pixels per line		
	if ($required < $min)	{return $min;}
	else					{return ($required > $max)?   $max:  $required;}
	}		// end function un_div_height ()	

function get_sess_vbl ($in_str) {				//	
	$default = 'error';
	@session_start();
	return (array_key_exists ( $in_str, $_SESSION ))?  $_SESSION [$in_str]: $default;
	}		// end get_sess_vbl()

function now() {		// returns date
	return (time() - intval(get_variable('delta_mins'))*60);
	}
function monday() {		// returns date
	return strtotime("last Monday");
	}
function day() {		// returns number
	return date("d", now());
	}
function month() {		// returns number
	return date("n", now());
	}
function year() {		// returns number
	return date("Y", now());
	}

function get_start($local_func){						// 5/2/10
	switch ($local_func) {
		case 1 :		// Today
			return mysql_format_date(mktime( 0, 0, 0, month(), day(), year()));		// m, d, y -- date ('D, M j',
			break;
			
		case 2 :		// Yesterday+
			return mysql_format_date(mktime(0,0,0, month(), (day()-1), year()));		// m, d, y -- date ('D, M j',
			break;
			
		case 3 :		// This week
			return mysql_format_date(monday());						// m, d, y -- date ('D, M j',
			break;
			
		case 4 :		// Last week
			return mysql_format_date(monday() - 7*24*3600);			// m, d, y -- monday a week ago
			break;
			
		case 5 :		// Last week+
			return mysql_format_date(monday() - 7*24*3600);			// m, d, y -- monday a week ago
			break;
			
		case 6 :		// This month
			return mysql_format_date(mktime(0,0,0,  month(), 1, year()));				// m, d, y -- date ('D, M j',
			break;
			
		case 7 :		// Last month
			return mysql_format_date(mktime(0,0,0, (month()-1), 1, year()));			// m, d, y -- date ('D, M j',
			break;
			
		case 8 :		// This year
			return mysql_format_date(mktime(0,0,0, 1, 1, year()));						// m, d, y -- date ('D, M j',
			break;
			
		case 9 :		// Last year
			return mysql_format_date(mktime(0,0,0,1, 1, (year()-1)));		// m, d, y -- date ('D, M j',
			break;
			
		default:
			echo __LINE__ . " error error error error error \n";
			}
		}		// end function get_start

function get_end($local_func){
	switch ($local_func) {
		case 1 :		// Today
		case 2 :		// Yesterday+
		case 3 :		// This week
		case 5 :		// Last week+
		case 6 :		// This month
		case 8 :		// This year
			return mysql_format_date(mktime( 23,59,59, month(), day(), year()));		// m, d, y -- date ('D, M j',

//			return mysql_format_date(now());		// m, d, y -- date ('D, M j',
			break;
						
		case 4 :		// Last week
			return mysql_format_date(monday()-1);			// m, d, y -- last monday 
			break;
			
		case 7 :		// Last month
			return mysql_format_date(mktime(0,0,0, month(), 1,year()));		// m, d, y -- date ('D, M j',
			break;
							
		case 9 :		// Last year
			return mysql_format_date(mktime(23,59,59, 12,31, (year()-1)));		// m, d, y -- date ('D, M j',
			break;
			
		default:
			echo __LINE__ . " error error error error error \n";
			}
		}		// end function get_end

$text_array = array();			// 8/17/10
function get_text($which){		/* get replacement text from db captions table, returns FALSE if absent  */
	global $text_array;
	if (empty($text_array)) {	// populate it to avoid hammering db
		$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]captions`") or do_error("get_text({$which})::mysql_query()", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$capt = $row['capt']; 
			$repl=$row['repl'] ;
			$text_array[$capt] = $repl;
			}
		}
	return (array_key_exists($which, $text_array))? $text_array[$which] : FALSE ;
	}


function get_cb_height () {		// returns pixel count for cb frame	height based on no. of lines - 7/10/10
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'";		// 2/12/09   
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$lines = mysql_num_rows($result);
	unset($result);

	$cb_per_line = 22;				// via trial and error
	$cb_fixed_part = 60;
	$cb_min = 96;
	$cb_max = 300;

	$height = (($lines*$cb_per_line ) + $cb_fixed_part);
	$height = ($height<$cb_min)? $cb_min: $height;
	$height = ($height>$cb_max)? $cb_max: $height;
	
	return (integer) $height;
	}		// function get_cb_height ()


?>