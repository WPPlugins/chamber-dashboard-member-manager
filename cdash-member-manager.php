<?php

/*

Plugin Name: Chamber Dashboard Member Manager
Plugin URI: http://chamberdashboard.com
Description: Manage the membership levels and payments for your chamber of commerce or other membership based organization
Version: 2.1.9
Author: Morgan Kay
Author URI: http://wpalchemists.com
Text Domain: cdashmm
*/

/*  Copyright 2015 Morgan Kay and Chamber Dashboard (email : info@chamberdashboard.com)
    This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public  License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:                                   
// ------------------------------------------------------------------------

function cdashmm_requires_wordpress_version() {
    global $wp_version;
    $plugin = plugin_basename( __FILE__ );
    $plugin_data = get_plugin_data( __FILE__, false );

    if ( version_compare($wp_version, "4.2", "<" ) ) {
        if( is_plugin_active($plugin) ) {
            deactivate_plugins( $plugin );
            wp_die( "'".$plugin_data['Name']."' requires WordPress 4.2 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
        }
    }
}

add_action( 'admin_init', 'cdashmm_requires_wordpress_version' );

// ------------------------------------------------------------------------
// REQUIRE CHAMBER DASHBOARD BUSINESS DIRECTORY   
// ------------------------------------------------------------------------

add_action( 'admin_init', 'cdashmm_require_business_directory' );
function cdashmm_require_business_directory() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !function_exists( 'cdash_requires_wordpress_version' ) ) {
        add_action( 'admin_notices', 'cdashmm_business_directory_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) ); 
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function cdashmm_business_directory_notice(){
    ?><div class="error"><p><?php _e('Sorry, but the Chamber Dashboard Member Manager requires the <a href="https://wordpress.org/plugins/chamber-dashboard-business-directory/" target="_blank">Chamber Dashboard Business Directory</a> to be installed and active.', 'cdashmm' ); ?></p></div>
<?php
}
// ------------------------------------------------------------------------

// REGISTER HOOKS & CALLBACK FUNCTIONS:

// ------------------------------------------------------------------------



// Set-up Action and Filter Hooks

register_activation_hook(__FILE__, 'cdashmm_add_defaults');

register_uninstall_hook(__FILE__, 'cdashmm_delete_plugin_options');

// add_action('admin_init', 'cdashmm_init' );

add_action('admin_menu', 'cdashmm_add_options_page');

add_filter( 'plugin_action_links', 'cdashmm_plugin_action_links', 10, 2 );



// Require options stuff

require_once( plugin_dir_path( __FILE__ ) . 'options.php' );

// Require views

require_once( plugin_dir_path( __FILE__ ) . 'views.php' );

// Require PayPal handler

require_once( plugin_dir_path( __FILE__ ) . 'paypal-ipn.php' );

// Require payment report

require_once( plugin_dir_path( __FILE__ ) . 'payment-report.php' );



// Initialize language so it can be translated

function cdashmm_language_init() {

  load_plugin_textdomain( 'cdashmm', false, 'chamber-dashboard-member-manager/languages' );

}

add_action('init', 'cdashmm_language_init');



define( 'CDASHMM_STATUS', 'installed' );



// ------------------------------------------------------------------------

// FILTER SO WE CAN SEARCH BY TITLE

// http://wordpress.stackexchange.com/questions/18703/wp-query-with-post-title-like-something

// ------------------------------------------------------------------------



function cdashmm_search_by_title( $where, &$wp_query ) {

    global $wpdb;

    if ( $post_title_like = $wp_query->get( 'post_title_like' ) ) {

        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'' . esc_sql( wpdb::esc_like( $post_title_like ) ) . '%\'';

    }

    return $where;

}



add_filter( 'posts_where', 'cdashmm_search_by_title', 10, 2 );



// ------------------------------------------------------------------------

// SET UP CUSTOM POST TYPES AND TAXONOMIES

// ------------------------------------------------------------------------



// Register Custom Taxonomy - Membership Status

function cdashmm_register_tax_membership_status() {



    $labels = array(

        'name'                       => _x( 'Membership Statuses', 'Taxonomy General Name', 'cdashmm' ),

        'singular_name'              => _x( 'Membership Status', 'Taxonomy Singular Name', 'cdashmm' ),

        'menu_name'                  => __( 'Membership Statuses', 'cdashmm' ),

        'all_items'                  => __( 'All Membership Statuses', 'cdashmm' ),

        'parent_item'                => __( 'Parent Membership Status', 'cdashmm' ),

        'parent_item_colon'          => __( 'Parent Membership Status:', 'cdashmm' ),

        'new_item_name'              => __( 'New Membership Status Name', 'cdashmm' ),

        'add_new_item'               => __( 'Add New Membership Status', 'cdashmm' ),

        'edit_item'                  => __( 'Edit Membership Status', 'cdashmm' ),

        'update_item'                => __( 'Update Membership Status', 'cdashmm' ),

        'separate_items_with_commas' => __( 'Separate Membership Statuses with commas', 'cdashmm' ),

        'search_items'               => __( 'Search Membership Statuses', 'cdashmm' ),

        'add_or_remove_items'        => __( 'Add or remove Membership Statuses', 'cdashmm' ),

        'choose_from_most_used'      => __( 'Choose from the most used Membership Statuses', 'cdashmm' ),

        'not_found'                  => __( 'Not Found', 'cdashmm' ),

    );

    $args = array(

        'labels'                     => $labels,

        'hierarchical'               => true,

        'public'                     => true,

        'show_ui'                    => true,

        'show_admin_column'          => true,

        'show_in_nav_menus'          => false,

        'show_tagcloud'              => false,

    );

    register_taxonomy( 'membership_status', array( 'business' ), $args );



    // Create default statuses

    wp_insert_term(

        'Current', // the term 

        'membership_status', // the taxonomy

        array(

            'description'=> __( 'Membership is current', 'cdashmm' ),

            'slug' => 'current',

        )

    );



    wp_insert_term(

        'Lapsed', // the term 

        'membership_status', // the taxonomy

        array(

            'description'=> __( 'Membership has lapsed due to lack of payment', 'cdashmm' ),

            'slug' => 'lapsed',

        )

    );



}

add_action( 'init', 'cdashmm_register_tax_membership_status', 0 );









// Register Custom Taxonomy - Invoice Status

function cdashmm_register_tax_invoice_status() {



    $labels = array(

        'name'                       => _x( 'Invoice Statuses', 'Taxonomy General Name', 'cdashmm' ),

        'singular_name'              => _x( 'Invoice Status', 'Taxonomy Singular Name', 'cdashmm' ),

        'menu_name'                  => __( 'Invoice Statuses', 'cdashmm' ),

        'all_items'                  => __( 'All Invoice Statuses', 'cdashmm' ),

        'parent_item'                => __( 'Parent Invoice Status', 'cdashmm' ),

        'parent_item_colon'          => __( 'Parent Invoice Status:', 'cdashmm' ),

        'new_item_name'              => __( 'New Invoice Status Name', 'cdashmm' ),

        'add_new_item'               => __( 'Add New Invoice Status', 'cdashmm' ),

        'edit_item'                  => __( 'Edit Invoice Status', 'cdashmm' ),

        'update_item'                => __( 'Update Invoice Status', 'cdashmm' ),

        'separate_items_with_commas' => __( 'Separate Invoice Statuses with commas', 'cdashmm' ),

        'search_items'               => __( 'Search Invoice Statuses', 'cdashmm' ),

        'add_or_remove_items'        => __( 'Add or remove Invoice Statuses', 'cdashmm' ),

        'choose_from_most_used'      => __( 'Choose from the most used Invoice Statuses', 'cdashmm' ),

        'not_found'                  => __( 'Not Found', 'cdashmm' ),

    );

    $args = array(

        'labels'                     => $labels,

        'hierarchical'               => true,

        'public'                     => true,

        'show_ui'                    => true,

        'show_admin_column'          => true,

        'show_in_nav_menus'          => false,

        'show_tagcloud'              => false,

    );

    register_taxonomy( 'invoice_status', array( 'invoice' ), $args );



    // Create default statuses

    wp_insert_term(

        'Paid', // the term 

        'invoice_status', // the taxonomy

        array(

            'description'=> __( 'Invoice has been paid', 'cdashmm' ),

            'slug' => 'paid',

        )

    );



    wp_insert_term(

        'Pending', // the term 

        'invoice_status', // the taxonomy

        array(

            'description'=> __( 'Payment initiated, but not completed', 'cdashmm' ),

            'slug' => 'pending',

        )

    );



    wp_insert_term(

        'Overdue', // the term 

        'invoice_status', // the taxonomy

        array(

            'description'=> __( 'Invoice is overdue', 'cdashmm' ),

            'slug' => 'overdue',

        )

    );



    wp_insert_term(

        'Open', // the term 

        'invoice_status', // the taxonomy

        array(

            'description'=> __( 'Invoice is open', 'cdashmm' ),

            'slug' => 'open',

        )

    );



    wp_insert_term(

        'Unpaid', // the term 

        'invoice_status', // the taxonomy

        array(

            'description'=> __( 'Invoice is unpaid.  Invoices will automatically be marked unpaid after 4 months', 'cdashmm' ),

            'slug' => 'unpaid',

        )

    );



}

add_action( 'init', 'cdashmm_register_tax_invoice_status', 0 );







// Register Custom Post Type

function cdashmm_register_cpt_invoice() {



    $labels = array(

        'name'                => _x( 'Invoices', 'Post Type General Name', 'cdashmm' ),

        'singular_name'       => _x( 'Invoice', 'Post Type Singular Name', 'cdashmm' ),

        'menu_name'           => __( 'Invoices', 'cdashmm' ),

        'parent_item_colon'   => __( 'Parent Invoice:', 'cdashmm' ),

        'all_items'           => __( 'All Invoices', 'cdashmm' ),

        'view_item'           => __( 'View Invoice', 'cdashmm' ),

        'add_new_item'        => __( 'Add New Invoice', 'cdashmm' ),

        'add_new'             => __( 'Add New', 'cdashmm' ),

        'edit_item'           => __( 'Edit Invoice', 'cdashmm' ),

        'update_item'         => __( 'Update Invoice', 'cdashmm' ),

        'search_items'        => __( 'Search Invoice', 'cdashmm' ),

        'not_found'           => __( 'Not found', 'cdashmm' ),

        'not_found_in_trash'  => __( 'Not found in Trash', 'cdashmm' ),

    );

    $args = array(

        'label'               => __( 'invoice', 'cdashmm' ),

        'description'         => __( 'Invoices for membership dues', 'cdashmm' ),

        'labels'              => $labels,

        'supports'            => array( 'title', 'editor', ),

        'taxonomies'          => array( 'invoice_status' ),

        'hierarchical'        => false,

        'public'              => true,

        'show_ui'             => true,

        'show_in_menu'        => true,

        'show_in_nav_menus'   => false,

        'show_in_admin_bar'   => true,

        'menu_position'       => 5,

        'menu_icon'           => 'dashicons-cart',

        'can_export'          => true,

        'has_archive'         => false,

        'exclude_from_search' => true,

        'publicly_queryable'  => true,

        'capability_type'     => 'page',

    );

    register_post_type( 'invoice', $args );



}

add_action( 'init', 'cdashmm_register_cpt_invoice', 0 );



// tell robots not to index invoices

function cdashmm_hide_invoices_from_robots() {

    if( is_singular( 'invoice' ) ) {

        $output= '<meta name="robots" content="noindex,follow" />';

        echo $output;

    }

}

add_action( 'wp_head','cdashmm_hide_invoices_from_robots' );

function cdashmm_posts_link_next_class($format){
     $format = str_replace('href=', 'class="next clean-gray" href=', $format);
     return $format;
}
add_filter('next_post_link', 'cdashmm_posts_link_next_class');

/*function posts_link_prev_class($format) {
     $format = str_replace('href=', 'class="prev clean-gray" href=', $format);
     return $format;
}
add_filter('previous_post_link', 'posts_link_prev_class');
*/




