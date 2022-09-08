<?php
/**
 * Plugin Name: Hadith Core
 * Plugin URI: 
 * Description: Hadith core plugin
 * Version: 1.0
 * Author URI: 
 * Text Domain: hadith
 * Domain Path: /languages
 * License: GPL-2.0+
 */
 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hadith_Core {
    public function __construct(){
        define( 'HADITH_URL', plugin_dir_url( __FILE__ ) );
        define( 'HADITH_ASSTES', HADITH_URL.'/assets');
        define( 'HADITH_PATH', plugin_dir_path( __FILE__ ) );
        define( 'HADITH_INC', HADITH_PATH.'inc' );
        define( 'HADITH_CLASSES__FILE__', __FILE__ );
        
        //Require hadith classes
        require_once(HADITH_INC.'/hadith.php');
    }
}
new Hadith_Core();