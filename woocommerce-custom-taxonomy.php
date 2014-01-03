<?php
/*
Plugin Name: WooCommerce Custom Product Taxonomy
Plugin URI: http://elmered.com
Description: Adds taxs taxonomy to productss
Version: 1.0
Author: Peter Elmered
Author URI: http://elmered.com
*/


if ( is_woocommerce_active() ) {

class pe_wc_product_custom_taxonomy
{
    var $taxonomies = array(
        'taxonomy',
        'test_taxonomy'
    );
    
    function __construct() {

        // Hook into the 'init' action
        add_action('init', array($this, 'init'));

        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_init', array($this, 'save_admin_settings'), 0);
    }
    
    
    function init() {

        foreach ( $this->taxonomies AS $taxonomy )
        {
            $this->register_taxonomy($taxonomy);
        }
    }
    
    function admin_init()
    {
        //add_settings_section( 'dt-wc-permalink', __( 'Product permalink base', 'woocommerce' ), 'woocommerce_permalink_settings', 'permalink' );

        
        foreach ( $this->taxonomies AS $taxonomy )
        {
            add_settings_field(
                'woocommerce_product_'.$taxonomy.'_slug',      	// id
                __( ucfirst($taxonomy).' base<br />(Custom taxonomy)', 'woocommerce' ), 	// setting title
                array($this, 'product_tax_slug_input'),  // display callback
                'permalink',                 				// settings page
                'optional',                  				// settings section
                $taxonomy   
            ); 
        }
        
    }
    
    function product_tax_slug_input( $tax_name ) {
        $permalinks = get_option( 'pe_wc_permalinks' );
	?>
	<input name="pe_wc_product_<?php echo $tax_name; ?>_slug" type="text" class="regular-text code" value="<?php if ( isset( $permalinks[$tax_name.'_tax_base'] ) ) echo esc_attr( $permalinks[$tax_name.'_tax_base'] ); ?>" placeholder="<?php echo _x('product-'.$tax_name, 'slug', 'pe_wc_product_custom_taxonomy') ?>" />
	<?php
    }
    
    function save_admin_settings()
    {
        if ( ! is_admin() )
            return;
        
        $permalinks = array();
        
        foreach ( $this->taxonomies AS $taxonomy )
        {
            if ( isset( $_POST['pe_wc_product_'.$taxonomy.'_slug'] ) )
            {
                $permalinks[$taxonomy.'_tax_base'] = untrailingslashit(woocommerce_clean($_POST['pe_wc_product_'.$taxonomy.'_slug']));
            }
        }
        
        if(!empty($permalinks))
        {
            update_option( 'pe_wc_permalinks', $permalinks );

            flush_rewrite_rules();   
        }
    }

    // Register Custom Taxonomy
    function register_taxonomy($taxonomy_name)
    {
        $permalinks = get_option( 'pe_wc_permalinks' );
        
        $product_tax_slug 	= empty( $permalinks[$taxonomy_name.'_tax_base'] ) ? 'product-'.$taxonomy_name : $permalinks[$taxonomy_name.'_tax_base'];
        
        //Admin labels
        $labels = array(
            'name' => _x( ucfirst($taxonomy_name).'s', 'Taxonomy General Name', 'pe_wc_product_custom_taxonomy'),
            'singular_name' => _x(ucfirst($taxonomy_name), 'Taxonomy Singular Name', 'pe_wc_product_custom_taxonomy'),
            'menu_name' => __(ucfirst($taxonomy_name).'s', 'pe_wc_product_custom_taxonomy'),
            'all_items' => __('All '.ucfirst($taxonomy_name).'s', 'pe_wc_product_custom_taxonomy'),
            'parent_item' => __('Parent '.ucfirst($taxonomy_name), 'pe_wc_product_custom_taxonomy'),
            'parent_item_colon' => __('Parent '.ucfirst($taxonomy_name).'s'.':', 'pe_wc_product_custom_taxonomy'),
            'new_item_name' => __('New '.ucfirst($taxonomy_name).' Name', 'pe_wc_product_custom_taxonomy'),
            'add_new_item' => __('Add New '.ucfirst($taxonomy_name), 'pe_wc_product_custom_taxonomy'),
            'edit_item' => __('Edit '.ucfirst($taxonomy_name), 'pe_wc_product_custom_taxonomy'),
            'update_item' => __('Update '.ucfirst($taxonomy_name), 'pe_wc_product_custom_taxonomy'),
            'separate_items_with_commas' => __('Separate '.$taxonomy_name.' with commas', 'pe_wc_product_custom_taxonomy'),
            'search_items' => __('Search '.$taxonomy_name, 'pe_wc_product_custom_taxonomy'),
            'add_or_remove_items' => __('Add or remove '.$taxonomy_name, 'pe_wc_product_custom_taxonomy'),
            'choose_from_most_used' => __('Choose from the most used '-$taxonomy_name, 'pe_wc_product_custom_taxonomy'),
        );

        
        //Taxonomy settings
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'query_var' => true,
            'rewrite' => array(
                'slug' => $product_tax_slug,
                'with_front' => false,
                'hierarchical' => true,
            )
        );
        register_taxonomy($taxonomy_name, 'product', $args);
    }

}

$_GLOBALS['pe_wc_product_custom_taxonomy'] = new pe_wc_product_custom_taxonomy();

}
