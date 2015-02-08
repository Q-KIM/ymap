
#!/bin/bash -e
#
# cleaning_genome.sh
#
set -e;
## All created files will have permission 760
umask 007;

### define script file locations.
user=$1;
genome=$2;
main_dir=$(pwd)"/../";

#user="darren"
#genome="test_02";
#main_dir="/heap/hapmap/bermanlab/";

reflocation=$main_dir"users/"$user"/genomes/"$genome"/";				# Directory where FASTA file is kept.
logName=$reflocation"process_log.txt";
condensedLog=$reflocation"condensed_log.txt";
FASTA=`sed -n 1,1'p' $reflocation"reference.txt"`;					# Name of FASTA file.
FASTAname=$(echo $FASTA | sed 's/.fasta//g');						# name of genome file, without file type.
ddRADseq_FASTA=$FASTAname".MfeI_MboI.fasta";						# Name of digested reference for ddRADseq analysis.
standard_bin_FASTA=$FASTAname".standard_bins.fasta";					# Name of reference genome broken up into standard bins.
nameString1=`cat $reflocation"name.txt"`;

##============================================#
# Intermediate file cleanup.                  #
#============================================##
echo "Cleaning and archiving." >> $condensedLog;
echo "Deleting unneeded intermediate files." >> $logName;


echo "\tGenerating 'complete.txt' file to let pipeline know installation of genome has completed." >> $logName;
## Generate "complete.txt" to indicate processing has completed normally.
timestamp=$(date +%T);

	timesLogFile=$main_dir"completion_times.log";
	if [ -f $timesLogFile ]
	then
		echo -n $user"("$genome")[genome]\t" >> $timesLogFile;
		cat $reflocation"working.txt" >> $timesLogFile;
		echo " -> "$timestamp >> $timesLogFile;
	fi

completeFile=$reflocation"complete.txt";
echo "complete" > $completeFile;
echo $timestamp >> $completeFile;
echo "\tGenerated 'complete.txt' file." >> $logName;
chmod 0755 $completeFile;
if [ -f $reflocation"working.txt" ]
then
	echo "\n"$timestamp >> $reflocation"working.txt"
	mv $reflocation"working.txt" $reflocation"working_done.txt";
	echo "\tworking.txt" >> $logName;
fi
echo "\n--== Last Line ==--\n" >> $logName;