// add unique string to invoice URLs

// http://stackoverflow.com/questions/4518527/customize-the-auto-generation-of-post-slug-in-wordpress

function cdashmm_check_for_new_invoice( $post ) {

    add_post_meta( $post, 'url_lock', 'locked', true );

}

add_action( 'publish_invoice', 'cdashmm_check_for_new_invoice', 10, 2 );



function cdashmm_obfuscate_invoice_slug( $slug, $post_ID, $post_status, $post_type ) {

    $lock = get_post_meta( $post_ID, 'url_lock', true );

    if ( 'invoice' == $post_type && 'locked' != $lock ) {

        $slug = md5( time() + rand() );

    }

    return $slug;

}

add_filter( 'wp_unique_post_slug', 'cdashmm_obfuscate_invoice_slug', 10, 4 );









// ------------------------------------------------------------------------

// Connect Invoices to Businesses

// https://github.com/scribu/wp-posts-to-posts/blob/master/posts-to-posts.php

// ------------------------------------------------------------------------



if( defined( 'CDASH_PATH' ) ) {

    // Create the connection between businesses and invoices

    function cdashmm_businesses_and_invoices() {

        p2p_register_connection_type( array(

            'name' => 'invoices_to_businesses',

            'from' => 'invoice',

            'to' => 'business',

            'cardinality' => 'many-to-one',

            'admin_column' => 'from',

            'to_query_vars' => array('post_status' => 'any')

        ) );

    }

    add_action( 'p2p_init', 'cdashmm_businesses_and_invoices' );

}



