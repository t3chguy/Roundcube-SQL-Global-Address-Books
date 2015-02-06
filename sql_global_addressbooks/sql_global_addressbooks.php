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
	    $sources= (array) $config->get('autocomplete_addressbooks', array());

	    if ($this->is_enabled('domain')) { $x[] = 'domain'; }
	    if ($this->is_enabled('global')) { $x[] = 'global'; }
	    foreach ($config->get('_sql_supportbook', array()) as $z) {
	    	$c = array_shift($z);
	    	if (!in_array($domain, $z, true) && !in_array($c, $sources)) { $sources[] = $c; }
	    }
	    foreach (array('domain', 'global') as $v) {
	    	if ($this->is_enabled($v) && !in_array($v, $sources)){ $sources[] = $v; }
	    }

	    $config->set('autocomplete_addressbooks', $sources);
	}

	private function is_enabled($book) {

		$rc = rcmail::get_instance();

		if ($rc->config->get('_sql_' . $book . 'book_name', false)) {
			return $this->wlbl(substr($book, 0, 1) . 'b', $rc->user->get_username('domain'));
		}

		return false;
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

	private function wlbl($id, $domain) {
		$rc = rcmail::get_instance();
		$cf = $rc->config->get('_sql_' . $id . '_read_allowed', array('*'));
		$fc = $rc->config->get('_sql_' . $id . '_read_hidden',  array());

		if (in_array($domain, $fc)) { return false; }
		if ($cf === array('*') || in_array($domain, $cf)) { return true; }
		return false;

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
		$dm     = $rc->user->get_username('domain');
		$xc     = $rc->config;

		if (($gb = $xc->get('_sql_globalbook_name', false)) && $this->wlbl('gb', $dm)) {
			$p['sources']['global'] = $this->touchbook('global', $gb, $xc->get('_sql_globalbook_gp', true));
		}

		if (($db = $xc->get('_sql_domainbook_name', false)) && $this->wlbl('db', $dm)) {
			$p['sources']['domain'] = $this->touchbook('domain', $db);
		}

		if ($sb = $xc->get('_sql_supportbook_list', array())) {
			foreach ($sb as $csb) {
				$csbn = array_shift($csb);
				if (!in_array($dm, $csb)) { $p['sources'][$csbn]= $this->touchbook($csbn, $csbn);
				}
			}
		}

		return $p;
	}

	public function get_address_book($p) {

		if ($p['id'] === 'global') {
			$p['instance'] = new sql_global_backend('global');
			$p['instance']->groups = rcmail::get_instance()->config->get('_sql_globalbook_gp', true);
		} elseif (in_array($p['id'], self::ac(rcmail::get_instance()->config->get('_sql_supportbook', array()), 0))) {
			$p['instance'] = new sql_global_backend($p['id']);
		} elseif ($p['id'] === 'domain') { $p['instance'] = new sql_global_backend('domain'); }

		return $p;
	}

}