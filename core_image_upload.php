<?php
/**********************************************************
 * EASY UPLOAD IMAGE CLASS v2.0 (http://creativform.com)
 * created: 12/25/2014 12:00 (EST)
 * Copyright 2014 CreativForm.com
 * Created by: Ivijan-Stefan Stipic (creativform@gmail.com)
 * Licensed under MIT (http://creativform.com/MIT-License.txt)
***********************************************************/

/*
	UPLOAD IMAGE ON SERVER
	----------------------
	EXAMPLE:
	
	<?php
		$options=array(
			'new_name'		=>	'john-doe',
			'location'		=>	'images/users/',
			'input_name'	=>	'user-image',
			'max_width'		=>	1600,
			'max_height'	=>	1600,
			'new_width'		=>	400, // height will be automaticly calculate
			'extensions'	=> "png, jpg, gif",
			'quality'		=>	70,
		);
		$upload=new uploadImage($options);
		
		if($upload->results['return'])
		{
			// RETURN ALL INFO FROM NEW IMAGE:
			
			// return success message
			echo $upload->results['message'].'<br>';
			// return image name
			echo $upload->results['name'].'<br>';
			// return location
			echo $upload->results['location'].'<br>';
			// return quality
			echo $upload->results['quality'].'<br>';
			// return extension
			echo $upload->results['extension'].'<br>';
			// return width
			echo $upload->results['width'].'<br>';
			// return height
			echo $upload->results['height'].'<br>';
			// return size
			echo $upload->results['size'].'<br>';
		}
		// return error message
		else echo $upload->results['message'];
	?>
	<form action="#" method="post">
		<input type="file" name="user-image">
		<input type="submit" name="submit" value="Submit">
	</form>
*/
class uploadImage
{
	/* Public Methods */
	public $results		=	array(
		'return'=>false,
		'message'=>NULL
	);
	
	/* Protected Methods */
	private $option	=	array();
	private $ext		=	false;
	private $files	=	false;
	private $alert = array(
        0	=>	"Image is uploaded successfully!",
		1	=>	"All images is successfully uploaded!",
		2	=>	"Appeared an unexpected error!",
        3	=>	"The uploaded file exceeds the upload_max_filesize directive in php.ini!",
        4	=>	"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form!",
        5	=>	"The uploaded file was only partially uploaded!",
        6	=>	"No file was uploaded!",
        7	=>	"Temporary folder missing!",
		8	=>	"Invalid file type. Allowed files types are: {FILE-TYPE}!",
		9	=>	"Imag what you want to upload is larger ({IMG-SIZE}) than allowed size: {ALLOWED-SIZE}!",
		10	=>	"{IMAGE-NAME} already exists.",
		11	=>	"Image what you want to upload is not between {MIN-SIZE} and {MAX-SIZE}!",
	);
	private $allowedExtensions=array('jpeg','jpg','gif','png','bmp');
	
	function __construct($options=array()){
		// Set options
		$this->option=$this->_setup($options);
		// Setup $_FILES
		$this->files =  $_FILES[$this->option['input_name']];
		$this->results=$this->_upload();
	}

	// Replace default with custom options and setup all
	private function _setup($options){
		/* Default Options */
		$option=array(
			// Rename image
			'new_name'		=>	NULL,
			// Destination
			'location'		=>	NULL,
			// Hash image name
			'hash'			=>	false,
			// Name of the input field
			'input_name'	=>	NULL,
			// Check max image width-height
			'max_width'		=>	0,
			'max_height'	=>	0,
			// Check min image width-height
			'min_width'		=>	0,
			'min_height'	=>	0,
			// Check max image size [(width*height) = image size] *NOTE: This option is not supported for this version
			'max_size'		=>	0,
			'min_size'		=>	0,
			// Resize image width-height
			'new_width'		=>	0,
			'new_height'	=>	0,
			// Allowed extensions
			'extensions'	=> "png, jpg, jpeg, gif",
			// image quality in % percentages min=0, max=100 (have overload protection)
			'quality'		=>	100,
		);
		/* Setup Options */
		if(function_exists("array_replace") && version_compare(phpversion(), '5.3.0', '>='))
			$option = array_replace($option, $options); // (PHP 5 >= 5.3.0)
		else 
			$option = array_merge($option, $options); // (PHP 5 < 5.3.0)
		/* Fix some options */
		$extensions	=	(is_array($option['extensions'])?$option['extensions']:array_map("trim",explode(",",$option['extensions'])));
		$new_name	=	$this->_cleanName($_FILES[$option['input_name']]['name'],$option['hash'],$option['new_name']);
		/* Setup folder if not exists */
		$this->_mkDirectory($option);
		/* Merge and return all */
		if(function_exists("array_replace") && version_compare(phpversion(), '5.3.0', '>='))
			return array_replace($option, array(
				'extensions'	=>	$extensions,
				'new_name'		=>	$new_name
			)); // (PHP 5 >= 5.3.0)
		else 
			return array_merge($option, array(
				'extensions'	=>	$extensions,
				'new_name'		=>	$new_name
			)); // (PHP 5 < 5.3.0)
	}
	