// ------------------------------------------------------------------------

// ADD CUSTOM META BOXES

// ------------------------------------------------------------------------



if( class_exists( 'WPAlchemy_MetaBox' ) ) {

    // Create metabox for businesses to display next payment due date

    $membership_metabox = new WPAlchemy_MetaBox(array

    (

        'id' => 'membership_renewal',

        'title' => 'Membership Renewal',

        'types' => array('business'),

        'template' => plugin_dir_path( __FILE__ ) . '/includes/membership_renewal.php',

        'mode' => WPALCHEMY_MODE_EXTRACT,

        'prefix' => '_cdashmm_',

        'context' => 'side',

        'priority' => 'high'

    ));



    // Create metabox for invoices

    $invoice_metabox = new WPAlchemy_MetaBox(array

    (

        'id' => 'invoice_meta',

        'title' => 'Invoice Details',

        'types' => array('invoice'),

        'template' => plugin_dir_path( __FILE__ ) . '/includes/invoice_meta.php',

        'mode' => WPALCHEMY_MODE_EXTRACT,

        'prefix' => '_cdashmm_',

        'context' => 'normal',

        'priority' => 'high'

    ));



    // Create metabox for invoice emails

    $notification_metabox = new WPAlchemy_MetaBox(array

    (

        'id' => 'notification_meta',

        'title' => 'Email Invoice',

        'types' => array('invoice'),

        'template' => plugin_dir_path( __FILE__ ) . '/includes/invoice_notification.php',

        'mode' => WPALCHEMY_MODE_EXTRACT,

        'prefix' => '_cdashmm_',

        'context' => 'side',

        'priority' => 'default'

    ));

}





// Enqueue JS for invoice metabox

function cdashmm_invoice_script_enqueue($hook) {

    global $post;  



    if ( 'post-new.php' == $hook || 'post.php' == $hook || 'invoice_page_payment-report' == $hook ) { 



        if ( ( isset( $post ) && ( 'invoice' === $post->post_type || 'business' === $post->post_type ) ) || 'invoice_page_payment-report' == $hook ) {       

            wp_enqueue_script( 'invoice-meta', plugin_dir_url(__FILE__) . 'js/invoices.js', array( 'jquery' ) );

            wp_localize_script( 'invoice-meta', 'invoiceajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ); 



            global $wp_locale;



            wp_register_script(

                'cdashmm-datetimepicker',

                plugin_dir_url(__FILE__) . 'js/jquery-timepicker-addon/jquery-ui-timepicker-addon.min.js',

                array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker',)

            );



            wp_enqueue_script('cdashmm-datetimepicker');

            

            $lang = str_replace('_', '-', get_locale());

            $lang_exp = explode('-', $lang);



            if(file_exists(plugin_dir_url(__FILE__) . 'js/jquery-timepicker-addon/i18n/jquery-ui-timepicker-'.$lang.'.js'))

                $lang_path = plugin_dir_url(__FILE__) . 'js/jquery-timepicker-addon/i18n/jquery-ui-timepicker-'.$lang.'.js';

            elseif(file_exists(plugin_dir_url(__FILE__) . 'js/jquery-timepicker-addon/i18n/jquery-ui-timepicker-'.$lang_exp[0].'.js'))

                $lang_path = plugin_dir_url(__FILE__) . 'js/jquery-timepicker-addon/i18n/jquery-ui-timepicker-'.$lang_exp[0].'.js';

            

            if(isset($lang_path))

            {

                wp_register_script(

                    'cdashmm-datetimepicker-localization',

                    $lang_path,

                    array('jquery', 'cdashmm-datetimepicker')

                );



                wp_enqueue_script('cdashmm-datetimepicker-localization');

            }



            wp_register_style(

                'cdashmm-datetimepicker',

                plugin_dir_url(__FILE__) . 'js/jquery-timepicker-addon/jquery-ui-timepicker-addon.min.css'

            );



            wp_enqueue_style('cdashmm-datetimepicker');

 

        }  

    } 

    

}

add_action( 'admin_enqueue_scripts', 'cdashmm_invoice_script_enqueue' );



function cdashmm_update_membership_price() {

 

    $levelid = $_POST['level_id'];

    $cost = get_tax_meta( $levelid, 'cost' );

    $results = $cost;



    die($results);

}

add_action( 'wp_ajax_cdashmm_update_membership_price', 'cdashmm_update_membership_price' );



function cdashmm_update_tax_field() {

    $options = get_option( 'cdashmm_options' );

 

    $levelid = $_POST['level_id'];

    $donation = $_POST['donation'];

    $processing_fee = $_POST['processing_fee'];

    $cost = get_tax_meta( $levelid, 'cost' );

    $sub_total = $cost + $donation + $processing_fee;

    

    if( isset( $options['charge_tax'] ) ) {

	    if( isset( $options['tax_rate'] ) ) {

		    $tax = round( ( ( $options['tax_rate'] / 100 ) * $sub_total ), 2 );

            //$tax = round( ( ( $options['tax_rate'] / 100 ) ), 2 );

		} else {

		    $tax = 0;

	      }

    }

       

    $results = $tax;



    die($results);

}

add_action( 'wp_ajax_cdashmm_update_tax_field', 'cdashmm_update_tax_field' );





// When you create an invoice, automatically generate the invoice number



function cdashmm_insert_invoice_id( $post_id ) {

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { // don't do this in quick edit

        return;

    }



    $invoicefields = array( 

        '_cdashmm_invoice_number',

        '_cdashmm_amount', 

        '_cdashmm_duedate', 

        '_cdashmm_item_membershiplevel', 

        '_cdashmm_item_membershipamt',

        '_cdashmm_item_donation', 

        '_cdashmm_paidamt', 

        '_cdashmm_paiddate',

        '_cdashmm_paymethod',

        '_cdashmm_transaction' 

        );

    $str = $invoicefields;

    update_post_meta( $post_id, 'invoice_meta_fields', $str );



    $invoice_id = cdashmm_calculate_invoice_number();

     

    // update the individual fields

    update_post_meta( $post_id, '_cdashmm_invoice_number', $invoice_id );



}

add_action( 'save_post_invoice', 'cdashmm_insert_invoice_id' );





// If no other invoice status has been selected, invoice will default to 'open'

// http://wordpress.mfields.org/2010/set-default-terms-for-your-custom-taxonomies-in-wordpress-3-0/

function cdashmm_default_invoice_status( $post_id, $post ) {

    if ( 'publish' === $post->post_status ) {

        $defaults = array(

            'invoice_status' => array( 'open' ),

            );

        $taxonomies = get_object_taxonomies( $post->post_type );

        foreach ( (array) $taxonomies as $taxonomy ) {

            $terms = wp_get_post_terms( $post_id, $taxonomy );

            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {

                wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );

            }

        }

    }

}

