<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Class Core Crop or Resize Image of Plugin
 */
class BAMobileCore {
	/**
	 * Resize or Crop images
	 * 
	 * @param string  $file  	path of images want resize or crop
	 * @param string  $dest  	path save new images
	 * @param string  $w        width want resize or crop
	 * @param string  $h        height want resize or crop
	 * @param boolean $crop     resize or crop
	 */
	public static function bamobile_resize_image($file, $dest, $w, $h, $crop=false) {
		list($width, $height) = getimagesize($file);
		if($height > 0){
			$r = $width / $height;
			$ri = $w / $h;
			(float)$w_rate = ((int)$width)/((int)$w);
			(float)$h_rate = ((int)$height)/((int)$h);
			if ($crop) {
				if ($width > $height) {
					$newheight = $h;
					$newwidth = floor($width * ($newheight / $height));
					$crop_x = ceil(($width - $height)/2);
					$crop_y = 0;
				} else {
					$newwidth = $w;
					$newheight = floor($height * ($newwidth / $width));
					$crop_x = 0;
					$crop_y = ceil(($height - $width)/2);
				}
			} else {
				$resize_width = 0;
				$resize_height = 0;
				$crop_width = $w;
				$crop_height = $h;
				if($w_rate >= 1 && $h_rate>=1) {
					if(is_int($w_rate) && is_int($h_rate)){
						$resize_width = $w;
						$resize_height = $h;
					}else{
						if ($width > $height) {
							$resize_width = $width*(1/$h_rate);
							$resize_height = $h;
						} elseif($width < $height) {
							$resize_width = $w;
							$resize_height = $height*(1/$w_rate);
						}elseif($width == $height){
							$resize_width = $w;
							$resize_height = $h;
						}
					}
				}else if($w_rate >= 1 && $h_rate<1) {
					$resize_width = $width*(1/$h_rate);
					$resize_height = $h;
				}else if($w_rate < 1 && $h_rate>=1) {
					$resize_width = $w;
					$resize_height = $height*(1/$w_rate);
				}else if($w_rate < 1 && $h_rate < 1){
					if ($width > $height) {
						$resize_width = $width*(1/$h_rate);
						$resize_height = $h;
					} elseif($width < $height) {
						$resize_width = $w;
						$resize_height = $height*(1/$w_rate);
					}elseif($width == $height){
						$resize_width = $w;
						$resize_height = $h;
					}
				}	
			}
			// end scale image
			$resize_width = ceil($resize_width);
			$resize_height = ceil($resize_height);
			
			$filetype = wp_check_filetype( basename( $file), null );
			$filetype['ext'] = strtolower($filetype['ext']);
			if($filetype['ext'] =='jpg' || $filetype['ext'] =='jpeg')
				$source = imagecreatefromjpeg($file);
			elseif($filetype['ext'] =='png')
				$source = imagecreatefrompng($file);
			elseif($filetype['ext'] =='gif')
				$source = imagecreatefromgif($file);
			// remove OLD image
			// Output
			if($filetype['ext'] =='jpg' || $filetype['ext'] =='jpeg'){
				$thumb_tmp = imagecreatetruecolor($resize_width, $resize_height);
				$black_tmp = imagecolorallocate($thumb_tmp, 0, 0, 0);
				imagecolortransparent($thumb_tmp, $black_tmp);
				imagecopyresampled($thumb_tmp, $source, 0, 0, 0, 0, $resize_width, $resize_height, $width, $height);
				$thumb = imagecreatetruecolor($crop_width, $crop_height);
				$black = imagecolorallocate($thumb, 0, 0, 0);
				@imagecolortransparent($thumb, $black);
				@imagecopyresampled($thumb, $thumb_tmp, 0, 0, 0, 0, $crop_width, $crop_height, $resize_width, $resize_height);
				@unlink($dest);
				@imagejpeg($thumb, $dest, 100);
				@imagedestroy($thumb);
			}elseif($filetype['ext'] == 'png'){
				$thumb_tmp = imagecreatetruecolor($resize_width, $resize_height);
				imagealphablending($thumb_tmp,false);
				imagesavealpha($thumb_tmp,true);
				imagecopyresampled($thumb_tmp, $source, 0, 0, 0, 0, $resize_width, $resize_height, $width, $height);
				$thumb = imagecreatetruecolor($crop_width, $crop_height);
				@imagealphablending($thumb,false);
				@imagesavealpha($thumb,true);
				@imagecopyresampled($thumb, $thumb_tmp, 0, 0, 0, 0, $crop_width, $crop_height, $resize_width, $resize_height);
				@unlink($dest);
				@imagepng($thumb, $dest, 9);
				@imagedestroy($thumb);
			}elseif($filetype['ext'] == 'gif'){
				$thumb_tmp = imagecreatetruecolor($resize_width, $resize_height);
				imagealphablending($thumb_tmp,false);
				imagesavealpha($thumb_tmp,true);
				imagecopyresampled($thumb_tmp, $source, 0, 0, 0, 0, $resize_width, $resize_height, $width, $height);
				$thumb = imagecreatetruecolor($crop_width, $crop_height);
				@imagealphablending($thumb,false);
				@imagesavealpha($thumb,true);
				@imagecopyresampled($thumb, $thumb_tmp, 0, 0, 0, 0, $crop_width, $crop_height, $resize_width, $resize_height);
				@unlink($dest);
				@imagegif($thumb, $dest);
				@imagedestroy($thumb);
			}
		}		
	}	
}
?>