	// Get extension of file
	private function _checkExtension($name,$type){
		// Get extension
		$ext	=	explode(".",$name);
		$ext	=	end($ext);
		$ext	=	strtolower($ext);
		$ext	=	trim($ext);
		// get options
		$allowedExt=(is_array($this->option['extensions'])?
						$this->option['extensions']:array_map("trim",explode(",",$this->option['extensions'])));
		// check mime type
		$mime=array();
		foreach($allowedExt as $e){
			if(in_array($e, $this->allowedExtensions)) $mime[]="image/".$e;
		}
		// return
		if((count($mime) > 0) && in_array($type, $mime, true) && in_array($ext, $allowedExt, true) ){
			return (object) array(
				'return'	=>	true,
				'extension'	=>	$ext,
				'type'		=>	$type,
				'message'	=>	NULL
			);
		}else{
			$message=str_replace("{FILE-TYPE}",$this->option['extensions'],$this->alert[8]);
			return (object) array(
				'return'	=>	false,
				'message'	=>	$message,
			);
		}
	}
	
	// Clean and hash image name
	private function _cleanName($str,$hash,$new_name=""){
		if(!empty($new_name) && $new_name!==''){
			$str=$new_name;
		}
		$exist = array('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.'|[\x00-\x7F][\x80-\xBF]+'.'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S','/\xE0[\x80-\x9F][\x80-\xBF]'.'|\xED[\xA0-\xBF][\x80-\xBF]/S','/\%/','/\@/','/\&/','/\s[\s]+/','/[\s\W]+/','/^[\-]+/','/[\-]+$/');
		$replace = array('','','%',' at ',' and ','-','-','','');
		$name = preg_replace($exist,$replace,$str);
		$name = strtolower(trim($name));
		$name = str_replace(array(' ','\r'),'-',$name);
		return ((bool)$hash===true ? md5($name) : $name);
	}
	
	// setup directory
	private function _mkDirectory($option){
		// make directory in not exist
		if (!file_exists($option['location']) && !is_dir($option['location'])){
			mkdir($option['location'], 0766, true);
		}
		// make empty index.htm if not exist
		if (!file_exists($option['location'].'index.htm') && !is_file($option['location'].'index.htm')){
			$file_html = @fopen($option['location'].'index.htm','w');
			fclose($file_html);
		}
	}
	
	// Check image size
	private function _checkSize($_fileSize, $_tmp_name){
		list($width,$height)=getimagesize($_tmp_name);
		if(round($this->option['min_width']*$this->option['min_height']) > 0)
		{
			if(
				(round($this->option['max_width']*$this->option['max_height']) > 0) && 
				($_fileSize <= round($this->option['max_width']*$this->option['max_height'])) &&
				($_fileSize >= round($this->option['min_width']*$this->option['min_height']))
			){
				// Size is OK
				return (object) array(
					'return'	=>	true,
					'width'		=>	$width,
					'height'	=>	$height,
					'message'	=>	NULL,
				);	
			}else{
				$message=str_replace(
							array('{MIN-SIZE}','{MAX-SIZE}'),
							array(
								$this->option['min_width']."x".$this->option['min_height'],
								$this->option['max_width'].'x'.$this->option['max_height']
							),
							$this->alert[11]
						);
				return (object) array(
					'return'	=>	false,
					'width'		=>	$width,
					'height'	=>	$height,
					'message'	=>	$message,
				);
			}
		}
		else
		{
			if((round($this->option['max_width']*$this->option['max_height']) > 0)){
				if(($_fileSize <= round($this->option['max_width']*$this->option['max_height']))){
					// Size is OK
					return (object) array(
						'return'	=>	true,
						'width'		=>	$width,
						'height'	=>	$height,
						'message'	=>	NULL,
					);	
				}else{
					$message=str_replace(
								array('{MIN-SIZE}','{MAX-SIZE}'),
								array($width."x".$height,$this->option['max_width'].'x'.$this->option['max_height']),
								$this->alert[11]
							);
					return (object) array(
						'return'	=>	false,
						'width'		=>	$width,
						'height'	=>	$height,
						'message'	=>	$message,
					);
				}
			}else{
				// unlimited image size
				return (object) array(
					'return'	=>	true,
					'width'		=>	$width,
					'height'	=>	$height,
					'message'	=>	NULL,
				);	
			}
		}
	}
	
	/* Upload image */
	private function _upload(){
		$return=$this->_imgUpload($this->files);
		return array(
			'return'	=>	$return->return,
			'message'	=>	$return->message,
			'name'		=>	$return->name,
			'location'	=>	$return->location,
			'path'		=>	$return->path,
			'quality'	=>	$return->quality,
			'extension'	=>	$return->extension,
			'width'		=>	$return->width,
			'height'	=>	$return->height,
		);
	}
	
	// Recalculate and upload new image
	private function _imgUpload($POST_FILES){
		// check size
	//	var_dump($POST_FILES);
		$size=$this->_checkSize($POST_FILES['size'], $POST_FILES['tmp_name']);
		if((bool)$size->return === true){
			// check extension
			$extension=$this->_checkExtension($POST_FILES['name'], $POST_FILES['type']);
			if((bool)$extension->return === true){
				// get image
				$image=$POST_FILES['tmp_name'];
				// encode image
				switch ($extension->extension)
				{
					case "png":
						$src = imagecreatefrompng($image);
						$background = imagecolorallocate($src, 0, 0, 0);
						imagecolortransparent($src, $background);
						imagealphablending($src, false);
						imagesavealpha($src, true);
					break;
					case "gif":
						$src = imagecreatefromgif($image);
						$background = imagecolorallocate($src, 0, 0, 0);
						imagecolortransparent($src, $background);
					break;
					case "bmp":
						$src = imagecreatefromwbmp($image);
					break;
					default:
						$src = imagecreatefromjpeg($image);
					break;
				}
				// Recalculate and resize
				list($width,$height)=getimagesize($image);
				if($this->option['new_width'] > 0 || $this->option['new_height'] > 0)
				{
					// new width
					if($width >= $this->option['new_width']) 
						$newWidth = $this->option['new_width'];
					else
						$newWidth = $width;
					// new height
					if($this->option['new_height'] > 0)
						$newHeight=$this->option['new_height'];
					else
						$newHeight=($height/$width)*$newWidth; 
					
				}
				else
				{
					// original size
					$newWidth=$width; $newHeight=$height;
				}
				// recalculate and create new resized image
				$tmp=imagecreatetruecolor($newWidth,$newHeight);
				switch ($extension->extension)
				{
					case "png":
						imagealphablending($tmp, false);
						imagesavealpha($tmp, true);
						imagecopyresampled($tmp,$src,0,0,0,0,$newWidth,$newHeight,$width,$height);
						$background = imagecolorallocate($tmp, 0, 0, 0); 
						imagecolortransparent($tmp, $background);
					break;
					case "gif":
						imagecopyresampled($tmp,$src,0,0,0,0,$newWidth,$newHeight,$width,$height);
						$background = imagecolorallocate($tmp, 0, 0, 0); 
						imagecolortransparent($tmp, $background);
					break;
					default:
						imagecopyresampled($tmp,$src,0,0,0,0,$newWidth,$newHeight,$width,$height);
					break;
				}
				// Setup Name
				$newName=$this->option['new_name'].'.'.$extension->extension;
				$location = $this->option['location'].$newName;
				switch ($extension->extension)
				{
					case "png":
						$quality = round(9 - $this->option['quality'] / 11.11);
						imagepng($tmp,$location,$quality);
					break;
					case "gif":
						$quality=NULL;
						imagegif($tmp,$location);
					break;
					case "bmp":
						$quality=NULL;
						imagewbmp($tmp,$location);
					break;
					default:
						$quality=(($this->option['quality']>=100) ? 100 : ($this->option['quality']<=0 ? 0 : $this->option['quality']));
						imagejpeg($tmp,$location,$quality);
					break;
				}
				imagedestroy($src);
				imagedestroy($tmp);
				return (object) array(
					'return'	=>	true,
					'message'	=>	$this->alert[0],
					'name'		=>	$newName,
					'location'	=>	$location,
					'path'		=>	$location.$newName,
					'quality'	=>	$quality,
					'extension'	=>	$extension->extension,
					'width'		=>	$newWidth,
					'height'	=>	$newHeight,
				);
			}
			else return $extension->message;
		}
		else return $size->message;
	}
	
	private function _reArrayFiles(&$file_post) {
		$file_ary = array();
		$file_count = count($file_post['name']);
		$file_keys = array_keys($file_post);
		for ($i=0; $i<$file_count; $i++) {
			foreach ($file_keys as $key) {
				$file_ary[$i][$key] = $file_post[$key][$i];
			}
		}
		return $file_ary;
	}
}
?>