add_action( 'save_post', 'cdashmm_default_invoice_status', 100, 2 );



// ------------------------------------------------------------------------

// ADD COLUMNS TO INVOICES OVERVIEW PAGE

// ------------------------------------------------------------------------



// thanks to https://github.com/bamadesigner/manage-wordpress-posts-using-bulk-edit-and-quick-edit/blob/master/manage_wordpress_posts_using_bulk_edit_and_quick_edit.php

add_filter('manage_invoice_posts_columns', 'cdashmm_invoices_overview_columns_headers', 10);

function cdashmm_invoices_overview_columns_headers($defaults) {

    $defaults['invoice_number'] = __( 'Invoice #', 'cdashmm' );

    $defaults['invoice_amount'] = __( 'Amount', 'cdashmm' );

    $defaults['due_date'] = __( 'Due Date', 'cdashmm' );

    return $defaults;

}



add_action('manage_invoice_posts_custom_column', 'cdashmm_invoices_overview_columns', 10, 2);

function cdashmm_invoices_overview_columns($column_name, $post_ID) {

    global $invoice_metabox;

    $invoicemeta = $invoice_metabox->the_meta();

    if ($column_name == 'invoice_number') {

        $invoice_number = '';

        if( isset( $invoicemeta['invoice_number'] ) ) {

            $invoice_number = $invoicemeta['invoice_number'];

        }

        echo $invoice_number;

    }    



    if ($column_name == 'invoice_amount') {

        $invoice_amount = '';

        if( isset( $invoicemeta['amount'] ) ) {

            $invoice_amount = $invoicemeta['amount'];

        }

        echo $invoice_amount;

    }  



    if ($column_name == 'due_date') {

        $due_date = '';

        if( isset( $invoicemeta['duedate'] ) ) {

            $due_date = $invoicemeta['duedate'];

        }

        echo $due_date;

    }  

}



add_filter( 'manage_edit-invoice_sortable_columns', 'cdashmm_make_invoice_columns_sortable' );

function cdashmm_make_invoice_columns_sortable( $sortable_columns ) {



    $sortable_columns[ 'due_date' ] = 'due_date';

    $sortable_columns[ 'invoice_amount' ] = 'invoice_amount';



    return $sortable_columns;

}



add_action( 'pre_get_posts', 'cdashmm_sort_invoice_columns', 1 );

function cdashmm_sort_invoice_columns( $query ) {



    if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {

        switch( $orderby ) {

            // If we're ordering by 'film_rating'

            case 'invoice_amount':

                $query->set( 'meta_key', '_cdashmm_amount' );

                $query->set( 'orderby', 'meta_value_num' );

                break;

        }

    }

}



add_filter( 'posts_clauses', 'cdashmm_sort_complicated_columns', 1, 2 );

function cdashmm_sort_complicated_columns( $pieces, $query ) {

    global $wpdb;



    if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {

        $order = strtoupper( $query->get( 'order' ) );

        if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) )

            $order = 'ASC';



        switch( $orderby ) {

            case 'due_date':

            $pieces[ 'join' ] .= " LEFT JOIN $wpdb->postmeta wp_rd ON wp_rd.post_id = {$wpdb->posts}.ID AND wp_rd.meta_key = '_cdashmm_duedate'";

            $pieces[ 'orderby' ] = "STR_TO_DATE( wp_rd.meta_value,'%Y/%m/%d' ) $order, " . $pieces[ 'orderby' ];

            break;

        }

    }

    return $pieces;

}





// ------------------------------------------------------------------------

// ADD CUSTOM META DATA TO TAXONOMIES - http://en.bainternet.info/wordpress-taxonomies-extra-fields-the-easy-way/

// ------------------------------------------------------------------------



if( function_exists( 'cdash_requires_wordpress_version' ) ) {

    // configure custom fields

    $config = array(

       'id' => 'member_level_meta',

       'title' => 'Membership Level Details',

       'pages' => array('membership_level'),

       'context' => 'normal',

       'fields' => array(),

       'local_images' => true,

       'use_with_theme' => false

    );



    $member_level_meta = new Tax_Meta_Class($config);

    $member_level_meta->addWysiwyg('perks',array('name'=> 'Membership Level Perks'));

    $member_level_meta->addText('cost',array('name'=> 'Membership Level Cost (number only, no currency symbol'));

    $member_level_meta->Finish();

} else {

    cdashmm_business_directory_notice();

}



// ------------------------------------------------------------------------

// MAKE MEMBERSHIP LEVEL SORTABLE BY PRIORITY

// largely borrowed from https://wordpress.org/plugins/custom-taxonomy-order-ne/

// ------------------------------------------------------------------------



function cdashmm_membership_level_menu() {

    add_submenu_page( '/chamber-dashboard-business-directory/options.php', __('Rank Membership Levels', 'cdashmm'), __('Rank Membership Levels', 'cdashmm'), 'manage_options', 'cdashmm_levels', 'cdashmm_rank_membership_levels' );

}

add_action('admin_menu', 'cdashmm_membership_level_menu');



function cdashmm_sort_membership_level_css() {

    if ( isset($_GET['page']) ) {

        $pos_page = $_GET['page'];

        $pos_args = 'cdashmm_levels';

        $pos = strpos($pos_page,$pos_args);

        if ( $pos === false ) {} else {

            wp_enqueue_style('cdashmm', plugins_url('css/reorder.css', __FILE__), 'screen');

        }

    }

}

