<?php
namespace mukto90;

/**
 * A simple PHP class to encrypt a string and decrypt an encrypted string
 *
 * @author Nazmul Ahsan <n.mukto@gmail.com>
 * @link https://nazmulahsan.me/?p=670
 * @version 1.0.0
 */
class Ncrypt {

	/**
	 * Secret Key
	 *
	 * Personalized string to be used in encryption/decryption. It's STRONGY recommended to
	 * change it to your own.
	 *
	 * @see set_secret_key() method below.
	 *
	 * @var string
	 */
	private $secret_key = '#@%)(#&%&-my-really-secret-key';

	/**
	 * Secret Init Vector
	 *
	 * Personalized string to be used in encryption/decryption. It's STRONGY recommended to
	 * change it to your own.
	 *
	 * @see set_secret_iv() method below.
	 *
	 * @var string
	 */
	private $secret_iv	= '!@)*&%#(*^(-my-really-secret-iv';

	/**
	 * Encryption method
	 *
	 * @var string $cipher
	 *
	 * Available options:
	 *	AES-128-CFB
	 *	AES-128-CFB1
	 *	AES-128-CFB8
	 *	AES-128-OFB
	 *	AES-192-CBC
	 *	AES-192-CFB
	 *	AES-192-CFB1
	 *	AES-192-CFB8
	 *	AES-192-OFB
	 *	AES-256-CBC
	 *	AES-256-CFB
	 *	AES-256-CFB1
	 *	AES-256-CFB8
	 *	AES-256-OFB
	 */
	private $cipher = 'AES-192-CFB1';
	
	/**
	 * Set your own $secret_key
	 *
	 * @param string $key
	 */
	public function set_secret_key( $key ) {
		$this->secret_key = $key;
	}
	
	/**
	 * Get the $secret_key that's currently set
	 *
	 * @return string
	 */
	public function get_secret_key() {
		return hash( 'sha256', $this->secret_key );
	}
	
	/**
	 * Set your own $secret_iv
	 *
	 * @param string $iv
	 */
	public function set_secret_iv( $iv ) {
		$this->secret_iv = $iv;
	}
	
	/**
	 * Get the $secret_iv that's currently set
	 *
	 * @return string
	 */
	public function get_secret_iv() {
		return substr( hash( 'sha256', $this->secret_iv ), 0, 16 );
	}
	
	/**
	 * Set your own encryption method
	 *
	 * Available options:
	 *	AES-128-CFB
	 *	AES-128-CFB1
	 *	AES-128-CFB8
	 *	AES-128-OFB
	 *	AES-192-CBC
	 *	AES-192-CFB
	 *	AES-192-CFB1
	 *	AES-192-CFB8
	 *	AES-192-OFB
	 *	AES-256-CBC
	 *	AES-256-CFB
	 *	AES-256-CFB1
	 *	AES-256-CFB8
	 *	AES-256-OFB
	 *
	 * @param string $cipher
	 */
	public function set_cipher( $cipher ) {
		$this->cipher = $cipher;
	}
	
	/**
	 * Get the $cipher that's currently set
	 *
	 * @return string
	 */
	public function get_cipher() {
		return $this->cipher;
	}
	
	/**
	 * Encrypt a given string
	 *
	 * @param string $string The string to be encrypted
	 *
	 * @return string
	 */
	public function encrypt( $string ) {
		$cipher 		= $this->get_cipher();
		$secret_key 	= $this->get_secret_key();
		$secret_iv 		= $this->get_secret_iv();

		return base64_encode( openssl_encrypt( $string, $cipher, $secret_key, 0, $secret_iv ) );
	}
	
	/**
	 * Decrypt a given string
	 *
	 * @param string $string The string that's already encrypted using this encrypt() method
	 *
	 * @return string
	 */
	public function decrypt( $string ) {
		$cipher 		= $this->get_cipher();
		$secret_key 	= $this->get_secret_key();
		$secret_iv 		= $this->get_secret_iv();

		return openssl_decrypt( base64_decode( $string ), $cipher, $secret_key, 0, $secret_iv );
	}
}