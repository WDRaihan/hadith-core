<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hadith_API_Endpoints {
    public function __construct() {
        add_action( 'rest_api_init', array($this, 'rest_api_init') );
    }
    
    private function get_results($table_name, $limit, $type = null, $conditions = null ){
        global $wpdb;
        
        if( $table_name != '' ){
            
            if( $type == null || $type === 'all' ){
                if( isset($limit) && $limit == '' || $limit == '-1' ){
                    $limit = '';
                }elseif( isset($limit) && !empty($limit) ){
                    $limit = 'LIMIT '.$limit;
                }else {
                    $limit = '';
                }
                return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %1s %2s", $table_name, $limit ), OBJECT );
                
            }elseif($type === 'single') {
                
                if( is_array($conditions) && !empty(array_filter($conditions)) ){
                    
                    $where = '';
                    foreach($conditions as $field=>$value) {
                        $where .= $field .' = '. $value . ' ';
                    }
                    
                    return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %s WHERE %s", $table_name, $where ), OBJECT );
                }
            }
            
        }else {

            return new WP_Error( 'no_table', 'Invalid table', array( 'status' => 404 ) );
        }
    }
    
    // Register rest-api routes
    public function rest_api_init(){

        /*
        * Register books route
        * Get all books Url: wp-json/hadith/v1/books/
        * Get books by title: wp-json/hadith/v1/books/?title=0
        */
        register_rest_route( 'hadith/v1', '/books/(?P<page>[0-9]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'hadith_get_all_books_endpoint_cb' ),
            'args' => array(
              'page' => array(
                'required' => true
              ),
            ),
            'permission_callback' => '__return_true',
        ) );
        
        //Filter
        register_rest_route( 'hadith/v1', '/book/(?P<book_id>[0-9]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'hadith_get_books_endpoint_cb' ),
            'args' => array(
              'book_id' => array(
                'required' => true
              )
            ),
            'permission_callback' => '__return_true',
        ) );
        
        /*
        * Register chapter route
        * Get all chapters Url: wp-json/hadith/v1/chapter/
        * Get chapter by book_id: wp-json/hadith/v1/chapter/?book_id=0
        */
        register_rest_route( 'hadith/v1', '/chapters/(?P<page>[0-9]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'hadith_get_all_chapters_endpoint_cb' ),
            'args' => array(
              'page' => array(
                'required' => true
              ),
            ),
            'permission_callback' => '__return_true',
        ) );
        register_rest_route( 'hadith/v1', '/chapter/(?P<book_id>[0-9]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'args' => array(
              'book_id' => array(
                'required' => true
              ),
            ),
            'callback' => array( $this, 'hadith_get_chapter_endpoint_cb' ),
            'permission_callback' => '__return_true',
        ) );
        
        /*
        * Register hadith route
        * Get all hadith: wp-json/hadith/v1/hadith/
        * Get hadith by section_id: wp-json/hadith/v1/hadith/?section_id=0
        */
        register_rest_route( 'hadith/v1', '/hadith/limit=(?P<limit>[0-9 -]+)', array(
            'methods' => 'GET',
            'callback' => array( $this, 'hadith_get_all_hadith_endpoint_cb' ),
            'permission_callback' => '__return_true',
        ) );
        
        register_rest_route( 'hadith/v1', '/hadith/book/(?P<book>[0-9]+)/chapter/(?P<chapter>[0-9]+)', array(
            'methods' => 'GET',
            'args' => array(
              'book' => array(
                'required' => true
              ),
              'chapter' => array(
                'required' => true
              ),
            ),
            'callback' => array( $this, 'hadith_get_hadith_endpoint_cb' ),
            'permission_callback' => '__return_true',
        ) );

    }
    
    //Books endpoint callback
    public function hadith_get_all_books_endpoint_cb( WP_REST_Request $request ) {

        global $wpdb;
        
        //make it 10 by default
        $limit = 10;
        if(isset($request['page']) && $request['page'] != ''){
            $limit = $request['page'];
        }
        
        $results = $this->get_results( 'books_en', $limit, 'all' );

        return new WP_REST_Response($results, 200);
    }
    public function hadith_get_books_endpoint_cb(  WP_REST_Request $request ) {

        global $wpdb;
        
        
        if( isset($request['book_id']) && !empty($request['book_id']) ){
            //$title = $request['title'];
            
            $results = $wpdb->get_results( 
                        $wpdb->prepare('SELECT * FROM books_en WHERE id = %d',$request['book_id'])
                );
        }

        return new WP_REST_Response($results, 200);
    }
    
    //Chapter endpoint callback
    public function hadith_get_all_chapters_endpoint_cb( WP_REST_Request $request ) {

        global $wpdb;

        $limit = '';
        if(isset($data['limit']) && $data['limit'] != ''){
            $limit = $data['limit'];
        }

        //$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %1s LIMIT %2s", 'chapter_en', $limit ), OBJECT );
        $results = $this->get_results( 'chapter_en', $limit, 'all' );

        return new WP_REST_Response($results, 200);
    }
    public function hadith_get_chapter_endpoint_cb( WP_REST_Request $request ) {

        global $wpdb;

        $limit = '';
        if(isset($request['limit']) && $request['limit'] != ''){
            $limit = intval($request['limit']);
        }
        
        if( isset($request['book_id']) && !empty($request['book_id']) ){
            $book_id = $request['book_id'];
            $results = $wpdb->get_results( "SELECT * FROM chapter_en WHERE book_id = $book_id " );
            
            //$results = $this->get_results( 'chapter_en', $limit, 'single', array( 'book_id'=>$book_id ) );
        }else {
            //$results = $wpdb->get_results( "SELECT * FROM chapter_en", OBJECT );
            $results = $this->get_results( 'chapter_en', $limit, 'all' );
        }

        return new WP_REST_Response($results, 200);
    }
    
    //Hadith endpoint callback
    public function hadith_get_all_hadith_endpoint_cb( $data ) {

        global $wpdb;
        
        $limit = '';
        if(isset($data['limit']) && $data['limit'] != ''){
            $limit = $data['limit'];
        }
        
        $results = $this->get_results( 'hadith_en', $limit, 'all' );

        return $results;
    }
    public function hadith_get_hadith_endpoint_cb( $data ) {

        global $wpdb;
        
        $limit = '';
        if(isset($data['limit']) && $data['limit'] != ''){
            $limit = intval($data['limit']);
        }
        
        if( isset($data['book']) && !empty($data['book']) && isset($data['chapter']) && !empty($data['chapter']) ){
            $book = $data['book'];
            $chapter = $data['chapter'];
            
            $results = $wpdb->get_results( "SELECT * FROM hadith_en WHERE book_id = $book AND chapter_id = $chapter ", OBJECT );
            
            //$results = $this->get_results( 'hadith_en', $limit, 'single', array( ' 	book_id'=>$book, 'chapter_id'=>$chapter ) );
            
        }else {
            //$results = $wpdb->get_results( "SELECT * FROM hadith_en", OBJECT );
            $results = $this->get_results( 'hadith_en', $limit, 'all' );
        }

        return $results;
    }
    
}
new Hadith_API_Endpoints();