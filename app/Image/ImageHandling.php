<?php
declare(strict_types=1);

/**
* Created by Gbenga Ogunbule.
* User: Gbenga Ogunbule
* Date: 08/07/2019
* Time: 07:47
*/

namespace App\Image;

class ImageHandling{
	private $image;
	private $filetypes;
	private $height;
	private $width;
	private $fromFile;
	private $toFile;
	private $file;
	private $type;
	private $quality;

	public function uploadFile($name, $filetypes, $maxlen){
		if(!isset($_FILES[$name]['name']))
		return array(-1, NULL, NULL);

		if(!in_array($_FILES[$name]['type'],$filetypes)){
			return array(-2, NULL, NULL);
		}

		if($_FILES[$name]['size']>$maxlen){
			return array(-3, NULL, NULL);
		}

		if($_FILES[$name]['error']>0){
			return array($_FILES[$name]['error'], NULL, NULL);
		}

		$temp = file_get_contents($_FILES[$name]['tmp_name']);
		return array(0, $_FILES[$name]['type'], $temp);
	}

	/**
	 This plug-in accepts an image to be resized and the new dimensions required. It takes these arguments:
	*
	* $image  An image to be transformed, as a GD library object
	* $width  The new required width
	* $height  The new height
	*/
	public function imageResize($image, $width, $height){
		$oldw = imagesx($image);
		$oldh = imagesy($image);
		$temp = imagecreatetruecolor($width, $height);
		imagecopyresampled($temp, $image, 0, 0, 0, 0, $width, $height, $oldw, $oldh);
		return $temp;
	}
	
	/** This plug-in accepts an image to be converted into a thumbnail and the new maximum width or height. It takes these arguments:
	* $image  A GD image to be transformed
	* $max  The new maximum width or height (whichever is the greater dimension)
	*/
	public function makeThumbnail($max){
		$thumbw = $w = imagesx($this->image);
		$thumbh = $h = imagesy($this->image);

		if($w > $h && $max < $w){
			$thumbh = $max / $w * $h;
			$thumbw = $max;
		}elseif($h > $w && $max < $h){
			$thumbw = $max / $h * $w;
			$thumbh = $max;
		}elseif($max < $w){
			$thumbw = $thumbh = $max;
		}

		return imageResize($image,$thumbw,$thumbh);
	}

	/**
	* This plug-in accepts an image to be converted, along with the transformation required. It takes these arguments:
 	* $image  A GD image to be transformed
	* $effect  The transformation to apply, between 1 and 14:
	*/
	public function imageAlter($image, $effect){
		switch($effect){
			case 1:imageconvolution($image, array(array(-1,-1,-1),
					array(-1,16,-1),array(-1,-1,-1)),8,0);
			break;
			case 2:imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
			break;
			case 3:imagefilter($image, IMG_FILTER_BRIGHTNESS,20);
			break;
			case 4:imagefilter($image, IMG_FILTER_BRIGHTNESS,-20);
			break;
			case 5:imagefilter($image, IMG_FILTER_CONTRAST,-20);
			break;
			case 6:imagefilter($image, IMG_FILTER_CONTRAST,20);
			break;
			case 7:imagefilter($image, IMG_FILTER_GRAYSCALE);
			break;
			case 8:imagefilter($image, IMG_FILTER_NEGATE);
			break;
			case 9:imagefilter($image, IMG_FILTER_COLORIZE,128,0,0,50);
			break;
			case 10:imagefilter($image, IMG_FILTER_COLORIZE,0,128,0,50);
			break;
			case 11:imagefilter($image, IMG_FILTER_COLORIZE,0,0,128,50);
			break;
			case 12:imagefilter($image, IMG_FILTER_EDGEDETECT);
			break;
			case 13:imagefilter($image, IMG_FILTER_EMBOSS);
			break;
			case 14:imagefilter($image, IMG_FILTER_MEAN_REMOVAL);
			break;
		}

		return $image;
	}

	/**
	* This plug-in accepts a GD image from which a portion is to be cropped, along with details about the crop offset and di
	* mensions. If any arguments are out of the image bounds, then FALSE is returned. It takes these arguments:
	* $image  A GD image to be transformed
	* $x  Offset from the left of the image
	* $y  Offset from the top of the image
	* $w  The width to crop
	* $h  The height to crop
	*/
	public function imageCrop($image, $x, $y, $w, $h){
		$tw = imagesx($image);
		$th = imagesy($image);

		if($x > $tw || $y > $th || $width > $tw || $height > $th)#{
			return FALSE;
		#}

		$temp = imagecreatetruecolor($width, $height);
		imagecopyresampled($temp, $image, 0, 0, $x, $y, $width, $height, $width, $height);
		return $temp;
	}

