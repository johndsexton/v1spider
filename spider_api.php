<?php
/**
 * Spider API to get a list of links from a given URL
 * @param $url (string) required
 * @return json data array
 */
include('spider_class.php');

$url = (isset($_GET['url'])) ? strip_tags($_GET['url']) :'https://news.ycombinator.com';
$crawl = new spider($url);

// store response to array
$arr = array();
$arr['error'] = $crawl->get_error();
$arr['links'] = $crawl->get_list();
$arr['cinfo'] = $crawl->get_info();

// return json data
echo json_encode($arr, true);

?>
Enter file contents here
