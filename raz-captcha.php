<?php

/*
Plugin Name: Raz-Captcha
Plugin URI: http://raz-soft.com/
Description: Wordpress Registration and Login <a href="http://en.wikipedia.org/wiki/Captcha">Captcha</a> Tests. Just click on <strong>activate</strong> and check your registration and login form! Featuring 5 different and customizable captcha algorithms.For more options see Options->Raz-Captcha. 
Author: <strong>Raz</strong>van-Nicusor Serban (razvaR at gmail dot com)
Version: 1.0
Author URI: http://raz-soft.com/
*/

$raz_captcha_version = '<b>v1.0.0 Beta</b> <br />first public release, more options to be added ;-)';


//---------------------------------------------------------------------------------------------------------
// THIS PLUGIN IS POWERED BY:
//--------------------------------------------------------------------------------------------------------- 
$CaptchaAlgs[0]['name']  = 'PNG-Raz MiXed Fonts';
$CaptchaAlgs[0]['author']='<strong>Raz</strong>van-Nicusor Serban (razvaR at gmail dot com)';
$CaptchaAlgs[0]['home']  ='http://raz-soft.com/about';

$CaptchaAlgs[1]['name']  = 'PNG-GOTCHA!';
$CaptchaAlgs[1]['author']= 'Sol Toure (sol2ray at gmail dot com)';
$CaptchaAlgs[1]['home']  = 'http://phpbtree.com/captcha/';

$CaptchaAlgs[2]['name']  = 'JPG-Tiny Mini';
$CaptchaAlgs[2]['author']='<strong>Raz</strong>van-Nicusor Serban (razvaR at gmail dotcom)';
$CaptchaAlgs[2]['home']  ='http://raz-soft.com/about';

$CaptchaAlgs[3]['name']  = 'PNG-phpBB3 8bit Grey';
$CaptchaAlgs[3]['author']='(c) phpBB Group';
$CaptchaAlgs[3]['home']  ='http://phpBB.com/';

$CaptchaAlgs[4]['name']  = 'PNG-phpBB3 Advanced';
$CaptchaAlgs[4]['author']='(c) phpBB Group: Xore (Robert Hetzler) With contributions from Neothermic';
$CaptchaAlgs[4]['home']  ='http://phpBB.com/';
//--------------------------------------------------------------------------------------------------------


require_once ('raz-functions.php');
if (basename($_SERVER['SCRIPT_FILENAME'])=='raz-captcha.php') clean_GLOBALS();





/*Let's avoid a brute force attack... 
$max_attempts=25;
if(empty($_SESSION['raz_captcha_attempts']))
	$_SESSION['raz_captcha_attempts'] = 1; //first atempt, welcome :-)
else 
{
	$_SESSION['raz_captcha_attempts']++;

	if($_SESSION['raz_captcha_attempts']>$max_attempts)
     {
		unset($_SESSION['rca_ses_option']);

		$bg = ImageColorAllocate($im,255,255,255);
		ImageColorTransparent($im,$bg);

		$red = ImageColorAllocate($im, 255, 0, 0);
		ImageString($im,5,15,20,"max attempts has been reached",$red);

		imagejpeg($im);
		exit();
	 }
}*/
function ImageEmboss($im)
{
	$width = imagesx($im);
	$height = imagesy($im);

	$tempim = imagecreatetruecolor($width,$height);
	$bg = imagecolorallocate($tempim,150,150,150);
	imagecolortransparent($tempim,$bg);
	imagefill($tempim,0,0,$bg);
	$dist =2;
	//merge with itself
	imagecopymerge($tempim, $im, 0, 0, 0, $dist, $width, $height-$dist,40);
	imagecopymerge($im, $tempim, 0, 0, $dist, 0, $width-$dist, $height, 40);
	imagecopymerge($tempim, $im, 0, $dist, 0, 0, $width, $height, 50);
	imagecopymerge($im, $tempim, $dist, 0, 0, 0, $width, $height, 50);
	imagedestroy($tempim);

	return $im;
}

