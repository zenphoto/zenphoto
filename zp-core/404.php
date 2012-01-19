<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
</head>
<body>
<?php

// force UTF-8 Ø

echo "\n<strong>".gettext("Zenphoto Error:</strong> the requested object was not found.");
if (isset($album)) {
	echo '<br />'.sprintf(gettext('Album: %s'),html_encode($album));
}
if (isset($image)) {
	echo '<br />'.sprintf(gettext('Image: %s'),html_encode($image));
}
if (isset($obj)) {
	echo '<br />'.sprintf(gettext('Page: %s'),html_encode(substr(basename($obj),0,-4)));
}
?>
<br />
<a href="<?php echo html_encode(getGalleryIndexURL());?>"
	title="<?php echo gettext('Albums Index'); ?>"><?php echo sprintf(gettext("Return to %s"),getGalleryTitle());?></a>
</body>
</html>