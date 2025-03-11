<?php

// Enqueue parent theme styles and custom scripts
function my_et_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_script( 'divi', get_stylesheet_directory_uri() . '/js/scripts.js', array( 'jquery', 'divi-custom-script' ), '0.1.1', true );
}
add_action( 'wp_enqueue_scripts', 'my_et_enqueue_styles' );

// Fix accessibility error from Pagespeed
add_action('after_setup_theme', 'db_remove_et_viewport_meta');
add_action('wp_head', 'db_enable_pinch_zoom');
function db_remove_et_viewport_meta() {
    remove_action('wp_head', 'et_add_viewport_meta');
}
function db_enable_pinch_zoom() {
    echo '<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0, minimum-scale=0.1, maximum-scale=10.0">';
}

// Add text under main menu based on item descriptions
function prefix_nav_description( $item_output, $item, $depth, $args ) {
    if ( !empty( $item->description ) ) {
        $item_output = str_replace( $args->link_after . '</a>', '<p class="menu-item-description">' . $item->description . '</p>' . $args->link_after . '</a>', $item_output );
    }
    return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'prefix_nav_description', 10, 4 );

/* START CUSTOM URLS FOR POWER SUPPLY PAGES */

// Flush rewrite rules (note: best practice is to flush on activation only)
//flush_rewrite_rules();

add_filter( 'rank_math/frontend/canonical', function( $canonical ) {
    if ( is_page(431) || is_page(449) ) {
        return ''; // Disable Rank Math's canonical tag for these pages.
    }
    return $canonical;
});

add_action( 'init', 'create_new_url_modelslist' );
function create_new_url_modelslist() {
    add_rewrite_rule(
        '^power-supply-repair-manufacturers/([^/]*)$',
        'index.php?page_id=431&brand=$matches[1]',
        'top'
    );
}

add_action( 'init', 'create_new_url_modeldetails' );
function create_new_url_modeldetails() {
    add_rewrite_rule(
        '^power-supply-repair-manufacturers/([^/]*)/([^/]*)$',
        'index.php?page_id=449&brand=$matches[1]&model=$matches[2]',
        'top'
    );
}

// Allow the custom query variables
add_filter('query_vars', 'my_query_vars', 10, 1);
function my_query_vars($vars) {
    $vars[] = 'brand'; 
    $vars[] = 'model'; 
    return $vars;
}
function my_rewrite_flush() {
    // Ensure that the custom rewrite rules are registered
    create_new_url_modelslist();
    create_new_url_modeldetails();
    // Now flush the rewrite rules
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'my_rewrite_flush' );