function ImageBlur($im)
{
	$im=ImageEmboss($im);
	$width = imagesx($im);
	$height = imagesy($im);
	$tempim = imagecreatetruecolor($width,$height);
	$bg = imagecolorallocate($tempim,150,150,150);
	imagecolortransparent($tempim,$bg);
	imagefill($tempim,0,0,$bg);
	$dist =2;
	//merge with itself
	imagecopymerge($tempim, $im, 0, 0, 0, $dist, $width, $height-$dist,70);
	imagecopymerge($im, $tempim, 0, 0, $dist, 0, $width-$dist, $height, 70);
	imagecopymerge($tempim, $im, 0, $dist, 0, 0, $width, $height, 70);
	imagecopymerge($im, $tempim, $dist, 0, 0, 0, $width, $height,70);
	imagedestroy($tempim);

	return $im;
}


  if (isset($_GET['captchagen']) || basename($_SERVER['SCRIPT_FILENAME'])=='raz-captcha.php')
  {
        session_start();   
        mt_srand(time()); //just in case ...
        $option=array();
	    $option = $_SESSION['rca_ses_option'];	  
		$acceptedChars = $option['rca_chars'];
		  
        if ($option['rca_engine'] ==1) //GOTCHA! thanks Sol Toure <sol2ray at gmail dot com> 
        {
			require_once('engines/gotcha/util.php');
            require_once('engines/gotcha/gotcha.php');
			 $image_width = mt_rand(230,240);
			 $image_height = mt_rand(60,70);
			 $font_size = mt_rand(32,35);
			 $font_depth = mt_rand(4,7);; //this is the size of shadow behind the character creating the 3d effect.
			 $password =strrand(mt_rand(4,5),$acceptedChars);
			 $_SESSION['raz_captcha_gen']=md5($password);				
			 $img = new GotchaPng($image_width, $image_height);
				
				
				if($img->create())
				{
					
					//fill the background color.
					$img->apply(new GradientEffect());
					//Apply the Grid.
					$img->apply(new GridEffect(2));
					//Add dots to the background
					$img->apply(new DotEffect());
					//Add the text.
					$t  = new TextEffect($password, $font_size, $font_depth);
					$ttfs=file_list_by_ext ('ttf','engines/gotcha/');
					for ($i=0;$i<count($ttfs);$i++)
					 	$t->addFont("engines/gotcha/$ttfs[$i]");
	
					// repeat the process for as much fonts as you want. Actually, the more the better.
					// A font type will be randomly selected for each character in the text code.
					$img->apply($t);
					//Add more dots
					$img->apply(new DotEffect());
					//Output the image.
					$img->render();
				}
            
							
		}
		else if ($option['rca_engine'] ==2) //tiny mini
		{ 
			//generate a tiny mini jpeg with some numbers            
			$password =strrand(5,$acceptedChars);
			$_SESSION['raz_captcha_gen']=md5($password);
			$image = imagecreate(47,17);
			$bcol=html2rgb($option['rca_backcol']);
			$background = imagecolorallocate($image,$bcol[0],$bcol[1],$bcol[2]);
		    // Get a random color
			if ($option['rca_rndcolor'])
		    {
					 	  $red = mt_rand(0,255);
						  $green = mt_rand(0,255);
						  $blue = 255 - sqrt($red * $red + $green * $green);
			}
			else
			{
					 	  $btcol=html2rgb($option['rca_textcol']);	
						  $red = $btcol[0];
						  $green =$btcol[1];
						  $blue = $btcol[2];
			}
			
			$textcolor = imagecolorallocate($image,$red,$green,$blue);
			
			imagefill($image,0,0,$background);
			
			Imagestring($image,50,1,1,$password,$textcolor);
			$num_dots =$option['rca_dots'] ? mt_rand(150,180) : 0;  // Number of dots to draw.  0 = none
			for ($k = 0; $k < $num_dots; $k++) 
			    {
					$px1 = mt_rand(0,47);
					$py1 = mt_rand(0,17);
					$pcolor = imagecolorallocate ($image, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
					imagesetpixel($image, $px1, $py1, $pcolor);
				}
			header("Content-type: image/jpg");
			imagejpeg($image);	
			
		}
		else if ($option['rca_engine'] ==3) // phpBB3 no-gd bw png engine
		{ 

			require_once("engines/phpbb-nogd.php");
            $captcha = new captcha();
            $password=gen_rand_string(mt_rand(5, 8));
            $captcha->execute($password, time()); 
            $_SESSION['raz_captcha_gen']=md5($password);
            exit();
		
		}
		else if ($option['rca_engine'] ==4) // phpBB3 Advanced
		{ 

			require_once("engines/phpbb-gd.php");
            $captcha = new captcha();
            $password=gen_rand_string(mt_rand(5, 8));
            $captcha->execute($password, time()); 
            $_SESSION['raz_captcha_gen']=md5($password);
            exit();
		
		}		
		else
		{  
			    //generate a png complicated image with a lot of options :-)
				$acceptedChars = $option['rca_chars'];//'ABCEFGHJKMNPRSTVWXYZ123456789';
				$stringlength = mt_rand(4,5);
				$contrast = $option['rca_contrast'];
				$num_polygons = $option['rca_poligons'] ? mt_rand(3,10) : 0; // Number of triangles to draw.  0 = none
				$num_ellipses =  $option['rca_poligons'] ? mt_rand(3,10) : 0;;  // Number of ellipses to draw.  0 = none
				$num_lines = $option['rca_lines'] ? mt_rand(5,6) : 0;  // Number of lines to draw.  0 = none
				$num_dots =$option['rca_dots'] ? mt_rand(1200,1400) : 0;  // Number of dots to draw.  0 = none
				$min_thickness = 1;  // Minimum thickness in pixels of lines
				$max_thickness = 3;  // Maximum thickness in pixles of lines
				$min_radius = 5;  // Minimum radius in pixels of ellipses
				$max_radius = 15;  // Maximum radius in pixels of ellipses
				$object_alpha = 100; // How opaque should the obscuring objects be. 0 is opaque, 127 is transparent.
				$dots_alpha = mt_rand(30,50);  // dots alpha
				/*------------------------------------------------*/
				$min_thickness = max(1,$min_thickness);
				$max_thickness = min(20,$max_thickness);
				$min_radius *= 3;// Make radii into height/width
				$max_radius *= 3;// Make radii into height/width
				$contrast = 255 * ($contrast / 100.0);
				$o_contrast = 1.3 * $contrast;
				$width =180; //20 * imagefontwidth (10);
				$height =80; //4 * imagefontheight (10);
				$image = imagecreatetruecolor ($width, $height);
				imagealphablending($image, true);
				$bcol=array();
				
				if ($option['rca_backmode']==1) //random back								
  				 { $bcol[0] = mt_rand(0,255);
				   $bcol[1] = mt_rand(0,255);
				   $bcol[2] = 255 - sqrt($bcol[0] * $bcol[0] + $bcol[1] * $bcol[1]);	
 				   $background= imagecolorallocatealpha($image,$bcol[0],$bcol[1],$bcol[2],0);
				}				  
 				else
 				 {
                    $bcol=html2rgb($option['rca_backcol']);
 				    $background= imagecolorallocatealpha($image,$bcol[0],$bcol[1],$bcol[2],0);
				 }
				 
				//make the pass
				$max = strlen($acceptedChars)-1;
				$password = NULL;
				for($i=0; $i < $stringlength; $i++) {
					$cnum[$i] = $acceptedChars{mt_rand(0, $max)};
					$password .= $cnum[$i];
				}
				$_SESSION['raz_captcha_gen'] =md5($password);
				

				
				
					if ($option['rca_backmode']==2) // texture background?
					 {
						$temp_im = imagecreatefromjpeg(dirname(__FILE__).'/img/razcap1.jpg');
						$temp_width = imagesx($temp_im);
						$temp_height = imagesy($temp_im);
						$blocksize = mt_rand(120,180);
						
						for($i=0 ; $i<$width*2 ; $i+=$blocksize)
						{
							for($j=0 ; $j<$height*2 ; $j+=$blocksize)
							{
								//$image_index = mt_rand(0,sizeof($temp_im)-1);
								$cut_x = mt_rand(0,$temp_width-$blocksize);
								$cut_y = mt_rand(0,$temp_height-$blocksize);
								imagecopymerge($image, $temp_im, $i, $j, $cut_x, $cut_y, $blocksize, $blocksize,mt_rand(35,45));
							}
						}
						
					ImageDestroy($temp_im);				
                    }
                    
                $rotated = imagecreatetruecolor ($width,$height);    
				$x = 0;					
				for ($i = 0; $i < $stringlength; $i++) 
				{
					$buffer = imagecreatetruecolor (90, 90);
					$buffer2 = imagecreatetruecolor (90, 90);
					imagefill($image,0,0,$background);
					imagefill($buffer,0,0,$background);
					imagefill($buffer2,0,0,$background);
					imagefill($rotated,0,0,$background);
					
					// Get a random color
					$btcol = array();
					if ($option['rca_rndcolor'])
					 {
					 	  $btcol[0] = mt_rand(0,255);
						  $btcol[1] = mt_rand(0,255);
						  $btcol[2] = 255 - sqrt($btcol[0] * $btcol[0] + $btcol[1] * $btcol[1]);
					 }
					 else
					   {
					 	  $btcol=html2rgb($option['rca_textcol']);	
					   }
					   
			         $color=imagecolorallocatealpha ($buffer, $btcol[0], $btcol[1], $btcol[2],$option['rca_textalpha']);									
					//get a random font, or just the first one
					$fontstot=count($option['rca_fonts'])-1;
					$fcur=mt_rand(0,$fontstot);
					 
				    $fname="fonts/".$option['rca_fonts'][$fcur];
				    
					while (!file_exists($fname))
				     {
					  if ($fcur>$fontstot)	
		 			   $fcur=0;
		 			  else
		 			   $fcur++;
		 			   
		 			  $fname="fonts/".$option['rca_fonts'][$fcur]; 
					 }
				    
					$font=imageloadfont("$fname");
					$fh=imagefontheight($font)*2;
					$fw=imagefontwidth($font)*2;
					
				
					// Create character		
					imagestring($buffer, $font, 0, 0, $cnum[$i], $color);
					
					//imagecolorallocatealpha ($buffer, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast),$dots_alpha);
					//draw dots on char
					if ($option['rca_blur']!=1 && $option['rca_emboss']!=1)//don't mix dots with blur or it will be a total mess :-)
					{ 
						if ($num_dots>0) $chd=$num_dots/3;
						for ($k = 0; $k < $chd; $k++) 
						{
							$px1 = mt_rand(0,80);
							$py1 = mt_rand(0,80);
							$pcolor = imagecolorallocatealpha ($buffer, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast),$dots_alpha);               							      
							imagesetpixel($buffer, $px1, $py1, $pcolor);
						}
					  }
					 $fwr= $fw + mt_rand(1,10);
					 $fhr= $fh + mt_rand($fw,$fw+10);
					// Resize character
				     imagecopyresized ($buffer2, $buffer, 0, 0, 0, 0, $fwr, $fhr, $fw, $fh);
				     //imagecopy($buffer2, $buffer, 0, 0, 0, 0, $fw, $fh);
				    
					// Rotate characters a little
					$angle=($i & 2) ?  mt_rand(-25, -5):  mt_rand(5, 25);
					imagecolortransparent ($buffer2, imagecolorallocatealpha($buffer2,$bcol[0],$bcol[1],$bcol[2],0));
					$rotated = imagerotate($buffer2,$angle, $background);//imagecolorallocatealpha($buffer2,0,0,0,0)); 
					imagecolortransparent ($rotated, imagecolorallocatealpha($rotated,$bcol[0],$bcol[1],$bcol[2],0));
					//imagecolortransparent ($rotated, imagecolorallocatealpha($rotated,0,0,0,0));
				    if ($option['rca_blur'])//blur char?
					  $rotated=ImageBlur($rotated);
					if ($option['rca_emboss']) //emboss char?
					  $rotated=ImageEmboss($rotated);
					  
					// Move characters around a little
					$y = mt_rand(0, 5);
					$x += mt_rand(0, 3);					 
					imagecopymerge ($image, $rotated, $x, $y, 0, 0, $fwr, $fhr, 50);
					$x += $fwr - ($fwr/3);
					
					imagedestroy ($buffer); 
					imagedestroy ($buffer2); 
					
				}
				
				if ($num_polygons > 0) for ($i = 0; $i < $num_polygons; $i++) {
					$vertices = array (
						mt_rand(-0.25*$width,$width*1.25),mt_rand(-0.25*$width,$width*1.25),
						mt_rand(-0.25*$width,$width*1.25),mt_rand(-0.25*$width,$width*1.25),
						mt_rand(-0.25*$width,$width*1.25),mt_rand(-0.25*$width,$width*1.25)
					);
					$color = imagecolorallocatealpha ($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), $object_alpha);
					imagefilledpolygon($image, $vertices, 3, $color);  
				}
				
				if ($num_ellipses > 0) for ($i = 0; $i < $num_ellipses; $i++) {
					$x1 = mt_rand(0,$width);
					$y1 = mt_rand(0,$height);
					$color = imagecolorallocatealpha ($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), $object_alpha);
				//	$color = imagecolorallocate($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast));
					imagefilledellipse($image, $x1, $y1, mt_rand($min_radius,$max_radius), mt_rand($min_radius,$max_radius), $color);  
				}
				
				if ($num_lines > 0) for ($i = 0; $i < $num_lines; $i++) {
					$x1 = mt_rand(-$width*0.25,$width*1.25);
					$y1 = mt_rand(-$height*0.25,$height*1.25);
					$x2 = mt_rand(-$width*0.25,$width*1.25);
					$y2 = mt_rand(-$height*0.25,$height*1.25);
					//$color = imagecolorallocatealpha ($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), $object_alpha);
					$color = imagecolorallocatealpha($image, $btcol[0], $btcol[1], $btcol[2],$option['rca_textalpha']);

					imagesetthickness ($image, mt_rand($min_thickness,$max_thickness));
					imageline($image, $x1, $y1, $x2, $y2 , $color);  
				}
				
				if ($num_dots > 0) for ($i = 0; $i < $num_dots; $i++) {
					$x1 = mt_rand(0,$width);
					$y1 = mt_rand(0,$height);
					if ($option['rca_dotsrandomcolor'])
					  $color = imagecolorallocatealpha ($image, mt_rand(0,$o_contrast), mt_rand(0,$o_contrast), mt_rand(0,$o_contrast),$dots_alpha);                  
					else
					 {
					  $bcol=html2rgb($option['rca_dotscolor']);	
					  $color = imagecolorallocatealpha ($image,$bcol[0],$bcol[1], $bcol[2],$dots_alpha);                   
					 }
					imagesetpixel($image, $x1, $y1, $color);
				}
				
					
				header('Content-type: image/png');
				imagepng($image);
	          }
	    if ($image) imagedestroy($image);
		unset($password); // just in case...
		unset($option);
		exit();
  }
  
