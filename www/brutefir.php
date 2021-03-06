<?php
		include('strings.php');
        include('functions.inc.php');
        include('style.css');
		
        if($_GET["lang"] === "en" || $_POST[lang]=='en')
		{
			$lang='en';
			$GLOBALS["lang"]='en';
		} 
		else
		{
			$lang='de';
			$GLOBALS["lang"]='de';
		}
		
		/* Save Configuration and reload */
		/*
		if(isset($_POST[save]))
		{
			$savedVol=getVol();
			for ($i=0; $i < 10; $i++) { 
				wrtToUserconfig('COEFF_NAME'.$i,$_POST[coeff.$i]);
				wrtToUserconfig('COEFF_COMMENT'.$i,$_POST[comm.$i]);
			}
			wrtToUserconfig('COEFF_ATT'.$_POST[savedbank],getVol());
			wrtToUserconfig('DEF_COEFF',$_POST[savedbank]);
			shell_exec('/etc/init.d/brutefir reload &> /dev/null ' );

			if ($ini_array[MSCODING]=='ON') {
				volControl(1,$savedVol);
			}
			else {
				volControl(0,$savedVol);
			}			
		}*/

        if(isset($_POST[set]))
		{
			$savedbank=$_POST[savedbank];
			validateAndSave(10,$_POST);
			wrtToUserconfig('DEF_COEFF',$_POST[savedbank]);
			if ($ini_array[MSCODING]=='ON') {
				volControl(1,($_POST[vol.$savedbank])*-1);
			}
			else {
				volControl(0,($_POST[vol.$savedbank])*-1);
			}
		}

		if(isset($_POST[save]))
		{
			$savedbank=$_POST[savedbank];
			validateAndSave(10,$_POST);
			wrtToUserconfig('DEF_COEFF',$_POST[savedbank]);
			shell_exec('/etc/init.d/brutefir reload &> /dev/null ' );
			if ($ini_array[MSCODING]=='ON') {
				volControl(1,$_POST[vol.$savedbank]);
			}
			else {
				volControl(0,$_POST[vol.$savedbank]);
			}
		}
    
// Load ini-array from userconfig.txt
    $ini_array = parse_ini_file("/mnt/mmcblk0p1/userconfig.txt", 1);
    
    // Switch filter bank
    if(isset($_POST['bank']))
    {
        switchFilter($_POST[bank]);
        $activeFilter = $_POST[bank];

        validateAndSave(10,$_POST);
        wrtToUserconfig('DEF_COEFF',$activeFilter);
        // Check if MSCODING is on
        if ($ini_array[MSCODING]=='ON') {
            volControl(1,$ini_array[COEFF_ATT.$activeFilter]);
        }
        else {
            volControl(0,$ini_array[COEFF_ATT.$activeFilter]);
        }
    }
    else
    {
        $activeFilter = getFilter();
        if(isset($_POST[save])){
            if($ini_array[MSCODING]=='ON') {
                volControl(1,$ini_array[COEFF_ATT.$activeFilter]);
            }
            else {
                volControl(0,$ini_array[COEFF_ATT.$activeFilter]);
            }
        }
    }
    
    // Mute channels
    if(isset($_POST[mute]))
		{
			tgglMute(0);
			tgglMute(1);
		}
		
		// LOUDER !!
		if(isset($_POST[volPlus]))
		{
			$actVol=getVol();
			$actVol-=0.5;
			//if($ini_array[MSCODING]=='ON')
			//{
			//	volControl(1,$actVol);
			//}
			//else
			//{
				volControl(0,$actVol);
			//}
			$ini_array[COEFF_ATT.$activeFilter]=$actVol;
			
		}
		
		// less louder ... 
		if(isset($_POST[volMinus]))
		{
			$actVol=getVol(); //auslesen
			$actVol+=0.5;	//setzen
            //if($ini_array[MSCODING]=='ON')
     	//	{
          //  	volControl(1,$actVol);                       
           // }
            //else
            //{
                volControl(0,$actVol);
            //}
			$ini_array[COEFF_ATT.$activeFilter]=$actVol; //ins array
		}
    
    
    
    $ini_array = parse_ini_file("/mnt/mmcblk0p1/userconfig.txt", 1);
    if ( isset($_POST['convolver_submit']))
    {
        if ( !$error )
        {
            $shell_exec_ret=shell_exec('mount -o remount,rw /mnt/mmcblk0p1/');
            write_config();
            $shell_exec_ret=shell_exec('mount -o remount,ro /mnt/mmcblk0p1/');
            unset($_POST['reboot']);
            $ini_array = parse_ini_file("/mnt/mmcblk0p1/userconfig.txt", 1);
            echo '<meta http-equiv="refresh"> ';
        }
        shell_exec('/usr/bin/killall brutefir &> /dev/null' );
        shell_exec('/usr/bin/killall jackd &> /dev/null' );
        shell_exec('/usr/bin/killall brutefir_connect_netjackports &> /dev/null' );
        shell_exec('/usr/bin/stopstreamer');
        shell_exec('/etc/init.d/brutefir &> /dev/null' );
        exec('/usr/bin/startstreamer.sh &> /dev/null &');
        shell_exec('/etc/init.d/amixer &> /dev/null &');
    }
    
    include "header.php";?>