add_action('admin_print_styles', 'cdashmm_sort_membership_level_css');



function cdashmm_sort_membership_level_js() {

    if ( isset($_GET['page']) ) {

        $pos_page = $_GET['page'];

        $pos_args = 'cdashmm_levels';

        $pos = strpos($pos_page,$pos_args);

        if ( $pos === false ) {} else {

            wp_enqueue_script('jquery');

            wp_enqueue_script('jquery-ui-core');

            wp_enqueue_script('jquery-ui-sortable');

        }

    }

}

add_action('admin_print_scripts', 'cdashmm_sort_membership_level_js');



function cdashmm_cmp( $a, $b ) {

    if ( $a->term_order ==  $b->term_order ) {

        return 0;

    } else if ( $a->term_order < $b->term_order ) {

        return -1;

    } else {

        return 1;

    }

}



function cdashmm_rank_membership_levels() {



    $message = "";

    if (isset($_POST['order-submit'])) {

        cdashmm_update_order();

    }

?>

<div class='wrap'>

    <h2><?php echo __('Rank Membership Levels ', 'cdashmm'); ?></h2>

    <form name="custom-order-form" method="post" action="">

        <?php

        $terms = get_terms( 'membership_level', 'hide_empty=0' );

        if ( $terms ) {

            usort($terms, 'cdashmm_cmp');

            ?>

            <div id="poststuff" class="metabox-holder">

                <div class="widget order-widget">

                    <p><?php _e('Order the membership levels by dragging and dropping them into the desired order.', 'cdashmm') ?></p>

                    <div class="misc-pub-section">

                        <ul id="custom-order-list">

                            <?php foreach ( $terms as $term ) : ?>

                            <li id="id_<?php echo $term->term_id; ?>" class="lineitem"><?php echo $term->name; ?></li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                    <div class="misc-pub-section misc-pub-section-last">

                        <div id="publishing-action">

                            <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" id="custom-loading" style="display:none" alt="" />

                            <input type="submit" name="order-submit" id="order-submit" class="button-primary" value="<?php _e('Update Order', 'cdashmm') ?>" />

                        </div>

                        <div class="clear"></div>

                    </div>

                    <input type="hidden" id="hidden-custom-order" name="hidden-custom-order" />

                </div>

            </div>

        <?php } else { ?>

            <p><?php _e('No terms found', 'cdashmm'); ?></p>

        <?php } ?>

    </form>



</div>

<?php if ( $terms ) { ?>

<script type="text/javascript">

// <![CDATA[



    jQuery(document).ready(function(jQuery) {

        jQuery("#custom-loading").hide();

        jQuery("#order-submit").click(function() {

            orderSubmit();

        });

        jQuery("#order-alpha").click(function(e) {

            e.preventDefault();

            jQuery("#custom-loading").show();

            orderAlpha();

            //jQuery("#order-submit").trigger("click");

            setTimeout(function(){

                jQuery("#custom-loading").hide();

            },500);

            jQuery("#order-alpha").blur();

        });

    });



    function customtaxorderAddLoadEvent(){

        jQuery("#custom-order-list").sortable({

            placeholder: "sortable-placeholder",

            revert: false,

            tolerance: "pointer"

        });

    };



    addLoadEvent(customtaxorderAddLoadEvent);



    function orderSubmit() {

        var newOrder = jQuery("#custom-order-list").sortable("toArray");

        jQuery("#custom-loading").show();

        jQuery("#hidden-custom-order").val(newOrder);

        return true;

    }



    // accending sort

    function asc_sort(a, b) {

        //return (jQuery(b).text()) < (jQuery(a).text()) ? 1 : -1;

        //console.log (jQuery(a).text());

        return jQuery(a).text().toUpperCase().localeCompare(jQuery(b).text().toUpperCase());

    }



// ]]>

</script>

<?php }

}



/*

 * Function to update the database with the submitted order

 */



function cdashmm_update_order() {

    if (isset($_POST['hidden-custom-order']) && $_POST['hidden-custom-order'] != "") {

        global $wpdb;

        $new_order = $_POST['hidden-custom-order'];

        $IDs = explode(",", $new_order);

        $ids = Array();

        $result = count($IDs);

        for($i = 0; $i < $result; $i++) {

            $id = (int) str_replace("id_", "", $IDs[$i]);

            $wpdb->query( $wpdb->prepare(

                "

                    UPDATE $wpdb->terms SET term_order = '%d' WHERE term_id ='%d'

                ",

                $i,

                $id

            ) );

            $wpdb->query( $wpdb->prepare(

                "

                    UPDATE $wpdb->term_relationships SET term_order = '%d' WHERE term_taxonomy_id ='%d'

                ",

                $i,

                $id

            ) );

            $ids[] = $id;

        }

        echo '<div id="message" class="updated fade"><p>'. __('Order updated successfully.', 'cdashmm').'</p></div>';

        do_action('cdashmm_update_order', $ids);

    } else {

        echo '<div id="message" class="error fade"><p>'. __('An error occured, order has not been saved.', 'cdashmm').'</p></div>';

    }

}



/*

 * cdashmm_apply_order_filter

 * Function to sort the standard WordPress Queries.

 */



/*function cdashmm_apply_order_filter($orderby, $args) {

    global $customtaxorder_settings;

    $options = $customtaxorder_settings;

    if ( isset( $args['taxonomy'] ) ) {

        $taxonomy = $args['taxonomy'];

    } else {

        $taxonomy = 'category';

    }

    if ( !isset ( $options[$taxonomy] ) ) {

        $options[$taxonomy] = 0; // default if not set in options yet

    }

    if ( $args['orderby'] == 'term_order' ) {

        return 't.term_order';

    } elseif ( $args['orderby'] == 'name' ) {

        return 't.name';

    } elseif ( $options[$taxonomy] == 1 && !isset($_GET['orderby']) ) {

        return 't.term_order';

    } elseif ( $options[$taxonomy] == 2 && !isset($_GET['orderby']) ) {

        return 't.name';

    } else {

        return $orderby;

    }

}

add_filter('get_terms_orderby', 'cdashmm_apply_order_filter', 10, 2);*/