//--------------------------------------------------------------------------
function get_default_preset($option=array())
{

	$option['rca_login'] = 1;
	$option['rca_register'] = 1;
	$option['rca_engine'] = 1;
	$option['rca_trys'] = 15;
	$option['rca_dots'] = 1;
	$option['rca_rndcolor'] = 0;
	$option['rca_contrast'] = 70;
	$option['rca_poligons'] = 0;
	$option['rca_lines'] = 1;
	$option['rca_blur'] = 0;
	$option['rca_emboss'] = 0;
	$option['rca_backmode'] = 2;
	$option['rca_backcol'] ="#0D3351";
	$option['rca_textcol'] ="#FFFFFF";
	$option['rca_chars'] = '0123456789';
	$option['rca_fonts'] = array("One-Stroke.gdf","wp-captcha1.gdf","wp-captcha9.gdf"); //file_list_by_ext ('gdf',ABSPATH.'/wp-content/plugins/raz-captcha/fonts/');
	$option['rca_textalpha']=30; 
	$option['rca_cpreset']="Default";
	$option['rca_dotsrandomcolor']=1;
	$option['rca_dotscolor']="#FFFFFF";
	$option['rca_msgnote']="Note: The verification code must be the same as the one from the generated image to continue (click on image to generate another)";

    return $option;	
}

