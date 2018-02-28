# ethos_rebooter
a fix for a problem I encountered with ethos. It can get hung up and not reboot on occassion.
This script is a php script that calls ethos own internal functions to gather up stats. it then can reboot the rig if the values are not in line with where you tell it that they should be. an updated version may look at values in your config script for ethos as well, but not yet.

how do you install?
just clone this repo to your /home/ethos/ folder, or copy the sanity.php file.

`git clone https://github.com/phatblinkie/ethos_rebooter.git`

what are the settings?
The script has currently four values you can modify, you can manually run the script to test with 'php sanity.php' if you set the debug to 1, then you will see the output of the scripts interpreted results.
inside the top of the sanity.php script, you can modify the following

    --------------------------------------------------------------------------
    //debug = output text about what was done, 0 for no text, 1 for text output
    $debug = "0";
    
    //$minhash = the minimum amount of combined hashpower, no decimals please. 
    //try to make this the value when about 2 gpus have failed, to account for any dev fee problems,
    //finding the right balance for your rig might take a few tries unless you know your rigs well
    $minhash = "120";
    
    //$uptime = the minimum amount of uptime for the rig in seconds, 
    //before verifying that the stats is considered a valid task
    $uptime = "500";
    
    //threshold = number of times in a row, the values for $minhash are below the set value, 
    //that means an action is required, for example to reboot only after 2 fails in a row. 
    //This is needed due to things like the claymore dev fee, which can skew the hash rate results
    $threshold = "3";
    ---------------------------------------------------------------------------

How do I run it automatically?
you can setup a crontab job to run it, add this line to the end of your crontab file

`*/5 * * * * /usr/bin/php /home/ethos/ethos_rebooter/sanity.php`

this can be done with the command `crontab -e`

Any success or failures, let me know
Phatblinkie
