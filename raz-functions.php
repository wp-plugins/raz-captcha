<?php
/*
  Raz-Captcha Mixed Functions
  (C) Raz-Soft.com

*/

//list_by_ext: returns an array containing an alphabetic list of files in the specified directory ($path) with a file extension that matches $extension
function file_list_by_ext($extension, $path,$exclude="")
{
    $list = array(); //initialise a variable
    $dir_handle = @opendir($path) or die("Unable to open $path"); //attempt to open path
    while($file = readdir($dir_handle)){ //loop through all the files in the path
        if($file == "." || $file == ".."){continue;} //ignore these
        $filename = explode(".",$file); //seperate filename from extenstion
        $cnt = count($filename); $cnt--; $ext = $filename[$cnt]; //as above
        if(strtolower($ext) == strtolower($extension) && $filename!=$exclude){ //if the extension of the file matches the extension we are looking for...
            array_push($list, $file); //...then stick it onto the end of the list array
        }
    }
    if($list[0]){ //...if matches were found...
    return $list; //...return the array
    } else {//otherwise...
    return false;
    }
}

/*example usage
if($win32_exectuables = list_by_ext("exe", "C:\WINDOWS")){
    var_dump($win32_exectuables);
} else {
    echo "No windows executables found :(\n";
}*/


//-----------------------------------------------------

function java_alert($msg)
{	
 ?>
   <script type="text/javascript">
   alert ("<?php echo $msg ?>");
   </script>	
<?php	
}


//---------------------------------------------------

function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0], $color[1], $color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

//--------------------------------------------------------------

function rgb2html($r, $g=-1, $b=-1)
{
    if (is_array($r) && sizeof($r) == 3)
        list($r, $g, $b) = $r;

    $r = intval($r); $g = intval($g);
    $b = intval($b);

    $r = dechex($r<0?0:($r>255?255:$r));
    $g = dechex($g<0?0:($g>255?255:$g));
    $b = dechex($b<0?0:($b>255?255:$b));

    $color = (strlen($r) < 2?'0':'').$r;
    $color .= (strlen($g) < 2?'0':'').$g;
    $color .= (strlen($b) < 2?'0':'').$b;
    return '#'.$color;
}

//------------------------------------------------------------------------------------------------------


function strrand($stringlength,$acceptedChars)
{
	$cnum=array();
	$srnd=NULL;
	$max = strlen($acceptedChars)-1;
	for($i=0; $i < $stringlength; $i++) 
	{
		$cnum[$i] = $acceptedChars{mt_rand(0, $max)};
		$srnd .= $cnum[$i];
	}
	//java_alert($srnd);
	return $srnd;
}
			
//---------------------------------------------------------------------------------------------------
function unique_id($extra = 'c')
{
    mt_srand();
	$val = mt_rand(200,50000). microtime();
	$val = md5($val). $extra;
	return substr($val, 4, 16);
}

function gen_rand_string($num_chars = 8)
{
	$rand_str = unique_id();
	$rand_str = str_replace('0', 'Z', strtoupper(base_convert($rand_str, 16, 35)));
	return substr($rand_str, 0, $num_chars);
}

//-------------------------------------------------------------------------------------------------------

function clean_GLOBALS() 
{
	if ( !ini_get('register_globals') )
		return;

	if ( isset($_REQUEST['GLOBALS']) )
		die('GLOBALS overwrite attempt detected');

	// Variables that shouldn't be unset
	$noUnset = array('SESSION','GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix');

	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES);
	foreach ( $input as $k => $v ) 
		if ( !in_array($k, $noUnset) && isset($GLOBALS[$k]) ) {
			$GLOBALS[$k] = NULL;
			unset($GLOBALS[$k]);
		}
}

			
?>