// Output a canonical link that matches the custom URLs
function custom_canonical_output() {
    if (is_page(431) && get_query_var('brand')) {
        $brand = get_query_var('brand');
        echo '<link rel="canonical" href="' . esc_url( home_url( '/power-supply-repair-manufacturers/' . $brand ) ) . '">' . "\n";
    } elseif (is_page(449) && get_query_var('brand') && get_query_var('model')) {
        $brand = get_query_var('brand');
        $model = str_replace('+', 'plus', get_query_var('model')); // Normalize here
        echo '<link rel="canonical" href="' . esc_url( home_url( '/power-supply-repair-manufacturers/' . $brand . '/' . $model ) ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'custom_canonical_output', 1 );

add_filter('redirect_canonical', 'disable_canonical_redirect_for_custom_urls', 10, 2);
function disable_canonical_redirect_for_custom_urls($redirect_url, $requested_url) {
    if ( is_page(431) && get_query_var('brand') ) {
        return false;
    }
    if ( is_page(449) && get_query_var('brand') && get_query_var('model') ) {
        return false;
    }
    return $redirect_url;
}
/* END CUSTOM URLS FOR POWER SUPPLY PAGES */
// Custom Title Tag for Dynamic Pages
add_filter('rank_math/frontend/title', function($title) {
    global $wpdb;

    if (is_page(431) && get_query_var('brand')) {
        $brand = get_query_var('brand');
        $query = "
            SELECT brand_name FROM dbfb8rrbg7jg1z.ps_brands
            WHERE brand_slug = '" . esc_sql($brand) . "'
        ";
        $brand_name = $wpdb->get_var($query);
        if ($brand_name) {
            return esc_html($brand_name) . " Power Supply Repair | The Power Clinic Inc.";
        }
    } elseif (is_page(449) && get_query_var('brand') && get_query_var('model')) {
        $brand = get_query_var('brand');
        $model = str_replace('+', 'plus', get_query_var('model')); // Normalize here
        $query_brand = "
            SELECT brand_name FROM dbfb8rrbg7jg1z.ps_brands
            WHERE brand_slug = '" . esc_sql($brand) . "'
        ";
        $brand_name = $wpdb->get_var($query_brand);
        $query_model = "
            SELECT model_number FROM dbfb8rrbg7jg1z.ps_models
            WHERE model_slug = '" . esc_sql($model) . "'
        ";
        $model_number = $wpdb->get_var($query_model);
        if ($brand_name && $model_number) {
            return esc_html($brand_name) . " " . esc_html($model_number) . " Power Supply Repair | The Power Clinic Inc.";
        }
    }
    return $title;
});

// Custom Meta Description for Dynamic Pages
add_filter('rank_math/frontend/description', function($description) {
    global $wpdb;

    if (is_page(431) && get_query_var('brand')) {
        $brand = get_query_var('brand');
        $query = "
            SELECT brand_name FROM dbfb8rrbg7jg1z.ps_brands
            WHERE brand_slug = '" . esc_sql($brand) . "'
        ";
        $brand_name = $wpdb->get_var($query);
        if ($brand_name) {
            return "Expert repair services for " . esc_html($brand_name) . " power supplies. Fast, reliable, and affordable solutions.";
        }
    } elseif (is_page(449) && get_query_var('brand') && get_query_var('model')) {
        $brand = get_query_var('brand');
        $model = str_replace('+', 'plus', get_query_var('model')); // Normalize here
        $query_brand = "
            SELECT brand_name FROM dbfb8rrbg7jg1z.ps_brands
            WHERE brand_slug = '" . esc_sql($brand) . "'
        ";
        $brand_name = $wpdb->get_var($query_brand);
        $query_model = "
            SELECT model_number FROM dbfb8rrbg7jg1z.ps_models
            WHERE model_slug = '" . esc_sql($model) . "'
        ";
        $model_number = $wpdb->get_var($query_model);
        if ($brand_name && $model_number) {
            return "Professional repair for " . esc_html($brand_name) . " " . esc_html($model_number) . " power supplies. Contact us for quick service!";
        }
    }
    return $description;
});
// Display brands page data Shortcode
function get_brands_list($atts) {
    $string = '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>';
    global $wpdb;
    $query = "
        SELECT SQL_CALC_FOUND_ROWS * FROM dbfb8rrbg7jg1z.ps_brands
        WHERE 1
        ORDER BY brand_name ASC
    ";
    $row = $wpdb->get_results($query);
    for($i = 0; $i < count($row); $i++){
        if($i % 2 == 0) {
            $string .= '</tr><tr>';
        }
        $string .= '<td>
        <a href="' . esc_url( home_url( '/power-supply-repair-manufacturers/' . $row[$i]->brand_slug ) ) . '">
        ' . esc_html( $row[$i]->brand_name ) . '</a>
        </td>';
    }
    $string .= '</tr></table>';
    return $string;
}
add_shortcode('brands_list', 'get_brands_list');

// Display models listing page data Shortcode
function models_list_tag_func( $atts ) {
    global $wpdb;
    
    $brand = get_query_var('brand');
    if (empty($_GET['brand'])) {
        $_GET['brand'] = 'acdc';       
    }
    $query = "
        SELECT b.* FROM dbfb8rrbg7jg1z.ps_brands b
        WHERE 1 AND b.brand_slug = '" . esc_sql($brand) . "'
    ";
    $b_row = $wpdb->get_row($query, ARRAY_A);

    $string = '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>';
    $query = "
        SELECT m.model_number, m.model_slug FROM dbfb8rrbg7jg1z.ps_models m
        WHERE 1 AND m.brand_id = '" . esc_sql($b_row['brand_id']) . "'
        ORDER BY model_number ASC
    ";
    $row = $wpdb->get_results($query);
    for ($i = 0; $i < count($row); $i++) {
        if ($i % 3 == 0) {
            $string .= '</tr><tr>';
        }
        // Replace "+" with "plus" in the model slug
        $normalized_model_slug = str_replace('+', 'plus', $row[$i]->model_slug);
        $string .= '<td>
        <a href="' . esc_url( home_url( '/power-supply-repair-manufacturers/' . $b_row['brand_slug'] . '/' . $normalized_model_slug ) ) . '">
        ' . esc_html( $row[$i]->model_number ) . '</a>
        </td>';
    }
    $string .= '</tr></table>';
    
    $args['brand_name'] = $b_row['brand_name'];
    $args['model_table'] = $string;
    return $args[$atts[0]];
}
add_shortcode( 'ml_tag', 'models_list_tag_func' );

// Display models details page data Shortcode
function models_details_tag_func( $atts ) {
    global $wpdb;
    
    $brand = get_query_var('brand');
    $model = get_query_var('model');
    if (empty($_GET['brand'])) {
        $_GET['brand'] = 'acdc';       
    }
    $query = "
        SELECT b.* FROM dbfb8rrbg7jg1z.ps_brands b
        WHERE 1 AND b.brand_slug = '" . esc_sql($brand) . "'
    ";
    $b_row = $wpdb->get_row($query, ARRAY_A);

    // Replace "+" with "plus" in the model query variable
    $normalized_model = str_replace('+', 'plus', $model);
    $query = "
        SELECT m.model_number, m.model_slug FROM dbfb8rrbg7jg1z.ps_models m
        WHERE 1 AND m.model_slug = '" . esc_sql($model) . "' 
        OR m.model_slug = '" . esc_sql($normalized_model) . "' 
    ";
    $row = $wpdb->get_row($query, ARRAY_A);
    
    // Normalize the returned model slug
    $normalized_model_slug = str_replace('+', 'plus', $row['model_slug']);
    
    $args['brand_name'] = $b_row['brand_name'];
    $args['brand_slug'] = $b_row['brand_slug'];
    $args['model_number'] = $row['model_number'];
    $args['model_slug'] = $normalized_model_slug;
    return $args[$atts[0]];
}
add_shortcode( 'md_tag', 'models_details_tag_func' );

?>
