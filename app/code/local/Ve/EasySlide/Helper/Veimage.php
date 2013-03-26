<?php 
/*------------------------------------------------------------------------
# Ve EasySlide - Banner Slide
# ------------------------------------------------------------------------
# author    Vsmart Extensions
# copyright Copyright (C) 2010 Vsmart-extensions.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.vsmart-extensions.com
# Technical Support:  Forum - http://www.vsmart-extensions.com
-------------------------------------------------------------------------*/
if( !class_exists('Ve_EasySlide_Helper_Veimage') ){
	class Ve_EasySlide_Helper_Veimage extends Mage_Core_Helper_Abstract {
		
		private $imagePath;
		
		private $exts = array();

		private $quality = 100;

		private $missImage = null;
		
		private $thumbType = 'crop';
		
		private $cacheUrl;

		private  $cLocation;
		
		function __construct()
		{
			$this->exts = array( 1 => "gif", "jpeg", "png", "jpg");	
			$this->imagePath =  Mage::getBaseDir().DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR;
			$this->cLocation = $this->imagePath.'resized'.DIRECTORY_SEPARATOR;
			$this->cacheUrl = 'media/resized/';
		}
		public function VeImage()
		{
			$this->__construct();
		}
		public function &getInstance()
		{
			static $instance = null;
			if( !$instance )
			{
				$instance = new VeImage();
			}
			return $instance;
		}
		public function resize( $image, $width, $height, $crop=true,  $aspect=true ){

			if( !$width || !$height ) return '';
		
			$imagSource =Mage::getBaseDir().DIRECTORY_SEPARATOR. str_replace( '/', DIRECTORY_SEPARATOR,  $image );
			if( !file_exists($imagSource) || !is_file($imagSource) ){ return ''; }
			$size = getimagesize( $imagSource );

			if( !$size ){ return ''; }
			
		 	$x_ratio = $width / $size[0];
			$y_ratio = $height / $size[1];
			
			$dst = new stdClass(); 
			$src = new stdClass();
			$src->y = $src->x = 0;
			$dst->y = $dst->x = 0;
		
			if ($width > $size[0])
				$width = $size[0];
			if ($height > $height)
				$height = $size[1];
				
		
			if ( $crop ) 
			{
				$dst->w = $width;
				$dst->h = $height;
				if ( ($size[0] <= $width) && ($size[1] <= $height) ) 
				{
					$src->w = $width;
					$src->h = $height;
				} 
				else 
				{
					if ($x_ratio < $y_ratio)
					{
						$src->w = ceil ( $width / $y_ratio );
						$src->h = $size[1];
					} 
					else
					{
						$src->w = $size[0];
						$src->h = ceil ( $height / $x_ratio );
					}
				}
				$src->x = floor ( ($size[0] - $src->w) / 2 );
				$src->y = floor ( ($size[1] - $src->h) / 2 );
			}
			else
			{
				$src->w = $size[0];
				$src->h = $size[1];
				if( $aspect ) 
				{
					if ( ($size[0] <= $width) && ($size[1] <= $height) )
					{
						$dst->w = $size[0];
						$dst->h = $size[1];
					} else if ( ($size[0] <= $width) && ($size[1] <= $height) ) 
					{
						$dst->w = $size[0];
						$dst->h = $size[1];
					} 
					else if ( ($x_ratio * $size[1]) < $height ) 
					{
						$dst->h = ceil ( $x_ratio * $size[1] );
						$dst->w = $width;
					} 
					else {
						$dst->w = ceil ( $y_ratio * $size[0] );
						$dst->h = $height;
					}
				} else {
					$dst->w = $width;
					$dst->h = $height;
				}
			}
			$ext =	substr ( strrchr ( $image, '.' ), 1 ); 
			$thumnail =  substr ( $image, 0, strpos ( $image, '.' )) . "_{$width}_{$height}.".$ext; 
			$imageCache   = $this->cLocation .  str_replace( '/', DIRECTORY_SEPARATOR, $thumnail );
		
			if( file_exists($imageCache) )
			{
				$smallImg = getimagesize ( $imageCache );
				if ( ($smallImg [0] == $dst->w && $smallImg [1] == $dst->h)  )
				{
					return  $this->cacheUrl. $thumnail;
				}
			} 
		
			if( !file_exists($this->cLocation) && !mkdir($this->cLocation) )
			{
				return '';
			}
			
			if( !$this->makeDir( $image ) ) {
				return '';
			}
			
			$this->_resizeImage( $imagSource, $src, $dst, $size, $imageCache ); 
			
			return  $this->cacheUrl. $thumnail;					
		}
		
		public function makeDir( $path )
		{
			$folders = explode ( '/',  ( $path ) );
			$tmppath = $this->cLocation;	
			for( $i = 0; $i < count ( $folders ) - 1; $i ++) 
			{
				if (! file_exists ( $tmppath . $folders [$i] ) && ! mkdir ( $tmppath . $folders [$i], 0755) )
				{
					return false;
				}	
				$tmppath = $tmppath . $folders [$i] . DIRECTORY_SEPARATOR;
			}		
			return true;
		}
		protected function _resizeImage( $imageSource, $src, $dst, $size, $imageCache )
		{
			$extension = $this->exts[$size[2]];
			$image = call_user_func( "imagecreatefrom".$extension, $imageSource );
			
			if( function_exists("imagecreatetruecolor") 
								&& ($newimage = imagecreatetruecolor($dst->w, $dst->h)) )
			{
				
				if( $extension == 'gif' || $extension == 'png' )
				{
					imagealphablending ( $newimage, false );
					imagesavealpha ( $newimage, true );
					$transparent = imagecolorallocatealpha ( $newimage, 255, 255, 255, 127 );
					imagefilledrectangle ( $newimage, 0, 0, $dst->w, $dst->h, $transparent );
				}
				
				imagecopyresampled ( $newimage, $image, $dst->x, $dst->y, $src->x, $src->y, $dst->w, $dst->h, $src->w, $src->h );
			} 
			else
			{
				$newimage = imagecreate ( $width, $height );
				imagecopyresized ( $newimage, $image, $dst->x, $dst->y, $src->x, $src->y, $dst->w, $dst->h, $size[0], $size[1] );
			}
	 	
			switch( $extension )
			{
				case 'jpeg' :
					call_user_func( 'image'.$extension, $newimage, $imageCache, $this->quality );	
					break;
				default:
					call_user_func( 'image'.$extension,$newimage, $imageCache );
					break;	
			}
			imagedestroy ( $image );
			imagedestroy ( $newimage );
		}
		
		public function getNoImage( $srcFile )
		{
			$this->missImage = $srcFile;
			return $this;
		}
		public function setQuality( $number = 10 )
		{
			$this->quality = $number;
			return $this;
		}
		
		public function isLinkedImage( $imageURL )
		{
			$parser = parse_url($imageURL);
		}
		
		public function isImage( $ext = '' )
		{
			return in_array($ext, $this->exts);
		}
		
		public function sourceExited( $imageSource ) 
		{
			
			if( $imageSource == '' || $imageSource == '..' || $imageSource == '.' )
			{
				return false;
			}
			$imageSource = rawurldecode ( $imageSource );
			return ( file_exists (Mage::getBaseDir() . '/' . $imageSource ) );	
		}
		
		public function setConfig( $thumbnailMode, $ratio=true )
		{
			$this->thumbType = $thumbnailMode;
			
			if( $thumbnailMode != 'none' )
			{
				$this->__isCrop = $thumbnailMode == 'crop' ? true:false;
				$this->__isResize = $ratio;
			}
			return $this;
		}
		
		public function resizeThumb( $image, $width, $height )
		{
			if( $this->thumbType == 'none' || empty($this->thumbType) )
			{
				return $image;	
			}
			return $this->resize( $image, $width, $height, $this->__isCrop, $this->__isResize  );
		}
		public function parseImage( $text ) 
		{
			$regex = "/\<img.+src\s*=\s*\"([^\"]*)\"[^\>]*\>/";
			preg_match ( $regex, $text, $matches );
			$images = (count ( $matches )) ? $matches : array ();
			$image = count ( $images ) > 1 ? $images [1] : '';
			return $image;
		}
	}
}
?>