function cdashmm_apply_order_filter($orderby, $args) {

    global $customtaxorder_settings;

    $options = $customtaxorder_settings;

    if ( isset( $args['taxonomy'] ) ) {

        $taxonomy = $args['taxonomy'];

    } else {

        $taxonomy = 'category';

    }



    if ( is_array( $taxonomy ) ) {

        $taxonomy = $taxonomy[0];

    }



    if ( !isset ( $options[$taxonomy] ) ) {

        $options[$taxonomy] = 0; // default if not set in options yet

    }

    if ( $args['orderby'] == 'term_order' ) {

        return 't.term_order';

    } elseif ( $args['orderby'] == 'name' ) {

        return 't.name';

    } elseif ( $options[$taxonomy] == 1 && !isset($_GET['orderby']) ) {

        return 't.term_order';

    } elseif ( $options[$taxonomy] == 2 && !isset($_GET['orderby']) ) {

        return 't.name';

    } else {

        return $orderby;

    }

}

add_filter('get_terms_orderby', 'cdashmm_apply_order_filter', 10, 2);





function cdashmm_object_terms_order_filter( $terms ) {

    global $customtaxorder_settings;

    $options = $customtaxorder_settings;



    if ( empty($terms) || !is_array($terms) ) {

        return $terms; // only work with an array of terms

    }

    foreach ($terms as $term) {

        if ( is_object($term) && isset( $term->taxonomy ) ) {

            $taxonomy = $term->taxonomy;

        } else {

            return $terms; // not an array with objects

        }

        break; // just the first one :)

    }



    if ( !isset ( $options[$taxonomy] ) ) {

        $options[$taxonomy] = 0; // default if not set in options yet

    }

    if ( $options[$taxonomy] == 1 && !isset($_GET['orderby']) ) {

        if (current_filter() == 'get_terms' ) {

            if ( $taxonomy == 'post_tag' || $taxonomy == 'product_tag' ) {

                return $terms;

            }

        }

        usort($terms, 'cdashmm_cmp');

        return $terms;

    }

    return $terms;

}

add_filter( 'wp_get_object_terms', 'cdashmm_object_terms_order_filter', 10, 3 );

add_filter( 'get_terms', 'cdashmm_object_terms_order_filter', 10, 3 );

add_filter( 'get_the_terms', 'cdashmm_object_terms_order_filter', 10, 3 );

add_filter( 'tag_cloud_sort', 'cdashmm_object_terms_order_filter', 10, 3 );







function _cdashmm_taxonomy_order_activate() {

    global $wpdb;

    $init_query = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'");

    if ($init_query == 0) { $wpdb->query("ALTER TABLE $wpdb->terms ADD term_order INT( 4 ) NULL DEFAULT '0'"); }

}

function cdashmm_taxonomy_order_activate($networkwide) {

    global $wpdb;

    if (function_exists('is_multisite') && is_multisite()) {

        $curr_blog = $wpdb->blogid;

        $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ($blogids as $blog_id) {

            switch_to_blog($blog_id);

            _cdashmm_taxonomy_order_activate();

        }

        switch_to_blog($curr_blog);

    } else {

        _cdashmm_taxonomy_order_activate();

    }

}

register_activation_hook(__FILE__, 'cdashmm_taxonomy_order_activate');







// ------------------------------------------------------------------------

// Calculate invoice number

// ------------------------------------------------------------------------

function cdashmm_calculate_invoice_number() {

    // find all the invoices

    $args = array( 

        'post_type' => 'invoice',

        'post_status' => 'any'

    );

    

    $invoices = new WP_Query( $args );



    if ( $invoices->have_posts() ) {

        $newinvoice = $invoices->found_posts;

        $invoicenumber = cdashmm_check_for_duplicate_invoice_number( $newinvoice );

    } else {

        $invoicenumber = cdashmm_check_for_duplicate_invoice_number( '1' );

    }

    wp_reset_postdata();



    return $invoicenumber;    

}



function cdashmm_check_for_duplicate_invoice_number( $number ) {

    $args = array( 

        'post_type' => 'invoice',

        'meta_key' => '_cdashmm_invoice_number',

        'meta_value' => $number, 

    );



    $thisinvoice = new WP_Query( $args );



    if ( $thisinvoice->have_posts() ) {

        // we need to find another invoice number

        $number = cdashmm_check_for_duplicate_invoice_number( $number + 1 );

    } 

    return $number;

    wp_reset_postdata();

}





// ------------------------------------------------------------------------

// figure out how to display price

// ------------------------------------------------------------------------



function cdashmm_display_price( $price ) {

    $options = get_option( 'cdash_directory_options' );

    $cur_symb = $options['currency_symbol'];

    $cur_pos = $options['currency_position'];



    if( "before" == $cur_pos ) {

        $price = $cur_symb . $price;

    }

    if( "after" == $cur_pos ) {

        $price = $price . $cur_symb;

    }



    return $price;

}





// ------------------------------------------------------------------------

// Reusable email

// ------------------------------------------------------------------------



