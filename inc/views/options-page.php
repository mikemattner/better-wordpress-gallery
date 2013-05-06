<?php 
/**
 * @package MM_Better_Gallery
 * @version 1.4.1
 */

 
//'include_css','show_captions','file_link','itemtag','icontag','captiontag','columns','size'
$options  = get_option('mm_gallery_options');

?>
        <?php if ( !empty($_POST) ) { ?>
			<div id="message" class="updated fade"><p><strong><?php _e('Options saved.', 'mm_bg') ?></strong></p></div>
		<?php } ?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e('Better WordPress Gallery', 'mm_bg'); ?></h2>
			<form action="" method="post" id="mm_bg-options">
				<h3><?php _e('Settings','mm_bg'); ?></h3>
				<table class="form-table">
					<tbody>
					    <tr>
							<th scope="row"><?php _e('Include CSS', 'mm_bg'); ?></th>
							<td><label><input name="mm-include_css" id="mm-include_css" value="true" type="checkbox" <?php if ( $options['include_css'] == 'true' ) echo ' checked="checked" '; ?> /> &mdash; <?php _e('Check if you want to include css provided with plugin.', 'include_css'); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><?php _e('Show Captions', 'mm_bg'); ?></th>
							<td><label><input name="mm-show_captions" id="mm-show_captions" value="true" type="checkbox" <?php if ( $options['show_captions'] == 'true' ) echo ' checked="checked" '; ?> /> &mdash; <?php _e('Check if you want to show captions with your gallery.', 'show_captions'); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><?php _e('Always Link to Attachment File', 'mm_bg'); ?></th>
							<td><label><input name="mm-file_link" id="mm-file_link" value="true" type="checkbox" <?php if ( $options['file_link'] == 'true' ) echo ' checked="checked" '; ?> /> &mdash; <?php _e('Check if you want link="file" to be a default attribute.', 'file_link'); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><label><?php _e('Item Tag', 'mm_bg'); ?></label></th>
							<td><input name="mm-itemtag" id="mm-itemtag" type="text" <?php echo ' value="'.$options['itemtag'].'" '; ?> /></td>
						</tr>
						<tr>
							<th scope="row"><label><?php _e('Icon Tag', 'mm_bg'); ?></label></th>
							<td><input name="mm-icontag" id="mm-icontag" type="text" <?php echo ' value="'.$options['icontag'].'" '; ?> /></td>
						</tr>
						<tr>
							<th scope="row"><label><?php _e('Caption Tag', 'mm_bg'); ?></label></th>
							<td><input name="mm-captiontag" id="mm-captiontag" type="text" <?php echo ' value="'.$options['captiontag'].'" '; ?> /></td>
						</tr>
						<tr>
							<th scope="row"><label><?php _e('Number of Columns', 'mm_bg'); ?></label></th>
							<td><input name="mm-columns" id="mm-columns" type="text" <?php echo ' value="'.$options['columns'].'" '; ?> /></td>
						</tr>
						<tr>
							<th scope="row"><label><?php _e('Thumbnail Size', 'mm_bg'); ?></label></th>
							<td><input name="mm-size" id="mm-size" type="text" <?php echo ' value="'.$options['size'].'" '; ?> /></td>
						</tr>
						<tr>
							<th scope="row"><label for="mm-css"><?php _e('Custom CSS', 'mm_bg'); ?></label></th>
							<td>
								<textarea style="background:#F9F9F9;font-family: Consolas,Monaco,monospace;font-size: 12px; outline: medium none; width:80%; height:400px;" id="mm-css" name="mm-css" cols="10" rows="8"><?php echo get_option('mm_gallery_css'); ?></textarea>
							</td>
						</tr>
					</tbody>
				</table>
				
				<p class="submit">
					<?php wp_nonce_field('mm_bg','_wp_mm_bg_nonce'); ?>
					<?php submit_button( __('Save Changes', 'mm_bg'), 'button-primary', 'submit', false ); ?>
				</p>
			</form>
			<p style="font-size: 75%; color: #999;">Version <?php echo $options['version']; ?></p>
		</div>