<?php
/**
 * @package Accarousel
 * @version 1.0
 */
/*
Plugin Name: Accarousel
Plugin URI: http://rojait.com/plugins/accarousel/
Description: Accordion and Carousel both effects inside the plugin. This plugin allows you to use shortcode to display carousel post,page or full width page. Easily create carousel using shortcode.
Author: Mahmudul Isalm
Version: 1.0
Author URI: http://rojait.com
*/

/*  Copyright 2015  Mahmudul Islam  (email : info.rojait@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Adding Latest jQurey from Wordpress */
function accarousel_wp_latest_jquery() {
    wp_enqueue_script('jquery');
}
add_action('init','accarousel_wp_latest_jquery');

/* Some Set-up*/
define('accarousel_jquery_wp_enqueue', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/');

wp_enqueue_script('accarousel-main-js', accarousel_jquery_wp_enqueue.'js/jquery.cjAccarousel.min.js', array('jquery') );
wp_enqueue_style('accarousel-main-css', accarousel_jquery_wp_enqueue.'css/accarousel.css');


// Image Support
add_theme_support('post-thumbnails', array ('post', 'accarousel-items') );
add_image_size('accarousel-thumb', 189, 285); // For Thumbnail Image
add_image_size( 'detail-thumb', 960, 384 ); // For Large Image

// Custom Post
add_action('init','accarousel_custom_post');
function accarousel_custom_post() {
	register_post_type('accarousel-items', 
		array(
			'labels' => array(
				'name' => __('Accarousel Items'),
				'singular_name' => __('Accarousel Item'),
				'add_new_item' => __('Add New Accarousel Item')
			),
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-slides',
			'public' => true, 
			'supports' => array('thumbnail','title','editor','custom_fields'),
			'has_archive' => true, 
			'rewrite' => array('slug' => 'accarousel_item')
		)	 
	);
}
// Taxonomy
function accarousel_taxonomy () {
	register_taxonomy (
	'accarousel_cat', // The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces). )
	'accarousel-items', // post type name
	array (
		'hierarchical' => true, 
		'label' => 'Accarousel Category', 
		'query_var' => true, 
		'show_admin_column' => true,
		'rewrite' => array (
			'slug' => 'accarousel-category', // This controls the base slug that will display before each term. 
		 'with_front' => true // Don't display the category base before. 	 
		)
	)
);
}
add_action('init', 'accarousel_taxonomy');

/*Carousel Shortcode Support */
 function accarouosel_get_carousel() {
	$accarouosel= '<div class="wrap"><div id="accarousel"><ul>';
	$efs_query="post_type=accarousel-items&posts_per_page=-1";
	query_posts($efs_query);
	if (have_posts()) : while (have_posts()) : the_post();
		$thumb = get_the_post_thumbnail ($post->ID, 'accarousel-thumb');
		$detail_thumb = get_the_post_thumbnail ($post->ID, 'detail-thumb');
		$accarouosel.='<li>
                <a href="" class="stand">'.$thumb.'</a>

                <div class="detail-panel">'.$detail_thumb.'</div>
            </li>		
		';
		endwhile; endif; wp_reset_query();
		$accarouosel.='</ul></div></div>';
		return $accarouosel;
	}	
	/*Add the shortcode for the slider for use in editor ***/
	function get_accarouosel ($atts, $content=null){
		$accarouosel = accarouosel_get_carousel();
		return $accarouosel;
	}
add_shortcode ('accarousel','get_accarouosel');

// Options page start 
function add_accarousel_options_framwrork()  
{  
	add_menu_page('Accarousel Options', 'Accarousel Options', 'manage_options', 'accarousel-settings','accarousel_options_framwrork', plugins_url( '/images/icon.png',  __FILE__ ), 59 );  
}  
add_action('admin_menu', 'add_accarousel_options_framwrork');

// Default options values
$accarousel_options_framwrork = array(
	'groupOf' => '5',    /* Number of stands for visible group*/
	'scrollSpeed' => '1000', /* Carousel Speed */
	'ease' => 'swing', /* Use jQuery Easing Plug in for more easing effects */
	'flyOutGap' => '3',  /* Gap between expanded and other two flyouts */
	'nextprev' => 'true' /* set false to disable Next/Prev Nav */
);

if ( is_admin() ) : // Load only if we are viewing an admin page

function accarousel_register_settings() {
	// Register settings and call sanitation functions
	register_setting( 'accarousel_p_options', 'accarousel_options_framwrork', 'accarousel_validate_options' );
}

add_action( 'admin_init', 'accarousel_register_settings' );


// Store layouts views in array
$next_prev_mode = array(
	'nextprev_yes' => array(
		'value' => 'true',
		'label' => 'Enable Next/Prev Nav'
	),
	'nextprev_no' => array(
		'value' => 'false',
		'label' => 'Disable Next/Prev Nav'
	),
);

// Function to generate options page
function accarousel_options_framwrork() {
	global $accarousel_options_framwrork, $next_prev_mode;

	if ( ! isset( $_REQUEST['updated'] ) )
		$_REQUEST['updated'] = false; // This checks whether the form has just been submitted. ?>

	<div class="wrap">

	
	<h2> Accarousel Options Page </h2>

	<?php if ( false !== $_REQUEST['updated'] ) : ?>
	<div class="updated fade"><p><strong><?php _e( 'Options saved' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>

	<form method="post" action="options.php">

	<?php $settings = get_option( 'accarousel_options_framwrork', $accarousel_options_framwrork ); ?>
	
	<?php settings_fields( 'accarousel_p_options' );
	/* This function outputs some hidden fields required by the form,
	including a nonce, a unique number used to ensure the form has been submitted from the admin page
	and not somewhere else, very important for security */ ?>

	
	<table class="form-table"><!-- Grab a hot cup of coffee, yes we're using tables! -->

		<tr valign="top">
			<th scope="row"><label for="groupOf">Group Of</label></th>
			<td>
				<input id="groupOf" type="text" name="accarousel_options_framwrork[groupOf]" value="<?php echo stripslashes($settings['groupOf']); ?>" />
				<p class="description">Number of stands for visible group</p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="scrollSpeed">Scroll Speed</label></th>
			<td>
				<input id="scrollSpeed" type="text" name="accarousel_options_framwrork[scrollSpeed]" value="<?php echo stripslashes($settings['scrollSpeed']); ?>" /><p class="description">Carousel Speed</p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="ease">Ease</label></th>
			<td>
				<input id="ease" type="text" name="accarousel_options_framwrork[ease]" value="<?php echo stripslashes($settings['ease']); ?>" /><p class="description">Use jQuery Easing Plug in for more easing effects.</p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="flyOutGap">Speed</label></th>
			<td>
				<input id="flyOutGap" type="text" name="accarousel_options_framwrork[flyOutGap]" value="<?php echo stripslashes($settings['flyOutGap']); ?>" /><p class="description">
Gap between expanded and other two flyouts
				</p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="background_color">Enable/Disable Next/Prev Nav</label></th>
			<td>
				<?php foreach( $next_prev_mode as $activate ) : ?>
				<input type="radio" id="<?php echo $activate['value']; ?>" name="accarousel_options_framwrork[next_prev_mode]" value="<?php esc_attr_e( $activate['value'] ); ?>" <?php checked( $settings['next_prev_mode'], $activate['value'] ); ?> />
				<label for="<?php echo $activate['value']; ?>"><?php echo $activate['label']; ?></label><br />
				<?php endforeach; ?>
			</td>
		</tr>		

	</table>

	<p class="submit"><input type="submit" class="button-primary" value="Save Options" /></p>

	</form>

	</div>

	<?php
}

function accarousel_validate_options( $input ) {
	global $accarousel_options_framwrork, $next_prev_mode;

	$settings = get_option( 'accarousel_options_framwrork', $accarousel_options_framwrork );
	
	// We strip all tags from the text field, to avoid vulnerablilties like XSS

	$input['groupOf'] = wp_filter_post_kses( $input['groupOf'] );
	$input['scrollSpeed'] = wp_filter_post_kses( $input['scrollSpeed'] );
	$input['ease'] = wp_filter_post_kses( $input['ease'] );
	$input['scroll_speed'] = wp_filter_post_kses( $input['scroll_speed'] );
	
	// We select the previous value of the field, to restore it in case an invalid entry has been given
	$prev = $settings['layout_only'];
	// We verify if the given value exists in the layouts array
	if ( !array_key_exists( $input['layout_only'], $next_prev_mode ) )
		$input['layout_only'] = $prev;	

	return $input;
}
endif;  // EndIf is_admin()


function accarouosel_active() {?>

<?php global $accarousel_options_framwrork; $accarouosel_settings = get_option( 'accarousel_options_framwrork', $accarousel_options_framwrork ); ?>

<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery("#accarousel").cjAccarousel({
			groupOf: "<?php echo $accarouosel_settings['groupOf']; ?>",
			scrollSpeed: "<?php echo $accarouosel_settings['scrollSpeed']; ?>",
			ease: "<?php echo $accarouosel_settings['ease']; ?>",
			flyOutGap: "<?php echo $accarouosel_settings['flyOutGap']; ?>",
			nextPrev: <?php echo $accarouosel_settings['next_prev_mode']; ?>
		});           
	});	
</script>
<?php
}
add_action('wp_head', 'accarouosel_active');

?>