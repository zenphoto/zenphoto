<?php
/**
 *  @package zpcore\plugins\uploaderjquery
 */

zp_register_filter('admin_headers', 'jQueryUpload_headers', 0);
//zp_register_filter('admin_head', 'jQueryUpload_head');

function jQueryUpload_headers() {
	ob_start();
}

function jQueryUpload_head() {
	$head = ob_get_contents();
	ob_end_clean();
	//insure we are running compatible scripts
}

function upload_head() {
	$myfolder = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_jQuery';
	?>
	<link rel="stylesheet" href="<?php echo $myfolder; ?>/css/jquery.fileupload.css">
	<!-- <link rel="stylesheet" href="<?php echo $myfolder; ?>/css/jquery.fileupload-ui.css"> -->
	<noscript><link rel="stylesheet" href="<?php echo $myfolder; ?>/js/css/jquery.fileupload-noscript.css"></noscript>
	<noscript><link rel="stylesheet" href="<?php echo $myfolder; ?>/js/css/jquery.fileupload-ui-noscript.css"></noscript>
	<script src="<?php echo $myfolder; ?>/js/vendor/jquery.ui.widget.js"></script>
   <!-- The Templates plugin is included to render the upload/download listings -->
  <script src="<?php echo $myfolder; ?>/js/javascript-templates/js/tmpl.min.js"></script>
   <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
  <script src="<?php echo $myfolder; ?>/js/load-image/load-image.all.min.js"></script>
  <!-- The Canvas to Blob plugin is included for image resizing functionality -->
  <script src="<?php echo $myfolder; ?>/js/canvas-to-blob/js/canvas-to-blob.min.js"></script>
    <!-- blueimp Gallery script -->
  <script src="<?php echo $myfolder; ?>/js/gallery/js/jquery.blueimp-gallery.min.js"></script>
   <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
  <script src="<?php echo $myfolder; ?>/js/jquery.iframe-transport.js"></script>
   <!-- The basic File Upload plugin -->
  <script src="<?php echo $myfolder; ?>/js/jquery.fileupload.js"></script>
  <!-- The File Upload processing plugin -->
  <script src="<?php echo $myfolder; ?>/js/jquery.fileupload-process.js"></script>
  <!-- The File Upload image preview & resize plugin -->
  <script src="<?php echo $myfolder; ?>/js/jquery.fileupload-image.js"></script>
  <!-- The File Upload audio preview plugin -->
  <script src="<?php echo $myfolder; ?>/js/jquery.fileupload-audio.js"></script>
  <!-- The File Upload video preview plugin -->
  <script src="<?php echo $myfolder; ?>/js/jquery.fileupload-video.js"></script>
  <!-- The File Upload validation plugin -->
  <script src="<?php echo $myfolder; ?>/js/jquery.fileupload-validate.js"></script>
  <!-- The File Upload user interface plugin -->
  <script src="<?php echo $myfolder; ?>/js/jquery.fileupload-ui.js"></script>
  <!-- The main application script -->
	<!-- <script src="<?php //echo $myfolder; ?>/js/demo.js"></script> -->

	<?php
	return $myfolder . '/uploader.php';
}

