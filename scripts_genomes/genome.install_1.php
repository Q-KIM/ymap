<?php
	session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
	<style type="text/css">
		body {font-family: arial;}
	</style>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
<title>Install genome into pipeline.</title>
</HEAD>
<?php
// current directory
	require_once '../constants.php';
	include_once 'process_input_files.genome.php';

	$fileName = filter_input(INPUT_POST, "fileName", FILTER_SANITIZE_STRING);
	$user     = filter_input(INPUT_POST, "user",     FILTER_SANITIZE_STRING);
	$genome   = filter_input(INPUT_POST, "genome",   FILTER_SANITIZE_STRING);
	$key      = filter_input(INPUT_POST, "key",      FILTER_SANITIZE_STRING);

//// Debugging
// echo getcwd() . "\n";
// echo $fileName."\n";
// echo $user."\n";
// echo $genome."\n";
// echo $key."\n";

	$fasta_name = $genome.".fasta";
	if (($fileName == ".") || ($fileName == "..") || ($user == ".") || ($user == "..") || ($genome == ".") || ($genome == "..") || ($key == ".") || ($key == "..")) {
		echo "Invalid input data";
		exit;
	}

// Initialize 'process_log.txt' file.
	$logOutputName = "../users/".$user."/genomes/".$genome."/process_log.txt";
	$logOutput     = fopen($logOutputName, 'w');
	fwrite($logOutput, "Log file initialized\n");
	fwrite($logOutput, "Running 'scripts_genomes/genome.install_1.php'.\n");

// Initialize 'condensed_log.txt' file.
	$condensedLogOutputName = "../users/".$user."/genomes/".$genome."/condensed_log.txt";
	$condensedLogOutput     = fopen($condensedLogOutputName, 'w');
	fwrite($condensedLogOutput, "Initializing.\n");
	fclose($condensedLogOutput);
	chmod($outputName,0755);


	// Generate 'reference.txt' file containing:
	//      one line; file name of reference FASTA file.
	fwrite($logOutput, "\tGenerating 'reference.txt' file.\n");
	$outputName       = "../users/".$user."/genomes/".$genome."/reference.txt";
	if (file_exists($outputName)) {
		$fileContents = file_get_contents($outputName);
		unlink($outputName);
		$output       = fopen($outputName, 'w');
		fwrite($output, $fasta_name);
	} else {
		$output       = fopen($outputName, 'w');
		fwrite($output, $fasta_name);
	}
	fclose($output);
	unset($outputName);
	unset($output);

	// Generate 'name.txt' file containing:
	//		one line; name of genome.
	fwrite($logOutput, "\tGenerating 'name.txt' file.\n");
	$outputName       = "../users/".$user."/genomes/".$genome."/name.txt";
	if (file_exists($outputName)) {
		$fileContents = file_get_contents($outputName);
		unlink($outputName);
		$output       = fopen($outputName, 'w');
		fwrite($output, $fileContents);
	} else {
		$output       = fopen($outputName, 'w');
		fwrite($output, str_replace("_"," ",$genome));
	}
	fclose($output);
	unset($outputName);
	unset($output);

	// Generate 'upload_size.txt' file to contain the size of the uploaded file (irrespective of format) for display in "Manage Datasets" tab.
	$genomePath      = "../users/".$user."/genomes/".$genome."/";
	$outputName      = $genomePath."upload_size_1.txt";
	$output          = fopen($outputName, 'w');
	$fileSizeString  = filesize($genomePath.$fileName);
	fwrite($output, $fileSizeString);
	fclose($output);
	chmod($outputName,0755);
	fwrite($logOutput, "\tGenerated 'upload_size_1.txt' file.\n");

		// Process uploaded file.
		$name        = str_replace("\\", ",", $fileName);
		rename($genomePath.$name,$genomePath.strtolower($name));
		$name        = strtolower($name);
		$ext         = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		$filename    = strtolower(pathinfo($name, PATHINFO_FILENAME));
		fwrite($logOutput, "\tKey         : ".$key."\n");
		fwrite($logOutput, "\tDatafile    : '".$name."'.\n");
		fwrite($logOutput, "\tFilename    : '".$filename."'.\n");
		fwrite($logOutput, "\tExtension   : '".$ext."'.\n");
		fwrite($logOutput, "\tScript_path : '".getcwd()."'.\n");
		fwrite($logOutput, "\tPath        : '".$genomePath."'.\n");

		// Generate 'upload_size.txt' file to contain the size of the uploaded file (irrespective of format) for display in "Manage Datasets" tab.
		$output2Name    = $genomePath."upload_size_1.txt";
		$output2        = fopen($output2Name, 'w');
		$fileSizeString = filesize($genomePath.$name);
		fwrite($output2, $fileSizeString);
		fclose($output2);
		chmod($output2Name,0755);
		fwrite($logOutput, "\tGenerated 'upload_size_1.txt' file.\n");

		// Process the uploaded file.
		process_input_files_genome($ext,$name,$genomePath,$key,$user,$genome,$output, $condensedLogOutput,$logOutput, $fasta_name);
		$fileName = $fasta_name;

	// Reformat FASTA file from multiple lines per text block to single line.
	fwrite($logOutput, "\tReformatting genome FASTA to single-line entries.\n");
	$currentdir = getcwd();
	fwrite($logOutput, "\tCurrDir     : '$currentdir'.\n");
	$file_path  = "../users/".$user."/genomes/".$genome."/".$fileName;
	fwrite($logOutput, "\tfile_path   : '$file_path'.\n");
	$system_call_string = "sh ../scripts_seqModules/FASTA_reformat_1.sh ".$file_path;
	exec($system_call_string, $result);

	// Process FASTA file for chromosome count, names, and lengths.
	fwrite($logOutput, "\tReading chromosome count, names, and lengths from FASTA.\n");
	$file_lines  = file($file_path);
	$num_lines   = sizeof($file_lines);
	$chr_count   = 0;
	$chr_names   = array();
	$chr_lengths = array();
	for ($i = 0; $i < $num_lines; $i += 1) {
		if ($file_lines[$i][0] == '>') {
			// chromosome name is the header string (starting with ">"), after trimming trailing whitespace characters.
			$line_parts = explode(" ",$file_lines[$i]);
			$chr_name   = str_replace(array("\r","\n"),"",$line_parts[0]);
			$chr_name   = substr($chr_name,1,strlen($chr_name)-1);
			array_push($chr_names, $chr_name);
			$chr_count  += 1;
		} else {
			// chromosome length is determined by the length of sequence strings.
			$chr_length = strlen($file_lines[$i]);
			array_push($chr_lengths, $chr_length);
		}
	}
	unset($file_lines);
	unset($num_lines);
	unset($line);
	unset($chr_length);
	unset($line_parts);
	unset($chr_name);
	fwrite($logOutput, "\tnum chrs    : '$chr_count'.\n");

	// Reformat FASTA file from multiple lines per text block to single line.
	fwrite($logOutput, "\tReformatting genome FASTA to multi-line entries.\n");
	$system_call_string = "sh ../scripts_seqModules/FASTA_reformat_2.sh ".$file_path;
	exec($system_call_string,$result);

	// Store variables of interest into $_SESSION.
	fwrite($logOutput, "\tStoring PHP session variables.\n");
	$_SESSION['genome_'.$key]      = $genome;
	$_SESSION['fileName_'.$key]    = $fileName;
	$_SESSION['chr_count_'.$key]   = $chr_count;
	$_SESSION['chr_names_'.$key]   = $chr_names;
	$_SESSION['chr_lengths_'.$key] = $chr_lengths;

