<?php
/*
    imageresize
    ===========
    A simple, yet efficient solution for image resizing and caching with php
    See https://github.com/nickdekruijk/imageresize for information and documentation
*/
    
    include('imageresize.config.php');
    
    # Set error reporting options, disable for security, enable for debugging
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    # Set high memory limit if possible
	ini_set('memory_limit','128M');
    
    # Clear all resized images for the template if any
	if (isset($_GET['clear'])) {
	    if ($_GET['clear']=='all')
    	    foreach($templates as $name=>$template)
    	        deleteDir($name);
        elseif (isset($templates[$_GET['clear']]))
    		deleteDir($_GET['clear']);
        die('Cleared');
    }

    # Parse the image URI and compare to this script URI to find the base folder
    $uri_parts2 = $uri_parts = parse_uri($_SERVER['REQUEST_URI']);
    foreach(parse_uri($_SERVER['PHP_SELF']) as $k=>$v)
        if ($v==$uri_parts2[$k])
            array_shift($uri_parts);
        else
            break;
            
    if (!count($uri_parts)) error404();
            
    # Set target, template and original variables
    $target = implode('/', $uri_parts);

    # Check if first folder part matches the targetFolder config
    if ($uri_parts[0]==$targetFolder)
        array_shift($uri_parts);

    if (!isset($templates[$uri_parts[0]])) 
        error404('Template not found');
    $template = $templates[$uri_parts[0]];
    array_shift($uri_parts);
    $original = implode('/', $uri_parts);
    
    # Check if original file exists and is a valid image
    if (!file_exists($original)) error404('Image not found');
	$originalSize = getimagesize($original) or error500('Invalid image');
	# Set some helper variables
	$type=$originalSize['mime'];
	$originalWidth=$originalSize[0];
	$originalHeigth=$originalSize[1];

    # Create new GD instance based on image type
	if ($type=='image/gif')	$image_a=imagecreatefromgif($original) or error();
	elseif ($type=='image/png')	$image_a=imagecreatefrompng($original) or error();
	else $image_a=imagecreatefromjpeg($original) or error();

    # Crop the image if template['type']=='crop'
    if ($template['type']=='crop') {
		$targetWidth=$template['width']; $targetHeigth=$originalHeigth*($targetWidth/$originalWidth);
		if ($targetHeigth<$template['height']) {
			$targetHeigth=$template['height']; $targetWidth=$originalWidth*($targetHeigth/$originalHeigth);
		}
		$dst_img=imagecreatetruecolor($targetWidth,$targetHeigth);
		imagealphablending($dst_img,false);
		imagesavealpha($dst_img,true);
		imagecopyresampled($dst_img,$image_a,0,0,0,0,$targetWidth,$targetHeigth,imagesx($image_a),imagesy($image_a));
		unset($image_a);
		$image_p=imagecreatetruecolor($template['width'],$template['height']);
		imagealphablending($image_p,false);
		imagesavealpha($image_p,true);
		imagecopy($image_p,$dst_img,0,0,round((imagesx($dst_img)-$template['width'])/2),round((imagesy($dst_img)-$template['height'])/2),$template['width'],$template['height']);
		unset($dst_img);
    } elseif ($template['type']=='fit') {
		$ratio_orig=$originalWidth/$originalHeigth;
		if ($template['width']/$template['height']>$ratio_orig) $template['width']=$template['height']*$ratio_orig; else $template['height']=$template['width']/$ratio_orig;
		$image_p=imagecreatetruecolor($template['width'],$template['height']);
		imagealphablending($image_p,false);
		imagesavealpha($image_p,true);
		imagecopyresampled($image_p,$image_a,0,0,0,0,$template['width'],$template['height'],$originalWidth,$originalHeigth);
		unset($image_a);	
    } else {
        error500('Invalid template type');
    }
    
    # Create the targe folder if needed
	makepath($target);

    # Add blur filter if needed
	if (isset($template['blur']) && $template['blur']>0)
		for ($x=1; $x<=$template['blur']; $x++)
		   imagefilter($image_p, IMG_FILTER_GAUSSIAN_BLUR);

    # Add grayscale filter if needed
	if (isset($template['grayscale']) && $template['grayscale'])
        imagefilter($image_p, IMG_FILTER_GRAYSCALE);

    # Save the resized image
	if ($type=='image/gif')
		imagegif($image_p, $target) or error500('Write error'); 
	elseif ($type=='image/png')
		imagepng($image_p, $target) or error500('Write error'); 
	else
		imagejpeg($image_p, $target, isset($template['quality'])?$template['quality']:80) or error500('Write error'); 

    # Redirect to the saved image
	if (file_exists($target)) redirect($target);


    # Helper functions below
    
    # Function to split the URI into an array
    function parse_uri($uri)
    {
        return explode('/', preg_replace('/\?.*/', '', urldecode(str_replace('+', '%2B', (string)$uri))));
    }
    
    # Raise 404 not found message
	function error404($e=false) 
	{
		header('HTTP/1.0 404 Not Found');
		header('Status: 404 Not Found');
		echo $e;
		die;
	}

    # Raise 500 server error message
	function error500($e='') 
	{
		header('HTTP/1.0 500 Internal server error');
		header('Status: 500 Internal server error');
		echo $e;
		die;
	}

	function makepath($target)
	{
		$dir=explode('/',$target);
		array_pop($dir);
		$p='';
		foreach($dir as $d) {
			$p.=$d.'/';
			if (!file_exists($p)) mkdir($p) or die('Unable to create '.$p);
			if (!is_dir($p)) die('Not a directory: '.$p);
			if (!is_writable($p)) die('Not writable: '.$p);
		}
	}

	function redirect($path)
    {
		$self=explode('/',$_SERVER['PHP_SELF']);
		array_pop($self);
		array_shift($self);
		$path='/'.implode('/',$self).'/'.$path;
		header('Location: '.$path);
		die;
	}

	function deleteDir($dir) 
	{
    	if (!file_exists($dir) || !is_dir($dir)) return false;
		$h=opendir($dir);
		while($f=readdir($h)) {
			if ($f[0]!='.')
				if (is_dir($dir.'/'.$f)) 
					deleteDir($dir.'/'.$f);
				else {
					echo 'Deleting '.$dir.'/'.$f.'<br>';
					unlink($dir.'/'.$f);
				}
		}
		echo 'Deleting dir '.$dir.'<br>';
		rmdir($dir);
		closedir($h);
	}