//---------------------------------------------------------------------------
 
function init_captcha()
{	
  	//we need captcha on login/register pages only ;-)
  	$cscrf=basename($_SERVER['SCRIPT_FILENAME']);
    if ($cscrf=='wp-login.php' || $cscrf=='wp-register.php' || ($cscrf=='options-general.php' && $_GET['page']=='raz-captcha.php') )
     {
        define ("RAZ_CAPTCHA",1);
        session_start();
        unset($_SESSION['rca_ses_option']);
        $rca_ses_option = array();
        $rca_ses_option = get_option('Raz_Captcha_Option');
        $_SESSION['rca_ses_option']=$rca_ses_option;	
	 }
	
}


//-----------------------------------------------------

function add_refresh_captcha()
{	
   if (defined("RAZ_CAPTCHA")) {   		
?>	<script type="text/javascript">
rnd.today=new Date();
rnd.seed=rnd.today.getTime();

	function rnd() {
	        rnd.seed = (rnd.seed*9301+49297) % 233280;
	        return rnd.seed/(233280.0);
	};
	
	function rand(number) {
	        return Math.ceil(rnd()*number);
	      };  
      
    function refresh_captcha() {
			var rcap=document.getElementById('raz_captcha');
			if (rcap) rcap.src='<?php echo get_option('home');?>/wp-content/plugins/raz-captcha/raz-captcha.php?captchagen='+rand(30000);	
			rcap=document.getElementById('imgver_string');
			if (rcap) rcap.focus()
			return false;
		};
	</script>     
<?php }
 	
}


//------------------------------------------------------


function insert_captcha_code()
{ 		
?><!-- raz captcha check -->
        <p><center><a onclick="javascript:event.returnValue=refresh_captcha(); return event.returnValue;" href="#" rel="nofollow" ><img title="click me to refresh" id="raz_captcha" align="center" src="<?php echo get_option('home');?>/wp-content/plugins/raz-captcha/raz-captcha.php?captchagen=<?php echo rand(10,999999);?>" border="0" /></a></center></p>
		<p><label for="code">Please enter the code shown above:</label>
		<input class="input" name="imgver_string" id="imgver_string" type="text" size="20" tabindex="30" value="" /></p>
 <!-- end of raz captcha check -->	
<?php
    //<input type="hidden" name="raz_captcha" value="raz_captcha" />
}

//---------------------------------------------------

function reget_session_options()
{
 //let's reget the options, being paranoia helps :-))
 unset($_SESSION['rca_ses_option']);
 $_SESSION['rca_ses_option'] = get_option('Raz_Captcha_Option'); 	
}

//-----------------------------------------------------

function captcha_login()
{ 
   reget_session_options();
   if ($_SESSION['rca_ses_option']['rca_login'])
      insert_captcha_code();	
}
	
