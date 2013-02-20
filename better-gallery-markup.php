<?php
/**
 * @package MM_Better_Gallery
 * @version 1.0.0
 */
/*
Plugin Name: Better Gallery Shortcode
Plugin URI: http://mikemattner.com
Description: This plugin uses more semantic markup for the gallery shortcode output. Modify plugin CSS on your own.
Author: Mike Mattner
Version: 1.0.0
Author URI: http://mikemattner.com/
*/

class MM_Better_Gallery {
    static $instance;
    var $plugin_url;
	
    public function __construct() {
        self::$instance   = $this;
        $this->init();
        $this->plugin_url = plugin_dir_url( __FILE__ );
    }

    public function init() {
		remove_shortcode( 'gallery' );                                    // remove default gallery shortcode
		add_shortcode( 'gallery', array($this,'mm_gallery_shortcode') );  // replace gallery shortcode
		add_action( 'the_posts', array($this,'mm_gallery_check') );       // check for shortcode, if exists add gallery css		
    }

	/**
 	 * Modified Gallery shortcode. Changes markup output.
 	 *
 	 * @param array $attr Attributes of the shortcode.
 	 * @return string HTML content to display gallery.
 	 */

	public function mm_gallery_shortcode($attr) {
  		$post = get_post();

  		static $instance = 0;
  		$instance++;

  		if ( ! empty( $attr['ids'] ) ) {
    		// 'ids' is explicitly ordered, unless you specify otherwise.
    		if ( empty( $attr['orderby'] ) )
      		$attr['orderby'] = 'post__in';
    		$attr['include'] = $attr['ids'];
  		}

  		// Allow plugins/themes to override the default gallery template.
  		$output = apply_filters('post_gallery', '', $attr);
  		if ( $output != '' )
    		return $output;

  		// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
  		if ( isset( $attr['orderby'] ) ) {
    		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
    		if ( !$attr['orderby'] )
      		unset( $attr['orderby'] );
  		}

  		extract(shortcode_atts(array(
    		'order'      => 'ASC',
    		'orderby'    => 'menu_order ID',
    		'id'         => $post->ID,
    		'itemtag'    => 'figure',
    		'icontag'    => '',
    		'captiontag' => 'figcaption',
    		'columns'    => 3,
    		'size'       => 'thumbnail',
    		'include'    => '',
    		'exclude'    => ''
  		), $attr));

  		$id = intval($id);
  		if ( 'RAND' == $order )
    		$orderby = 'none';

  		if ( !empty($include) ) {
    		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

    		$attachments = array();
    		foreach ( $_attachments as $key => $val ) {
      			$attachments[$val->ID] = $_attachments[$key];
    		}
  		} elseif ( !empty($exclude) ) {
    		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
  		} else {
    		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
  		}

  		if ( empty($attachments) )
    		return '';

  		if ( is_feed() ) {
    		$output = "\n";
    		foreach ( $attachments as $att_id => $attachment )
      			$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
    		return $output;
  		}

  		$itemtag = tag_escape($itemtag);
  		$captiontag = tag_escape($captiontag);
  		$icontag = tag_escape($icontag);
  		$valid_tags = wp_kses_allowed_html( 'post' );
  		if ( ! isset( $valid_tags[ $itemtag ] ) )
    		$itemtag = 'figure';
  		if ( ! isset( $valid_tags[ $captiontag ] ) )
    		$captiontag = 'figcaption';
  		if ( ! isset( $valid_tags[ $icontag ] ) )
    		$icontag = '';

  		$columns = intval($columns);
  		$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
  		$float = is_rtl() ? 'right' : 'left';

  		$selector = "gallery-{$instance}";

  		$gallery_style = $gallery_div = '';

  		$gallery_div = "<section id='$selector' class='gallery gal-col-{$columns} clearfix'>";
  		$output = apply_filters( 'gallery_style', $gallery_style . "\n\t\t" . $gallery_div );

  		$i = 0;
  		foreach ( $attachments as $id => $attachment ) {
    		$link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);
    		$link = str_replace( '<a href', '<a rel="'. $selector .'" href', $link );

    		$output .= "
    		<{$itemtag} class='gallery-item'>";
    		$output .= "
        		$link
      		";
    		if ( $captiontag && trim($attachment->post_excerpt) ) {
      			$output .= "
        			<{$captiontag} class='wp-caption-text gallery-caption'>
        			" . wptexturize($attachment->post_excerpt) . "
        			</{$captiontag}>";
    		}
    		$output .= "</{$itemtag}>";
  		}

  		$output .= "
    		</section>\n";

  		return $output;
	}
    
    /* Modified Version of check_for_shortcodes
     * http://pippinsplugins.com/load-scripts-if-post-has-short-code/
     *
     * @param $posts
     */
    public function mm_gallery_check($posts) {
    	if ( empty($posts) )
        	return $posts;
 
    	// false because we have to search through the posts first
    	$found = false;
 
    	// search through each post
    	foreach ($posts as $post) {
        	if ( stripos($post->post_content, '[gallery') )
            	$found = true;
            	break;
        }
 
    	if ($found){
        	$this->mm_gallery_custom_css();
    	}
    	return $posts;
	}

	/************* Basic CSS *****************/ 
	public function mm_gallery_custom_css() {
		wp_register_style( 'better-gallery-style', $this->plugin_url . 'assets/css/better-gallery.css', array(), '2013-2-04T15:38', 'all' );
        wp_enqueue_style( 'better-gallery-style' );

    }
}

$mm = new MM_Better_Gallery;

?>