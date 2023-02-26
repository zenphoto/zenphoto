function addUploadBoxes(num) {
	for (i=0; i<num; i++) {
		jQuery('#uploadboxes').append('<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>');
		window.totalinputs++;
		if (window.totalinputs >= 50) {
			jQuery('#addUploadBoxes').toggle('slow');
			return;
		}
	}
}