//---------------------------------------------------


function captcha_register()
{ 
    reget_session_options();
	if ($_SESSION['rca_ses_option']['rca_register'])
      insert_captcha_code();	
}
	
//---------------------------------------------------

function check_captcha()
{ 
	 //just a quick check ;-)	 
  if (!defined("RAZ_CAPTCHA") )     	
   	    return "Hacking attempt?";
   	
  if ((!isset($_POST['wp-submit']) && !isset($_POST['submit'])) || empty($_SESSION['raz_captcha_gen'])) 
    {		
		if (!empty($_SESSION['rca_ses_option']['rca_msgnote'])) 
		 return $_SESSION['rca_ses_option']['rca_msgnote']; 
        
		return;
    }
  elseif (md5($_POST['imgver_string'])!=$_SESSION['raz_captcha_gen'])
	  return "Invalid verification code! ".$_SESSION['rca_ses_option']['rca_msgnote'];
 
  //no spoofed session id re-use?	  
  unset($_SESSION['raz_captcha_gen']); 
  $_SESSION['raz_captcha_attempts']=0;
  unset($_SESSION['rca_ses_option']);
}  
  
//---------------------------------------------------
 
function check_captcha_login()
{ 
  reget_session_options();	
  if ($_SESSION['rca_ses_option']['rca_login'])
    {
      global $errors;
	  $err=check_captcha();	
	  if ($err) $errors['error']=$err;
	  //return $errors['error']; 
	}
  
}
 
//----------------------------------------------------------------

function check_captcha_register()
{ 
  reget_session_options(); 	
  if ($_SESSION['rca_ses_option']['rca_register'])
    return check_captcha();
  
}

//---------------------------------------------------------------

function get_preset_index($presets,$presetname)
{
	if (!is_array($presets)) $presets = get_option('Raz_Captcha_Presets'); //load presets if they are not already...
	
				
			$prescnt = count($presets);
			for ($p=0;$p<$prescnt;$p++)
			 {
			    if (strtolower($presets[$p]['rca_cpreset'])==$presetname) // search for a preset with the same name
			     {
			       $prescnt=$p;
				   break;	  	
				 }
				 	
			 }
	
	return $prescnt;
}

//----------------------------------------------------------------
//Wordpress Options page goes here...
//----------------------------------------------------------------

