<?php
	if(!defined('AJAX_INIT_DONE'))
	{
		die('Permission denied');
	}
?><?php
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "config.php");
?>
<select class="input inputSearch" name="search_folder" id="search_folder">
	<?php 
	
					foreach(getFolderListing(CONFIG_SYS_ROOT_PATH) as $k=>$v)
					{
						?>
      <option value="<?php echo $v; ?>" ><?php echo shortenFileName($k, 30); ?></option>
      <?php 
					}
		
				?>            	
</select>