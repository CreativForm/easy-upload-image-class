Easy upload image class
=======================
This class provides a quick, easy and safe access to images for upload with the possibility of changing the size and quality of JPG, JPEG, GIF, PNG and BMP type of photos or images. You only need to make some setup and everything continues to work automaticly.

SETUP AND USING
=======================
```
    $options=array(
			'new_name'		=>	'john-doe',
			'location'		=>	'your-folder/location-for-image/',
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
```
As you can see, you just invite the class and do a setup for the image. Via object "results" you receive array of any other information from the images that you needed in your script.

ALL OPTIONS
=======================
This is complete list of your setup with example

### Rename image

'*new_name*'		=>	"*NEY NAME*"

### Destination folder (path)

'*location*'		=>	"ROOT/IMAGE-PATH/"

### Hash encode image name BOOLEAN (true/false)

'*hash*'			=>	false

### Name of the input field $_FILES[ "*NAME*" ]

'*input_name*'	=>	"*NAME*"

### Check max image width-height

'*max_width*'		=>	1280

'*max_height*'	=>	1280

### Check min image width-height

'*min_width*'		=>	100

'*min_height*'	=>	100

### Check max image size [(width*height) = image size] *NOTE: This option is not supported for this version

'*max_size*'		=>	0

'*min_size*'		=>	0

### Resize image width-height

'*new_width*'		=>	800

'*new_height*'	=>	0  (*if is 0 then is automatic*)

### Allowed extensions (coma separated array)

'*extensions*'	=> "*png, jpg, jpeg, gif, bmp*"

### Setup image quality in % percentages min=0, max=100

'*quality*'		=>	100

ALL RESULTS ARRAY VALUES
=======================
This object return array of all information about uploaded function.

**Global returns**
- **results['return']** BOOLEAN return true if upload is completed or false if error occurs
- **results['message']** returns all informations and errors from upload

**Return ony if upload is successful**
- **results['name']** returns new or current image name
- **results['location']** returns destination folder or path
- **results['quality']** return number of image quality in percent %
- **results['extension']** return image extension
- **results['width']** return image width
- **results['height']** return image height
- **results['size']** return image size in buytes