//// Debugging output of all variables.
//	print_r($_SESSION);
//	print_r($GLOBALS);

	// The following section defines a form for collecting the information needed to build the last of the genome setup files.
	fwrite($logOutput, "\tGenerating form to request centromere location and other genome specific data from the user.\n");
?>

<BODY onload = "parent.parent.resize_genome('<?php echo $key; ?>', 150)<?php
//	$genomePath      = "../users/".$user."/genomes/".$genome."/";
//	$outputName      = $genomePath."upload_size_1.txt";
//	$output          = fopen($outputName, 'w');
//	$fileSizeString  = filesize($genomePath.$fileName);

	$sizeFile_1   = "upload_size_1.txt";
	$handle       = fopen($sizeFile_1,'r');
	$sizeString_1 = trim(fgets($handle));
	fclose($handle);
	if ($sizeString_1 !== "") {
		echo "; parent.parent.update_genome_file_size('".$key."','".$sizeString_1."');";
	}
?>">

<font color="red" size="2">Fill in genome details:</font>
	<form action="genome.install_2.php" method="post">
		<table border="0">
		<tr>
			<th rowspan="2"><font size="2">Use</font></th>
			<th rowspan="2"><font size="2">FASTA entry name</font></th>
			<th rowspan="2"><font size="2">Short</font></th>
			<th colspan="2"><font size="2">Centromere</font></th>
			<th rowspan="2"><font size="2">rDNA</font></th>
		</tr>
		<tr>
			<th><font size="2">start bp</font></th>
			<th><font size="2">end bp</font></th>
		</tr>
<?php
			for ($chr=0; $chr<$chr_count; $chr+=1) {
				$chrID = $chr+1;
				echo "\t\t<tr>\n";
				echo "\t\t\t<td align=\"middle\"><input type=\"checkbox\" name=\"draw_{$chrID}\" checked></td>\n";
				echo "\t\t\t<td><font size='2'>{$chr_names[$chr]}</font></td>\n";
				echo "\t\t\t<td><input type=\"text\"     name=\"short_{$chrID}\"    value=\"Chr{$chrID}\" size=\"6\"></td>\n";
				echo "\t\t\t<td><input type=\"text\"     name=\"cenStart_{$chrID}\" value=\"0\"           size=\"6\"></td>\n";
				echo "\t\t\t<td><input type=\"text\"     name=\"cenEnd_{$chrID}\"   value=\"0\"           size=\"6\"></td>\n";
				echo "\t\t\t<td align=\"middle\" ><input type=\"radio\"    name=\"rDNAchr\"      value=\"{$chrID}\"></td>\n";
				echo "\t\t</tr>\n";
			}
		?>
		</table><br>
		<font size="2">
		Ploidy = <input type="text" name="ploidy" value="2.0" size="6"><br>
		rDNA (start = <input type="text" name="rDNAstart" value="0" size="6">; end = <input type="text" name="rDNAend" value="0" size="6">)<br>
		Futher annotations to add to the genome? <input type="text" name="annotation_count" value="0" size="6"><br>
		Select <input type="checkbox" name="expression_regions"> if a tab-delimited-text file listing ORF coordinates is available.<br>
		</font>
		<br>
		<input type="submit" value="Save genome details...">
		<input type="hidden" id="key" name="key" value="<?php echo $key; ?>">
	</form>
</BODY>
</HTML>
<?php
	fwrite($logOutput, "\t'scripts_genomes/genome.install_1.php' has completed.\n");
	fclose($logOutput);
?>
