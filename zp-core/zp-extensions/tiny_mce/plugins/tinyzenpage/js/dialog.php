<script language="javascript" type="text/javascript">
/* tinyMCEPopup.requireLangPack(); */

function stripHTML(oldString) {

   var newString = "";
   var inTag = false;
   for(var i = 0; i < oldString.length; i++) {

        if(oldString.charAt(i) == '<') inTag = true;
        if(oldString.charAt(i) == '>') {
              if(oldString.charAt(i+1)=="<")
              {
              		//dont do anything
	}
	else
	{
		inTag = false;
		i++;
	}
        }

        if(!inTag) newString += oldString.charAt(i);

   }

   return newString;
}

var ZenpageDialog = {
	init : function(ed) {
		tinyMCEPopup.resizeToInnerSize();
	},

	insert : function(imgurl,thumburl,sizedimage,imgname,imgtitle,albumtitle,fullimage,type, wm_thumb, wm_img,video,imgdesc,albumdesc) {
		var ed = tinyMCEPopup.editor, dom = ed.dom;
		var imglink = '';
		var includetype = '';
		var imagesize = '';
		var linkpart1 = '';
		var linkpart2 = '';
		var linktype = '';
		var textwrap = '';
		var textwrap_float = '';
		var infowrap1 = '';
		var infowrap2 = '';
		var titlewrap = '';
		var descwrap = '';
		var cssclass = '';
		var albumname = '<?php if(isset($_GET["album"]))  { echo html_encode(sanitize($_GET["album"])); } else { $_GET["album"] = ""; } ?>';
		var webpath = '<?php echo WEBPATH; ?>'
		var modrewrite = '<?php echo MOD_REWRITE; ?>';
		var modrewritesuffix = '<?php echo getOption("mod_rewrite_image_suffix"); ?>';
		var plainimgtitle = imgtitle.replace(/'|\\'/g, "\\'");
		var plainalbumtitle = albumtitle.replace(/'|\\'/g, "\\'");


		plainimgtitle = stripHTML(plainimgtitle);
		plainalbumtitle = stripHTML(plainalbumtitle);

		<?php
		chdir(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/flowplayer3');
		$filelist = safe_glob('flowplayer-*.swf');
		$swf = array_shift($filelist);
		$flowplayerpath = pathurlencode(WEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER . '/flowplayer3/'.$swf);
		?>
		var flowplayerpath = '<?php echo $flowplayerpath; ?>';
		var playerheight = '';
		var stopincluding = false;
		// getting the image size checkbox values
		if($('#thumbnail').attr('checked') == 'checked') {
			cssclass ='zenpage_thumb';
		}
		if($('#customthumb').attr('checked') == 'checked') {
			imagesize = '&amp;s='+$('#cropsize').val()+'&amp;cw='+$('#cropwidth').val()+'&amp;ch='+$('#cropheight').val()+'&amp;t=true';
			if (wm_thumb) {
				imagesize += '&amp;wmk='+wm_thumb;
			}
			cssclass ='zenpage_customthumb';
		}
		if($('#sizedimage').attr('checked') == 'checked') {
			cssclass ='zenpage_sizedimage';
		}
		if($('#customsize').attr('checked') == 'checked') {
			if(video) {
				alert('<?php echo gettext('It is not possible to a include a custom size %s.'); ?>'.replace(/%s/,imgname));
				stopincluding = true;
			}
			imagesize = '&amp;s='+$('#size').val();
			if (wm_img) {
				imagesize += '&amp;wmk='+wm_img;
			}
			cssclass ='zenpage_customimage';
		}
		if($('#fullimage').attr('checked') == 'checked') {
			if(video) {
				alert('<?php echo gettext('It is not possible to a include a full size %s.'); ?>'.replace(/%s/,imgname));
				stopincluding = true;
			}
			imagesize = '';
			cssclass ='zenpage_fullimage';
		}

		// getting the text wrap checkbox values
		// Customize the textwrap variable if you need specific CSS
		if($('#nowrap').attr('checked') == 'checked') {
			textwrap = 'class=\''+cssclass+'\'';
			textwrap_title = '';
			textwrap_title_add = '';
		}
		if($('#rightwrap').attr('checked') == 'checked') {
			// we don't need (inline) css attached to the image/link it they are wrapped for the title, the div does the wrapping!
			if($('#showtitle').attr('checked') == 'checked') {
				textwrap_float = ' style=\'float: left;\'';
			}
			textwrap = 'class=\''+cssclass+'_left\''+textwrap_float;
			textwrap_title = ' style=\'float: left;\' ';
			textwrap_title_add = '_left';
		}
		if($('#leftwrap').attr('checked') == 'checked') {
			// we don't need (inline) css attached to the image/link it they are wrapped for the title, the div does the wrapping!
			if($('#showtitle:checked').val() != 1 && $('#showdesc:checked').val() != 1) {
				textwrap_float = ' style=\'float: right;\'';
			}
			textwrap = 'class=\''+cssclass+'_right\''+textwrap_float;
			textwrap_title = ' style=\'float: right;\' ';
			textwrap_title_add = '_right';
		}
		// getting the link type checkbox values
		if($('#imagelink').attr('checked') == 'checked') {
			if(modrewrite == '1') {
				linkpart1 = '<a href=\''+webpath+'/'+albumname+'/'+imgname+modrewritesuffix+'\' title=\''+plainimgtitle+'\' class=\'zenpage_imagelink\'>';
			} else {
				linkpart1 = '<a href=\''+webpath+'/index.php?album='+albumname+'&amp;image='+imgname+'\' title=\''+plainimgtitle+'\' class=\'zenpage_imagelink\'>';
			}
			linkpart2 = '</a>';
		}
		if($('#fullimagelink').attr('checked') == 'checked') {
				linkpart1 = '<a href=\''+fullimage+'\' title=\''+plainimgtitle+'\' class=\'zenpage_fullimagelink\' rel=\'colorbox\'>';
				linkpart2 = '</a>';
		}
		if($('#albumlink').attr('checked') == 'checked') {
			if(modrewrite == '1') {
				linkpart1 = '<a href=\''+webpath+'/'+albumname+'\' title=\''+plainalbumtitle+'\' class=\'zenpage_albumlink\'>';
			} else {
				linkpart1 = '<a href=\''+webpath+'/index.php?album='+albumname+'\' title=\''+plainalbumtitle+'\' class=\'zenpage_albumlink\'>';
			}
			linkpart2 = '</a>';
		}
		if($('#customlink').attr('checked') == 'checked') {
			linkpart1 = '<a href=\''+$('#linkurl').val()+'\' title=\''+linktype+'\' '+textwrap+' class=\'zenpage_customlink\'>';
			linkpart2 = '</a>';
		}
		// getting the include type checkbox values
		if($('#image').attr('checked') == 'checked') {
			if($('#fullimage').attr('checked') == 'checked') {
				includetype = '<img src=\''+fullimage+'\' alt=\''+imgtitle+'\' '+textwrap+' />';
			} else if ($('#thumbnail').attr('checked') == 'checked') {
				includetype = '<img src=\''+thumburl+'\' alt=\''+imgtitle+'\' '+textwrap+' />';
			} else {
				includetype = '<img src=\''+imgurl+imagesize+'\' alt=\''+imgtitle+'\' '+textwrap+' />';
			}
			if($('#showtitle').attr('checked') == 'checked' || $('#imagedesc').attr('checked') == 'checked' || $('#albumdesc').attr('checked') == 'checked') {
				infowrap1 = '<div class=\'zenpage_wrapper'+textwrap_title_add+'\''+textwrap_title+'>';
				infowrap2 = '</div>';
			}
			if($('#showtitle').attr('checked') == 'checked') {
				if($('#albumlink').attr('checked') == 'checked') {
					titlewrap = '<div class=\'zenpage_title\'>'+albumtitle+'</div>';
				} else {
					titlewrap = '<div class=\'zenpage_title\'>'+imgtitle+'</div>';
				}
			}
			if($('#imagedesc').attr('checked') == 'checked') {
				descwrap = '<div class=\'zenpage_desc\'>'+imgdesc+'</div>';
			}
			if($('#albumdesc').attr('checked') == 'checked') {
				descwrap = '<div class=\'zenpage_desc\'>'+albumdesc+'</div>';
			}
			infowrap2 = titlewrap+descwrap+infowrap2;
		}
		if($('#imagetitle').attr('checked') == 'checked') {
			includetype = html_encode(imgtitle);
		}
		if($('#albumtitle').attr('checked') == 'checked') {
			includetype = html_encode(albumtitle);
		}
		if($('#customtext').attr('checked') == 'checked') {
			includetype = $('#text').val();
		}

		// building the final item to include
		switch (type) {
			case 'zenphoto':

				if((video == 'video' || video == 'audio') && $('#sizedimage').attr('checked')=='checked') {
					if(video == 'video') {
						playerheight = "<?php echo getOption('tinymce_tinyzenpage_flowplayer_height'); ?>";
					} else {
						playerheight = "<?php echo getOption('tinymce_tinyzenpage_flowplayer_mp3_height'); ?>";
					}
					imglink = infowrap1;
					imglink += '<object '+textwrap+' width="<?php echo getOption('tinymce_tinyzenpage_flowplayer_width'); ?>" height="'+playerheight+'" data="'+flowplayerpath+'" type="application/x-shockwave-flash">';
					imglink += '<param name="movie" value="'+flowplayerpath+'" />';
					imglink += '<param name="allowfullscreen" value="true" />';
					imglink += '<param name="allowscriptaccess" value="always" />';
					imglink += '<param name="flashvars" value=\'config={';
					imglink += '"plugins": {';
					imglink += '"controls":{';
					imglink += '"backgroundColor": "<?php echo getOption('flow_player3_controlsbackgroundcolor'); ?>",';
					imglink += '"backgroundGradient": "<?php echo getOption('flow_player3_controlsbackgroundcolorgradient'); ?>",';
					imglink += '"autoHide": "<?php echo getOption('flow_player3_controlsautohide'); ?>",';
					imglink += '"timeColor":"<?php echo getOption('flow_player3_controlstimecolor'); ?>",';
					imglink += '"durationColor": "<?php echo getOption('flow_player3_controlsdurationcolor'); ?>",';
					imglink += '"progressColor": "<?php echo getOption('flow_player3_controlsprogresscolor'); ?>",';
					imglink += '"progressGradient": "<?php echo getOption('flow_player3_controlsprogressgradient'); ?>",';
					imglink += '"bufferColor": "<?php echo getOption('flow_player3_controlsbuffercolor'); ?>",';
					imglink += '"bufferGradient":	 "<?php echo getOption('flow_player3_controlsbuffergradient'); ?>",';
					imglink += '"sliderColor": "<?php echo getOption('flow_player3_controlsslidercolor'); ?>",';
					imglink += '"sliderGradient": "<?php echo getOption('flow_player3_controlsslidergradient'); ?>",';
					imglink += '"buttonColor": "<?php echo getOption('flow_player3_controlsbuttoncolor'); ?>",';
					imglink += '"buttonOverColor": "<?php echo getOption('flow_player3_controlsbuttonovercolor'); ?>"';
					imglink += '}';
					imglink += '},';
					imglink += '"canvas": {';
					imglink += '"backgroundColor": "<?php echo getOption('flow_player3_backgroundcolor'); ?>",';
					imglink += '"backgroundGradient": "<?php echo getOption('flow_player3_backgroundcolorgradient'); ?>"';
					imglink += '},';
					imglink += '"clip":{';
					imglink += '"url":"'+fullimage+'",';
					<?php
						if(getOption("flow_player3_autoplay") == 1) {
							$autoplay = "true";
						} else {
							$autoplay = "false";
						}
					?>
					imglink += '"autoPlay":<?php echo $autoplay; ?>,';
					imglink += '"autoBuffering": <?php echo $autoplay; ?>,';
					imglink += '"scaling": "<?php echo getOption('flow_player3_scaling'); ?>"';
					imglink += '}';
					imglink += '}\' />';
					imglink += '</object>';
					imglink += infowrap2;
				}	else if($('#sizedimage').attr('checked')=='checked') {
					imglink = infowrap1+sizedimage+infowrap2;
				} else {
					imglink = infowrap1+linkpart1+includetype+linkpart2+infowrap2;
				}
				break;
			case 'pages':
				if(modrewrite) {
					imglink = '<a href=\''+webpath+'/'+imgurl+'\' title=\''+plainimgtitle+'\'>'+imgtitle+'</a>';
				} else {
					imglink = '<a href=\''+webpath+'/index.php?p=pages&amp;title='+imgname+'\' title=\''+plainimgtitle+'\'>'+imgtitle+'</a>';
				}
				break;
			case 'articles':
				if(modrewrite) {
					imglink = '<a href=\''+webpath+'/'+imgurl+'\' title=\''+plainimgtitle+'\'>'+imgtitle+'</a>';
				} else {
					imglink = '<a href=\''+webpath+'/index.php?p=news&amp;title='+imgname+'\' title=\''+plainimgtitle+'\'>'+imgtitle+'</a>';
				}
				break;
			case 'categories':
				if(modrewrite) {
					imglink = '<a href=\''+webpath+'/'+imgurl+'\' title=\''+plainimgtitle+'\'>'+imgtitle+'</a>';
				} else {
					imglink = '<a href=\''+webpath+'/index.php?p=news&amp;category='+imgname+'\' title=\''+plainimgtitle+'\'>'+imgtitle+'</a>';
				}
			break;
		}
		if(!stopincluding) {
			tinyMCEPopup.execCommand('mceInsertContent', false, imglink);
		}
	}
};

tinyMCEPopup.onInit.add(ZenpageDialog.init, ZenpageDialog);
</script>