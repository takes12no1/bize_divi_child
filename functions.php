// Enqueue parent theme styles and custom scripts
function my_et_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_script( 'divi', get_stylesheet_directory_uri() . '/js/scripts.js', array( 'jquery', 'divi-custom-script' ), '0.1.1', true );
}
add_action( 'wp_enqueue_scripts', 'my_et_enqueue_styles' );
