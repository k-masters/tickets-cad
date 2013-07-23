<?php
/*
10/25/09 Created from functions_maj.inc.php
10/26/09 Added Facilities and hide and show unavailable units from functions_major.inc.php
10/27/09 Added check for scheduled incidents being due and bring to current situation screen if due and mark with * in list.
10/27/09 Added Booked date to Info Window tab 1.
11/27/09 corrections to indexing
3/27/10 added 'elapsed time' to IW
4/21/10 added closed incidents selection by time period, call history incident display replacing infowin
8/13/10 map.setUIToDefault();										// 

*/
error_reporting(E_ALL);


//	dump ( $_GET);

//	snap(basename(__FILE__), __LINE__);
//	{ -- dummy

		$now_num = (time() - get_variable('delta_mins')*60);
		$temp = explode ("-", mysql_format_date($now_num));		// 2009-07-23 07:20:00
		$temp1 = explode (" ", $temp[2]);
		$now_day = (integer) $temp1[0];
		$now_mon = (integer) $temp[1];
		$now_year = (integer) $temp[0];
		for ($i=0; $i<7; $i++) {												// find time() at last Monday
			$temp_monday = mktime(0, 0, 0, date("m"), date("d")-$i, date("Y"));
			if (date('w', $temp_monday) == 1){
				break;
				}
			}
		$monday =  $temp_monday;

	function full_scr($sort_by_field='',$sort_value='') {	// list tickets ===================================================
		global $now_num, $now_day, $now_mon, $now_year, $monday;
		extract ($_GET);
		$func = (isset($func))? $func : 0; 
		global $istest;
	//	$dzf = get_variable('def_zoom_fixed');			// 4/2/09
		$cwi = get_variable('closed_interval');			// closed window interval in hours
		$captions = array("Current situation", "Incidents closed today", "Incidents closed yesterday+", "Incidents closed this week", "Incidents closed last week", "Incidents closed last week+", "Incidents closed this month", "Incidents closed last month", "Incidents closed this year", "Incidents closed last year");
	
		$heading = $captions[$func];
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = {$GLOBALS['STATUS_CLOSED']} ";		// 10/26/09
	
			$result_ct = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_closed = mysql_num_rows($result_ct); 
			unset($result_ct);
	
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = {$GLOBALS['STATUS_SCHEDULED']} ";		// 10/26/09
			$result_scheduled = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_scheduled = mysql_num_rows($result_scheduled); 
			unset($result_scheduled);
	
?>
	<TABLE BORDER=0 STYLE= "margin-top:0;">
		<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header'><?php print get_variable('map_caption') . " - " .  $heading;?> <SPAN ID='sev_counts' STYLE = 'margin-left: 40px'></SPAN></FONT></TD></TR>	<!-- 1/17/09 -->
		<TR>
			<TD COLSPAN='99' CLASS='td_label' width="100%">
<?php
	@session_start();							// 
	
	$map_width = round($_SESSION['scr_width'] * 0.98);
	$map_height = round($_SESSION['scr_height'] *0.72);

//	$captions = array("error - 0", "error - 1", "Closed - Today", "Open", "Closed - Yesterday+", "Closed - This week", "Closed - Last week", "Closed - Last week+", "Closed - This month", "Closed - Last month", "Closed - This year", "Closed - Last year");
	$by_severity = array(0, 0, 0);				// counters
?>
			<DIV ID='map' STYLE='WIDTH:<?php print $map_width;?>PX; HEIGHT: <?php print $map_height;?>PX'></DIV>
			</TD></TR>
			<TR>
			<TD ALIGN='center' ><BR />
				<SPAN STYLE =  'margin-left: 100px'><B>Units</B>:<IMG SRC = './icons/sm_white.png' BORDER=0><IMG SRC = './icons/sm_black.png' BORDER=0></SPAN>
	
				<SPAN ID="show_it" STYLE="display: none" onClick = "do_show_Units();"><U>Show</U></SPAN>
				<SPAN ID="hide_it" STYLE="display: ''" onClick = "do_hide_Units();"><U>Hide</U></SPAN>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN ID="hide_unavail" STYLE="display: ''" onClick = "hide_unit_stat_unavail();"><U>Hide unavailable</U></SPAN>	<!-- 10/26/09 -->
				<SPAN ID="show_unavail" STYLE="display: ''" onClick = "show_unit_stat_unavail();"><U>Show unavailable</U></SPAN>	<!-- 10/26/09 -->
				<BR /><BR /></TD>
			<TD STYLE="WIDTH:4PX; background-color:#DEE3E7;"></TD>
				
			<TD ALIGN='center'><BR />
				<B>Facilities</B>:<IMG SRC = './icons/sm_shield_green.png' BORDER=0><IMG SRC = './icons/sm_square_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 10/26/09 -->
	
				<SPAN ID="hide_fac" STYLE="display: ''" onClick = "hide_Facilities();"><U>Hide</U></SPAN>
				<SPAN ID="show_fac" STYLE="display: none" onClick = "show_Facilities();"><U>Show</U></SPAN>
	
				<BR /><BR /></TD>
			<TD STYLE="WIDTH:4PX; background-color:#DEE3E7;"></TD>
			<TD ALIGN='left'><BR />
	
			<SPAN ID="incidents" STYLE="display: inline-block; margin-left: 100px">
				<B>Incident Priority</B>:<IMG SRC = './icons/sm_blue.png' BORDER=0><IMG SRC = './icons/sm_green.png' BORDER=0><IMG SRC = './icons/sm_red.png' BORDER=0>&nbsp;&nbsp;	<!-- 10/26/09 -->
				<A HREF="#" onClick = "hideGroup(1)">Typical: 	<IMG SRC = './icons/sm_blue.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp; <!-- 1/9/09 -->
				<A HREF="#" onClick = "hideGroup(2)">	High: 	<IMG SRC = './icons/sm_green.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;
				<A HREF="#" onClick = "hideGroup(3)">Highest: 	<IMG SRC = './icons/sm_red.png' BORDER=0></A>
				</SPAN>
			<SPAN ID="show_all_icon" STYLE="display: none; margin-left: '40px'" onClick = "show_All()">Show all: <IMG SRC = './markers/sm_white.png' BORDER=0></SPAN>
			</NOBR></CENTER>
			<BR /><BR />
	
			</TD>
		</TR>
<SCRIPT>
	function show_btns_closed() {
		$('btn_go').style.display = 'inline';
		$('btn_can').style.display = 'inline';
		}
	function hide_btns_closed() {
		$('btn_go').style.display = 'none';
		$('btn_can').style.display = 'none';
		document.dummy.frm_interval.selectedIndex=99;
		}
</SCRIPT>
	<FORM name='dummy' STYLE='block-inline'>
	<TR CLASS='even'><TD COLSPAN=99 ALIGN = 'center' ID = 'misc'><BR /><B><NOBR>
		<SPAN onClick='doGrid()'><U>Grid</U></SPAN>
		<SPAN onClick='doTraffic()' STYLE = 'margin-left: 60px'><U>Traffic</U></SPAN>
<?php
		if((empty($closed)) && ($num_closed > 0)) {					// 10/26/09  added button, 10/21/09 added check for closed incidents on the database
			echo "<SPAN STYLE =  'margin-left: 60px'><U>Change display</U>&nbsp;&raquo;&nbsp;</SPAN>";
			echo "\n\t\t <SELECT NAME = 'frm_interval' onChange = 'document.to_all.func.value=this.value; show_btns_closed();'>
				<OPTION VALUE='99' SELECTED>Select</OPTION>
				<OPTION VALUE='0'>Current situation</OPTION>
				<OPTION VALUE='1'>Incidents closed today</OPTION>
				<OPTION VALUE='2'>Incidents closed yesterday+</OPTION>
				<OPTION VALUE='3'>Incidents closed this week</OPTION>
				<OPTION VALUE='4'>Incidents closed last week</OPTION>
				<OPTION VALUE='5'>Incidents closed last week+</OPTION>
				<OPTION VALUE='6'>Incidents closed this month</OPTION>
				<OPTION VALUE='7'>Incidents closed last month</OPTION>
				<OPTION VALUE='8'>Incidents closed this year</OPTION>
				<OPTION VALUE='9'>Incidents closed last year</OPTION>
				</SELECT>\n";
			echo "<SPAN ID = 'btn_go' onClick='document.to_all.submit()' STYLE = 'margin-left: 10px; display:none'><U>Go</U></SPAN>";
			echo "<SPAN ID = 'btn_can'  onClick='hide_btns_closed()' STYLE = 'margin-left: 10px; display:none'><U>Cancel</U></SPAN>";

			}
?>
			
	
		<SPAN onClick = "opener.focus()" STYLE =  'margin-left: 60px'><U>Back</U></SPAN>
		<A HREF="mailto:shoreas@Gmail.com?subject=Comment%20on%20Tickets%20Dispatch%20System"><SPAN STYLE = 'margin-left: 20px; font-size:10px; '><U>Contact us</U> <IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom; margin-left: 10px;"></SPAN></A>
		<SPAN onClick = "window.close();" STYLE =  'margin-left: 60px'><U>Close</U></SPAN></NOBR>
		</B><BR /><BR />
		</TD></TR>
		</FORM>
		</TABLE>
			
		<FORM NAME='view_form' METHOD='get' ACTION='units.php'>
		<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
		<INPUT TYPE='hidden' NAME='view' VALUE='true'>
		<INPUT TYPE='hidden' NAME='id' VALUE=''>
		</FORM>
	
	<SCRIPT>
		function isNull(val) {								// checks var stuff = null;
			return val === null;
			}
	
		function to_str(instr) {			// 0-based conversion - 2/13/09
	//		alert("143 " + instr);
			function ord( string ) {
			    return (string+'').charCodeAt(0);
				}
	
			function chr( ascii ) {
			    return String.fromCharCode(ascii);
				}
			function to_char(val) {
				return(chr(ord("A")+val));
				}
	
			var lop = (instr % 26);													// low-order portion, a number
			var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
			return hop+to_char(lop);
			}
	
		function sendRequest(url,callback,postData) {								// 2/14/09
			var req = createXMLHTTPObject();
			if (!req) return;
			var method = (postData) ? "POST" : "GET";
			req.open(method,url,true);
			req.setRequestHeader('User-Agent','XMLHTTP/1.0');
			if (postData)
				req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
			req.onreadystatechange = function () {
				if (req.readyState != 4) return;
				if (req.status != 200 && req.status != 304) {
<?php
		if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
	?>
					return;
					}
				callback(req);
				}
			if (req.readyState == 4) return;
			req.send(postData);
			}
	
		var XMLHttpFactories = [
			function () {return new XMLHttpRequest()	},
			function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
			function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
			function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
			];
	
		function createXMLHTTPObject() {
			var xmlhttp = false;
			for (var i=0;i<XMLHttpFactories.length;i++) {
				try {
					xmlhttp = XMLHttpFactories[i]();
					}
				catch (e) {
					continue;
					}
				break;
				}
			return xmlhttp;
			}
	
	if (GBrowserIsCompatible()) {
	
		$("map").style.backgroundImage = "url('http://maps.google.com/staticmap?center=<?php echo get_variable('def_lat');?>,<?php echo get_variable('def_lng');?>&zoom=<?php echo get_variable('def_zoom');?>&size=<?php echo get_variable('map_width');?>x<?php echo get_variable('map_height');?>&key=<?php echo get_variable('gmaps_api_key');?> ')";
	
		var colors = new Array ('odd', 'even');
	
		function drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity) {		// 8/19/09
		
	//		drawCircle(53.479874, -2.246704, 10.0, "#000080", 1, 0.75, "#0000FF", .5);
	
			var d2r = Math.PI/180;
			var r2d = 180/Math.PI;
			var Clat = radius * 0.014483;
			var Clng = Clat/Math.cos(lat * d2r);
			var Cpoints = [];
			for (var i=0; i < 33; i++) {
				var theta = Math.PI * (i/16);
				Cy = lat + (Clat * Math.sin(theta));
				Cx = lng + (Clng * Math.cos(theta));
				var P = new GPoint(Cx,Cy);
				Cpoints.push(P);
				}
			var polygon = new GPolygon(Cpoints, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity);
			map.addOverlay(polygon);
			}
	
		function hideGroup(color) {							// 8/7/09 Revised function to correct incorrect display
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if (gmarkers[i].id == color) {
						gmarkers[i].show();
						}
					else {
						gmarkers[i].hide();			// 1/11/09
						}
					}		// end if (gmarkers[i])
				} 	// end for ()
			$("show_all_icon").style.display = "inline-block";
			$("incidents").style.display = "inline-block";
	
			}			// end function
	
	
		function show_All() {						// 8/7/09 Revised function to correct incorrect display
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					gmarkers[i].show();
					}
				} 	// end for ()
			$("show_all_icon").style.display = "none";
			$("incidents").style.display = "inline-block";
			}			// end function
	
	
		function show_Units() {						// 8/7/09 Revised function to correct incorrect display
			for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0 - 2/12/09
				if (gmarkers[i]) {
					if ((gmarkers[i].id == 0) || (gmarkers[i].id == 4)) {
						gmarkers[i].show();
						}
					else {
	//					gmarkers[i].hide();						// hide incidents - 1/8/09
						}
					}		// end if (gmarkers[i])
				} 	// end for ()
			$("incidents").style.display = "inline-block";
			$("show_all_icon").style.display =	"inline-block";
			$('show_it').style.display='none';
			$('hide_it').style.display='inline';
			}
	
		function hide_Units () {								// 10/17/08
			for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0
				if (gmarkers[i]) {
					if ((gmarkers[i].id == 0) || (gmarkers[i].id == 4)) {			// 8/7/09 Revised function to correct incorrect display
						gmarkers[i].hide();
						}
					else {
						gmarkers[i].show();
						}
					}		// end if (gmarkers[i])
				} 	// end for ()
			$("incidents").style.display = "inline-block";
			$("show_all_icon").style.display =	"inline-block";
			$("show_it").style.display=			"inline";				// 12/02/09
			$("hide_it").style.display=			"none";
			}				// end function hide_units ()
	
		function hide_unit_stat_unavail() {								// 10/26/09
			for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0
				if (gmarkers[i]) {
					if (gmarkers[i].stat == 1) {
						gmarkers[i].hide();
						}
					else {
						gmarkers[i].show();
						}
					}		// end if (gmarkers[i])
				} 	// end for ()
			$("incidents").style.display = "inline-block";
			$("show_all_icon").style.display =	"inline-block";
			$("show_unavail").style.display=			"inline";
			$("hide_unavail").style.display=			"none";
			}				// end function hide_unit_stat_unavail ()		
			
		function show_unit_stat_unavail() {								// 10/26/09
			for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0
				if (gmarkers[i]) {
					gmarkers[i].show();
					}
				} 	// end for ()
			$("incidents").style.display = "inline-block";
			$("show_all_icon").style.display =	"inline-block";
			$("show_unavail").style.display=			"none";
			$("hide_unavail").style.display=			"inline";
			}				// end function hide_unit_stat_unavail ()			
	
		function do_hide_Units() {						// 2/14/09
			var params = "f_n=show_hide_unit&v_n=h&sess_id=<?php print get_sess_key(basename(__FILE__) . __LINE__); ?>";					// flag 1, value h
	//		var url = "persist.php";
			var url = "do_session_get.php";
			sendRequest (url, h_handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
			}			// end function do_hide_Units()
	
		function hide_Facilities() {								// 8/1/09
			for (var i = 0; i < fmarkers.length; i++) {			// traverse gmarkers array for icon type==0
				if (fmarkers[i]) {
						fmarkers[i].hide();
						}
				} 	// end for ()
			$("hide_fac").style.display = "none";
			$("show_fac").style.display = "inline-block";
			}				// end function hide_Facilities ()
	
		function show_Facilities () {								// 8/1/09
			for (var i = 0; i < fmarkers.length; i++) {			// traverse gmarkers array for icon type==0
				if (fmarkers[i]) {
						fmarkers[i].show();
						}
				} 	// end for ()
			$("hide_fac").style.display = "inline-block";
			$("show_fac").style.display = "none";
			}				// end function show_Facilities ()
	
	
		function h_handleResult(req) {					// the 'called-back' persist function - hide
			hide_Units();
			}
	
		var starting = false;
	
		function do_mail_fac_win(id) {			// Facility email 9/22/09
			if(starting) {return;}					
			starting=true;	
			var url = "do_fac_mail.php?fac_id=" + id;	
			newwindow_in=window.open (url, 'Email_Window',  'titlebar, resizable=1, scrollbars, height=300,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
			if (isNull(newwindow_in)) {
				alert ("This requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_in.focus();
			starting = false;
			}
	
		function do_show_Units() {
			var params = "f_n=show_hide_unit&v_n=s&sess_id=<?php print get_sess_key(basename(__FILE__) . __LINE__); ?>";					// flag 1, value s
			var url = "persist.php";
			sendRequest (url, s_handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
			}			// end function do notify()
	
		function s_handleResult(req) {					// the 'called-back' persist function - show
			show_Units();
			}
	
		function createMarker(point, tabs, color, stat, id, sym) {					// Creates marker and sets up click event infowindow
			points = true;
			var icon = new GIcon(baseIcon);
			var icon_url = "./icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + sym;				// 1/6/09
			icon.image = icon_url;
	
			var marker = new GMarker(point, icon);
			marker.id = color;				// for hide/unhide
			marker.stat = stat;				// 10/21/09
	
			GEvent.addListener(marker, "click", function() {					// here for icon click
	//			alert("389 " + (id) );
				if (ticket_ids[(id-1)]) {
					open_tick_window (ticket_ids[(id-1)]);
					}
				else {
	//				alert("389 " + id);
	//				alert(ticket_ids[(id-1)]);
					map.closeInfoWindow();
					which = id;
					gmarkers[which].hide();
					marker.openInfoWindowTabsHtml(infoTabs[id]);
		
					setTimeout(function() {											// wait for rendering complete - 11/6/08
						if ($("detailmap")) {				// 10/9/08
							var dMapDiv = $("detailmap");
							var detailmap = new GMap2(dMapDiv);
							detailmap.addControl(new GSmallMapControl());
							detailmap.setCenter(point, 17);  						// larger # = closer
							detailmap.addOverlay(marker);
							}
						else {
		//					alert(62);
		//					alert($("detailmap"));
							}
						},3000);				// end setTimeout(...)
					}});				// end function(marker, point)
			gmarkers[id] = marker;							// marker to array for side_bar click function
			infoTabs[id] = tabs;							// tabs to array
			if (!(map_is_fixed)){
				bounds.extend(point);
				}
			return marker;
			}				// end function create Marker()
	
		var the_grid;
		var grid = false;
		function doGrid() {
			if (grid) {
				map.removeOverlay(the_grid);
				}
			else {
				the_grid = new LatLonGraticule();
				map.addOverlay(the_grid);
				}
			grid = !grid;
			}			// end function doGrid
	
	    var trafficInfo = new GTrafficOverlay();
	    var toggleState = true;
	
		function doTraffic() {				// 10/16/08
			if (toggleState) {
		        map.removeOverlay(trafficInfo);
		     	}
			else {
		        map.addOverlay(trafficInfo);
		    	}
	        toggleState = !toggleState;			// swap
		    }				// end function doTraffic()
	
	
		var icons=[];						// note globals
		icons[0] = 											 4;	// units white
		icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>+1] = 1;	// blue
		icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>+1] = 2;	// yellow
		icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  3;	// red
		icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+2] =  0;	// black
	
		var map;
		var center;
		var zoom;
		var points = false;
<?php
	
	$dzf = get_variable('def_zoom_fixed');
	print "\tvar map_is_fixed = ";
	print (($dzf==1) || ($dzf==3))? "true;\n":"false;\n";
	
	$kml_olays = array();
	$dir = "./kml_files";
	$dh  = opendir($dir);
	$i = 1;
	$temp = explode ("/", $_SERVER['REQUEST_URI']);
	$temp[count($temp)-1] = "kml_files";				//
	$server_str = "http://" . $_SERVER['SERVER_NAME'] .":" .  $_SERVER['SERVER_PORT'] .  implode("/", $temp) . "/";
	while (false !== ($filename = readdir($dh))) {
		if (!is_dir($filename)) {
		    echo "\tvar kml_" . $i . " = new GGeoXml(\"" . $server_str . $filename . "\");\n";
		    $kml_olays[] = "map.addOverlay(kml_". $i . ");";
		    $i++;
		    }
		}
	//	dump ($kml_olays);
?>
	
	function do_mail_win() {			// 6/13/09
		if(starting) {return;}					
		starting=true;	
	
		newwindow_um=window.open('do_unit_mail.php', 'E_mail_Window',  'titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
	
		if (isNull(newwindow_um)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_um.focus();
		starting = false;
		}
	
	function open_tick_window (id) {				// 4/12/10
		var url = "single.php?ticket_id="+ id;
		var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		tickWindow.focus();
		}
	
	function do_add_note (id) {				// 8/12/09
		var url = "add_note.php?ticket_id="+ id;
		var noteWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100');
		noteWindow.focus();
		}
		
	function do_track(callsign) {		
		if (parent.frames["upper"].logged_in()) {
	//		if(starting) {return;}					// 6/6/08
	//		starting=true;
			map.closeInfoWindow();
			var width = <?php print get_variable('map_width');?>+360;
			var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
			var url = "track_u.php?source="+callsign;
	
			newwindow=window.open(url, callsign,  spec);
			if (isNull(newwindow)) {
				alert ("Track display requires popups to be enabled. Please adjust your browser options.");
				return;
				}
	//		starting = false;
			newwindow.focus();
			}
		}				// end function do track()
	
	//function do_popup(id) {					// added 7/9/09
	//	if (parent.frames["upper"].logged_in()) {
	//		map.closeInfoWindow();
	//		var width = <?php print get_variable('map_width');?>+32;
	//		var spec ="titlebar, resizable=1, scrollbars, height=590,width=" + width + ",status=no,toolbar=no,menubar=no,location=0, left=100,top=300,screenX=100,screenY=300";
	//		var url = "incident_popup.php?id="+id;
	//
	//		newwindow=window.open(url, id, spec);
	//		if (isNull(newwindow)) {
	//			alert ("Popup Incident display requires popups to be enabled. Please adjust your browser options.");
	//			return;
	//			}
	////		starting = false;
	//		newwindow.focus();
	//		}
	//	}				// end function do popup()
	
		var ticket_ids = [];
		var gmarkers = [];
		var fmarkers = [];
		var infoTabs = [];
		var facinfoTabs = [];
		var which;
		var i = 0;			// sidebar/icon index
	
		$("show_unavail").style.display=			"none";				// 10/21/09
		$("hide_unavail").style.display=			"inline";
	
		map = new GMap2($("map"));		// create the map
<?php
	$maptype = get_variable('maptype');	// 08/02/09
	
		switch($maptype) { 
			case "1":
			break;
	
			case "2":?>
			map.setMapType(G_SATELLITE_MAP);<?php
			break;
		
			case "3":?>
			map.setMapType(G_PHYSICAL_MAP);<?php
			break;
		
			case "4":?>
			map.setMapType(G_HYBRID_MAP);<?php
			break;
	
			default:
			print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}
?>
	
//		map.addControl(new GSmallMapControl());					// 8/25/08
		map.setUIToDefault();									// 8/13/10

		map.addControl(new GMapTypeControl());
	
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
	
		mapBounds=new GLatLngBounds(map.getBounds().getSouthWest(), map.getBounds().getNorthEast());		// 4/4/09
	
		var bounds = new GLatLngBounds();						// create  bounding box
	<?php if (get_variable('terrain') == 1) { ?>
		map.addMapType(G_PHYSICAL_MAP);
	<?php } ?>
	
		map.enableScrollWheelZoom();
	
		var baseIcon = new GIcon();
		baseIcon.shadow = "./markers/sm_shadow.png";		// ./markers/sm_shadow.png
	
		baseIcon.iconSize = new GSize(20, 34);
		baseIcon.shadowSize = new GSize(37, 34);
		baseIcon.iconAnchor = new GPoint(9, 34);
		baseIcon.infoWindowAnchor = new GPoint(9, 2);
		baseIcon.infoShadowAnchor = new GPoint(18, 25);
		GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
			map.setCenter(center,zoom);
			map.addOverlay(gmarkers[which])
			});
	
<?php
		$order_by =  (!empty ($get_sortby))? $get_sortby: $_SESSION['sortorder']; // use default sort order?
																					//fix limits according to setting "ticket_per_page"
		$limit = "";
		if ($_SESSION['ticket_per_page'] && (check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]ticket`") > $_SESSION['ticket_per_page']))	{
			if ($_GET['offset']) {
				$limit = "LIMIT $_GET[offset],$_SESSION[ticket_per_page]";
				}
			else {
				$limit = "LIMIT 0,$_SESSION[ticket_per_page]";
				}
			}
		$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
		$time_back = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($cwi*3600));

		switch($func) {				//9/29/09 Added capability for Special Incidents 10/27/09 changed to bring scheduled incidents to front when due.
				case 0: 
					$where = "WHERE `status`='{$GLOBALS['STATUS_OPEN']}' OR 
						(`status`='{$GLOBALS['STATUS_SCHEDULED']}') OR 
						(`status`='{$GLOBALS['STATUS_CLOSED']}'  AND `problemend` >= '{$time_back}')";
					break;
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
					$the_start = get_start($func);		// mysql timestamp format 
					$the_end = get_end($func);
					$where = " WHERE `status`='{$GLOBALS['STATUS_CLOSED']}' AND `problemstart` BETWEEN '{$the_start}' AND '{$the_end}' ";
					break;				
				default: print "error - error - error - error " . __LINE__;
//				default: $where = "WHERE `status`='{$GLOBALS['STATUS_OPEN']}' OR (`status`='3'  AND `booked_date` <= (NOW() - INTERVAL 6 HOUR))"; break;
				}				// end switch($func) 
	
		$query = "SELECT *, UNIX_TIMESTAMP(problemstart) AS `problemstart`, 
			UNIX_TIMESTAMP(problemend) AS `problemend`,
			UNIX_TIMESTAMP(booked_date) AS booked_date,UNIX_TIMESTAMP(date) AS `date`,
			UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.updated) AS `updated`, 
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `ticket_id`, 
			`$GLOBALS[mysql_prefix]in_types`.type AS `type`,
			`$GLOBALS[mysql_prefix]in_types`.`id` AS `t_id`,
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
			`$GLOBALS[mysql_prefix]ticket`.lat AS `lat`,
			`$GLOBALS[mysql_prefix]ticket`.lng AS `lng`,
			`$GLOBALS[mysql_prefix]facilities`.lat AS `fac_lat`,
			`$GLOBALS[mysql_prefix]facilities`.lng AS `fac_lng`, 
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name` 
			FROM `$GLOBALS[mysql_prefix]ticket`
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id` 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON `$GLOBALS[mysql_prefix]ticket`.rec_facility=`$GLOBALS[mysql_prefix]facilities`.`id` 
			$where $restrict_ticket 
			ORDER BY `status` DESC, `severity` DESC, `$GLOBALS[mysql_prefix]ticket`.`id` ASC";		// 2/2/09, 10/28/09
	

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//		dump ($query);
								// major while ... starts here
	
		while ($row = stripslashes_deep(mysql_fetch_array($result))) 	{
			$by_severity[$row['severity']] ++;
			print "\t\t ticket_ids.push({$row['ticket_id']});\n";
		
			switch($row['status']) {				//10/27/09 to Add star to scheduled incidents on current situation screen
				case 1: $sp = ""; break;
				case 2: $sp = ""; break;
				case 3: $sp = "*"; break;
				default: $sp = ""; break;
				}
		
				print "\t\tvar scheduled = '$sp';\n";
?>
		//		var sym = i.toString();						// for sidebar and icon
				var sym = scheduled + (i+1).toString();					// for sidebar and icon
		
<?php
				$the_id = $row[0];
		
				if ($row['tick_descr'] == '') $row['tick_descr'] = '[no description]';	// 8/12/09
				if (get_variable('abbreviate_description'))	{	//do abbreviations on description, affected if neccesary
					if (strlen($row['tick_descr']) > get_variable('abbreviate_description')) {
						$row['tick_descr'] = substr($row['tick_descr'],0,get_variable('abbreviate_description')).'...';
						}
					}
				if (get_variable('abbreviate_affected')) {
					if (strlen($row['affected']) > get_variable('abbreviate_affected')) {
						$row['affected'] = substr($row['affected'],0,get_variable('abbreviate_affected')).'...';
						}
					}
				switch($row['severity'])		{		//color tickets by severity
				 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
					case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
					default: 				$severityclass='severity_normal'; break;
					}
		
				$street = empty($row['street'])? "" : $row['street'] . "<BR/>" . $row['city'] . " " . $row['state'] ;
				$todisp = (is_guest())? "": "&nbsp;<A HREF='routes.php?ticket_id=" . $the_id . "'><U>Dispatch</U></A>";	// 8/2/08
		
				if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
					$strike = "<strike>"; $strikend = "</strike>";
					}
				else { $strike = $strikend = "";}
				$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08
		
				$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>$strike" . shorten($row['scope'], 48)  . "$strikend</B></TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
				if (good_date($row['booked_date'])) {	//4/13/10
					$tab_1 .= "<TR CLASS='odd'><TD>Booked Date:</TD><TD>" . format_date($row['booked_date']) . "</TD></TR>";
					}			
				$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>$street</TD></TR>";
				$end_date = (intval($row['problemend'])> 1)? $row['problemend']:  (time() - (get_variable('delta_mins')*60));				
				$elapsed = my_date_diff($row['problemstart'], $end_date);		// 5/13/10
				$tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>Status:</TD><TD ALIGN='left'>" . get_status($row['status']) . "&nbsp;&nbsp;&nbsp;($elapsed)</TD></TR>";	// 3/27/10
				if (!(empty($row['fac_name']))) {		
					$tab_1 .= "<TR CLASS='even'><TD>Receiving Facility:</TD><TD>" . shorten($row['fac_name'], 30)  . "</TD></TR>";	//10/28/09
					}
		
				$utm = get_variable('UTM');
				if ($utm==1) {
					$coords =  $row['lat'] . "," . $row['lng'];																	// 8/12/09
					$tab_1 .= "<TR CLASS='even'><TD>UTM grid:</TD><TD>" . toUTM($coords) . "</TD></TR>";
					}
				$tab_1 .= 	"</TABLE>";			// 11/6/08
		
		
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";	// 8/12/09
				$tab_2 .= "<TR CLASS='even'>	<TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['tick_descr']), 48) . "</TD></TR>";	// str_replace("\r\n", " ", $my_string)
				$tab_2 .= "<TR CLASS='odd'>		<TD>Disposition:</TD><TD>" . shorten($row['comments'], 48) . "</TD></TR>";		// 8/13/09
		
				$locale = get_variable('locale');	// 08/03/09
				switch($locale) { 
					case "0":
					$tab_2 .= "<TR CLASS='even'>	<TD>USNG:</TD><TD>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "1":
					$tab_2 .= "<TR CLASS='even'>	<TD>OSGB:</TD><TD>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "2":
					$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
					$tab_2 .= "<TR CLASS='even'>	<TD>UTM:</TD><TD>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					default:
					print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
					}
		
		//		$tab_2 .= "<TR>					<TD>&nbsp;</TD></TR>";
				$tab_2 .= "<TR>					<TD COLSPAN=2>" . show_assigns(0, $the_id) . "</TD></TR>";
				$tab_2 .= 	"</TABLE>";		// 11/6/08
				$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE `ticket_id` = " . $the_id;
				$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
				$A = mysql_affected_rows();
		
				$query= "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE `ticket_id` = " . $the_id;
				$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
				$P = mysql_affected_rows ();
		?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['scope'], 12));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $tab_2);?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
		
				var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// for each ticket
				if (!(map_is_fixed)){																// 4/3/09
					bounds.extend(point);
					}
				i++;																				// step the index
				var marker = createMarker(point, myinfoTabs,<?php print $row['severity']+1;?>, 0, i, sym);	// (point,tabs, color, id, sym) - 1/6/09
				var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";
		
				map.addOverlay(marker);
<?php
				if (intval($row['radius']) > 0) {
					$color= (substr($row['color'], 0, 1)=="#")? $row['color']: "#000000";		// black default
?>	
		//		drawCircle(				38.479874, 				-78.246704, 						50.0, 					"#000080",						 1, 		0.75,	 "#0000FF", 					.2);
				drawCircle(	<?php print $row['lat']?>, <?php print $row['lng']?>, <?php print $row['radius']?>, "<?php print $color?>", 1, 0.75, "<?php print $color?>", .<?php print $row['opacity']?>);
<?php
					}			// end if (intval($row['radius']) 
		//		dump($row);
				}				// end tickets while ($row = ...)
			$sev_string = "Severities: normal ({$by_severity[$GLOBALS['SEVERITY_NORMAL']]}), Medium ({$by_severity[$GLOBALS['SEVERITY_MEDIUM']]}), High ({$by_severity[$GLOBALS['SEVERITY_HIGH']]})";
?>
			$('sev_counts').innerHTML = "<?php print $sev_string; ?>";
	// ==========================================      RESPONDER start    ================================================
			points = false;
			i++;
			var j=0;
<?php
		$u_types = array();												// 1/1/09
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
			}
		//dump($u_types);
		unset($result);
	
		$assigns = array();					// 08/8/3
		$tickets = array();					// ticket id's
	
		$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";
	
		$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
			$assigns[$row_as['responder_id']] = $row_as['ticket'];
			$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
			}
		unset($result_as);
	
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	
		$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
		$status_vals = array();											// build array of $status_vals
		$status_vals[''] = $status_vals['0']="TBD";
	
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
		$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
		while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
			$temp = $row_st['id'];
			$status_vals[$temp] = $row_st['status_val'];
			$status_hide[$temp] = $row_st['hide'];
	
			}
		unset($result_st);
	
	//	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `handle`";	//
		$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";	//
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
		$aprs = FALSE;
		$instam = FALSE;
		$locatea = FALSE;		//7/23/09
		$gtrack = FALSE;		//7/23/09
		$glat = FALSE;		//7/23/09
		$i=0;				// counter
	// =============================================================================
		$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
		$utc = gmdate ("U");				// 3/25/09
	
		while ($row = stripslashes_deep(mysql_fetch_array($result))) {		// ==========  major while() for RESPONDER ==========
			$got_point = FALSE;
	
		$name = $row['name'];			//	10/8/09
		$temp = explode("/", $name );
		$index =  (strlen($temp[count($temp) -1])<3)? substr($temp[count($temp) -1] ,0,strlen($temp[count($temp) -1])): substr($temp[count($temp) -1] ,-3 ,strlen($temp[count($temp) -1]));		
		
		print "\t\tvar sym = '$index';\n";				// for sidebar and icon 10/8/09
		
													// 2/13/09
			$todisp = (is_guest())? "": "&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&disp=true&id=" . $row['id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;";	// 08/8/02
			$toedit = (is_guest())? "" :"&nbsp;&nbsp;<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;" ;	// 10/8/08
			$totrack  = ((intval($row['mobile'])==0)||(empty($row['callsign'])))? "" : "&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><B><U>Tracks</B></U>&nbsp;&nbsp;</SPAN>" ;
			$tofac = (is_guest())? "": "<A HREF='units.php?func=responder&view=true&dispfac=true&id=" . $row['id'] . "'><U>To Facility</U></A>&nbsp;&nbsp;";	// 08/8/02
	
			$temp = $row['un_status_id'] ;		// 2/24/09
			$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
			$hide_status = (array_key_exists($temp, $status_hide))? $status_hide[$temp] : "??";				// 10/21/09
			if ($hide_status == "y") {
				$hide_unit = 1;
				} else {
				$hide_unit = 0;
				}
	
			$temp = $row['un_status_id'] ;		// 2/24/09
			$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
	
			if ($row['aprs']==1) {				// get most recent aprs position data
				$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
					WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_aprs = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$aprs_updated = $row_aprs['updated'];
				$aprs_speed = $row_aprs['speed'];
	//			if (($row_aprs) && (settype($row_aprs['latitude'], "float"))) {
				if (($row_aprs) && (my_is_float($row_aprs['latitude']))) {
					echo "\t\tvar point = new GLatLng(" . $row_aprs['latitude'] . ", " . $row_aprs['longitude'] ."); // 677\n";
					$got_point = TRUE;
	
					}
				unset($result_tr);
				}
			else { $row_aprs = FALSE; }
	//		dump($row_aprs);
	
			if ($row['instam']==1) {			// get most recent instamapper data
				$temp = explode ("/", $row['callsign']);			// callsign/account no. 3/22/09
	
				$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
					WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest
	
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_instam = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$instam_updated = $row_instam['updated'];
				$instam_speed = $row_instam['speed'];
				if (($row_instam) && (my_is_float($row_instam['latitude']))) {											// 4/29/09
					echo "\t\tvar point = new GLatLng(" . $row_instam['latitude'] . ", " . $row_instam['longitude'] ."); // 724\n";
					$got_point = TRUE;
					}
				unset($result_tr);
				}
			else { $row_instam = FALSE; }
	
			if ($row['locatea']==1) {			// get most recent locatea data		// 7/23/09
				$temp = explode ("/", $row['callsign']);			// callsign/account no.
	
				$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
					WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest
	
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_locatea = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$locatea_updated = $row_locatea['updated'];
				$locatea_speed = $row_locatea['speed'];
				if (($row_locatea) && (my_is_float($row_locatea['latitude']))) {
					echo "\t\tvar point = new GLatLng(" . $row_locatea['latitude'] . ", " . $row_locatea['longitude'] ."); // 687\n";
					$got_point = TRUE;
					}
				unset($result_tr);
				}
			else { $row_locatea = FALSE; }
	
			if ($row['gtrack']==1) {			// get most recent gtrack data		// 7/23/09
				$temp = explode ("/", $row['callsign']);			// callsign/account no.
	
				$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
					WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest
	
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_gtrack = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$gtrack_updated = $row_gtrack['updated'];
				$gtrack_speed = $row_gtrack['speed'];
				if (($row_gtrack) && (my_is_float($row_gtrack['latitude']))) {
					echo "\t\tvar point = new GLatLng(" . $row_gtrack['latitude'] . ", " . $row_gtrack['longitude'] ."); // 687\n";
					$got_point = TRUE;
					}
				unset($result_tr);
				}
			else { $row_gtrack = FALSE; }
	
			if ($row['glat']==1) {			// get most recent latitude data		// 7/23/09
				$temp = explode ("/", $row['callsign']);			// callsign/account no.
	
				$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
					WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest
	
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_glat = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$glat_updated = $row_glat['updated'];
				if (($row_glat) && (my_is_float($row_glat['latitude']))) {
					echo "\t\tvar point = new GLatLng(" . $row_glat['latitude'] . ", " . $row_glat['longitude'] ."); // 687\n";
					$got_point = TRUE;
					}
				unset($result_tr);
				}
			else { $row_glat = FALSE; }
	
			if (!($got_point) && ((my_is_float($row['lat'])))) {
				echo "\t\tvar point = new GLatLng(" . $row['lat'] . ", " . $row['lng'] .");	// 753\n";
				$got_point= TRUE;
				}
	
	//		print __LINE__ . "<BR />";
			$the_bull = "";											// define the bullet
			$update_error = strtotime('now - 6 hours');								// set the time for silent setting
	//		echo $update_error;
			if ($row['aprs']==1) {
				if ($row_aprs) {
					$spd = 2;										// default
					if($aprs_speed == 0) {$spd = 1;}			// stopped
					if($aprs_speed >= 50) {$spd = 3;}		// fast
					}
				else {
					$spd = 0;				// no data
					}
				$the_bull = "<FONT COLOR=" . $bulls[$spd] ."><B>AP</B></FONT>";
				}			// end aprs
	
			if ($row['instam']==1) {
				if ($instam_speed>50) {$the_bull = "<FONT COLOR = 'white'><B>IN</B></FONT>";}
				if ($instam_speed<50) {$the_bull = "<FONT COLOR = 'green'><B>IN</B></FONT>";}
				if ($instam_speed==0) {$the_bull = "<FONT COLOR = 'red'><B>IN</B></FONT>";}
				if ($instam_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>IN</B></FONT>";}
				}
	
			if ($row['locatea']==1) {
				if ($locatea_speed>50) {$the_bull = "<FONT COLOR = 'white'><B>LO</B></FONT>";}		// 7/23/09
				if ($locatea_speed<50) {$the_bull = "<FONT COLOR = 'green'><B>LO</B></FONT>";}
				if ($locatea_speed==0) {$the_bull = "<FONT COLOR = 'red'><B>LO</B></FONT>";}
				if ($locatea_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>LO</B></FONT>";}
				}
	
			if ($row['gtrack']==1) {
				if ($gtrack_speed>50) {$the_bull = "<FONT COLOR = 'white'><B>GT</B></FONT>";}		// 7/23/09
				if ($gtrack_speed<50) {$the_bull = "<FONT COLOR = 'green'><B>GT</B></FONT>";}
				if ($gtrack_speed==0) {$the_bull = "<FONT COLOR = 'red'><B>GT</B></FONT>";}
				if ($gtrack_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>GT</B></FONT>";}
				}
			if ($row['glat']==1) {
	
				$the_bull = "<FONT COLOR = 'green'><B>GL</B></FONT>";		// 7/23/09
				if ($glat_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>GL</B></FONT>";}
				}
							// end bullet stuff
	// name
	
			$name = $row['name'];		//	10/8/09
			$temp = explode("/", $name );
			$display_name = $temp[0];
	
	// assignments 3/16/09
	
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
				WHERE `responder_id` = '{$row['id']}' AND `clear` IS NULL ";
	//		dump($query);
	
			$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_assign = (mysql_affected_rows()==0)?  FALSE : stripslashes_deep(mysql_fetch_assoc($result_as)) ;
			unset($result_as);
	
			switch($row_assign['severity'])		{		//color tickets by severity
			 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
				case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
				default: 				$severityclass='severity_normal'; break;
				}
	
			$tick_ct = (mysql_affected_rows()>1)? "(" .mysql_affected_rows() . ") ": "";
			$ass_td =  (mysql_affected_rows()>0)? "<TD COLSPAN=2 CLASS='$severityclass' TITLE = '" .$row_assign['scope'] . "' >" .$tick_ct . shorten($row_assign['scope'], 24) . "</TD>": "<TD>na</TD>";
	
	// status, mobility
	// as of
			$strike = $strike_end = "";
			if ((($row['instam']==1) && $row_instam ) || (($row['aprs']==1) && $row_aprs ) || (($row['locatea']==1) && $row_locatea ) || (($row['gtrack']==1) && $row_gtrack ) || (($row['glat']==1) && $row_glat )) {		// either remote source?
				$the_class = "emph";
				if ($row['aprs']==1) {															// 3/24/09
					$the_time = $aprs_updated;
					$instam = TRUE;				// show footer legend
					}
				if ($row['instam']==1) {															// 3/24/09
					$the_time = $instam_updated;
					$instam = TRUE;				// show footer legend
					}
				if ($row['locatea']==1) {															// 7/23/09
					$the_time = $locatea_updated;
					$locatea = TRUE;				// show footer legend
					}
				if ($row['gtrack']==1) {															// 7/23/09
					$the_time = $gtrack_updated;
					$gtrack = TRUE;				// show footer legend
					}
				if ($row['glat']==1) {																// 7/23/09
					$the_time = $glat_updated;
					$glat = TRUE;				// show footer legend
					}
			} else {
				$the_time = $row['updated'];
				$the_class = "td_data";
			}
	
			if (abs($utc - $the_time) > $GLOBALS['TOLERANCE']) {								// attempt to identify  non-current values
				$strike = "<STRIKE>";
				$strike_end = "</STRIKE>";
			} else {
			$strike = $strike_end = "";
			}
	
	//	    snap(basename( __FILE__) . __LINE__, $the_class );
	
	
	// tab 1
	
	//		if (((settype($row['lat'], "float"))) || ($row_aprs) || ($row_instam)) {						// position data?
			if (((my_is_float($row['lat']))) || ($row_aprs) || ($row_instam) || ($row_locatea) || ($row_gtrack) || ($row_glat)) {						// 5/4/09
	//			dump(__LINE__);
	
				$temptype = $u_types[$row['type']];
				$the_type = $temptype[0];																	// 1/1/09
	
				$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $the_type . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $the_status . " </TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
				if (array_key_exists($row['id'], $assigns)) {
					$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['id']] . "'>" . shorten($assigns[$row['id']], 20) . "</A></TD></TR>";
					}
				$tab_1 .= "<TR CLASS='odd'><TD COLSPAN = 2>&nbsp;</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD COLSPAN = 2 ALIGN = 'center' onClick = 'do_mail_win();'><B><U>Email units</U></B></TD></TR>";
				$tab_1 .= "</TABLE>";
	
	// tab 2
			$tabs_done=FALSE;
			if ($row_aprs) {		// three tabs if APRS data
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_aprs['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_aprs['course'] . ", Speed:  " . $row_aprs['speed'] . ", Alt: " . $row_aprs['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $row_aprs['closest_city'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $row_aprs['status'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_aprs['packet_date']) . " $strike_end (UTC)</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
	
?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("APRS <?php print addslashes(substr($row_aprs['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php
				}	// end if ($row_aprs)
	
			if ($row_instam) {		// three tabs if instam data
	//			dump(__LINE__);
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_instam['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_instam['course'] . ", Speed:  " . $row_instam['speed'] . ", Alt: " . $row_instam['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_instam['updated']) . " $strike_end</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("Instam <?php print addslashes(substr($row_instam['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
					];
<?php
				}	// end if ($row_instam)
	
			if ($row_locatea) {		// three tabs if locatea data		7/23/09
	//			dump(__LINE__);
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_locatea['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_locatea['course'] . ", Speed:  " . $row_locatea['speed'] . ", Alt: " . $row_locatea['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_locatea['updated']) . " $strike_end</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
	?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("LocateA <?php print addslashes(substr($row_locatea['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
					];
<?php
				}	// end if ($row_gtrack)
	
			if ($row_gtrack) {		// three tabs if gtrack data		7/23/09
	//			dump(__LINE__);
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_gtrack['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_gtrack['course'] . ", Speed:  " . $row_gtrack['speed'] . ", Alt: " . $row_gtrack['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_gtrack['updated']) . " $strike_end</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
	?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("Gtrack <?php print addslashes(substr($row_gtrack['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
					];
<?php
				}	// end if ($row_gtrack)
	
			if ($row_glat) {		// three tabs if glat data			7/23/09
	//			dump(__LINE__);
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . $row_glat['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>As of: </TD><TD> $strike " . format_date($row_glat['updated']) . " $strike_end</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("G Lat <?php print addslashes(substr($row_glat['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
					];
<?php
				}	// end if ($row_gtrack)
	
			if (!($tabs_done)) {	// else two tabs
?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php
				}		// end if(!($tabs_done))
	
			$temp = $u_types[$row['type']];		// array ($row['name'], $row['icon'])
	//		dump($temp);
			$the_color = ($row['mobile']=="1")? 0 : 4;		// icon color black, white		-- 4/18/09
	?>
			var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";
	
			var marker = createMarker(point, myinfoTabs, <?php print $the_color;?>, <?php print $hide_unit;?>, i, sym);	// 859  - 4/18/09
			map.addOverlay(marker);
<?php
			}		// end position data available
	
		else {					// (sidebar, line_no, rcd_id, letter)
	//		dump(__LINE__);
				}
	
		$i++;				// zero-based
		print "\t\ti++;\n"; 	// 3/20/09
	
		}				// end  ==========  while() for RESPONDER ==========
	
		$source_legend = (($aprs)||($instam)||($gtrack)||($locatea)||($glat))? "<TD CLASS='emph' ALIGN='center'>Source time</TD>": "<TD></TD>";		// if any remote data/time 3/24/09
	
	// }-------- END NEW -----------------------------------------------------------------------------------------
	// ====================================Add Facilities to Map 8/1/09================================================
?>
		var icons=[];	
		var g=0;
	
		var fmarkers = [];
	
		var baseIcon = new GIcon();
		baseIcon.shadow = "./markers/sm_shadow.png";
	
		baseIcon.iconSize = new GSize(30, 30);
		baseIcon.iconAnchor = new GPoint(15, 30);
		baseIcon.infoWindowAnchor = new GPoint(9, 2);
	
		var fac_icon = new GIcon(baseIcon);
		fac_icon.image = icons[1];
	
		$("hide_fac").style.display = "none";
		$("show_fac").style.display = "inline-block";
	
	function createfacMarker(fac_point, fac_name, id, fac_icon) {
		var fac_marker = new GMarker(fac_point, fac_icon);
		// Show this markers index in the info window when it is clicked
		var fac_html = fac_name;
		fmarkers[id] = fac_marker;
		GEvent.addListener(fac_marker, "click", function() {fac_marker.openInfoWindowHtml(fac_html);});
		return fac_marker;
	}
	
<?php
	
	
		$query_fac = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]facilities`.id AS fac_id, `$GLOBALS[mysql_prefix]facilities`.description AS facility_description, `$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, `$GLOBALS[mysql_prefix]facilities`.name AS facility_name FROM `$GLOBALS[mysql_prefix]facilities` LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
		$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	//	dump($query_fac);
		
		while($row_fac = mysql_fetch_array($result_fac)){
		$fac_id=($row_fac['fac_id']);
		$fac_type=($row_fac['icon']);
	
		$fac_name = $row_fac['facility_name'];			//	10/8/09
	//	$fac_name = $row_fac['name'];					//	10/8/09
		$fac_temp = explode("/", $fac_name );			//  11/27/09
		$fac_index =  (strlen($fac_temp[count($fac_temp) -1])<3)? 
			substr($fac_temp[count($fac_temp) -1] ,0,strlen($fac_temp[count($fac_temp) -1])):
			substr($fac_temp[count($fac_temp) -1] ,-3 ,strlen($fac_temp[count($fac_temp) -1]));		
		
		print "\t\tvar fac_sym = '$fac_index';\n";				// for sidebar and icon 10/8/09
		
			$toroute = (is_guest())? "": "&nbsp;<A HREF='routes.php?ticket_id=" . $fac_id . "'><U>Dispatch</U></A>";	// 8/2/08
	
		if(is_guest()) {
			$facedit = $toroute = $facmail = "";
			}
		else {
			$facedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='facilities.php?func=responder&edit=true&id=" . $row_fac['fac_id'] . "'><U>Edit</U></A>" ;
			$facmail = "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_mail_fac_win('" .$row_fac['fac_id']  . "');><U><B>Email</B></U></SPAN>" ;
			$toroute = "&nbsp;<A HREF='fac_routes.php?fac_id=" . $fac_id . "'><U>Route To Facility</U></A>";	// 8/2/08
			}
	
			if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {
	
			$f_disp_name = $row_fac['facility_name'];		//	10/8/09
			$f_disp_temp = explode("/", $f_disp_name );
			$facility_display_name = $f_disp_temp[0];
	
				$fac_tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
				$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
				$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['facility_description'])) . "</TD></TR>";
				$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
				$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
				$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
				$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'> " . format_date($row_fac['updated']) . "</TD></TR>";
				$fac_tab_1 .= "</TABLE>";
	
				$fac_tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";
				$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
				$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
				$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";
				$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";
				$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['opening_hours'])) . "</TD></TR>";
				$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
				$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
				$fac_tab_2 .= "</TABLE>";
				
				?>
	//			var fac_sym = (g + 1).toString();
				var myfacinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
					new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
					];
				<?php
	
				echo "var fac_icon = new GIcon(baseIcon);\n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_icon_url = \"./icons/gen_fac_icon.php?blank=$fac_type&text=\" + (fac_sym) + \"\";\n";
				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new GLatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);\n";
				echo "map.addOverlay(fac_marker);\n";
				echo "\n";
?>
				if (fac_marker.isHidden()) {
					fac_marker.show();
				} else {
					fac_marker.hide();
				}
<?php
			}	// end if my_is_float
	
?>
			g++;
	<?php
		}	// end while
	
	//}
	// =====================================End of functions to show facilities========================================================================
	
		for ($i = 0; $i<count($kml_olays); $i++) {				// emit kml overlay calls
			echo "\t\t" . $kml_olays[$i] . "\n";
			}
?>
		if (!(map_is_fixed)){
			if (!points) {		// any?
				map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
				}
			else {
				center = bounds.getCenter();
				zoom = map.getBoundsZoomLevel(bounds);
				map.setCenter(center,zoom);
				}			// end if/else (!points)
		}				// end if (!(map_is_fixed))
	
<?php
	
		switch ($_SESSION['show_hide_unit']) {		// persistence flags 2/14/09
			case NULL:						// default 3/23/09
			case " ":						//
			case "s":
				print "\tshow_Units();\n";
			    break;
			case "h":
				print "\thide_Units();\n";
			    break;
			default:
			    echo "error" . __LINE__ . "\n";
			}
?>
	
	
	// =============================================================================================================
		}		// end if (GBrowserIsCompatible())
	else {
		alert("Sorry, browser compatibility problem. Contact your tech support group.");
		}
	</SCRIPT>
	
	
<?php
	
	}				// end function full_scr() ===========================================================
