<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hadith_Functions {
    public function __construct() {
        add_action( 'admin_menu', array($this, 'register_report_menu_page') );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
        register_activation_hook( HADITH_CLASSES__FILE__, array( $this, 'reports_table' ) );
    }
    
    /*
    * Enqueue scripts
    */
    public function admin_scripts(){
        wp_enqueue_style('datatable-style', '//cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css');
        wp_enqueue_style('hadith-style', plugin_dir_url( __DIR__ ).'assets/css/style.css');
        wp_enqueue_script('datatable-script', '//cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js', array('jquery'), null, true);
        wp_enqueue_script('hadith-scripts', plugin_dir_url( __DIR__ ).'assets/js/scripts.js', array('jquery'), null, true);
        $hadith_obj = array(
            'api_nonce' => wp_create_nonce( 'wp_rest' ),
            'api_url'   => esc_url_raw(rest_url('hadith/v1/')),
        );
        wp_localize_script( 'hadith-scripts', 'hadith_obj', $hadith_obj );
    }
    
    /* 
    * Database table: reports table
    */
    public function reports_table(){
        global $wpdb;

        $collate = '';
        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }

        $pilgrim_complaints_table = "CREATE TABLE {$wpdb->prefix}reports (
            ID INT NOT NULL AUTO_INCREMENT,
            name varchar(50) NOT NULL,
            email varchar(50) NOT NULL,
            message varchar(350) NOT NULL,
            status TEXT(15) NOT NULL,
            date datetime NOT NULL,
            PRIMARY KEY (ID)
            ) $collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $pilgrim_complaints_table );
    }
    
    /**
    * Register a custom menu page.
    */
    public function register_report_menu_page(){
        add_menu_page( 
            __( 'Reports', 'hadith' ),
            'Reports',
            'manage_options',
            'reports',
            array($this, 'hadith_reports'),
            'dashicons-flag',
            6
        ); 
    }
    
    /**
    * Display a custom menu page
    */
    public function hadith_reports(){
        global $wpdb;
        
        $reports = $wpdb->get_results(
           "SELECT * FROM {$wpdb->prefix}reports"
        );
        ?>
        <div class="hadith-reports-table">
            <table id="hadith_reports" class="display">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('ID','hadith'); ?></th>
                        <th><?php echo esc_html__('Date','hadith'); ?></th>
                        <th><?php echo esc_html__('Name','hadith'); ?></th>
                        <th><?php echo esc_html__('Email','hadith'); ?></th>
                        <th><?php echo esc_html__('Message','hadith'); ?></th>
                        <th><?php echo esc_html__('Status','hadith'); ?></th>
                    </tr>
                </thead>
                <tbody>
                  
                   <?php foreach( $reports as $report ) : ?>
                    <tr>
                        <td><?php echo esc_html($report->ID); ?></td>
                        <td><?php echo esc_html($report->date); ?></td>
                        <td><?php echo esc_html($report->name); ?></td>
                        <td><?php echo esc_html($report->email); ?></td>
                        <td><?php echo esc_html($report->message); ?></td>
                        <td>
                           <?php $status = !empty($report->status) ? $report->status : 'pending'; ?>
                            <select name="" class="hadith_report_status" report-id="<?php echo esc_attr($report->ID); ?>">
                                <option value="pending" <?php selected($status, 'pending', true); ?>>Pending</option>
                                <option value="working" <?php selected($status, 'working', true); ?>>Working</option>
                                <option value="completed" <?php selected($status, 'completed', true); ?>>Completed</option>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                </tbody>
            </table>
        </div>
        
        <?php
    }
}
new Hadith_Functions();



/*

$url = "http://alhadith.test/wp-json/hadith/v1/report/";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Content-Type: application/json",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = '{"name":"raihan","email":"asdfsaf","message":"asdf"}';

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);
*/


?>

