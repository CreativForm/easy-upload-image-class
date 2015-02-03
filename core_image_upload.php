<?php
/**********************************************************
 * EASY UPLOAD IMAGE CLASS v1.0.5 (http://creativform.com)
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
	public $size		=	array();
	public $extension	=	array();
	public $results		=	array(
		'return'=>false,
		'message'=>NULL
	);
	protected $option	=	array();
	protected $ext		=	false;
	protected $file		=	false;
	protected $post_errors= array(
        0=>"There is no error, the file uploaded with success",
        1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
        2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        3=>"The uploaded file was only partially uploaded",
        4=>"No file was uploaded",
        6=>"Missing a temporary folder"
	); 
	function __construct($options=array())
	{
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
		// manual change values
		foreach($options as $key=>$value)
		{
			if(!empty($key))
			{
				unset($option[$key]);
				$option[$key]=$value;
			}
		}
		if(isset($_FILES[$option['input_name']]["name"]) && !empty($_FILES[$option['input_name']]["name"]))
		{
			// if file have error
			if ($_FILES[$option['input_name']]["error"] > 0)
			{
				$this->results = array(
					'return'=>false,
					'message'=>$this->post_errors[$_FILES[$option['input_name']]['error']],
				);
			}
			else
			{
				// Private
				$this->file			=	$_FILES[$option['input_name']];
				$this->option		=	$option;
				@$this->ext			=	strtolower(end(explode(".",implode("",explode("\\",$_FILES[$option['input_name']]['name'])))));
				// Public
				$this->size			=	$this->size();
				$this->extension	=	$this->extension();
				// Functions
				$this->results 		= $this->upload();
			}
		}
		else
		{
			$this->results = array(
				'return'=>false,
				'message'=>"No file selected. Please select file for upload!",
			);	
		}
	}
	/* 
		Check image size 
		$this->size();
		return array();
	*/
	protected function size()
	{
		if(round($this->option['min_width']*$this->option['min_height']) > 0)
		{
			if(
				(round($this->option['max_width']*$this->option['max_height']) > 0) && 
				($this->file["size"] <= round($this->option['max_width']*$this->option['max_height'])) &&
				($this->file["size"] >= round($this->option['min_width']*$this->option['min_height']))
			)
			{
				// Size is OK
				return array(
					'return'	=>	true,
					'message'	=>	NULL,
				);	
			}
			else
			{
				// Too large image
				list($width,$height)=getimagesize($this->file["tmp_name"]);
				return array(
					'return'	=>	false,
					'message'	=>	"Imag what you want to upload is not between ".
									$this->option['max_width'].'x'.$this->option['max_height'].'px and '.$this->option['min_width'].'x'.$this->option['min_height'].'px',
				);	
			}
		}
		else
		{
			if((round($this->option['max_width']*$this->option['max_height']) > 0))
			{
				if(($this->file["size"] <= round($this->option['max_width']*$this->option['max_height'])))
				{
					// Size is OK
					return array(
						'return'	=>	true,
						'message'	=>	NULL,
					);	
				}
				else
				{
					// Too large image
					list($width,$height)=getimagesize($this->file["tmp_name"]);
					return array(
						'return'	=>	false,
						'message'	=>	"Imag what you want to upload is larger (".$width."x".$height.
										") than allowed size: ".$this->option['max_width'].'x'.$this->option['max_height'].'px',
					);	
				}
			}
			else
			{
				// unlimited image size
				return array(
						'return'	=>	true,
						'message'	=>	NULL,
					);	
			}
		}
	}
	/* 
		Check image Extension 
		$this->extension();
		return array();
	*/
	protected function extension()
	{
		$extensions=(is_array($this->option['extensions'])?$this->option['extensions']:array_map("trim",explode(",",$this->option['extensions'])));
		$type=array();
		foreach($extensions as $e)
		{
			if(in_array($e, array('jpeg','jpg','gif','png','bmp')))
			{
				$type[]="image/".$e;
			}
		}
		if((count($type) > 0) && in_array($this->ext, $extensions, true) && in_array($this->file['type'], $type, true) )
		{
			return array(
				'return'=>true,
				'message'=>NULL
			);
		}
		else
		{
			return array(
				'return'=>false,
				'message'=>"Invalid file type. Allowed files types are: ".$this->option['extensions'],
			);
		}
	}
	/* 
		Upload and resize image 
		$this->upload();
		return array();
	*/
	protected function upload()
	{
		if($this->size['return']===true)
		{
			if($this->extension['return']===true)
			{
				/* Setup images */
				$file_name=preg_replace("/[^a-zA-Z0-9\.\-\_\%\$\ ]/","",(!empty($this->option['new_name'])?$this->option['new_name']:$this->file['name']));
				$image=$this->file['tmp_name'];
				switch ($this->ext)
				{
					case "png":
						$src = @imagecreatefrompng($image);
						$background = imagecolorallocate($src, 0, 0, 0);
						@imagecolortransparent($src, $background);
						@imagealphablending($src, false);
						@imagesavealpha($src, true);
					break;
					case "gif":
						$src = @imagecreatefromgif($image);
						$background = imagecolorallocate($src, 0, 0, 0);
						@imagecolortransparent($src, $background);
					break;
					case "bmp":
						$src = @imagecreatefromwbmp($image);
					break;
					default:
						$src = @imagecreatefromjpeg($image);
					break;
				}
				/* Resize */
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
				switch ($this->ext)
				{
					case "png":
						@imagealphablending($tmp, FALSE);
						@imagesavealpha($tmp, TRUE);
						@imagecopyresampled($tmp,$src,0,0,0,0,$newWidth,$newHeight,$width,$height);
						$background = imagecolorallocate($tmp, 0, 0, 0); 
						@imagecolortransparent($tmp, $background);
					break;
					case "gif":
						@imagecopyresampled($tmp,$src,0,0,0,0,$newWidth,$newHeight,$width,$height);
						$background = imagecolorallocate($tmp, 0, 0, 0); 
						@imagecolortransparent($tmp, $background);
					break;
					default:
						@imagecopyresampled($tmp,$src,0,0,0,0,$newWidth,$newHeight,$width,$height);
					break;
				}
				// rename
				@$rename=(empty($this->option['new_name'])?implode("",explode('.'.$this->ext,strtolower($this->file["name"]))):$this->option['new_name']);
				$rename=strtolower($rename);
				if(is_bool($this->option['hash']) && $this->option['hash']===true)
				{
					$newName=$this->encode($rename).'.'.$this->ext;
				}
				else
				{
					$newName=$this->set_name($rename).'.'.$this->ext;
				}
				// make directory in not exist
				if (!file_exists($this->option['location']) && !is_dir($this->option['location'])) {
					@mkdir($this->option['location'], 0766, true);
				}
				// make empty index.htm if not exist
				if (!file_exists($this->option['location'].'index.htm') && !is_file($this->option['location'].'index.htm')) {
					$file_html = @fopen($this->option['location'].'index.htm','w');
					@fclose($file_html);
				}
				$location = NULL;
				if (file_exists($this->option['location'].$newName))
				{
					return array(
						'return'=>false,
						'message'=>$newName . " already exists. ",
					);
				}
				else
				{
					// store image on server
					$location = $this->option['location'].$newName; 
				}
				switch ($this->ext)
				{
					case "png":
						if($this->option['quality']<10)
							$quality=0;
						if($this->option['quality']>=10 && $this->option['quality']<20)
							$quality=1;
						if($this->option['quality']>=20 && $this->option['quality']<30)
							$quality=2;
						if($this->option['quality']>=30 && $this->option['quality']<40)
							$quality=3;
						if($this->option['quality']>=40 && $this->option['quality']<50)
							$quality=4;
						if($this->option['quality']>=50 && $this->option['quality']<60)
							$quality=5;
						if($this->option['quality']>=60 && $this->option['quality']<70)
							$quality=6;
						if($this->option['quality']>=70 && $this->option['quality']<80)
							$quality=7;
						if($this->option['quality']>=80 && $this->option['quality']<90)
							$quality=8;
						if($this->option['quality']>90)
							$quality=9;
						@imagepng($tmp,$location,$quality);
					break;
					case "gif":
						@imagegif($tmp,$location);
					break;
					case "bmp":
						@imagewbmp($tmp,$location);
					break;
					default:
						$quality=(($this->option['quality']>=100) ? 100 : ($this->option['quality']<=0 ? 0 : $this->option['quality']));
						@imagejpeg($tmp,$location,$quality);
					break;
				}
				@imagedestroy($src);
				@imagedestroy($tmp);
				return array(
					'return'	=>	true,
					'message'	=>	"You successful upload image!",
					'name'		=>	$newName,
					'location'	=>	$location,
					'quality'	=>	$quality,
					'extension'	=>	$this->ext,
					'width'		=>	$newWidth,
					'height'	=>	$newHeight,
				);
			}
			else
			{
				return $this->extension['message'];
			}
		}
		else
		{
			return $this->size['message'];
		}
	}
	## clean filename or generate hash name
	protected function set_name($content)
	{
		$exist = array('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.'|[\x00-\x7F][\x80-\xBF]+'.'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S','/\xE0[\x80-\x9F][\x80-\xBF]'.'|\xED[\xA0-\xBF][\x80-\xBF]/S','/\%/','/\@/','/\&/','/\s[\s]+/','/[\s\W]+/','/^[\-]+/','/[\-]+$/');
		$replace = array('','',' % ',' at ',' and ','-','-','','');
		$content = preg_replace($exist,$replace,$content);
		return strtolower(trim($content));
	}
	protected function encode($string)
	{
		return strtolower(
				implode("",
					explode("=",
						str_rot13(
							base64_encode(
								str_rot13(
									base64_encode(
										$this->set_name($string)
										)
									)
								)
							)
						)
					)
				);
	}
}
?>
