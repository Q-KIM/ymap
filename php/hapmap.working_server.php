<?php
    session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
<head>
<style type="text/css">
	body {font-family: arial;}
	.tab {margin-left:   1cm;}
	.clock {
		float:          left;
		margin-right:   0.25cm;
	}
</style>
</head>
<?php
	require_once 'constants.php';

	$user    = filter_input(INPUT_POST, "user",     FILTER_SANITIZE_STRING);
	$hapmap  = filter_input(INPUT_POST, "hapmap",  FILTER_SANITIZE_STRING);
	$key     = filter_input(INPUT_POST, "key",      FILTER_SANITIZE_STRING);
	$status  = filter_input(INPUT_POST, "status",   FILTER_SANITIZE_STRING);

	// increment clock animation...
	$status   = ($status + 1) % 12;
	if ($status == 0) {          $clock = "<img src=\"../images/12.png\" alt-text=\"12\" class=\"clock\" >";
	} else if ($status == 1) {   $clock = "<img src=\"../images/01.png\" alt-text=\"01\" class=\"clock\" >";
	} else if ($status == 2) {   $clock = "<img src=\"../images/02.png\" alt-text=\"02\" class=\"clock\" >";
	} else if ($status == 3) {   $clock = "<img src=\"../images/03.png\" alt-text=\"03\" class=\"clock\" >";
	} else if ($status == 4) {   $clock = "<img src=\"../images/04.png\" alt-text=\"04\" class=\"clock\" >";
	} else if ($status == 5) {   $clock = "<img src=\"../images/05.png\" alt-text=\"05\" class=\"clock\" >";
	} else if ($status == 6) {   $clock = "<img src=\"../images/06.png\" alt-text=\"06\" class=\"clock\" >";
	} else if ($status == 7) {   $clock = "<img src=\"../images/07.png\" alt-text=\"07\" class=\"clock\" >";
	} else if ($status == 8) {   $clock = "<img src=\"../images/08.png\" alt-text=\"08\" class=\"clock\" >";
	} else if ($status == 9) {   $clock = "<img src=\"../images/09.png\" alt-text=\"09\" class=\"clock\" >";
	} else if ($status == 10) {  $clock = "<img src=\"../images/10.png\" alt-text=\"10\" class=\"clock\" >";
	} else if ($status == 11) {  $clock = "<img src=\"../images/11.png\" alt-text=\"11\" class=\"clock\" >";
	} else {                     $clock = "[ * ]";
	}

	if (file_exists($directory."users/".$user."/hapmaps/".$hapmap."/complete.txt")) {
		?>
		<html>
		<body onload = "parent.update_hapmap_label_color('<?php echo $key; ?>','#00AA00'); parent.resize_iframe('<?php echo $key; ?>', 0);" >
		</body>
		</html>
		<?php
	} else if (file_exists($directory."users/".$user."/hapmaps/".$hapmap."/error.txt")) {
		// Load error.txt from hapmap folder.
        $handle = fopen($GLOBALS['directory']."users/".$user."/hapmaps/".$hapmap."/error.txt", "r");
        $error = fgets($handle);
        fclose($handle);
		?>
		<html>
		<body onload = "parent.update_hapmap_label_color('<?php echo $key; ?>','#AA0000'); parent.resize_iframe('<?php echo $key; ?>', 115);" >
			<font color="red"><b>[Error : Consult site admin.]</b></font><br>
			<?php echo $error; ?>
		</body>
		</html>
		<?php
	} else if (file_exists($directory."users/".$user."/hapmaps/".$hapmap."/working.txt")) {
		// Load last line from "condensed_log.txt" file.
		$condensedLog      = explode("\n", trim(file_get_contents($GLOBALS['directory']."users/".$user."/hapmaps/".$hapmap."/condensed_log.txt")));
		$condensedLogEntry = $condensedLog[count($condensedLog)-1];
		?>
		<script type="text/javascript">
		var user    = "<?php echo $user; ?>";
		var hapmap  = "<?php echo $hapmap; ?>";
		var key     = "<?php echo $key; ?>";
		var status  = "<?php echo $status; ?>";
		reload_page=function() {
			<?php
			// Make a form to generate a form to POST information to pass along to page reloads, auto-triggered by form submit.
			echo "\tvar autoSubmitForm = document.createElement('form');\n";
			echo "\t\tautoSubmitForm.setAttribute('method','post');\n";
			echo "\t\tautoSubmitForm.setAttribute('action','hapmap.working_server.php');\n";
			echo "\tvar input2 = document.createElement('input');\n";
			echo "\t\tinput2.setAttribute('type','hidden');\n";
			echo "\t\tinput2.setAttribute('name','key');\n";
			echo "\t\tinput2.setAttribute('value',key);\n";
			echo "\t\tautoSubmitForm.appendChild(input2);\n";
			echo "\tvar input2 = document.createElement('input');\n";
			echo "\t\tinput2.setAttribute('type','hidden');\n";
			echo "\t\tinput2.setAttribute('name','user');\n";
			echo "\t\tinput2.setAttribute('value',user);\n";
			echo "\t\tautoSubmitForm.appendChild(input2);\n";
			echo "\tvar input3 = document.createElement('input');\n";
			echo "\t\tinput3.setAttribute('type','hidden');\n";
			echo "\t\tinput3.setAttribute('name','hapmap');\n";
			echo "\t\tinput3.setAttribute('value',hapmap);\n";
			echo "\t\tautoSubmitForm.appendChild(input3);\n";
			echo "\tvar input4 = document.createElement('input');\n";
			echo "\t\tinput4.setAttribute('type','hidden');\n";
			echo "\t\tinput4.setAttribute('name','status');\n";
			echo "\t\tinput4.setAttribute('value',status);\n";
			echo "\t\tautoSubmitForm.appendChild(input4);\n";
			echo "\tautoSubmitForm.submit();\n";
			?>
		}
		// Initiate recurrent call to reload_page function, which depends upon hapmap status.
		var internalIntervalID = window.setInterval(reload_page, 3000);
		</script>
		<HTML>
		<BODY onload = "parent.resize_iframe('<?php echo $key; ?>', 20*2+12);" class="tab">
			<font color="red"><b>[Processing selected data.]</b></font>
			<?php
			echo $clock."<br>";
			echo "Haplotype analysis in process.";
			echo "\n";
			?>
		</BODY>
		</HTML>
<?php
	} else {
		?>
		<html>
		<body onload = "parent.update_hapmap_label_color('<?php echo $key; ?>','#00AA00'); parent.hapmap_UI_refresh_1_<?php echo str_replace('h_','',$key); ?>();">
		</body>
		</html>
		<?php
	}
?>