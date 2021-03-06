<?php
/**
 * @package WebGizmo
 * @author Alexander R B Whillas
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 **/

/**
 * Web Image file
 * 
 * Will create an IMG tag in the HTML.
 *
 * @package WebGizmo
 * @subpackage	ContentHanders
 **/
class ImageFileContent extends FSFile
{
	public $width;
	
	public $height;
	
	function html($format = 'html')
	{
		$attrs = array();
		
		if (extension_loaded('gd') && function_exists('getimagesize'))
		{
			$meta = getimagesize($this->getPath()->get(), $info);
			$attrs['width'] = $this->width = $meta[0];
			$attrs['height'] = $this->height = $meta[1]; 
		}

		return img($this->getFileURL(), $this->getCleanName(), 'ImageFileContent', $this->getID(), $attrs);
	}
}