<?php
declare(strict_types=1);

/**
* Created by Gbenga Ogunbule.
* User: Gbenga Ogunbule
* Date: 28/04/2020
* Time: 12:13
*/

namespace App\Image;

class ImageSlide
{
	
	function PIPHP_SlideShow($images) 
{ 
   $count = count($images); 
   echo "<script>images = new Array($count);\n"; 
 
   for ($j=0 ; $j < $count ; ++$j) 
   { 
      echo "images[$j] = new Image();"; 
      echo "images[$j].src = '$images[$j]'\n"; 
   } 
 
   return <<<_END 
counter = 0 
step    = 4 
fade    = 100 
delay   = 0 
pause   = 250 
startup = pause 
load('DRW_SS1', images[0]); 
load('DRW_SS2', images[0]); 
setInterval('process()', 20); 
 
function process() 
{ 
   if (startup-- > 0) return; 
 
   if (fade == 100) 
   { 
      if (delay < pause) 
      { 
         if (delay == 0) 
         { 
            fade = 0; 
            load('DRW_SS1', images[counter]); 
            opacity('DRW_SS1', 100); 
            ++counter; 
 
            if (counter == $count) counter = 0; 
 
            load('DRW_SS2', images[counter]); 
            opacity('DRW_SS2', 0); 
         } 
         ++delay; 
      } 
      else delay = 0; 
   } 
   else 
   { 
      fade += step; 
      opacity('DRW_SS1', 100 - fade); 
      opacity('DRW_SS2', fade); 
   } 
} 
 
function opacity(id, deg) 
{ 
    var object          = $(id).style; 
    object.opacity      = (deg/100); 
    object.MozOpacity   = (deg/100); 
    object.KhtmlOpacity = (deg/100); 
    object.filter       = "alpha(opacity = " + deg + ")"; 
} 
 
function load(id, img) 
{ 
   $(id).src = img.src; 
} 
 
function $(id) 
{ 
   return document.getElementById(id) 
} 
 
</script> 
_END; 
}
}