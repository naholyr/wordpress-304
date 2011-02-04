<?php
/*
Plugin Name: 304 - Not modified
Plugin Description: Let your blog return 304 Not Modified HTTP status, to optimize client-side caching
*/

class WPHeader304Manager
{

	protected static $instance = null;
	public static function get()
	{
		if (is_null(self::$instance))
		{
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	public function initialize()
	{
		if (function_exists('add_filter') && function_exists('add_action'))
		{
			// Set valid "Last-Modified" header
			add_filter('wp_headers', array($this, 'setLastModifiedHeader'), 10, 2);
			// Check request, compare modified dates, and throw a 304 status if possible
			add_action('send_headers', array($this, 'check304'));
		}
	}

	protected $headers = null;

	function setLastModifiedHeader(array $headers, WP $wp)
	{
		global $wpdb;
		$this->lastModified = null;
		if (empty($wp->query_vars['error']) && empty($wp->query_vars['feed'])) {
			// Retrieve last post modified, not depending on type (includes standard posts, pages, but also any future type of post)
			$wp_last_modified_date = $wpdb->get_var("SELECT GREATEST(post_modified_gmt, post_date_gmt) d FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY d DESC LIMIT 1");
			$wp_last_modified_date = max($wp_last_modified_date, get_lastcommentmodified('GMT'));
			$wp_last_modified = mysql2date('D, d M Y H:i:s', $wp_last_modified_date, 0) . ' GMT';
			$headers['Last-Modified'] = $wp_last_modified;
			$headers['ETag'] = '"' . md5($wp_last_modified) . '"';
			$this->headers = $headers;
		}
		return $headers;
	}

	function check304(WP $wp)
	{
		if (!is_array($this->headers) || !isset($this->headers['Last-Modified'])) {
			return; // No response header
		}
		$wp_last_modified = strtotime($this->headers['Last-Modified']);
		if (!$wp_last_modified) {
			return; // Invalid response header
		}
		$client_last_modified_date = empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? '' : trim($_SERVER['HTTP_IF_MODIFIED_SINCE']);
		if (!$client_last_modified_date) {
			return; // No request header
		}
		$client_last_modified = strtotime($client_last_modified_date);
		if (!$client_last_modified) {
			return; // Invalid request header
		}
		if ($client_last_modified >= $wp_last_modified) {
			$protocol = $_SERVER["SERVER_PROTOCOL"];
			if ($protocol != 'HTTP/1.1' && $protocol != 'HTTP/1.0') {
				$protocol = 'HTTP/1.0';
			}
			header('$protocol 304 Not Modified', true, 304);
			foreach ($this->headers as $header => $value) {
				header("$header: $value");
			}
			exit(0);
		}
	}
}

WPHeader304Manager::get()->initialize();
