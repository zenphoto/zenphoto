<?php
/**
 * encryption support functions
 * @package core
 */

// force UTF-8 Ø


/************************************************************/
/*                                                          */
/*        WWW.PHPWEST.COM                                   */
/*                                                          */
/*        Encryption/decryption class                       */
/*                                                          */
/*                                                          */
/************************************************************/

function rc4 ($pass, $message)
{
 	$k = unpack( 'C*', $pass );
 	for ($i=0;$i<=255;$i++) {
 				$state[$i]=$i;
 	}
 	$x=0;$y=0;
 	for ($x=0;$x<=255;$x++) {
			$y=($k[($x % count($k))+1]+$state[$x]+$y)%256;
			$temp=$state[$x];
			$state[$x]=$state[$y];
			$state[$y]=$temp;
 	}
 	$MAX_CHUNK_SIZE = 1024;
 	$num = strlen($message) / $MAX_CHUNK_SIZE;
 	$int = floor ($num);
 	$int == $num ? $int : $int+1;
 	$num_pieces=$int;
 	$x=0;$y=0;
 	for ($piece=0;$piece<=$num_pieces;$piece++) {
			$mess_arr=unpack ("C*", substr($message, $piece * $MAX_CHUNK_SIZE, $MAX_CHUNK_SIZE));
			for($i=1;$i<=count($mess_arr);$i++) {
 				if (++$x > 255) {
						$x = 0 ;
 				}
 				if (($y += $state[$x]) > 255) {
						$y -= 256 ;
 				}
 				$temp=$state[$x];
 				$state[$x]=$state[$y];
 				$state[$y]=$temp;
 				$mess_arr[$i]^=$state[( $state[$x] + $state[$y] ) % 256];
			}
			$addition='';
			foreach ($mess_arr as $char) {
 				$addition.=pack("C*",$char);
			}
			$message=substr($message,0,$piece * $MAX_CHUNK_SIZE).$addition.substr($message,($piece+1)*$MAX_CHUNK_SIZE);
 	}
 	return $message;
}
?>
