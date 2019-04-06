<?php
require_once( __DIR__.'/class-mobiconnector-core-mobile-detect.php' );
class BAMobile_Detect{
	public function output($userAgent){
		$detect = new BAMobile_Mobile_Detect;
		if(empty($userAgent)){
			return null;
		}
		$detect->setUserAgent($userAgent);
		$header = $detect->getHttpHeaders();
		$os = "";
		if($detect->isMobile($userAgent,$header)){
			$listOs = $detect->getOperatingSystems();
			foreach($listOs as $los => $value){
				$value = strtolower($value);
				if($detect->match($value,$userAgent)){
					$os = $los;
				}		
			}	
			return array(
				'os' => $os,
			);
		}elseif($detect->isTablet($userAgent,$header)){
			$listOs = $detect->getOperatingSystems();
			foreach($listOs as $los => $value){
				$value = strtolower($value);
				if($detect->match($value,$userAgent)){
					$os = $los;
				}		
			}	
			return array(
				'os' => $os
			);
		}else{
			return array(
				'os' => 'pc'
			);
		}
	}	
}	
?>