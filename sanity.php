<?php

//please see readme for additional info
//created by phatblinkie to solve an ethos problem of hung miners not rebooting properly

//set some variables.
//debug = output text about what was done , 0 for no text, 1 for text
$debug = "1";
//$minhash = the minimum amount of combined hashpower, no decimals please. try to make this  the value when about 2 gpus have failed, to account for any dev fee problems
$minhash = "290";
//$uptime = the minimum amount of uptime for the rig in seconds, before verifying the stats is considered a valid task
$uptime = "500";
//threshold = number of times in a row, the values for $minhash are below the set value, that means an action is required, for example to reboot only after 2 fails in a row. This is needed due to things like the claymore dev fee, which can skew the hash rate results
$threshold = "2";












//no more adjustable variables below this line
//set location and name of statusfile
$statusfile = "/tmp/sanity.php.history";
//check uptime first
if ( trim(`cat /proc/uptime | cut -d"." -f1`) <= $uptime) {
        //not enough time passed, remove status file from before the last reboot happened, and then exit silently
        if ("$debug" == "1") { echo "\nuptimemin = $uptime, actual uptime = ".trim(`cat /proc/uptime | cut -d"." -f1`)."\n"; }

        if (file_exists($statusfile)) {unlink($statusfile);}
        exit;
        }

//include native ethos functions
//include("/opt/ethos/lib/functions.php");
//grab stats array locally
//$data = get_stats();

function get_somestats() {
//helps with a stuck crashed miner problem
        $send['hash'] = trim(`tail -10 /var/run/ethos/miner_hashes.file | sort -V | tail -1 | tr ' ' '\n' | awk '{sum += \$1} END {print sum}'`);
        $send['miner_hashes'] = trim(`tail -10 /var/run/ethos/miner_hashes.file | sort -V | tail -1`);
return $send;
}

$data = get_somestats();

if ("$debug" == "1") { print_r($data); }

$date=`date`;
$hash = $data["hash"];
$miner_hashes = $data["miner_hashes"];
$history=0;


//read in if present, the previous results fail count
if (file_exists($statusfile)) {
        $myfile = fopen($statusfile, "r") or die("Unable to open statusfile $statusfile");
        //not much in the file, just a number of fails, file is removed if no fail.
        $history = fgets($myfile);
        if ("$debug" == "1") { echo "previous fail found, value = $history\n"; }
        fclose($myfile);
        }
//test hash values
if ( $hash < $minhash)
        {
        $failcount = $history + 1;
        if ("$debug" == "1") { echo "hash is too low,(min = $minhash, Current = $hash)\n"; }
        if ("$debug" == "1") { echo "fail count is $failcount, threshold is $threshold\n"; }
        //add to file for next run
        $myfile = fopen($statusfile, "w") or die("Unable to create statusfile $statusfile");
        fwrite($myfile,$failcount);
        fclose($myfile);
        if ("$debug" == "1") { echo "failure value $failcount written to $statusfile \n"; }
//test fail count
        if ($failcount >= $threshold){
                if ("$debug" == "1") { echo "fail count too high, taking actions to reboot \n ";}
                //reboot with built in r command
                //could be replaced with other commands if desired
                $date = `date`;
                `echo "Rebooting, hash = $hash, minhash = $minhash, failcount = $failcount, threshold = $threshold, miner data = $miner_hashes, @ $date" >>/home/ethos/reboot.log`;
                `/usr/bin/sudo /opt/ethos/bin/hard-reboot`;
                }
        }
//remove status file to reset count if hash was ok
else {
        if ("$debug" == "1") { echo "hash rates were acceptable, removing any existing fail history\n"; }
        if (file_exists($statusfile)) {unlink($statusfile);}
     }

?>
