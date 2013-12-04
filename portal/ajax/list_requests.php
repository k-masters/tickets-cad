<?php
/**
 * 
 * 
 * @package list_requests.php
 * @author John Doe <john.doe@example.com>
 * @since version
 * @version string
 */
@session_start();
require_once('../../incs/functions.inc.php');
require_once('../incs/portal.inc.php');
include('../../incs/html2text.php');

/**
 * br2nl
 * Insert description here
 *
 * @param $input
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}
	
/**
 * get_contact_details
 * Insert description here
 *
 * @param $the_id
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function get_contact_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret[] = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
		$the_ret[] = ($row['email'] != "") ? $row['email'] : "Unknown";
		$the_ret[] = ($row['email_s'] != "") ? $row['email_s'] : "Unknown";		
		$the_ret[] = ($row['phone_p'] != "") ? $row['phone_p'] : "Unknown";			
		$the_ret[] = ($row['phone_s'] != "") ? $row['phone_s'] : "Unknown";		
		}
	return $the_ret;
	}
	
//$where = ((!empty($_GET)) && (isset($_GET['id']))) ? "WHERE `requester` = " . strip_tags($_GET['id']): "WHERE `status` = 'Open' ";
$where = ((!empty($_GET)) && (isset($_GET['id']))) ? "WHERE `requester` = " . strip_tags($_GET['id']): " ";
$order = "ORDER BY `request_date`";
$order2 = "ASC";
$showall = ((isset($_GET['showall'])) && ($_GET['showall'] == 'yes')) ? true : false;
$where .= ($showall == false) ? " AND `r`.`status` != 'Closed' " : "";

$query = "SELECT *, 
		`r`.`id` AS `request_id`,
		`t`.`id` AS `tick_id`,
		`r`.`status` AS `req_status`,
		`t`.`status` AS `tick_status`,
		`r`.`phone` AS `req_phone`,
		`t`.`phone` AS `tick_phone`,
		`r`.`street` AS `req_street`,
		`r`.`city` AS `req_city`,
		`r`.`state` AS `req_state`,
		`r`.`to_address` AS `req_to_address`,
		`r`.`description` AS `req_description`,
		`r`.`comments` AS `req_comments`,
		`r`.`scope` AS `req_scope`,
		`t`.`street` AS `tick_street`,
		`t`.`city` AS `tick_city`,
		`t`.`state` AS `tick_state`,
		`t`.`to_address` AS `tick_to_address`,
		`t`.`description` AS `tick_description`,
		`t`.`comments` AS `tick_comments`,
		`t`.`scope` AS `tick_scope`,
		`a`.`id` AS `assigns_id`,
		`a`.`start_miles` AS `start_miles`,
		`a`.`end_miles` AS `end_miles`,
		`r`.`rec_facility` AS `recFacility`,
		`r`.`orig_facility` AS `origFacility`,
		`r`.`contact` AS `req_contact`,
		`r`.`lat` AS `r_lat`,
		`r`.`lng` AS `r_lng`,
		`t`.`lat` AS `t_lat`,
		`t`.`lng` AS `t_lng`,		
		`request_date` AS `request_date`,
		`tentative_date` AS `tentative_date`,		
		`accepted_date` AS `accepted_date`,
		`declined_date` AS `declined_date`,		
		`resourced_date` AS `resourced_date`,
		`completed_date` AS `completed_date`,	
		`closed` AS `closed`,
		`_on` AS `_on`,
		`r`.`_by` AS `r_by`,
		`t`.`_by` AS `t_by`,
		`a`.`dispatched` AS `dispatched`,
		`a`.`clear` AS `clear`		
		FROM `$GLOBALS[mysql_prefix]requests` `r`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON `a`.`ticket_id`=`r`.`ticket_id` 	
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON `r`.`ticket_id`=`t`.`id` 			
		{$where} GROUP BY `r`.`id` {$order} {$order2}";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
$i=0;
if (mysql_num_rows($result) == 0) { 				// 8/6/08
	$ret_arr[$i][0] = "No Current Requests";
	} else {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$miles = $row['end_miles'] - $row['start_miles'];
		$request_id = $row['request_id'];
		$requester = get_owner($row['requester']);
		$name = $row['the_name'];
		$phone = $row['req_phone'];		
		$contact = $row['req_contact'];
		$the_details = get_contact_details($row['requester']);
		$contact_email_p = $the_details[1];
		$contact_email_s = $the_details[2];			
		$contact_phone_p = $the_details[3];
		$contact_phone_s = $the_details[4];		
		$street = $row['req_street'];
		$city = $row['req_city'];
		$state = $row['req_state'];	
		$toAddress = $row['req_to_address'];
		$orig_facility = $row['origFacility'];				
		$rec_facility = $row['recFacility'];		
		$scope = $row['req_scope'];	
		$description = $row['req_description'];	
		$comments = $row['req_comments'];
		$lat = (($row['r_lat'] != "") && ($row['r_lat'] != NULL) && ($row['r_lat'] != 0.999999)) ? $row['r_lat'] : 0.999999;
		$lng = (($row['r_lng'] != "") && ($row['r_lng'] != NULL) && ($row['r_lng'] != 0.999999)) ? $row['r_lng'] : 0.999999;
		$status = (!is_service_user()) ? get_status_selection($request_id, $row['req_status']) : $row['req_status'];		
		$request_date = $row['request_date'];	
		$tentative_date = $row['tentative_date'];
		if(($tentative_date != "") && ($row['accepted_date'] == "") && ($row['resourced_date'] == "") && ($row['completed_date'] == "") && ($row['closed'] == "") && ($row['req_status'] != "Tentative")) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Tentative' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}			
		$accepted_date = $row['accepted_date'];	
		if(($accepted_date != "") && ($row['resourced_date'] == "") && ($row['completed_date'] == "") && ($row['closed'] == "") && ($row['req_status'] != "Accepted")) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Accepted' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}		
		$declined_date = $row['declined_date'];	
		if(($declined_date != "") && ($row['tentative_date'] == "") && ($row['accepted_date'] == "") && ($row['resourced_date'] == "") && ($row['completed_date'] == "") && ($row['closed'] == "") && ($row['req_status'] != "Declined")) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Declined' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}	
		$resourced_date = (($row['dispatched'] != "") || ($row['dispatched'] != NULL)) ? $row['dispatched'] : $row['resourced_date'];
		if(($row['dispatched'] != "") && ($row['dispatched'] != NULL) && ($row['resourced_date'] == NULL)) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Resourced', `resourced_date` = '" . mysql_format_date($row['dispatched']) . "' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}
		$completed_date = (($row['clear'] != "") || ($row['clear'] != NULL)) ? $row['clear'] : $row['completed_date'];
		if(($row['clear'] != "") && ($row['clear'] != NULL) && ($row['completed_date'] == NULL)) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Complete', `completed_date` = '" . mysql_format_date($row['clear']) . "' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}		
		$closed = $row['closed'];
		if(($row['tick_status'] == 1) && ($row['closed'] == NULL) && ($row['problemend'] != NULL)) {
			$update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Closed', `closed` = '" . mysql_format_date($row['problemend']) . "' WHERE `id` = " . $request_id;
			$result = mysql_query($update) or do_error($update, "", mysql_error(), basename( __FILE__), __LINE__);
			}				
		$updated_by = get_owner($row['r_by']);
		$updated = format_date_2(strtotime($row['_on']));		
		
		if ($row['req_status'] == 'Open') {
			$color = "background-color: #FFFF00; color: #000000;";
			} elseif ($row['req_status'] == 'Tentative') {
			$color = "background-color: #CC9900; color: #000000;";				
			} elseif ($row['req_status'] == 'Accepted') {
			$color = "background-color: #33CCFF; color: #000000;";			
			} elseif ($row['req_status'] == 'Resourced') {
			$color = "background-color: #00FF00; color: #000000;";			
			} elseif ($row['req_status'] == 'Completed') {
			$color = "background-color: #FFFFFF; color: #00FF00;";		
			} elseif ($row['req_status'] == 'Declined') {
			$color = "background-color: #FF9900; color: #FFFFFF;";	
			} elseif ($row['req_status'] == 'Closed') {
			$color = "background-color: #FFFFFF; color: #707070;";					
			} elseif ($row['cancelled'] != NULL) {
			$color = "background-color: red; color: yellow;";					
			} else {
			$color = "";				
			}

		if ($row['cancelled'] != NULL) {
			$color = "background-color: red; color: yellow;";	
			$status = "Cancelled";
			}
			
		$ret_arr[$i][0] = $request_id;		
		$ret_arr[$i][1] = $requester;
		$ret_arr[$i][2] = $name;
		$ret_arr[$i][3] = $phone;
		$ret_arr[$i][4] = $contact;
		$ret_arr[$i][5] = $contact_phone_p;
		$ret_arr[$i][6] = $contact_phone_s;
		$ret_arr[$i][7] = $contact_email_p;
		$ret_arr[$i][8] = $contact_email_s;
		$ret_arr[$i][9] = $street;
		$ret_arr[$i][10] = $city;
		$ret_arr[$i][11] = $state;
		$ret_arr[$i][12] = $rec_facility;		
		$ret_arr[$i][13] = $scope;	
		$ret_arr[$i][14] = $description;	
		$ret_arr[$i][15] = $comments;	
		$ret_arr[$i][16] = $status;	
		$ret_arr[$i][17] = $color;			
		$ret_arr[$i][18] = $request_date;	
		$ret_arr[$i][19] = $tentative_date;		
		$ret_arr[$i][20] = $accepted_date;		
		$ret_arr[$i][21] = $declined_date;		
		$ret_arr[$i][22] = $resourced_date;		
		$ret_arr[$i][23] = $completed_date;				
		$ret_arr[$i][24] = $closed;			
		$ret_arr[$i][25] = $updated;				
		$ret_arr[$i][26] = $updated_by;	
		$ret_arr[$i][27] = $miles;
		$ret_arr[$i][28] = $orig_facility;		
		$ret_arr[$i][29] = $lat;		
		$ret_arr[$i][30] = $lng;
		$ret_arr[$i][31] = $toAddress;
		$i++;
		} // end while	
	}				// end else

//dump($ret_arr);

print json_encode($ret_arr);
?>