function upload_extra($uploadlimit, $passedalbum) {
	global $_zp_current_admin_obj;
	?>

	<script>
		/*jslint unparam: true */
		/*global window, $ */
		
		var zp_editurl = '';
		$(function () {
			$('.edit_upload').hide();
			'use strict';
			// Change this to the location of your server-side upload handler:

			// Initialize the jQuery File Upload widget:
			$('#fileupload').fileupload({
				// Uncomment the following to send cross-domain cookies:
				//xhrFields: {withCredentials: true},
				url: '<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_jQuery/uploader.php' ?>',
				dataType: 'json',
				dropZone: $( '.upload_dropzone' )
				<?php if ($uploadlimit) {
					echo ",
				maxFileSize:" . $uploadlimit; 
				} ?>
			});
			$(document).on('drop dragover', function (e) {
				e.preventDefault();
			});

			// Enable iframe cross-domain access via redirect option:
			$('#fileupload').fileupload(
							'option',
							'redirect',
							window.location.href.replace(/\/[^/]*$/, '/cors/result.html?%s')
			);
	
			$('#fileupload').bind('fileuploaddone', function(e, data) {
					<?php
					if (zp_loggedin(ALBUM_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
						?>
					  zp_editurl = 'admin-edit.php?page=edit&pagenumber=1&tab=imageinfo&album=' + encodeURIComponent($('#folderdisplay').val()) + '&uploaded=1&albumimagesort=id_desc';
						//	launchScript('admin-edit.php', ['page=edit', 'subpage=1', 'tab=imageinfo', 'album=' + encodeURIComponent($('#folderdisplay').val()), 'uploaded=1', 'albumimagesort=id_desc']);
						<?php
					} else {
						?>
						//	launchScript('admin-upload.php', ['uploaded=1']);
						<?php
					}
					?>
			});
			
		});

	</script>
		<form id="fileupload" action="uploader.php" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="existingfolder" id="existingfolder" value="false" />
			<input type="hidden" name="auth" id="auth" value="<?php echo $_zp_current_admin_obj->getPass(); ?>" />
			<input type="hidden" name="id" id="id" value="<?php echo $_zp_current_admin_obj->getID(); ?>" />
			<input type="hidden" name="folder" id="folderslot" value="<?php echo html_encode($passedalbum); ?>" />
			<input type="hidden" name="albumtitle" id="albumtitleslot" value="" />
			<input type="hidden" name="publishalbum" id="publishalbumslot" value="" />
			<p><?php
			$behaviour = getOption('uploaderjquery_behaviour');
			switch($behaviour) {
				default:
				case 'rename':
					echo gettext('Duplicate filenames will be renamed with an appended time string.');
					break;
				case 'disallow':
					echo gettext('NOTE: Duplicate filenames are not allowed to upload.');
					break;
				case 'overwrite':
					echo gettext('CAUTION: Duplicate filenames will overwrite existing files.');
					break;
			}
			?>
			</p>
			<div class="fileupload-buttonbar">
				<p id="fileUploadbuttons">
					<label class="fileinput-button btn"><?php echo gettext('Add files...'); ?>
						<input type="file" name="files[]" multiple>
					</label>
					<button type="submit" class="start"><?php echo gettext('Start upload'); ?></button>
					<button type="reset" class="cancel"><?php echo gettext('Cancel upload'); ?></button>
					<div class="upload_dropzone" style="margin: 15px 0 15p 0; width: 100%; height: 100px; border: 2px dashed lightgray; vertical-align: middle; text-align: center;"><p style="margin-top: 35px"><?php echo gettext('Drag files to upload here'); ?></p></div>
      
				</p>
				<!-- The global progress state -->
          <div class="fileupload-progress fade">
            <!-- The global progress bar -->
            <div
              class="progress progress-striped active"
              role="progressbar"
              aria-valuemin="0"
              aria-valuemax="100"
            >
              <div
                class="progress-bar progress-bar-success"
                style="width: 0%;"
              ></div>
            </div>
            <!-- The extended global progress state -->
            <div class="progress-extended">&nbsp;</div>
          </div>
				
			</div>
			
			 <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped">
          <tbody class="files"></tbody>
        </table>
		</form>

	<!-- The template to display files available for upload -->
    <script id="template-upload" type="text/x-tmpl">
      {% for (var i=0, file; file=o.files[i]; i++) { %}
          <tr class="template-upload fade{%=o.options.loadImageFileTypes.test(file.type)?' image':''%}">
              <td>
                  <span class="preview"></span>
              </td>
              <td>
                  <p class="name">{%=file.name%}</p>
                  <strong class="error text-danger"></strong>
              </td>
              <td>
                  <p class="size">Processing...</p>
                  <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
              </td>
              <td>
                  {% if (!o.options.autoUpload && o.options.edit && o.options.loadImageFileTypes.test(file.type)) { %}
                    <button class="btn btn-success edit" data-index="{%=i%}" disabled>
                        <span>Edit</span>
                    </button>
                  {% } %}
                  {% if (!i && !o.options.autoUpload) { %}
                      <button class="btn btn-primary start" disabled>
                          <span>Start</span>
                      </button>
                  {% } %}
                  {% if (!i) { %}
                      <button class="btn btn-warning cancel">
                          <span>Cancel</span>
                      </button>
                  {% } %}
              </td>
          </tr>
      {% } %}
    </script>
    <!-- The template to display files available for download -->
    <script id="template-download" type="text/x-tmpl">
      {% for (var i=0, file; file=o.files[i]; i++) { %}
          <tr class="template-download fade{%=file.thumbnailUrl?' image':''%}">
               <td>
                  <p class="name">{%=file.name%}</p>
                  {% if (file.error) { %}
                      <div><span class="label label-danger"><?php echo gettext('Error'); ?></span> {%=file.error%}</div>
                  {% } else { %}
											<div><span class="label label-success"><?php echo gettext('Done'); ?></span> <?php echo gettext('Successfully uploaded'); ?></div>
									{% }  %}
              </td>
							<td>
								 {% if (file.url) { %}
                          <a href="{%=file.url%}" title="{%=file.name%}" target="_blank" {%=file.thumbnailUrl?'data-gallery':''%}><?php echo gettext('Edit file'); ?></a>
                 {% }  %}
							</td>
              <td>
                  <span class="size">{%=o.formatFileSize(file.size)%}</span>
              </td>
          </tr>
      {% } %}
    </script>
	<?php
}

function upload_form($uploadlimit, $passedalbum) {
	
}