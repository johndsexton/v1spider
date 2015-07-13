<?php
/**
 * Class for spider search crawlling
 * 
 * @param $url (string) required
 */
class spider
{
	private $url;
	private $host;
	private $path;
	private $base;
	private $page;
	private $list;
	private $error;
	private $curlinfo;
	
	public function __construct($source_url)
	{
		$this->url = $source_url;
		$this->list = $this->error = $this->curlinfo = array();
		$this->host = $this->get_host();
		$this->path = $this->get_path();
		
		// get page links
		if ($this->get_url()) {
			$this->base = $this->get_base();
			$this->get_links();
		}
	}
	
	/**
	 * Public getters
	 */
	
	public function get_list() {
		return $this->list;
	}
	
	public function get_error() {
		return $this->error;
	}
	
	public function get_info() {
		return $this->curlinfo;
	}
	
	/**
	 * Get the base href if exists
	 */
	private function get_base() {
		if (preg_match('/\<base\shref\s?=\s?["\']?([^"\'\>\s]+)/', $this->page, $match)) {
			return rtrim($match[1],'/').'/';
		}
		return false;
	}
	
	/**
	 * Get the host name
	 */
	private function get_host() {
		if (preg_match('/(http[s]?:)?([\/]{0,2})([^\/\?\#]+)/', $this->url, $match)) {
			if (empty($match[1])) $match[1] = 'http:';
			if (empty($match[2])) $match[2] = '//';
			return $match[1] . $match[2] . $match[3];
		} else {
			return $this->url;
		}
	}
	
	/**
	 * Get the path uri
	 */
	private function get_path() {
		$path = str_replace($this->host, '', $this->url);
		$filter = strpbrk($path, '?#');
		if (false !== $filter) $path = str_replace($filter, '', $path);
		if (false !== strrpos($path, '.')) {
			$path = substr($path, 0, strrpos($path, '/'));
		}
		if (empty($path) || $path=='/') return '/';
		return $path.'/';
	}
	
	/**
	 * Get the response from a given URL
	 * 
	 * @return response (string)
	 * Credit to: http://flwebsites.biz/posts/how-fix-curl-error-60-ssl-issue
	 */
	private function get_url()
	{
		// create a new cURL resource
		$ch = curl_init();
		
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
		// Check for & set SSL CA info | disable SSL verification
		if (file_exists(dirname(__FILE__).'/cacert.pem')) {
			curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		
		// grab URL and store the response
		$this->page = curl_exec($ch);
		
		// check for curl errors
		if ($errno = curl_errno($ch)) {
			$this->add_error('CURL ERROR ('.$errno.') '.curl_strerror($errno).' @URL '.$this->url);
		}
		
		// Get the curl info
		$this->curlinfo = curl_getinfo($ch);
		
		// close cURL resource, and free up system resources
		curl_close($ch);
		
		return ($errno>0)?false:true;
	}
	
	/**
	 * Add error message to error log
	 */
	private function add_error($err) {
		$this->error[] = $err;
	}
	
	/**
	 * Get a list of hyperlinks from a string
	 * @return array of strings
	 */
	private function get_links()
	{
		$found = false; // hyperlinks found boolean
		$anchor = '/\<a\s+([^\>]+)\>/i'; // regexp pattern for opening anchor tag
		$hyperlink = '/href\s*=\s*["|\']?([^"\'\s]+)/i'; // regexp pattern for hyperlink
		
		// perform regexp & return all anchors found
		if (preg_match_all($anchor, $this->page, $matches, PREG_PATTERN_ORDER) > 0) {
			foreach ($matches[1] as $match) {
				// check for href attribute & not mailto: or inline javascript function
				if (preg_match($hyperlink, $match, $link) && strtolower(substr($link[1],0,11))!='javascript:' && strtolower(substr($link[1],0,7))!='mailto:') {
					if (strtolower(substr($link[1], 0, 4)) != 'http' && substr($link[1], 0, 2)!='//') {
						$link[1] = (false === $this->base) ? $this->host . $this->path . ltrim($link[1], '/') : $this->base . ltrim($link[1], '/');
					}
					if (strtolower(substr($link[1], 0, 4)) != 'http' && substr($link[1], 0, 2)=='//') {
						$link[1] = 'http:' . $link[1];
					}
					$this->list[] = $link[1];
					$found = true;
				}
			}
		}
		return $found;
	}
	
}
?>
Enter file contents here
