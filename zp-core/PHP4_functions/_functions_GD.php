<?php
/* 
 * lib-GD functions for PHP version < 5.1
 * @package functions
 * 
 */


if (!function_exists('imagerotate')) {
	 
	/**
	 * Substitute for GD imagerotate
	 *
	 * @param image $imgSrc
	 * @param int $angle
	 * @param int $bgd_colour
	 * @return image
	 */
	function imagerotate($imgSrc, $angle, $bgd_colour) {
		// ensuring we got really RightAngle (if not we choose the closest one)
		$angle = min( ( (int)(($angle+45) / 90) * 90), 270 );

		// no need to fight
		if ($angle == 0)
		return ($imgSrc);

		// dimenstion of source image
		$srcX = imagesx($imgSrc);
		$srcY = imagesy($imgSrc);

		switch ($angle) {
			case 90:
				$imgDest = imagecreatetruecolor($srcY, $srcX);
				for ($x=0; $x<$srcX; $x++)
				for ($y=0; $y<$srcY; $y++)
				imagecopy($imgDest, $imgSrc, $srcY-$y-1, $x, $x, $y, 1, 1);
				break;

			case 180:
				$imgDest = imageflip($imgSrc, IMAGE_FLIP_BOTH);
				break;

			case 270:
				$imgDest = imagecreatetruecolor($srcY, $srcX);
				for ($x=0; $x<$srcX; $x++)
				for ($y=0; $y<$srcY; $y++)
				imagecopy($imgDest, $imgSrc, $y, $srcX-$x-1, $x, $y, 1, 1);
				break;
		}

		return ($imgDest);
	}
}

/**
 * provides image blur support for lib-GD:zp_imageUnsharpMask
 *
 * @param image $imgCanvas
 * @param int $radius 
 * @param int $w
 * @param int $h
 */
function imageBlurGD($imgCanvas, $imgCanvas2, $radius, $w, $h) {
	$imgBlur = imagecreatetruecolor($w, $h);
	$imgBlur2 = imagecreatetruecolor($w, $h);
	imagecopy($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h); // background
	for ($i = 0; $i < $radius; $i++)    {
		// Move copies of the image around one pixel at the time and merge them with weight
		// according to the matrix. The same matrix is simply repeated for higher radii.

		imagecopy      ($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1); // up left
		imagecopymerge ($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
		imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
		imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25); // up right
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
		imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 ); // up
		imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
		imagecopy      ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

		// During the loop above the blurred copy darkens, possibly due to a roundoff
		// error. Therefore the sharp picture has to go through the same loop to
		// produce a similar image for comparison. This is not a good thing, as processing
		// time increases heavily.
		imagecopy      ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 20 );
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 16.666667);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
		imagecopy      ($imgCanvas2, $imgBlur2, 0, 0, 0, 0, $w, $h);
	}
}
?>