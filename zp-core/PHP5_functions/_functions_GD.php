<?php
/* 
 * lib-GD functions for PHP version >= 5.1
 * @package functions
 * 
 */

/**
 * provides image blur support for lib-GD:zp_imageUnsharpMask
 *
 * @param image $imgCanvas
 * @param int $radius 
 * @param int $w
 * @param int $h
 */
function imageBlurGD($imgCanvas, $imgCanvas2, $radius, $w, $h) {
	// Gaussian blur matrix:
	//    1    2    1
	//    2    4    2
	//    1    2    1
	//////////////////////////////////////////////////
	for ($i = 0; $i < $radius; $i++)    {
		if (function_exists('imageconvolution')) { // PHP >= 5.1
			$matrix = array(
			array( 1, 2, 1 ),
			array( 2, 4, 2 ),
			array( 1, 2, 1 )
			);
			imageconvolution($imgCanvas, $matrix, 16, 0);
		}
	}
}

?>