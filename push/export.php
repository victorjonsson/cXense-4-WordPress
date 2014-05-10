<?php
/**
 * File used to export URL:s for WordPress posts and saves them to file urls.txt
 *
 * Usage:
 *  $ php ./export.php sport    # extract all urls from category "sport"
 *  $ php ./export 231          # extract all urls from category with id 231
 *  $ php ./export              # extract all urls
 *
 * - This script will extract 500 posts per second (to reduce allocated resources)
 *   until all URLs of the posts in the declared category is extracted
 *
 * - Running this script several times will not create duplications of the extracted
 *
 */

// Short hand function for outputting text
function _p($str, $err=false) { fwrite($err ? STDOUT:STDERR, $str . PHP_EOL); }

// Setup server vars expected to exist by wordpress
$_SERVER['DOCUMENT_ROOT'] = getcwd();
$_SERVER['SERVER_PROTOCOL'] = '';
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Bootstrap WP
require_once( current(explode('wp-content', __FILE__)) .'/wp-load.php' );

// Do we have a category
$cat_name = current( array_splice($argv, -1) );
$cat_id = null;
if( basename($cat_name) != 'export.php' ) {
    if( is_numeric($cat_name) ) {
        $cat_id = $cat_name;
    } else {
        $cat = get_term_by('name', $cat_name, 'category');
        if( !$cat ) {
            _p('It does not exist any category with given name '.escapeshellarg($cat_name), true);
            die;
        } else {
            $cat_id = $cat->term_id;
        }
    }
}

$export_file = __DIR__.'/urls.txt';
$extracted_urls = file_exists($export_file) ? explode('\n', file_get_contents($export_file)) : array();
array_flip($extracted_urls); // has entries as keys to prevent duplication when running script several times
$offset = 0;
$limit = 500;

_p('----> About to extract URLs '.( $cat_id ? 'from category '.$cat_id:''));

while( $posts = get_posts(array('posts_per_page' => $limit, 'offset'=> $offset, 'category' => $cat_id)) ) {
    _p('* About to extract URLs from '.$offset.' to '.($offset+$limit));
    foreach($posts as $p) {
        if( $url = get_permalink($p) )
            $extracted_urls[$url] = 1;
    }
    $offset += $limit;
    sleep(1);
}

// Write to file
file_put_contents($export_file, implode(PHP_EOL, array_keys($extracted_urls)));

global $wpdb;
if( isset($wpdb->num_queries) ) {
    _p('(Database queries: '.$wpdb->num_queries.')');
}
_p('----> Finished extracting URLs to '.$export_file);