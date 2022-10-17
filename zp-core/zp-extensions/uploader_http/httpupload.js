function addUploadBoxes(placeholderid, copyfromid, num) {
	for (i=0; i<num; i++) {
		jQuery('#'+copyfromid).clone().removeAttr('id').insertBefore('#'+placeholderid);
		window.totalinputs++;
		if (window.totalinputs >= 50) {
			jQuery('#addUploadBoxes').toggle('slow');
			return;
		}
	}
}

