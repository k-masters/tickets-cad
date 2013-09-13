<?php
/*
3/31/2013 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL	^ E_DEPRECATED);
require_once('../incs/functions.inc.php');		//7/28/10
require_once('incs/sp_functions.inc.php');		// 4/8/2013 

@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
$me = $_SESSION['user_unit_id'] ;		// possibly empty
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
	<meta charset="utf-8" />
	<title>Tickets SP <?php echo get_text("Calls");?></title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="./js/misc.js" type="text/javascript"></script> 

<?php
//		====================================	TOP		=========================================
	if ( ( isset($_POST['id'] ) ) && (  strlen ( $_POST['id'] ) > 0 )  ) {
																			/*	show the one record	*/
		$id_array = explode (",", $_POST['id_str']);
		$the_id = $id_array[intval($_POST['id'])];		// nth entry is record id
?>
			<script>
			
		    DomReady.ready(function() {
		    	var id_array = document.navForm.id_str.value.split(",");		
//				var timer = setInterval(function(){getLocation(<?php echo $me;?>)}, (60*1000)) ;		// get position one per minute
				});
		
//			do_set_time("dispatched", {$the_id}, 0)
			function do_set_time (column, record_id, cell_id, function_id) {		// ajax call to set selected dispatch time
		
				function the_callback(req) {
					document.getElementById(cell_id).innerHTML=req.responseText;
					CngClass(cell_id, "bright");									// highlight for 2 seconds
					setTimeout ( function() {  CngClass(cell_id, "plain")}, 3000);
					}		// end function the_callback()
		
				var params = "the_column="+ column + "&record_id=" +record_id + "&function_id=" + function_id;		// 
				var url = "./ajax/set_disp_times.php";
		 		sendRequest(url,the_callback, params);		//  issue AJAX request
		 		}		// end function do set_time
		
		</script>
<?php
if (intval(get_variable('broadcast'))==1) {	
	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013 
	}
?>		
		</head>
		<body>		
<?php		
			function set_click_td ($rcd_id, $cell_id, $the_str) {
				$click_str = "onclick = \"do_set_time('{$the_str}', {$rcd_id}, '{$cell_id}', 0)\"";
				return "<td id = '{$cell_id}' {$click_str} class='click'><b>Set</b></td></tr>\n";
				}
				
			require_once('incs/header.php');
		 	$id_array = explode (",", $_POST['id_str']);
		 	$larrow = (intval($_POST['id'] == 0))? "" : "&laquo;&nbsp;" ;	// suppress display if at origin
			$rarrow = (intval($_POST['id']) == count ($id_array) -1 )? "" : "&nbsp;&raquo;" ;	// suppress display if at end
?>
		<div style='float:left; '>
			<div id = "left-side" onclick = 'navBack();' style = "position:fixed; left: 50px; top:125px; margin-left:100px; font-size: 4.0em; opacity:0.50;"><?php echo $larrow; ?></div>
		</div>
		<div style='float:right; '>
			<div id = "right-side" onclick = 'navFwd ();' style = "position:fixed; right: 25px; top:125px;font-size: 4.0em; opacity:0.5;"><?php echo $rarrow; ?></div>
		</div>
<?php	
			$the_count =  count ($id_array);
			echo "\n<br/><br/><center><h2>Selected " . get_text("Responder") . " (of {$the_count} )</h2>\n";		
		
			$div_height = $_SESSION['scr_height'] - 120;								// nav bars			
			$div_width = floor($_SESSION['scr_width'] * .6) ;							// allow for nav arrows		
			echo "<div style = 'height:{$div_height}px; width:auto; overflow: auto; width:{$div_width}px;'><br />";	
		
			echo "<table border=1>\n";
			$query = get_resp_sql ($the_id);			

			$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			$hides = array("callsign");
			$row = stripslashes_deep(mysql_fetch_array($result)) ;
			for ($i=0; $i< mysql_num_fields($result); $i++) {						// each field
				if ( ( !empty($row[$i] ) ) && ( !in_array(mysql_field_name($result, $i), $hides ) ) )  {	// suppress metadata and hides
				
					if ( ! (empty($row[$i]) ) ) {			
						$fn = get_text ( ucfirst(mysql_field_name($result, $i ) ) );
						echo "<tr><td>{$fn}:</td><td>";

						switch (mysql_field_name($result, $i) ) {
							case "contact via":
//								if (is_email($row[$i]) {}
							
								echo $row[$i];
								break;

							case "severity":
								echo get_severity($row[$i]);
								break;

							case "APRS":
							case "instamapper":
							case "open GTS":
							case "locateA":
							case "g tracker":
							case "t tracker":
							case "latitude":
								echo "{$row['callsign']}";
								break;
								
							default: 
								echo $row[$i];
							};				// end switch ()
							
						echo "</td></tr>\n";
						}			
					}				// end meta-data?
				}		// end for ($i...) each row element
			echo "</table>\n";

			echo "</div><br/>";
			
			$id_array = explode (",", $_POST['id_str']);
			}
//	==============================================	LIST	================================================			
else {								
		
		$query = "SELECT `r`.`id`, 
			`r`.`handle` AS `unit`, 
			`r`.`mobile` AS `m`, 
			`y`.`description` AS `type`, 
			`s`.`description` AS `status`,
			`r`.`lat`, `r`.`lng`, 
			`s`.`dispatch` AS `calls`,
			`r`.`description` AS `description`, 
			CONCAT_WS(' ',`street`,`city`) AS `loc`,
			SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `as of`
			FROM `$GLOBALS[mysql_prefix]responder` `r` 
			LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `y` 	ON ( `r`.`type` = `y`.`id` )	
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON ( `r`.`un_status_id` = `s`.`id` ) 	
			ORDER BY `handle` ASC ";			
		
		$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		
		$sep = $_POST['id_str'] = "";
		while ($in_row = stripslashes_deep(mysql_fetch_array($result))) {		// to comma sep'd string of id's
		 	$_POST['id_str'] .= $sep . $in_row['id'];
		 	$sep = ",";
		 	}
		@mysql_data_seek($result, 0) ;		// reset for pass 2
		
?>
		<script>
		    DomReady.ready(function() {
		    	document.getElementById("fr").style.display = "none";
				var timer = setInterval(function(){getLocation(<?php echo $me;?>)}, (60*1000)) ;		// get position one per minute		    	
				});
		</script>
		</head>
		<body><center>
<?php
			require_once('incs/header.php');	
			if ( mysql_num_rows($result) == 0 ) {
				echo "<div style = 'text-align:center; margin-top:100px;'><h2>No " . get_text("Responders") . " in database</h2></div>\n";		
				}
			else {
				$hides = array("the_group", "as_of", "assign_id", "id", "lat", "lng");		// hide these columns
				echo "<br/><br/><br/><br/><br/>";
				$top_row = get_text("Responders") . " - <i>click for details</i>";
				echo sp_show_list($result, $hides, basename(__FILE__), $top_row ) . "\n" ;	
				echo "<br/><br/>";			// show bottom rows
				}
			} 		// end else {} ==================================================


	require_once('incs/footer.php');	
	$idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 		value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>" />
</form>
<script>
	function navTo (url, id) {
		var ts = Math.round((new Date()).getTime() / 1000);
		document.navForm.action = url +"?rand=" + ts;
		document.navForm.id.value = (id == null)? "": id;
		document.navForm.submit();
		}				// end function navTo ()
</script>
</body>
</html>
