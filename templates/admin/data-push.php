<?php

if(isset($_POST['id'])){
    $absolute_path = __FILE__;
    $path_to_file = explode( 'wp-content', $absolute_path );
    $path_to_wp = $path_to_file[0];
    require_once( $path_to_wp.'/wp-load.php' );
    require_once $path_to_wp.'/wp-content/plugins/cXense-4-WordPress/class-api.php';
    $return = CxenseAPI::pingCrawler($_POST['id']);
    error_log('Pushar ID:'.$_POST['id'].' | '. $return->url);
}elseif(isset($_POST['getPosts'])){
    $absolute_path = __FILE__;
    $path_to_file = explode( 'wp-content', $absolute_path );
    $path_to_wp = $path_to_file[0];
    require_once( $path_to_wp.'/wp-load.php' );
    require_once $path_to_wp.'/wp-content/plugins/cXense-4-WordPress/class-api.php';
    echo CxenseAPI::getPosts();
}