function Raz_Captcha_Page() 
{
	global $raz_captcha_version,$CaptchaAlgs; //$wpdb, $table_prefix
	$option  = array();
	$prests  = array();
	$option  = get_option('Raz_Captcha_Option');
	$presets = get_option('Raz_Captcha_Presets');
				
if (isset($_POST['rca_savesett']) ||isset($_POST['rca_savepreset']) || isset($_POST['rca_applypreset']) || isset($_POST['rca_applyengine']) ) 
{

	if ($_POST['rca_applypreset']) //load selected preset
	{
		    //load selected preset				
		    $newpres=strtolower($_POST['rca_applypreset']);
			//java_alert($newpres." set active preset");  	
			$prescnt = count($presets);
			for ($p=0;$p<$prescnt;$p++)
			 {
			    if (strtolower($presets[$p]['rca_cpreset'])==$newpres) // search for the preset
			     {
				   $option=$presets[$p]	; //found it!
				   break; //bail...  	
				 }
				 	
			 }
			 
	}
	else
	{	
		if (is_numeric($_POST['rca_applyengine']))
		  {
		  	//load defaults
		  	$option=get_default_preset($option);
		  	$option['rca_engine'] =$_POST['rca_applyengine'];

		  }
		  else
		  { //load the one modified
			$option['rca_login'] = $_POST['rca_login'];
			$option['rca_register'] = $_POST['rca_register'];
			$option['rca_dots'] = $_POST['rca_dots'];
			$option['rca_rndcolor'] = $_POST['rca_rndcolor'];
			$option['rca_contrast'] = $_POST['rca_contrast'];
			$option['rca_chars'] = $_POST['rca_chars'];//ABCEFGHJKMNPRSTVWXYZ123456789';
			$option['rca_poligons'] = $_POST['rca_poligons'];
			$option['rca_lines'] = $_POST['rca_lines'];
			$option['rca_backcol']=$_POST['rca_backcol'];
			$option['rca_textcol']=	$_POST['rca_textcol'];
			$option['rca_fonts']= $_POST['rca_fonts'];
			$option['rca_textalpha']=$_POST['rca_textalpha'];
			$option['rca_blur']=$_POST['rca_blur'];
			$option['rca_emboss']=$_POST['rca_emboss'];
			$option['rca_backmode']=$_POST['rca_backmode']; //0= custom text, 1= random color, 2= random image
			$option['rca_dotscolor']=$_POST['rca_dotscolor'];
			$option['rca_dotsrandomcolor']=$_POST['rca_dotsrandomcolor'];
			$option['rca_msgnote']=$_POST['rca_msgnote'];
		  }
		  
		if (isset($_POST['rca_savepreset'])) //save preset?
		  {
		  	 $newpres=strtolower($_POST['rca_setpresetname']);  
             $prescnt=get_preset_index($presets,$newpres);
			 $presets[$prescnt]=$option;
			 $presets[$prescnt]['rca_cpreset']=$_POST['rca_setpresetname']; //preset name 
			 $option['rca_cpreset']=$_POST['rca_setpresetname']; //set last saved preset as active one...
			 update_option('Raz_Captcha_Presets', $presets); //update presets...
					  	
		  }
	   else if (isset($_POST['rca_deletepreset'])) //delete preset?
	    {
		   $newpres=strtolower($_POST['rca_presset']);		     	  
		   $prescnt=get_preset_index($presets,$newpres);
		   //java_alert($newpres. " index:".$prescnt);
		   unset($presets[$prescnt]);
		   array_unshift ($presets, array_shift ($presets));
		   update_option('Raz_Captcha_Presets', $presets); //update presets...
		   
		 }
		 
	}
		
	update_option('Raz_Captcha_Option', $option);
	$_SESSION['rca_ses_option']=$option;	
?><div class="updated fade"><p><strong><?php  _e('Captcha Options updated.', 'mt_trans_domain' ); ?></strong></p></div>
<?php
}
/*else if (isset($_POST['rca_savepreset'])) //save to new preset
{
  java_alert ($_POST['rca_setpresetname']." -save to new preset");
}
else if (isset($_POST['rca_setpreset'])) //set selected preset
{
  java_alert ($_POST['rca_setpresetname']." -set selected preset");
}*/



   $fntlist=file_list_by_ext ('gdf',ABSPATH.'/wp-content/plugins/raz-captcha/fonts/');?>		

<script type="text/javascript">
      
    function preview_captcha_font(fname) {
			var rcap=document.getElementById('raz_captcha_font_preview');
			if (rcap) rcap.src='<?php echo get_option('home');?>/wp-content/plugins/raz-captcha/raz-fontpreview.php?font='+fname;	
			return false;
		};
		
		
    function options_select_item(theid,theindex) {
			var rcap=document.getElementById(theid);
			if (rcap) rcap.options[theindex].selected=true;	
			return false;
		};
		
	  function validate_rca(frm){
		var rcap=document.getElementById('rca_fonts');
		if (rcap)
		 {
		 	/*if (rcap.selectedIndex == -1)
		 	{
				alert ('Please select at least one font!');
				return false;
			}*/
		 	 rcap.name = 'rca_fonts[]'; //php workaround: <SELECT> is buggy with names containing arrays []
		 
		 }
		return true;
	  };
	  
	 function set_preset_name(defname) {
            var inputone = prompt("Give your preset a name:\n(preset with the same name will be overwrited)",defname);
			if (inputone) 
			  { 
			  	var rcap=document.getElementById('rca_setpresetname');
				  if (rcap)
				   { 
				   	 rcap.value=inputone;
				     return true;
				   }
				  else
				    return false;
			  }
			else
			  return false;
		}; 

	 function apply_selected(presvalue,elementid) {
			  	var rcap=document.getElementById(elementid);
				  if (rcap)
				   { 
				   	 rcap.value=presvalue;
				   	 document.manageoptions.submit();
				     return true;
				   }
				  else
				    return false;
		}; 
		
	 function areyousure(question) {	  
	 	
	 	 return confirm (question);
	 	
	 	};
</script>	
<div class="wrap">		
		<fieldset class="option">
			<form name="manageoptions" id="manageoptions" method="post" onsubmit="javascript:return validate_rca(this);">			
			<h2>Raz-Captcha Options</h2>
<p style="float:right"> Captcha engine: <SELECT NAME="rca_engine" onchange="javascript:apply_selected(this.selectedIndex,'rca_applyengine');" ><?php
		for ($e=0;$e<count($CaptchaAlgs);$e++)
		{
			   echo "\r\n<OPTION VALUE=\"{$CaptchaAlgs[$e]['name']}\"". ($option['rca_engine']==$e ?  " SELECTED " : "") .">{$CaptchaAlgs[$e]['name']}";	
		}
		echo "\r\n";				 	
?></SELECT></p>			
				<p><input name="rca_register" type="checkbox" id="rca_register" value="1" <?php if ($option['rca_register']==1) echo ' checked="1"';?> />
<label for="rca_register">Captcha check on <b>Register</b> page </label></p>
			    <p><input name="rca_login" type="checkbox" id="rca_login" value="1" <?php if ($option['rca_login']==1) echo ' checked="1"';?>  />
<label for="rca_login">Captcha check on <b>Login</b> page </label></p>
			<h3>- - ---- Customize Captcha - - ----</h3>
<p>Info Message: <input type="text" name="rca_msgnote" id="rca_msgnote" size="90" value="<?php echo $option['rca_msgnote']; ?>" /></p>
<?php if ($option['rca_engine']==0 || $option['rca_engine']==2) { ?> 	
			<p><input name="rca_dots" type="checkbox" id="rca_dots" value="1" <?php if ($option['rca_dots']==1) echo ' checked="1"';?>  />
<label for="rca_dots">Draw Random <b>Dots</b> </label></p>
			<p><input name="rca_dotsrandomcolor" type="checkbox" id="rca_dotsrandomcolor" value="1" <?php if ($option['rca_dotsrandomcolor']==1) echo ' checked="1"';?>  />
<label for="rca_dotsrandomcolor">Random Color <b>Dots</b> </label></p><?php if ($option['rca_engine']==0) { ?>
			<p><input name="rca_poligons" type="checkbox" id="rca_poligons" value="1" <?php if ($option['rca_poligons']==1) echo ' checked="1"';?>  />
<label for="rca_poligons">Draw Random <b>Poligons</b> </label></p>
			<p><input name="rca_lines" type="checkbox" id="rca_lines" value="1" <?php if ($option['rca_lines']==1) echo ' checked="1"';?>  />
<label for="rca_lines">Draw Random <b>Lines</b> </label></p><?php } ?>
			<p><input name="rca_rndcolor" type="checkbox" id="rca_rndcolor" value="1" <?php if ($option['rca_rndcolor']==1) echo ' checked="1"';?>  />
<label for="rca_rndcolor">Random Color <b>Text</b> </label> <small>(draw text with random colors)</small></p>
<p><input name="rca_blur" type="checkbox" id="rca_blur" value="1" <?php if ($option['rca_blur']==1) echo ' checked="1"';?>  />
<label for="rca_blur">Blur <b>Text</b> </label> <small>(draw text with blur effect)</small></p>
<p><input name="rca_emboss" type="checkbox" id="rca_emboss" value="1" <?php if ($option['rca_emboss']==1) echo ' checked="1"';?>  />
<label for="rca_emboss">Emboss <b>Text</b> </label> <small>(draw text with emboss effect)</small></p>
<p>Back Mode:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SELECT NAME="rca_backmode"><?php
$backmode[0] = 'Custom Color';
$backmode[1] = 'Random Color';
$backmode[2] = 'Random Image Texture';
for ($e=0;$e<count($backmode);$e++)
{
	   echo "\r\n<OPTION VALUE=\"$e\"". ($option['rca_backmode']==$e ?  " SELECTED " : "") .">$backmode[$e]";	
}
echo "\r\n";?></SELECT></p>	
<p>Dots Color:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="rca_dotscolor" id="rca_textcol" size="10" value="<?php echo $option['rca_dotscolor']; ?>" /> <small>(default: #FFFFFF)</small></p>			 				
				<p>Text Color:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="rca_textcol" id="rca_textcol" size="10" value="<?php echo $option['rca_textcol']; ?>" /> <small>(default: #FFFFFF)</small></p>
<?php if ($option['rca_engine']==0) { ?><p>Text Alpha:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="rca_textalpha" id="rca_textalpha" size="10" value="<?php echo $option['rca_textalpha']; ?>" /> <small>(<b>0</b>=opaque or <b>127</b>=completely transparent; default: <b>30</b>)</small></p> <?php } ?>
				<p>Back Color:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="rca_backcol" id="rca_backcol" size="10" value="<?php echo $option['rca_backcol']; ?>" /> <small>(default: #0D3351)</small></p>
<?php if ($option['rca_engine']==0) { ?><p>Draw Contrast: <input type="text" name="rca_contrast" id="rca_contrast" size="10" value="<?php echo $option['rca_contrast']; ?>" /> <small>(default: 70)</small></p> <?php } } if ($option['rca_engine']<3) {  ?>
				<p>Accepted Chars: <input type="text" name="rca_chars" id="rca_chars" size="30" value="<?php echo $option['rca_chars']; ?>" /> <small>(<b>case <u>sensitive!</u></b> -default: ABCDEFGHIJKLMNOPWRSTUVWXYZ123456789)</small></p>
<?php } if ($option['rca_engine']==0) { ?><p>Captcha text Font(s)</u>: <img title="font preview" id="raz_captcha_font_preview" src="<?php echo get_option('home');?>/wp-content/plugins/raz-captcha/raz-fontpreview.php?font=<?php echo $option['rca_fonts'][0]; ?>" border="0" align="right" /> <br /> 				
				<SELECT NAME="rca_fonts" ID="rca_fonts" ONCHANGE="javascript:preview_captcha_font(this.options[this.selectedIndex].value);" MULTIPLE SIZE="8"  ><?php //alert('Index: ' + this.selectedIndex+ '\nValue: ' + this.options[this.selectedIndex].value)"> 				
				$totfiles=count($fntlist);
				$totselected=count($option['rca_fonts']);
				for ($i=0;$i<$totfiles;$i++)
				{
				   $sel="";
				   //check if we need to select him...				  
				   for ($j=0;$j<$totselected;$j++)
					 	if ($option['rca_fonts'][$j]==$fntlist[$i]) $sel="SELECTED";	
				   echo "\r\n<OPTION VALUE=\"$fntlist[$i]\" $sel >$fntlist[$i]";
					
				}echo "\r\n";?></SELECT><br/> <small>- use <b>ctrl</b> or <b>shift</b> for multiple selections ( multiple selected font(s) will be used randomly for each character)</small> </p><?php } ?> <br />

<h3>- - ---- Preview Captcha - - ----</h3>
<p><a onclick="event.returnValue=refresh_captcha(); return event.returnValue;" href="#" ><img title="click on image to regenerate" id="raz_captcha" src="<?php echo get_option('home');?>/wp-content/plugins/raz-captcha/raz-captcha.php?captchagen=<?php echo rand(10,999999);?>" border="0" /></a><br /><small>- <b>click</b> on image to regenerate<br /> -  you must <b>Update Options</b> to apply your changes</small></p>
		<p>&nbsp;&nbsp;</p>	
		<p align="center"><input name="rca_savesett" type="submit" style="font-weight:bold" value="&nbsp;&nbsp;Update Options...&nbsp;&nbsp;" /><input name="rca_savepreset" type="submit" style="font-weight:bold" value="&nbsp;Save To New Preset..." onclick="javascript:event.returnValue=set_preset_name('new captcha preset ('+ rca_engine.value +')'); return event.returnValue;" />
</p>	
<h3>- - ---- Captcha Presets - - ----</h3>
<p style="float:left">
<SELECT NAME="rca_presset" onchange="javascript:apply_selected(this.options[this.selectedIndex].value,'rca_applypreset');" ><?php
$prescnt = count($presets);
for ($p=0;$p<$prescnt;$p++)
{
	if ($presets[$p]['rca_cpreset']==$option['rca_cpreset']) // is this the selected preset?
	   echo "\r\n<OPTION VALUE=\"{$presets[$p]['rca_cpreset']}\" SELECTED >{$presets[$p]['rca_cpreset']}";	
	else
	   echo "\r\n<OPTION VALUE=\"{$presets[$p]['rca_cpreset']}\" >{$presets[$p]['rca_cpreset']}";	
}

echo "\r\n";				 	
?></SELECT><br/><input name="rca_deletepreset" type="submit" style="font-weight:bold" value="Delete" onclick="javascript:event.returnValue=areyousure('Delete preset ('+ rca_presset.value +')? \nAre you sure?'); return event.returnValue;"/><!--<input name="rca_exportpreset" type="submit" style="font-weight:bold" value="Export..." /><input name="rca_importpreset" type="submit" style="font-weight:bold" value="Import..." />--></p><br /><p style="clear:both"><small>- here you can <b>manage</b> your captcha settings<br /> 
- customize the image to satisfy your creative needs and click on <b>Save to new Preset</b> to save it<br />
- if you want to use a preset just select one
- remove a preset by selecting it and pressing on <b>Delete</b><br />
- don't forget to activate the plugin with "Captcha check on Register page" or "Captcha check on Login page" options
<!-- - you can share the presets with your friends, just use <b>Export</b> And <b>Import</b>... --> </small><br />
				</p>
<input type="hidden" id="rca_setpresetname" name="rca_setpresetname" value="" />
<input type="hidden" id="rca_applypreset" name="rca_applypreset" value="" />
<input type="hidden" id="rca_applyengine" name="rca_applyengine" value="" />											
			</form> 
		</fieldset>


		<h2>&nbsp;&nbsp;</h2>

		
		<br />
		<p align="center">Powered by <br /> <a href="http://raz-soft.com/about" title="Raz"><img alt="Raz" src="<?php echo get_option('home').'/wp-content/plugins/raz-captcha/img/raz.gif'; ?>" /></a><br /> <small><?php echo $raz_captcha_version ?></small></p>
	    <p align="center"> Current <b>active</b> engine: <b><font color="#FF0000"><?php echo $CaptchaAlgs[$option['rca_engine']]['name'];?></font></b><br /> powered <b>by :</b> <br /><a href="<?php echo $CaptchaAlgs[$option['rca_engine']]['home'];?>"> <?php echo $CaptchaAlgs[$option['rca_engine']]['author']; ?></a></p>
	    
		<h2>&nbsp;&nbsp;</h2>
	</div>
<?php	

}

//----------------------------------------------------------------
//... and will end here...
//----------------------------------------------------------------



//install the plugin
function Raz_Captcha_Install() 
{
	//global $wpdb, $table_prefix;
	
	$option  = array();
	$presets = array();
    $option=get_default_preset($option);
	$presets[0]=$option;
	$presets[1]=$option;
	$presets[1]['rca_login'] = 1;
	$presets[1]['rca_register'] = 1;
	$presets[1]['rca_engine'] = 0;
	$presets[1]['rca_dots'] = 0;
	$presets[1]['rca_rndcolor'] = 1;
	$presets[1]['rca_contrast'] = 70;
	$presets[1]['rca_poligons'] = 0;
	$presets[1]['rca_lines'] = 1;
	$presets[1]['rca_blur'] = 0;
	$presets[1]['rca_emboss'] = 0;
	$presets[1]['rca_backmode'] = 1;
	$presets[1]['rca_backcol'] ="#000000";
	$presets[1]['rca_textcol'] ="#000000";
	$presets[1]['rca_chars'] = '0123456789';
	$presets[1]['rca_fonts'] = array("One-Stroke.gdf","wp-captcha1.gdf","wp-captcha9.gdf"); //file_list_by_ext ('gdf',ABSPATH.'/wp-content/plugins/raz-captcha/fonts/');
	$presets[1]['rca_textalpha']=30; 
	$presets[1]['rca_cpreset']="numbers and lines on random back color (PNG-Raz MiXed Fonts)";
	$presets[1]['rca_dotsrandomcolor']=1;

	
	add_option('Raz_Captcha_Option', $option, 'Raz-Captcha Plugin Settings');
	add_option('Raz_Captcha_Presets', $presets, 'Raz-Captcha Plugin Presets');
	
	/*$table_name = $table_prefix . 'raz_captcha';
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE `$table_name` (
				`id` bigint(20) NOT NULL auto_increment,
				`title` varchar(255) NOT NULL,
				`url` varchar(255) NOT NULL,
				`count` int(11) NOT NULL default '0',
				'details' varchar(255) NOT NULL,
				'notes' varchar(255) NOT NULL,
				primary key (`id`),
				UNIQUE KEY `url` (`url`)
				);";
		mysql_query($sql) or die("An unexpected error occured." . mysql_error());
	}*/
}


//------------------------------------------------------------------------------
//add Raz-Captcha submenu in Wordpress
//------------------------------------------------------------------------------

function Raz_Captcha_Configuration() 
{
	if (function_exists('add_submenu_page')) {
		add_options_page('Raz-Captcha', 'Raz-Captcha', 8, basename(__FILE__), 'Raz_Captcha_Page');
	}
	
}

//uninstall the plugin, clean up...
function Raz_Captcha_Uninstall() 
{
	
	delete_option('Raz_Captcha_Option');
	delete_option('Raz_Captcha_Presets');
	/*global $wpdb, $table_prefix;
	
	$table_name = $table_prefix . 'raz_captcha';
	/*$option = get_option('Raz_Captcha_Option');
	
	if ($option['cu'] == 1) {
		$sql = "DROP TABLE `$table_name`";
		mysql_query($sql) or die("An unexpected error occured." . mysql_error());		
	}*/	
}


//-----------------------------------------------------------------------------------
// add Wordpress actions and filters

add_action('activate_raz-captcha/'.basename(__FILE__), 'Raz_Captcha_Install');
add_action('deactivate_raz-captcha/'.basename(__FILE__), 'Raz_Captcha_Uninstall');
add_action('admin_menu', 'Raz_Captcha_Configuration');
add_action('admin_head','add_refresh_captcha'); 
/*add_action('admin_menu', 'iMP_Download_Configuration');
add_action('wp_head', 'iMP_Download_Style');
add_filter('the_content', 'iMP_Download_Insert');*/


add_action('register_form', 'captcha_register');
add_action('login_form', 'captcha_login');
add_action('init', 'init_captcha');

add_filter('registration_errors', 'check_captcha_register'); 
add_action('wp_authenticate', 'check_captcha_login');

add_action('login_head' ,'add_refresh_captcha');


?>