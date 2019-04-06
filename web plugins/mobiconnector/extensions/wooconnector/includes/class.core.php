<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooConnectorCore {
	public static function resize_image($file, $dest, $w, $h, $crop=false) {
		list($width, $height) = getimagesize($file);
		if($height > 0){
			$r = $width / $height;
			$ri = $w / $h;
			(float)$w_rate = ((int)$width)/((int)$w);
			(float)$h_rate = ((int)$height)/((int)$h);
			$filetype = wp_check_filetype( basename( $file), null );
			$filetype['ext'] = strtolower($filetype['ext']);
			$resize_width = 0;
			$resize_height = 0;
			$crop_width = $w;
			$crop_height = $h;
			if($w_rate >= 1 && $h_rate>=1) {
				if(is_int($w_rate) && is_int($h_rate)){
					$resize_width = $w;
					$resize_height = $h;
				}else{
					if($filetype['ext'] === 'png'){
						if ($width > $height) {
							$resize_width = $w;
							$resize_height = $height*(1/$w_rate);
						} elseif($width < $height) {
							$resize_width = $w;
							$resize_height = $height*(1/$w_rate);
						}elseif($width == $height){
							$resize_width = $w;
							$resize_height = $h;
						}						
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
				}
			}else if($w_rate >= 1 && $h_rate<1) {
				$resize_width = $width*(1/$h_rate);
				$resize_height = $h;
			}else if($w_rate < 1 && $h_rate>=1) {
				$resize_width = $w;
				$resize_height = $height*(1/$w_rate);
			}else if($w_rate < 1 && $h_rate < 1){
				if($filetype['ext'] === 'png'){
					if ($width > $height) {
						$resize_width = $w;
						$resize_height = $height*(1/$w_rate);
					} elseif($width < $height) {
						$resize_width = $width*(1/$h_rate);
						$resize_height = $h;
					}elseif($width == $height){
						$resize_width = $w;
						$resize_height = $h;
					}
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
			}	
			$resize_width = ceil($resize_width);
			$resize_height = ceil($resize_height);
	
			if($filetype['ext'] =='jpg' || $filetype['ext'] =='jpeg')
				$source = imagecreatefromjpeg($file);
			elseif($filetype['ext'] =='png')
				$source = imagecreatefrompng($file);
			elseif($filetype['ext'] =='gif')
				$source = imagecreatefromgif($file);
	
			@unlink($dest);
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
				@imagejpeg($thumb, $dest, 100);
				@imagedestroy($thumb);
			}elseif($filetype['ext'] =='png'){
				$thumb_tmp = imagecreatetruecolor($resize_width, $resize_height);
				imagealphablending($thumb_tmp,false);
				imagesavealpha($thumb_tmp,true);
				imagecopyresampled($thumb_tmp, $source, 0, 0, 0, 0, $resize_width, $resize_height, $width, $height);
				$thumb = imagecreatetruecolor($crop_width, $crop_height);
				@imagealphablending($thumb,false);
				@imagesavealpha($thumb,true);
				@imagecopyresampled($thumb, $thumb_tmp, 0, 0, 0, 0, $crop_width, $crop_height, $resize_width, $resize_height);
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
				@imagegif($thumb, $dest);
				@imagedestroy($thumb);
			}
		}		
	}
}
?>