<?php

/**
 * jQuery File Upload Plugin PHP Class based on the 
 * https://github.com/blueimp/jQuery-File-Upload
 * 
 * with ZenphotoCMS customisations
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 * 
 * @package zpcore\plugins\uploaderjquery
 */

class UploadHandlerZP extends UploadHandler {

	protected $options;
	protected $error_messages = array();

	const IMAGETYPE_GIF = 'image/gif';
	const IMAGETYPE_JPEG = 'image/jpeg';
	const IMAGETYPE_PNG = 'image/png';

	protected $image_objects = array();
	protected $response = array();

	public function __construct($options = null, $initialize = true, $error_messages = null) {
		//definition required here as gettext seems invalid in property defines
		$this->error_messages = array(
				1 => gettext('The uploaded file exceeds the upload_max_filesize directive in php.ini'),
				2 => gettext('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
				3 => gettext('The uploaded file was only partially uploaded'),
				4 => gettext('No file was uploaded'),
				6 => gettext('Missing a temporary folder'),
				7 => gettext('Failed to write file to disk'),
				8 => gettext('A PHP extension stopped the file upload'),
				'post_max_size' => gettext('The uploaded file exceeds the post_max_size directive in php.ini'),
				'max_file_size' => gettext('File is too big'),
				'min_file_size' => gettext('File is too small'),
				'accept_file_types' => gettext('Filetype not allowed'),
				'max_number_of_files' => gettext('Maximum number of files exceeded'),
				'max_width' => gettext('Image exceeds maximum width'),
				'min_width' => gettext('Image requires a minimum width'),
				'max_height' => gettext('Image exceeds maximum height'),
				'min_height' => gettext('Image requires a minimum height'),
				'abort' => gettext('File upload aborted'),
				'image_resize' => gettext('Failed to resize image'),
				'duplicate' => gettext('File not uploaded. Duplicates are not allowed')
		);
		$this->options = array(
				'script_url' => $this->get_full_url() . '/' . $this->basename(strval($this->get_server_var('SCRIPT_NAME'))),
				'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')) . '/files/',
				'upload_url' => $this->get_full_url() . '/files/',
				'param_name' => 'files',
				'input_stream' => 'php://input',
				'user_dirs' => false,
				'mkdir_mode' => 0755,
				// Set the following option to 'POST', if your server does not support
				// DELETE requests. This is a parameter sent to the client:
				'delete_type' => 'DELETE',
				'access_control_allow_origin' => '*',
				'access_control_allow_credentials' => false,
				'access_control_allow_methods' => array(
						'OPTIONS',
						'HEAD',
						'GET',
						'POST',
						'PUT',
						'PATCH',
						'DELETE'
				),
				'access_control_allow_headers' => array(
						'Content-Type',
						'Content-Range',
						'Content-Disposition'
				),
				// By default, allow redirects to the referer protocol+host:
				'redirect_allow_target' => '/^' . preg_quote(
								parse_url($this->get_server_var('HTTP_REFERER'), PHP_URL_SCHEME)
								. '://'
								. parse_url($this->get_server_var('HTTP_REFERER'), PHP_URL_HOST)
								. '/', // Trailing slash to not match subdomains by mistake
								'/' // preg_quote delimiter param
				) . '/',
				// Enable to provide file downloads via GET requests to the PHP script:
				//     1. Set to 1 to download files via readfile method through PHP
				//     2. Set to 2 to send a X-Sendfile header for lighttpd/Apache
				//     3. Set to 3 to send a X-Accel-Redirect header for nginx
				// If set to 2 or 3, adjust the upload_url option to the base path of
				// the redirect parameter, e.g. '/files/'.
				'download_via_php' => false,
				// Read files in chunks to avoid memory limits when download_via_php
				// is enabled, set to 0 to disable chunked reading of files:
				'readfile_chunk_size' => 10 * 1024 * 1024, // 10 MiB
				// Defines which files can be displayed inline when downloaded:
				'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
				// Defines which files (based on their names) are accepted for upload.
				// By default, only allows file uploads with image file extensions.
				// Only change this setting after making sure that any allowed file
				// types cannot be executed by the webserver in the files directory,
				// e.g. PHP scripts, nor executed by the browser when downloaded,
				// e.g. HTML files with embedded JavaScript code.
				// Please also read the SECURITY.md document in this repository.
				'accept_file_types' => '/.+$/i', // Original setting '/\.(gif|jpe?g|png)$/i',
				// Replaces dots in filenames with the given string.
				// Can be disabled by setting it to false or an empty string.
				// Note that this is a security feature for servers that support
				// multiple file extensions, e.g. the Apache AddHandler Directive:
				// https://httpd.apache.org/docs/current/mod/mod_mime.html#addhandler
				// Before disabling it, make sure that files uploaded with multiple
				// extensions cannot be executed by the webserver, e.g.
				// "example.php.png" with embedded PHP code, nor executed by the
				// browser when downloaded, e.g. "example.html.gif" with embedded
				// JavaScript code.
				'replace_dots_in_filenames' => '-',
				// The php.ini settings upload_max_filesize and post_max_size
				// take precedence over the following max_file_size setting:
				'max_file_size' => null,
				'min_file_size' => 1,
				// The maximum number of files for the upload directory:
				'max_number_of_files' => null,
				// Reads first file bytes to identify and correct file extensions:
				'correct_image_extensions' => false,
				// Image resolution restrictions:
				'max_width' => null,
				'max_height' => null,
				'min_width' => 1,
				'min_height' => 1,
				// Set the following option to false to enable resumable uploads:
				'discard_aborted_uploads' => true,
				// Set to 0 to use the GD library to scale and orient images,
				// set to 1 to use imagick (if installed, falls back to GD),
				// set to 2 to use the ImageMagick convert binary directly:
				'image_library' => 1,
				// Uncomment the following to define an array of resource limits
				// for imagick:
				/*
				  'imagick_resource_limits' => array(
				  imagick::RESOURCETYPE_MAP => 32,
				  imagick::RESOURCETYPE_MEMORY => 32
				  ),
				 */
				// Command or path for to the ImageMagick convert binary:
				'convert_bin' => 'convert',
				// Uncomment the following to add parameters in front of each
				// ImageMagick convert call (the limit constraints seem only
				// to have an effect if put in front):
				/*
				  'convert_params' => '-limit memory 32MiB -limit map 32MiB',
				 */
				// Command or path for to the ImageMagick identify binary:
				'identify_bin' => 'identify',
				'image_versions' => array(
						// The empty image version key defines options for the original image.
						// Keep in mind: these image manipulations are inherited by all other image versions from this point onwards.
						// Also note that the property 'no_cache' is not inherited, since it's not a manipulation.
						//'' => array(
								// Automatically rotate images based on EXIF meta data:
								//'auto_orient' => true
						//),
				// You can add arrays to generate different versions.
				// The name of the key is the name of the version (example: 'medium').
				// the array contains the options to apply.
				/*
				  'medium' => array(
				  'max_width' => 800,
				  'max_height' => 600
				  ),
				 */
				//'thumbnail' => array(
				// Uncomment the following to use a defined directory for the thumbnails
				// instead of a subdirectory based on the version identifier.
				// Make sure that this directory doesn't allow execution of files if you
				// don't pose any restrictions on the type of uploaded files, e.g. by
				// copying the .htaccess file from the files directory for Apache:
				//'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/thumb/',
				//'upload_url' => $this->get_full_url().'/thumb/',
				// Uncomment the following to force the max
				// dimensions and e.g. create square thumbnails:
				// 'auto_orient' => true,
				// 'crop' => true,
				// 'jpeg_quality' => 70,
				//'no_cache' => true, //(there's a caching option, but this remembers thumbnail sizes from a previous action!)
				// 'strip' => true, (this strips EXIF tags, such as geolocation)
				//'max_width' => 80, // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
				//'max_height' => 80 // either specify height, or set to 0. Then height is automatically adjusted - keeping aspect ratio to a specified max_width.
				//)
				),
				'print_response' => true
		);
		if ($options) {
			$this->options = $options + $this->options;
		}
		if ($error_messages) {
			$this->error_messages = $error_messages + $this->error_messages;
		}
		if ($initialize) {
			$this->initialize();
		}
	}

	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null) {
		global $_zp_uploader_folder, $_zp_uploader_targetpath, $_zp_current_admin_obj;
		$albumobj = $imageobj = null;
		$file = new \stdClass();
		$file->name = $this->get_file_name($uploaded_file, $name, $size, $type, $error, $index, $content_range);
		$is_zip_upload = false;
		
		// zpcms addition
		$seoname = seoFriendly($name);
		if (strrpos($seoname, '.') === 0) {
			$seoname = sha1($name) . $seoname; // soe stripped out all the name.
		}
		$targetFile = $_zp_uploader_targetpath . '/' . internalToFilesystem($seoname);
		if (file_exists($targetFile)) {
			$behaviour = getOption('uploaderjquery_behaviour');
			switch($behaviour) {
				default:
				case 'rename':
					$append = '_' . time();
					$seoname = stripSuffix($seoname) . $append . '.' . getSuffix($seoname);
					$targetFile = $_zp_uploader_targetpath . '/' . internalToFilesystem($seoname);
					break;
				case 'disallow':
					$file->error = $error = $this->get_error_message('duplicate');
					break;
				case 'overwrite':
					// nothing to do
					break;
			}
		}
		$file->name = $seoname;
		// zpcms addition end
		
		$file->size = $this->fix_integer_overflow((int) $size);
		$file->type = $type;
		if ($this->validate($uploaded_file, $file, $error, $index, $content_range)) {
			$this->handle_form_data($file, $index);
			$upload_dir = $this->get_upload_path();
			if (!is_dir($upload_dir)) {
				mkdir($upload_dir, $this->options['mkdir_mode'], true);
			}
			$file_path = $this->get_upload_path($file->name);
			$append_file = $content_range && is_file($file_path) &&
							$file->size > $this->get_file_size($file_path);
			if ($uploaded_file && is_uploaded_file($uploaded_file)) {
				// multipart/formdata uploads (POST method uploads)
				if ($append_file) {
					file_put_contents(
									$file_path,
									fopen($uploaded_file, 'r'),
									FILE_APPEND
					);
				} else {
					move_uploaded_file($uploaded_file, $file_path);
					// zpcms addition
					if (Gallery::validImage($name) || Gallery::validImageAlt($name)) {
						@chmod($targetFile, FILE_MOD);
						$albumobj = Albumbase::newAlbum($_zp_uploader_folder);
						$imageobj = Image::newImage($albumobj, $seoname);
						$imageobj->setOwner($_zp_current_admin_obj->getUser());
						if ($name != $seoname && $imageobj->getTitle() == substr($seoname, 0, strrpos($seoname, '.'))) {
							$imageobj->setTitle(stripSuffix($name, '.'));
						}
						$imageobj->save();
					} else if (is_zip($targetFile)) {
						$zip_success = unzip($targetFile, $_zp_uploader_targetpath);
						if($zip_success) {
							$is_zip_upload = true;
						} else {
							unlink($targetFile);
						}
					} else {
						$file->error = $error = $this->get_error_message(UPLOAD_ERR_EXTENSION); // invalid file uploaded
					}
					// zpcms addition end
				}
			} else {
				// Non-multipart uploads (PUT method support)
				file_put_contents(
								$file_path,
								fopen($this->options['input_stream'], 'r'),
								$append_file ? FILE_APPEND : 0
				);
			}
			$file_size = $this->get_file_size($file_path, $append_file);
			if ($file_size === $file->size) {
				$file->url = $this->get_download_url($file->name, null, false, $imageobj);
				if ($this->has_image_file_extension($file->name)) {
					if ($content_range && !$this->validate_image_file($file_path, $file, $error, $index)) {
						unlink($file_path);
					} else {
						$this->handle_image_file($file_path, $file);
					}
				} else if ($is_zip_upload) {
					unlink($targetFile);
				}
			} else {
				$file->size = $file_size;
				if (!$content_range && $this->options['discard_aborted_uploads']) {
					unlink($file_path);
					$file->error = $this->get_error_message('abort');
				}
			}
			$this->set_additional_file_properties($file);
		} 
		return $file;
	}
	
	protected function get_download_url($file_name, $version = null, $direct = false, $imageobj = null) {
		if(!is_null($imageobj)) {
			$link = FULLWEBPATH .'/' . ZENFOLDER .'/admin-edit.php?page=edit&tab=imageinfo&album='. sanitize_path($imageobj->album->name).'&singleimage='. html_encode($imageobj->filename).'&pagenumber=1';
			return $link;
		}
	}
	
	public function post($print_response = true) {
		if ($this->get_query_param('_method') === 'DELETE') {
			return $this->delete($print_response);
		}
		$upload = $this->get_upload_data($this->options['param_name']);
		// Parse the Content-Disposition header, if available:
		$content_disposition_header = $this->get_server_var('HTTP_CONTENT_DISPOSITION');
		$file_name = $content_disposition_header ?
						rawurldecode(preg_replace(
														'/(^[^"]+")|("$)/',
														'',
														$content_disposition_header
						)) : null;
		// Parse the Content-Range header, which has the following form:
		// Content-Range: bytes 0-524287/2000000
		$content_range_header = $this->get_server_var('HTTP_CONTENT_RANGE');
		$content_range = $content_range_header ?
						preg_split('/[^0-9]+/', $content_range_header) : null;
		$size = @$content_range[3];
		$files = array();
		if ($upload) {
			if (is_array($upload['tmp_name'])) {
				// param_name is an array identifier like "files[]",
				// $upload is a multi-dimensional array:
				foreach ($upload['tmp_name'] as $index => $value) {
					$files[] = $this->handle_file_upload(
									$upload['tmp_name'][$index],
									$file_name ? $file_name : $upload['name'][$index],
									$size ? $size : $upload['size'][$index],
									$upload['type'][$index],
									$upload['error'][$index],
									$index,
									$content_range
					);
				}
			} else {
				// param_name is a single object identifier like "file",
				// $upload is a one-dimensional array:
				$files[] = $this->handle_file_upload(
								isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
								$file_name ? $file_name : (isset($upload['name']) ?
								$upload['name'] : null),
								$size ? $size : (isset($upload['size']) ?
								$upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
								isset($upload['type']) ?
								$upload['type'] : $this->get_server_var('CONTENT_TYPE'),
								isset($upload['error']) ? $upload['error'] : null,
								null,
								$content_range
				);
			}
		}
		$response = array($this->options['param_name'] => $files);
		return $this->generate_response($response, $print_response);
	}
	
	protected function basename($filepath, $suffix = null) {
		$splited = preg_split('/\//', rtrim($filepath, '/ '));
		return substr(basename('X' . $splited[count($splited) - 1], strval($suffix)), 1);
	}

}