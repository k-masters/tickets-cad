<?php
require_once('../../incs/functions.inc.php');
include('../../incs/html2text.php');
if(!isset($_GET['id'])) {
	exit();
	}
@session_start();
$ret_arr = array();
$by = $_SESSION['user_id'];
$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
$from = $_SERVER['REMOTE_ADDR'];
function get_requester_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		if($row['email'] == "") {
			if($row['email_s'] == "") {
				$the_ret[0] = "";
				} else {
				$the_ret[0] = $row['email_s'];
				}
			} else {
				$the_ret[0] = $row['email'];
			}
		} else {
		$the_ret[0] = "";
		}
		$the_ret[1] = $row['user'];
	return $the_ret;
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `id` = " . strip_tags($_GET['id']) . " LIMIT 1";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
if(mysql_num_rows($result) == 0) {
	exit();
	} else {
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_user = $row['requester'];
	$the_scope = $row['scope'];
	}

$theDetails = get_requester_details($by);
$the_email = $theDetails[0];
$the_requester = strip_tags($theDetails[1]);

$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Closed', `closed` = '" . $now . "', `cancelled` = '" . $now . "', `_by` = " . $by . ", `_from` = '" . $from . "', `_on` = '" . $now . "' WHERE `id` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
if(mysql_affected_rows() > 0) {
	$ret_arr[0] = 100;
	do_log($GLOBALS['LOG_CANCEL_REQUEST'], $_GET['id']);
	if ($the_email != "") {				// any addresses?
		$to_str = $the_email;
		$smsg_to_str = "";
		$subject_str = "Your request " . $row['scope'] . " has been cancelled";
		$text_str = "Your Request " . $row['scope'] . " has been cancelled\n\n"; 
		$text_str .= "Thank you for your informing us of the change\n\n";
		do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
		}				// end if/else ($addrs)	
	$addrs = notify_newreq($_SESSION['user_id']);		// returns array of adddr's for notification, or FALSE
	if ($addrs) {				// any addresses?
		$to_str = implode("|", $addrs);
		$smsg_to_str = "";
		$subject_str = "Service User request declined";
		$text_str = "Service User Request " . $row['scope'] . " has been cancelled\n\n"; 
		$text_str .= "This has been confirmed to Service User " . $the_requester . "\n\n";
		$text_str .= "The Service User email address is " . $the_email . "\n\n";
		do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
		}				// end if/else ($addrs)				
	} else {
	$ret_arr[0] = 999;
	}
print json_encode($ret_arr);
?>