<!-- Navigation -->
<ul>
<li><a href="index.php" target=""><? print ${linktext_configuration._.$lang} ?></span></a></li>
<li><a href="system.php" target=""><? print ${linktext_system._.$lang} ?></a></li>
<li><a href="measurement.php" target=""><? print${linktext_measurement._.$lang} ?></a></li>
<li>
<? if ($ini_array['BRUTEFIR'] == "OFF"){?>
    <a class="select" style="color: #c5c5c5" href="brutefir.php"target=""><? print ${linktext_brutefir._.$lang} ?></a>
    <?}else{?>
        <a class="select" href="brutefir.php"target=""><? print ${linktext_brutefir._.$lang} ?></a>
    <?}?>
</li>

<li style="float:right"><a href="credits.php" target=""><? print ${linktext_credits._.$lang} ?></a></li>
</ul><!-- Ende Navigation -->

<hr class="top">
</div> <!-- Ende vom Head -->

<div class="content">

<h1><? print $ini_array["HOSTNAME"] ?> - <? print ${page_title_convolver._.$lang}?></h1>

<form action="<?echo $_SERVER['PHP_SELF'] ?>" method="post">

<fieldset>
<table> <!-- Audio Convolution Abfrage -->
<legend><? print ${convolution_legend._.$lang}?></legend>
  <tr>
    <td>
      <a style="text-decoration: none href="#" title="<? print ${helptext_convolution._.$lang} ?>"class="tooltip">
      <span title=""><label class="audio" for="Convolution"> <? print ${convolution._.$lang} ; ?> </label></span></a>
    </td>
    <td>
      <? if ($ini_array["BRUTEFIR"] == "ON") { ?>
          <input class="actiongroup" type="radio" name="BRUTEFIR" value="ON" checked> <? print ${on._.$lang} ; ?>
          <input class="actiongroup" type="radio" name="BRUTEFIR" value="OFF"> <? print ${off._.$lang} ; ?>
      <?}else{ ?>
          <input class="actiongroup" type="radio" name="BRUTEFIR" value="ON"> <? print ${on._.$lang} ; ?>
          <input class="actiongroup" type="radio" name="BRUTEFIR" value="OFF" checked> <? print ${off._.$lang} ;
      }?>
    </td>
    <td rowspan="3">
        <input class="button" type="submit" value=" <? print ${button_submit_audiosettings._.$lang} ?> " name="convolver_submit">
    </td>
  </tr>


<!-- Prefilter -->
  <tr>
    <td>
        <a style="text-decoration: none href="#" title="<? print ${helptext_prefilter._.$lang} ?>"class="tooltip">
        <span title=""><label class="audio" for="Prefilter"> <? print ${prefilter._.$lang} ; ?> </label></span></a>
    </td>
    <td>
      <? if ($ini_array["LOAD_PREFILTER"] =="ON"){ ?>
        <input class="actiongroup" type="radio" name="LOAD_PREFILTER" value="ON" checked> <? print ${on._.$lang} ; ?>
        <input class="actiongroup" type="radio" name="LOAD_PREFILTER" value="OFF"> <? print ${off._.$lang} ; ?>
      <?}else {?>
          <input class="actiongroup" type="radio" name="LOAD_PREFILTER" value="ON" > <? print ${on._.$lang} ; ?>
          <input class="actiongroup" type="radio" name="LOAD_PREFILTER" value="OFF" checked> <? print ${off._.$lang} ; ?>
      <?}?>
    </td>
    <td rowspan="3">
    </td>
</tr>
</table>
</fieldset>

<? if($ini_array["BRUTEFIR"] =="OFF") $disable="disabled";
    else $disable=""; ?>

<fieldset <? echo $disable ?> > <!-- Filterauswahl -->
    <legend><? print ${convolution_filterselection._.$lang}?></legend>
    <? echo print_filterset(10,$ini_array)?>
</fieldset>

<input type="hidden" name="actVol" value="<?echo getVol()?>">
<input type="hidden" name="lang" value="<?echo $lang?>">
<input type="hidden" name="savedbank" value="<?echo $activeFilter?>">

<? if($ini_array['BRUTEFIR']=="ON"){?>
    <button type="submit" name="save" title="<? print ${helptext_bf_button_savereload._.$lang} ?>"><? print ${button_savefilter._.$lang}?></button>
    <button type="submit" name="set" title="<? print ${helptext_bf_button_setcoeffs._.$lang} ?>"><? print ${button_setcoeffs._.$lang}?></button>
    <button type="submit" <?if(isMuted()) echo 'style="background-color:#a00; "'; else echo 'style="color:white"';?> name="mute" value="1"><? print ${button_mute._.$lang}?></button>
<?}else{?>
    <button style="Background-color: #d6d6d6"><? print ${button_savefilter._.$lang}?></button>
    <button style="Background-color: #d6d6d6"><? print ${button_setcoeffs._.$lang}?></button>
    <button style="Background-color: #d6d6d6"><? print ${button_mute._.$lang}?></button>
<?}?>
</form>
</div>

<div class="content">
<fieldset> <!-- Erläuterungen -->
    <legend><? print ${helptext_convolver_.$lang} ?></legend>
    <? print ${helptext_bf._.$lang} ;?>
</fieldset>
</div>

<? include "footer.php"; ?>
