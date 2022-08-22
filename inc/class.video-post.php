<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hadith_Video_Post {
    public function __construct() {
        //Add video post thumbnail from video URL meta field
        add_action( 'save_post', array($this, 'hadith_video_to_post' ), 10, 3 );
        
        //Test functionalities
        //add_action( 'wp_footer', array( $this, 'hadith_footer_area' ) );
    }
    
    public function hadith_video_to_post( $post_id, $post, $update ) {
        if ( $post->post_type != 'video' ) return;
        
        //$video_link = 'https://www.youtube.com/watch?v=dP-k9-IL05g';

        $video_link = !empty(get_post_meta( $post_id, 'video_link', true )) ? get_post_meta( $post_id, 'video_link', true ) : '';
        $video_id = $this->hadith_get_youtube_video_id($video_link);
        

        if( $video_link != '' ){
            $hadith_youtube_thumb = $this->hadith_youtube_thumbnail($video_link);

            $attachment_id = $this->hadith_insert_attachment_from_url($hadith_youtube_thumb, $post_id );

            set_post_thumbnail( $post_id, $attachment_id );
        }
        
        //Update title
        $video_info = json_decode($this->hadith_get_youtube_video_info($video_id));
        $vtitle = $video_info->items[0]->snippet->title;

        $post_update = array(
            'ID'         => $post_id,
            'post_title' => $vtitle
        );
        if ( empty($post->post_title) ){
            wp_update_post( $post_update );
        }

    }
    
    //Get youtube video id
    public function hadith_get_youtube_video_id($link){
    
        $video_id = explode("?v=", $link);
        $video_id = $video_id[1];
        return $video_id;
    }
    
    public function hadith_youtube_thumbnail($link){

        $video_id = explode("?v=", $link);
        $video_id = $video_id[1];
        $thumbnail="http://img.youtube.com/vi/".$video_id."/maxresdefault.jpg";
        //$thumbnail="https://i.ytimg.com/vi_webp/".$video_id."/maxresdefault.webp";
        //echo '<img src="'.$thumbnail.'"/>';   
        return $thumbnail;
    }
    
    public function hadith_insert_attachment_from_url($url, $parent_post_id = null) {

        $exists_path = array();

        $args = array( 'post_type' => 'attachment', 'orderby' => 'menu_order', 'order' => 'ASC', 'post_mime_type' => 'image/jpeg,image/jpg,image/png', 'posts_per_page' => -1 );
        $attachments = get_posts($args);

        if ($attachments) {
            foreach ( $attachments as $attachment ) {
               $exists_path[] = get_post_meta( $attachment->ID, 'exists_path', true );

             }
        }

        if( in_array($url, $exists_path) ) {
            foreach ( $attachments as $attachment ) {

               if( get_post_meta( $attachment->ID, 'exists_path', true ) == $url ){
                   return $attachment->ID;
                }

             }
        }

        if( !class_exists( 'WP_Http' ) ){
            include_once( ABSPATH . WPINC . '/class-http.php' );
        }

        $http = new WP_Http();
        $response = $http->request( $url );
        if( $response['response']['code'] != 200 ) {
            return false;
        }

        $upload = wp_upload_bits( basename($url), null, $response['body'] );
        if( !empty( $upload['error'] ) ) {
            return false;
        }

        $file_path = $upload['file'];
        $file_name = basename( $file_path );
        $file_type = wp_check_filetype( $file_name, null );
        $attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
        $wp_upload_dir = wp_upload_dir();

        $post_info = array(
            'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => $file_type['type'],
            'post_title'     => $attachment_title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        // Create the attachment
        $attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );

        // Include image.php
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Define attachment metadata
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

        // Assign metadata to attachment
        wp_update_attachment_metadata( $attach_id,  $attach_data );

        update_post_meta( $attach_id, 'exists_path', $url );

        return $attach_id;

    }
    
    public function hadith_get_youtube_video_info($videoid){

        //$videoid = ''; // change this
        $apikey = 'AIzaSyD_1fMxw4yeopZqqVS0VJOJjwI-KQCKTk4'; // change this

        $url = 'https://www.googleapis.com/youtube/v3/videos?id=' . $videoid . '&key=' . $apikey . '&part=snippet';

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);

        return $resp;

    }
    
    //Display results in the footer to check
    function hadith_footer_area(){
    
        $video_link = 'https://www.youtube.com/watch?v=f-w8Yn8LA3o';
        $hadith_youtube_thumb = $this->hadith_youtube_thumbnail($video_link);
        echo $hadith_youtube_thumb;
        //echo hadith_insert_attachment_from_url($hadith_youtube_thumb, $parent_post_id = null);

        /*$video_id = $this->hadith_get_youtube_video_id($video_link);
        $video_info = json_decode($this->hadith_get_youtube_video_info($video_id));
        echo '<pre>';
        print_r($video_info);
        echo '</pre>';
        $vtitle = $video_info->items[0]->snippet->title;
        echo $vtitle;*/
    }
}

new Hadith_Video_Post();