function cdashmm_send_email( $from, $to, $cc, $subject, $message ) {



    $options = get_option( 'cdashmm_options' );



    $headers = "From: " . $from . " \r\n";

    if( isset( $cc ) && '' !== $cc ) {

        $headers .= "Cc: " . $cc . "\r\n";

    }

    $headers .= "MIME-Version: 1.0\r\n";

    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";



    $subject = $subject;



    $message = '

        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

        <html xmlns="http://www.w3.org/1999/xhtml">

        <head>

        <meta name="viewport" content="width=device-width" />

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <title>' . $subject . '</title>

        <style>

        /* -------------------------------------

                GLOBAL

        ------------------------------------- */

        * {

            margin: 0;

            padding: 0;

            font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;

            font-size: 100%;

            line-height: 1.6;

        }



        img {

            max-width: 100%;

        }



        body {

            -webkit-font-smoothing: antialiased;

            -webkit-text-size-adjust: none;

            width: 100%!important;

            height: 100%;

        }





        /* -------------------------------------

                ELEMENTS

        ------------------------------------- */

        a {

            color: #0F75BD;

        }



        .btn-primary {

            text-decoration: none;

            color: #FFF;

            background-color: #348eda;

            border: solid #348eda;

            border-width: 10px 20px;

            line-height: 2;

            font-weight: bold;

            margin-right: 10px;

            text-align: center;

            cursor: pointer;

            display: inline-block;

            border-radius: 25px;

        }



        .btn-secondary {

            text-decoration: none;

            color: #FFF;

            background-color: #aaa;

            border: solid #aaa;

            border-width: 10px 20px;

            line-height: 2;

            font-weight: bold;

            margin-right: 10px;

            text-align: center;

            cursor: pointer;

            display: inline-block;

            border-radius: 25px;

        }



        .last {

            margin-bottom: 0;

        }



        .first {

            margin-top: 0;

        }



        .padding {

            padding: 10px 0;

        }





        /* -------------------------------------

                BODY

        ------------------------------------- */

        table.body-wrap {

            width: 100%;

            padding: 20px;

        }



        table.body-wrap .container {

            border: 1px solid #f0f0f0;

        }





        /* -------------------------------------

                FOOTER

        ------------------------------------- */

        table.footer-wrap {

            width: 100%;    

            clear: both!important;

        }



        .footer-wrap .container p {

            font-size: 12px;

            color: #666;

            

        }



        table.footer-wrap a {

            color: #999;

        }





        /* -------------------------------------

                TYPOGRAPHY

        ------------------------------------- */

        h1, h2, h3 {

            font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;

            color: #000;

            margin: 40px 0 10px;

            line-height: 1.2;

            font-weight: 200;

        }



        h1 {

            font-size: 36px;

        }

        h2 {

            font-size: 28px;

        }

        h3 {

            font-size: 22px;

        }



        p, ul, ol {

            margin-bottom: 10px;

            font-weight: normal;

            font-size: 14px;

        }



        ul li, ol li {

            margin-left: 5px;

            list-style-position: inside;

        }



        /* ---------------------------------------------------

                RESPONSIVENESS

                Nuke it from orbit. It is the only way to be sure.

        ------------------------------------------------------ */



        /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */

        .container {

            display: block!important;

            max-width: 600px!important;

            margin: 0 auto!important; /* makes it centered */

            clear: both!important;

        }



        /* Set the padding on the td rather than the div for Outlook compatibility */

        .body-wrap .container {

            padding: 20px;

        }



        /* This should also be a block element, so that it will fill 100% of the .container */

        .content {

            max-width: 600px;

            margin: 0 auto;

            display: block;

        }



        /* Make sure tables in the content area are 100% wide */

        .content table {

            width: 100%;

        }



        </style>

        </head>



        <body bgcolor="#f6f6f6">



        <!-- body -->

        <table class="body-wrap" bgcolor="#f6f6f6">

            <tr>

                <td></td>

                <td class="container" bgcolor="#FFFFFF">



                    <!-- content -->

                    <div class="content">

                    <table>

                        <tr>

                            <td>

                                ' . $message . '

                            </td>

                        </tr>

                    </table>

                    </div>

                    <!-- /content -->

                    

                </td>

                <td></td>

            </tr>

        </table>

        <!-- /body -->



        <!-- footer -->

        <table class="footer-wrap">

            <tr>

                <td></td>

                <td class="container">

                    

                    <!-- content -->

                    <div class="content">

                        <table>

                            <tr>

                                <td align="center">

                                    <p>

                                        <a href="' . site_url() . '">' . $options['orgname'] . '</a>

                                    </p>

                                </td>

                            </tr>

                        </table>

                    </div>

                    <!-- /content -->

                    

                </td>

                <td></td>

            </tr>

        </table>

        <!-- /footer -->



        </body>

        </html>

    ';





    $success = wp_mail( $to, $subject, $message, $headers );



    return $success;



}



// ------------------------------------------------------------------------

// Send invoice notification email

// ------------------------------------------------------------------------



function cdashmm_send_invoice_notification_email() {



    if ( !wp_verify_nonce( $_POST['nonce'], "cdashmm_notification_nonce")) {

        $results = array(

            'message' => '<p class="error">' . __( 'Verification error', 'cdashmm' ) . '</p>'

            );

        wp_send_json($results);

        die();

    }



    // send an error if there isn't a "to" email address

    if( !isset( $_POST['send_to'] ) || $_POST['send_to'] == '') {

        $results = array(

            'message' => '<p class="error">' . __( 'You must select at least one email address!', 'cdashmm' ) . '</p>',

            );

        wp_send_json($results);

        die();

    }



    $options = get_option( 'cdashmm_options' );



    // get the invoice information to put in the email 

    $invoiceid = $_POST['invoice_id'];

    $thisinvoice = get_post( $invoiceid );

    global $invoice_metabox;

    $invoiceinfo = $invoice_metabox->the_meta( $invoiceid );



    $send_tos = array();

    parse_str( $_POST['send_to'], $send_tos );

    $emails = $send_tos['send_to'];



    $to = '';

    foreach( $emails as $email ) {

        $to .= $email . ', ';

    }



    $from = $options['receipt_from_name'] . " <" . $options['receipt_from_email'] . ">";



    $subject = __( 'Invoice from ', 'cdashmm' ) . $options['orgname'];



    $cc = '';



    if( isset( $_POST['copy_to'] ) && $_POST['copy_to'] !== '') {

        $copy_tos = array();

        parse_str( $_POST['copy_to'], $copy_tos );

        $emails = $copy_tos['copy_to'];

        foreach( $emails as $email ) {

            // $headers[] = "Cc: " . $email . "\r\n";

        }

        $cc = $email . ",";

    }



    $message = '';

    if( isset( $_POST['message'] ) && $_POST['message'] !== '' ) {

        $message .= '<p>' . stripslashes( implode( "<br />", array_map( 'sanitize_text_field', explode( "\n", $_POST['message'] ) ) ) ) . '</p><br />';

    }

    $message .= '<p><strong>' . __( 'Invoice from: ', 'cdashmm' ) . '</strong>' . $options['receipt_from_name'] . '</p>';

    $message .= '<p><strong>' . __( 'Invoice #: ', 'cdashmm' ) . '</strong>' . $invoiceinfo['invoice_number'] . '</p>';

    $message .= '<p><strong>' . __( 'Amount: ', 'cdashmm' ) . '</strong>' . cdashmm_display_price( $invoiceinfo['amount'] ) . '</p>';

    $message .= '<p><strong>' . __( 'Due date: ', 'cdashmm' ) . '</strong>' . $invoiceinfo['duedate'] . '</p><br />';

    $message .= '<p><strong>' . __( 'View this invoice online: ', 'cdashmm' ) . '</strong><a href="' . get_the_permalink( $invoiceid ) . '">' . get_the_permalink( $invoiceid ) . '</a></p>';



    cdashmm_send_email( $from, $to, $cc, $subject, $message );



    $results = array();



    $results['message'] = '<p class="success">' . __( 'Email sent successfully!', 'cdashmm' ) . '</p>';



    // update post meta to record that the message was sent

    $today = current_time('Y-m-d');



    cdashmm_update_notification_history( $invoiceid, $today, $to );



    $results['today'] = $today;

    $results['to'] = $to;



    wp_send_json($results);



    die();



}

