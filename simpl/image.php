<?php/*** Manage Image Fucntions**/class Image {	var $width;	var $height;	var $thumb_width;	var $thumb_height;	var $maxSize = array();			function Image($folder='', $width='', $height='', $thumb_width='', $thumb_height=''){		$this->folder = $folder;		$this->width = $width;		$this->height = $height;		$this->thumb_width = $thumb_width;		$this->thumb_height = $thumb_height;	}		function MaxSize($size){		$this->maxSize = $size;	}		//calculates how to stuff a big img into a lil space	function CalcSize($CurH,$CurW,$MaxH,$MaxW) {		$HRatio = $CurH/$MaxH;		$WRatio = $CurW/$MaxW;				if($HRatio > $WRatio){			$result[0] = floor($CurW*($MaxH/$CurH));			$result[1] = $MaxH;		}else{			$result[0] = $MaxW;			$result[1] = floor($CurH*($MaxW/$CurW));		}				return($result);	}			function Resize($img,$target='', $type='full', $quality=80){		// create an image of the given filetype		if (strpos($img,".jpg") !== false or strpos($img,".jpeg") !== false) {		   $image = @imagecreatefromjpeg($img);		   $extension = ".jpg";		} elseif (strpos($img,".png") !== false) {		   $image = @imagecreatefrompng($img);		   $extension = ".png";		} elseif (strpos($img,".gif") !== false) {		   $image = @imagecreatefromgif($img);		   $extension = ".gif";		}		if (!$image)			return false;				$size = getimagesize ($img);				if ($size[1] >= $this->maxSize[1] || $size[0] >= $this->maxSize[0]){ 			// calculate missing values			$size2 = $this->CalcSize($size[1],$size[0],$this->maxSize[1],$this->maxSize[0]);			$width = $size2[0];			$height = $size2[1];		}else{			$width = $size[0];			$height = $size[1];		}				$thumb = imagecreatetruecolor ($width, $height);				if (function_exists("imagecopyresampled")){		   if (!@imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1])) {			   imagecopyresized($thumb, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);		   }		}else{		   imagecopyresized($thumb, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);		}				if (!$target) {		   $target = "temp".$extension;		}				switch($type){			case 'full':				$this->width = $width;				$this->height = $height;				break;			case 'thumb':				$this->thumb_width = $width;				$this->thumb_height = $height;				break;		}				$return = true;   				switch ($extension) {		   case ".jpg": 			   imagejpeg($thumb, $target, $quality);			   break;		   case ".gif": 			   imagegif($thumb, $target);			   break;		   case ".png": 			   imagepng($thumb, $target);			   break;		   default: 			   $return = false;		}				// report the success (or fail) of the action		return $return;	}		function Rotate($degrees){		for ($i=0; $i<2; $i++){					$img = DIR_PICTURES . $this->gallery_id . '/';			if ($i > 0)				$img .= 'thumbs/';			$img .= $this->name;									if (strpos($img,".jpg") !== false or strpos($img,".jpeg") !== false) {			   $source = imagecreatefromjpeg($img);			   $extension = ".jpg";			} elseif (strpos($img,".png") !== false) {			   $source = ImageCreateFromPng($img);			   $extension = ".png";			} elseif (strpos($img,".gif") !== false) {			   $source = ImageCreateFromGif($img);			   $extension = ".gif";			}						// Rotate			$rotate = imagerotate($source, $degrees, 0);			$return = 'The Image has successfully been rotated';   					switch ($extension) {			   case ".jpg": 				   imagejpeg($rotate, $img, 100);				   break;			   case ".gif": 				   imagegif($rotate, $img);				   break;			   case ".png": 				   imagepng($rotate, $img);				   break;			   default: 				   $return = 'Image could not be rotated, Please try again.';			}						clearstatcache();			$size = getimagesize ($img);			if ($i > 0){				$this->thumb_width = $size[0];				$this->thumb_height = $size[1];			}else{				$this->width = $size[0];				$this->height = $size[1];			}			// report the success (or fail) of the action		}		return $return;	}}?>