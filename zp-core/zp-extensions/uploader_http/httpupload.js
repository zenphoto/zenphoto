function addUploadBoxes(placeholderid, copyfromid, num) {
	for (i=0; i<num; i++) {
		jQuery('#'+copyfromid).clone().insertBefore('#'+placeholderid);
		window.totalinputs++;
		if (window.totalinputs >= 50) {
			jQuery('#addUploadBoxes').toggle('slow');
			return;
		}
	}
}

