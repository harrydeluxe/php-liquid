<?php
/**
* usage:
* {{ 'content,blog' | stylesheet: 'screen' }}
*
*/
class SiteFilter
{
	public function stylesheet($file, $media = 'sreen')
	{
		$files = explode(',', $file);
		$media = isset($media) ? ' media="'.$media.'"' : '';
		$r = '';
		
		foreach($files as $link)
			$r .= '<link href="/theme/default/stylesheets/'.$link.'.css"'.$media.' rel="stylesheet" type="text/css" />'."\n";
		return $r;
	}
}