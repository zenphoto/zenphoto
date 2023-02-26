<?php
/**
 * Transient image class
 * @package zpcore\classes\objects
 */
class Transientimage extends Image {

	/**
	 * creates a transient image (that is, one that is not stored in the database)
	 *
	 * @param string $image the full path to the image
	 * @return transientimage
	 */
	function __construct($album, $image) {
		if (!is_object($album)) {
			$album = new AlbumBase('Transient');
		}
		$this->album = $album;
		$this->localpath = $image;
		$filename = makeSpecialImageName($image);
		$this->filename = $filename;
		$this->displayname = stripSuffix(basename($image));
		if (empty($this->displayname)) {
			$this->displayname = $this->filename['name'];
		}
		$this->filemtime = @filemtime($this->localpath);
		$this->comments = null;
		$this->instantiate('images', array('filename' => $filename['name'], 'albumid' => $this->album->getID()), 'filename', true, true);
		$this->exists = false;
	}

}