add_action( 'wp_ajax_cdashmm_send_invoice_notification_email', 'cdashmm_send_invoice_notification_email' );



// ------------------------------------------------------------------------

// Function to update notifications

// ------------------------------------------------------------------------



function cdashmm_update_notification_history( $id, $date, $to ) {



    $oldnotifications = get_post_meta( $id, '_cdashmm_notification' );



    if( !empty( $oldnotifications ) ) {

        $notification_array = $oldnotifications[0];

        $new_notification = array(

                'notification_date' => $date,

                'notification_to' => $to,

            );

        $notification_array[] = $new_notification;



        update_post_meta( $id, '_cdashmm_notification', $notification_array ); 



    } else {

        $fields = array( '_cdashmm_notification' );

        $str = $fields;

        update_post_meta( $id, 'notification_meta_fields', $fields );



        $notificationinfo = array(

            array(

                'notification_date' => $date,

                'notification_to' => $to,

            )

        );

        add_post_meta( $id, '_cdashmm_notification', $notificationinfo );    

    }

}





// ------------------------------------------------------------------------

// Cron job - once a day, check for overdue invoices, unpaid invoices, and lapsed memberships

// ------------------------------------------------------------------------



if ( ! wp_next_scheduled( 'cdashmm_check_for_overdue_invoices' ) ) {

    wp_schedule_event( time(), 'daily', 'cdashmm_check_for_overdue_invoices' );

}



add_action( 'cdashmm_check_for_overdue_invoices', 'cdashmm_update_overdue_invoices' );



function cdashmm_update_overdue_invoices() {

    // get today's date

    $today = current_time('Y-m-d');



    // get overdue status

    $overdue_status = get_term_by( 'slug', 'overdue', 'invoice_status' );



    // find unpaid invoices with a due date earlier than today

    $args = array( 

        'post_type' => 'invoice',

        'post_status' => 'any',

        'posts_per_page' => -1,                  

        'meta_key' => '_cdashmm_duedate',

        'meta_value' => $today,

        'meta_compare' => '<', 

        'tax_query' => array(

            array(

                'taxonomy' => 'invoice_status',

                'field'    => 'slug',

                'terms'    => 'paid',

                'operator' => 'NOT IN',

            ),

        ),

        // should I also check for whether the "amount paid" field is filled in?

    );

    

    $overdue = new WP_Query( $args );



    if ( $overdue->have_posts() ) :

        while ( $overdue->have_posts() ) : $overdue->the_post();

            // change invoice status to overdue

            wp_set_object_terms( get_the_id(), $overdue_status->term_id, 'invoice_status', false );



            // check whether we need to lapse membership

            $options = get_option( 'cdashmm_options' );

            if( isset( $options['lapse_membership'] ) && "1" == $options['lapse_membership'] ) {

                // if invoice includes membership, find the business

                global $invoice_metabox;

                $meta = $invoice_metabox->the_meta();

                if( isset( $meta['item_membershiplevel'] ) ) {

                    $lapsed_status = get_term_by( 'slug', 'lapsed', 'membership_status' );

                    $lapsed_business = new WP_Query( array(

                      'connected_type' => 'invoices_to_businesses',

                      'connected_items' => get_the_id(),

                      'nopaging' => true,

                    ) );



                    if ( $lapsed_business->have_posts() ) :

                        while ( $lapsed_business->have_posts() ) : $lapsed_business->the_post();

                            // mark membership as lapsed

                            wp_set_object_terms( get_the_id(), $lapsed_status->term_id, 'membership_status', false );

                        endwhile;

                    endif;

                    wp_reset_postdata();

                }

            }

        endwhile;

    endif;

    wp_reset_postdata();



    // get unpaid status

    $unpaid_status = get_term_by( 'slug', 'unpaid', 'invoice_status' );

    $too_long = date( 'Y-m-d', strtotime( '-4 months', current_time( 'timestamp' ) ) );



    // find overdue invoices with a due date earlier than 4 months ago

    $args = array( 

        'post_type' => 'invoice',

        'post_status' => 'any',

        'posts_per_page' => -1,                  

        'meta_key' => '_cdashmm_duedate',

        'meta_value' => $too_long,

        'meta_compare' => '<', 

        'tax_query' => array(

            array(

                'taxonomy' => 'invoice_status',

                'field'    => 'slug',

                'terms'    => 'overdue',

            ),

        ),

    );

    

    $unpaid = new WP_Query( $args );



    if ( $unpaid->have_posts() ) :

        while ( $unpaid->have_posts() ) : $unpaid->the_post();

            // change invoice status to unpaid

            wp_set_object_terms( get_the_id(), $unpaid_status->term_id, 'invoice_status', false );



        endwhile;

    endif;

    wp_reset_postdata();

}



// remove the cron job on deactivation

register_deactivation_hook( __FILE__, 'cdashmm_remove_overdue_invoice_cron_job' );

function cdashmm_remove_overdue_invoice_cron_job() {

    wp_clear_scheduled_hook( 'cdashmm_check_for_overdue_invoices' );

}





// ------------------------------------------------------------------------

// Display ad for recurring payments in membership renewal metabox

// ------------------------------------------------------------------------



function cdashmm_recurring_payments_ad() { ?>

    <p>

        <?php _e( 'Manage memberships more efficiently with the <strong>Recurring Payments</strong> add-on!', 'cdashrp' ); ?>

    </p>

    <ul id='cdashmm-advert'>

        <li>

            <?php _e( 'Let members sign up for automatic recurring payments', 'cdashrp' ); ?>

        </li>  

        <li>

            <?php _e( 'Automatically generate and send annual membership invoices', 'cdashrp' ); ?>

        </li>

        <li>

            <?php _e( 'Automatically send past due invoice reminders', 'cdashrp' ); ?>

        </li>

    </ul>

    <p><a href="<?php echo admin_url( '/admin.php?page=chamber-dashboard-business-directory%2Foptions.php-addons' ); ?>"><?php _e( 'See all Chamber Dashboard Add-Ons', 'cdashmm' ); ?></a></p>



<?php }



add_action( 'cdashmm_membership_renewal_metabox', 'cdashmm_recurring_payments_ad', 10 );



?>