	/**
	* This plug-in accepts a filename to display, the image type, and the quality required. It takes these arguments:
	* $filename  A string containing the path/filename of an image
	* $type  The file type of the image (either gif, jpeg, or png)
	* $quality  The display quality if jpeg or png (0 = lowest, up to 99 = highest quality)
	*/
	public function imageDisplay($filename, $type, $quality){
		$contents = file_get_contents($filename);

		if($type == ""){
			$filetype = getimagesize($filename);
			$mime = image_type_to_mime_type($filetype[2]);
			header("Content-type:$mime");
			die($contents);
		}

		$image = imagecreatefromstring($contents);
		header("Content-type:image/$type");

		switch($type){
			case "gif": 
			imagegif($image);
			break;
			case "jpeg": 
			imagejpeg($image, NULL, $quality);
			break;
			case "png":
			imagepng($image, NULL, round(9 - $quality * .09));
			break;
		}
	}
	
	/**
	* This plug-in accepts the name of a file to convert, the name of the file it should be saved as, and the quality requir
	* ed. It takes these arguments:
   	* $fromfile  String containing the path/filename of an image
   	* $tofile  String containing the path/filename to save the new image
   	* $type  The file type of the image (either gif, jpeg, or png)
   	* $quality  The image quality if JPEG or PNG (0 = lowest, up to 99 = highest quality)
	*/
	public function imageConvert($fromfile, $tofile, $type, $quality){
		$contents=file_get_contents($fromfile);
		$image=imagecreatefromstring($contents);

		switch($type){
			case "gif":imagegif($image, $tofile);
			break;
			case "jpeg":imagejpeg($image, $tofile, $quality);
			break;
			case "png":imagepng($image, $tofile,
				round(9 - $quality * .09));
			break;
		}
	}

	/**
	* This plug-in takes the name of a file to save as a finished GIF, the text and font to use in it,  and various details 
	* such as color, size, and shadowing. It takes these arguments:
	*
	* $file The path/filename to save the image
	* $text The text to create
	* $font Thepath/filename of the True Type font to use
	* $size The font size
	* $fore The foreground color in hexadecimal(such as "000000")
	* $back The background color(such as "FFFFFF")
	* $shadow The number of pixels to offset a shadow underneath the text(0 = no shadow)
	* $shadow color The shadow color(such as "444444")
	*/
	public function gifText($file, $text,$font,$size,$fore,$back, $shadow,$shadowcolor){
		// Set the enviroment variable for GD
		putenv('GDFONTPATH=' . realpath('.'));

		$bound = imagettfbbox($size, 0, $font, $text);
		$width = $bound[2] + $bound[0] + 6 + $shadow;
		$height = abs($bound[1]) + abs($bound[7]) + 5 + $shadow;
		$image = imagecreatetruecolor($width, $height);
		$bgcol = HP_GD_FN1($image, $back);
		$fgcol = HP_GD_FN1($image, $fore);
		$shcol = HP_GD_FN1($image, $shadowcolor);
		imagefilledrectangle($image, 0, 0, $width, $height, $bgcol);

		if($shadow > 0){
			imagettftext($image, $size, 0, $shadow + 2, abs($bound[5]) + $shadow + 2, $shcol, $font, $text);
		}

		imagettftext($image, $size, 0, 2, abs($bound[5]) + 2, $fgcol, $font, $text);
		imagegif($image, $file);
	}

	/**
	* This plug-in takes the name of a file in which to save a finished GIF, the text and font to use, and various details 
	* such as color, size, and shadowing. It takes these arguments:
    * $fromfile  The path/filename of the original image
    * $tofile  The path/filename to save the image
	* $type  One of gif, jpeg, or png
    * $quality  Quality setting of final image (0 = worst, up to 99 = best)
    * $text  The text to create
    * $font  The path/filename of the TrueType font to use
    * $size  The font size
    * $fore  The foreground color in hexadecimal (such as “000000”)
    * $opacity  The opacity of the watermark (0 = transparent, up to 100 = opaque)
	*/
	public function imageWatermark($fromfile, $tofile, $type, $quality, $text, $font, $size, $fore, $opacity){
		$contents = file_get_contents($fromfile);
		$image1 = imagecreatefromstring($contents);
		$bound = imagettfbbox($size, 0, $font, $text);
		$width = $bound[2] + $bound[0] + 6;
		$height = abs($bound[1]) + abs($bound[7]) + 5;
		$image2 = imagecreatetruecolor($width, $height);
		$bgcol = HP_GD_FN1($image2, "fedcba");
		$fgcol = HP_GD_FN1($image2, $fore);

		imagecolortransparent($image2, $bgcol);
		imagefilledrectangle($image2, 0, 0, $width, $height, $bgcol);
		imagettftext($image2, $size, 0, 2, abs($bound[5]) + 2, $fgcol, $font, $text);
		imagecopymerge($image1,$image2, (imagesx($image1) - $width) / 2, (imagesy($image1) - $height) / 2, 0, 0, $width, $height, $opacity);

		switch($type){
			case"gif":
			imagegif($image1, $tofile);
			break;
			case"jpeg":
			imagejpeg($image1, $tofile, $quality);
			break;
			case"png":
			imagepng($image1, $tofile, round(9 - $quality * .09));
			break;
		}
	}


	function HP_GD_FN1($image, $color){
		return imagecolorallocate($image, hexdec(substr($color,0,2)), hexdec(substr($color,2,2)), hexdec(substr($color,4,2)));
	}
}