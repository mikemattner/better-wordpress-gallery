<?php
/**
 * @package MM_Better_Gallery
 * @version 1.2.0
 */
/*
Plugin Name: Better Gallery Shortcode
Plugin URI: http://mikemattner.com
Description: This plugin uses more semantic markup for the gallery shortcode output. Modify plugin CSS on your own.
Author: Mike Mattner
Version: 1.2.0
Author URI: http://mikemattner.com/
*/

define('MM_PLUGIN_VER', '1.2.0');
define('MM_PLUGIN_NAME', 'MM_Better_Gallery');

class MM_Better_Gallery {
    static $instance;
    var $plugin_url;
    var $plugin_dir;
    var $options = null;
    protected $defaults = array(
            'include_css'   => 'true',
            'show_captions' => 'true',
            'file_link'     => 'false',
            'version'       => MM_PLUGIN_VER,
            'name'          => MM_PLUGIN_NAME
          );
  
    public function __construct() {
      self::$instance   = $this;
      $this->init();
      $this->plugin_dir = plugin_dir_path( __FILE__ );
      $this->plugin_url = plugin_dir_url( __FILE__ );
      $this->options = $this->mm_get_options();
    }

    public function init() {
      remove_shortcode( 'gallery' );                                                          // remove default gallery shortcode
      add_shortcode( 'gallery', array( $this,'mm_gallery_shortcode' ) );                      // replace gallery shortcode
      add_action( 'the_posts', array( $this,'mm_gallery_check' ) );                           // check for shortcode, if exists add gallery css
      add_filter( 'post_gallery', array( $this, 'new_gallery_shortcode_defaults' ), 10, 2 );  // if file_link true, set link="file" as default shortcode

      //admin page and options
      isset($_REQUEST['_wp_mm_bg_nonce']) ? add_action('admin_init',array($this,'mm_options_save') ) : null;
      add_action( 'admin_init', array($this,'mm_get_options') );                              // set default values on first run
      add_filter( 'plugin_action_links', array($this,'mm_plugin_action_links'), 10, 3 );      // add settings page to menu
      add_action( 'admin_menu', array($this,'gads_options_menu') );                           // options page
    }

    /**
    * Basic options, will add admin page for options
    *
    * updates option 'mm_gallery_options' as an array of options
    * @options include_css, show_captions, file_link, version,name
    */

    public function mm_get_options() {
      $options = get_option('mm_gallery_options');
      $current = $this->mm_current_version();

      // Test to see if options exist
      if( $options == FALSE || !$current) {  
        update_option('mm_gallery_options', $this->defaults);
        $options = $this->defaults;
      }
      return $options;
    }

    /**
    * Check if version is current
    * Won't have anything to check against for now
    */
    protected function mm_current_version() {
      $options = $this->options;
      if($options['version'] == MM_PLUGIN_VER)
        return true;
    }

    /*
    * Admin Options Save
    */
    public function mm_options_save() {
      $options = $this->options;

      //include_css,show_captions,file_link
      if(wp_verify_nonce($_REQUEST['_wp_mm_bg_nonce'],'mm_bg')) {
        if ( isset($_POST['submit']) ) {
          ( function_exists('current_user_can') && !current_user_can('manage_options') ) ? die(__('Cheatin&#8217; uh?', 'mm_custom')) : null;
                        
            $options['include_css']      = ( isset($_POST['mm-include_css'])    ? 'true' : 'false' );
            $options['show_captions']    = ( isset($_POST['mm-show_captions'])  ? 'true' : 'false' );
            $options['file_link']        = ( isset($_POST['mm-file_link'])      ? 'true' : 'false' );
            
            update_option('mm_gallery_options', $options);
        }
      }
    }

    public function mm_plugin_action_links($links, $file) {
      $plugin_file = basename(__FILE__);
      if (basename($file) == $plugin_file) {
        $settings_link = '<a href="options-general.php?page=mm-bg-options">'.__('Settings', 'mm_bg').'</a>';
        array_unshift($links, $settings_link);
      }
      return $links;
    }
  
    /*
    * Admin Options Page
    */
    public function mm_options_page() {   
      $tmp = $this->plugin_dir . '/inc/views/options-page.php';
     
      ob_start();
      include( $tmp );
      $output = ob_get_contents();
      ob_end_clean();
      echo $output;
    }
  
    /*
    * Add Options Page to Settings menu
    */
    public function gads_options_menu() {   
      if(function_exists('add_submenu_page')) {
        add_options_page(__('Better Gallery Settings', 'mm_bg'), __('Better Gallery Settings', 'mm_bg'), 'manage_options', 'mm-bg-options', array($this,'mm_options_page'));
      }
    }

    /**
    * Set gallery shortcode link option to file by default
    */
    public function new_gallery_shortcode_defaults( $output, $attr ) {
      $options = $this->options;
      if($options['file_link'] == 'true') {
        global $post;
        $attr = array(
          'link' => 'file'
        );
        return $output;
      }
    }

    /**
    * /\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/
    * Basic CSS 
    */ 
    public function mm_gallery_custom_css() {
      $options = $this->options;

      if($options['include_css'] == 'true') {
        wp_register_style( 'better-gallery-style', $this->plugin_url . 'assets/css/better-gallery.css', array(), '2013-2-04T15:38', 'all' );
        wp_enqueue_style( 'better-gallery-style' );
      }
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
}

$mm = new MM_Better_Gallery;

?>