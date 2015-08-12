<?php
//ini_set('display_errors', 1);
require_once(__DIR__ . '/sql_global_backend.php');
/**
 * Specialised Global Addressbook Contacts Class!
 *
 * Roundcube Plugin to create an Address Book from list of users in the SQL View.
 * Currently Natively Supporting:
 *  + iRedMail [Aliases Supported]
 *
 * @author Michael Daniel Telatynski <postmaster@webdevguru.co.uk>
 * @copyright 2015 Web Development Guru
 * @license http://bit.ly/16ABH2R
 * @license MIT
 *
 * @version 2.5.0
 */
class sql_global_addressbooks extends rcube_plugin {

	public $task = 'mail|addressbook';

	public function init() {
		$this->add_hook('addressbooks_list', array($this, 'address_sources'));
		$this->add_hook('addressbook_get',   array($this, 'get_address_book'));
		$this->load_config();

		$rcmail = rcmail::get_instance();
		$config = $rcmail->config;
		$domain = $rcmail->user->get_username('domain');
	    $sources= (array)$config->get('autocomplete_addressbooks', array());

	    foreach ($config->get('ABX', array()) as $hash => $book) {
	    	$sources[] = $hash;
	    }
	    $config->set('autocomplete_addressbooks', $sources);
	}

	private function touchbook($id, $name, $groups=false) {
		return array(
			'id'           => $id,
			'name'         => $name,
			'groups'       => $groups,
			'readonly'     => true,
			'autocomplete' => true,
		);
	}

	public static function ac($arr, $id) {

		if (function_exists('array_column')) {
			return array_column($arr, $id);
		}

		$ret = array();
		foreach ($arr as $val) {
			if (isset($val[$id])) {
				$ret[] = $val[$id];
			}
		}

		return $ret;

	}

	public function address_sources($p) {
		$rc     = rcmail::get_instance();

		foreach ($rc->config->get('ABX', array()) as $hash => $book) {
	    	if ($book->valid($rc->user->get_username())) {
		    	$p['sources'][$book->id] = $this->touchbook(
            		$hash, $book->name, $book->groups
				);
	    	}
	    }

		return $p;
	}

	public function get_address_book($p) {

		$ABX = rcmail::get_instance()->config->get('ABX', FALSE);
		if ($ABX && isset($ABX[$p['id']])) {
			$p['instance'] = $ABX[$p['id']];
		}

		/*if ($p['id'] === 'global') {
			$p['instance'] = new sql_global_backend('global');
			$p['instance']->groups = rcmail::get_instance()->config->get('_sql_globalbook_gp', true);
		} elseif (in_array($p['id'], self::ac(rcmail::get_instance()->config->get('_sql_supportbook', array()), 0))) {
			$p['instance'] = new sql_global_backend($p['id']);
		} elseif ($p['id'] === 'domain') { $p['instance'] = new sql_global_backend('domain'); }*/

		return $p;
	}

}