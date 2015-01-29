<?php

/**
 * Specialised Global Addressbook Contacts Class!
 *
 * @author Michael Daniel Telatynski <postmaster@webdevguru.co.uk>
 */
class wdgrc_sql_contacts_backend extends rcube_addressbook {

	public $primary_key = 'ID';
	public $readonly = true;
	public $groups, $group_id;

	private $filter, $result, $name;

	public function __construct($name) {
		$this->ready = true;
		$this->name  = $name;
	}

	public function get_name() { return $this->name; }
	public function set_search_set($filter) { $this->filter = $filter; }
	public function get_search_set() { return $this->filter; }

	public function reset() {
		$this->result = null;
		$this->filter = null;
	}

	public function get_record($id, $assoc=false) {
		$db = rcube::get_instance()->db;
		$db->query('SELECT * FROM global_addressbook WHERE `ID`=?', $id);
		if ($sql_arr = $db->fetch_assoc()) {
			$this->result = new rcube_result_set(1);
			$this->result->add($sql_arr);
		}

		return $assoc && $record ? $record : $this->result;

	}

	public function list_records($cols=null, $subset=0) {
		$this->result = $this->count();
		$db = rcube::get_instance()->db;

		if (empty($this->group_id)) {

			switch ($this->name) {
				case 'global':
					$cf = rcmail::get_instance()->config->get('_sql_gb_data_allowed', array('*'));
					$fc = rcmail::get_instance()->config->get('_sql_gb_data_hidden', array(''));
					if ($cf === array('*')) {
						$cd = '';
						$cf = array();
					} else { $cd = ' domain IN ("", ' . str_repeat(', ?', count($cf)) . ') AND'; }
					$q  = 'SELECT * FROM global_addressbook WHERE' . $cd . ' domain NOT IN (""' . str_repeat(', ?', count($fc)) . ')';
					call_user_func_array(array($db, 'query'), array_merge(array($q), $cf, $fc));
					break;

				case 'domain':
					$db->query('SELECT * FROM global_addressbook WHERE domain=?', rcmail::get_instance()->user->get_username('domain'));
					break;

				default:
					$d = rcmail::get_instance()->config->get('_sql_supportbook', array());
					$f = array_flip(array_column($d, 0));
					array_shift($x = $d[$f[$this->name]]);
					$q = 'SELECT * FROM global_addressbook WHERE domain IN (""' . str_repeat(', ?', count($x)) . ')';
					call_user_func_array(array($db, 'query'), array_merge(array($q), $x));
			}

		} else {$db->query('SELECT * FROM global_addressbook WHERE domain=?', $this->group_id); }

		while ($ret = $db->fetch_assoc()) { $this->result->add($ret); }
		return $this->result;

	}

	function list_groups($search = null, $mode=0) {
		if (!$this->groups) { return array(); }
		$rc = rcmail::get_instance();
		$cf = $rc->config->get('_sql_gb_data_allowed', array('*'));
		$fc = $rc->config->get('_sql_gb_data_hidden', array(''));

		if ($cf === array('*')) {
			$cf = array();
			$db = rcube::get_instance()->db;
			$db->query('SELECT domain FROM global_addressbook GROUP BY domain');
			while ($ret = $db->fetch_assoc()) {$cf[] = $ret['domain']; }
		}
		foreach (array_diff($cf, $fc) as $v) { $co[] = ['ID' => $v, 'name' => $v]; }
		return $co;

	}

	function get_group($group_id) { return $this->groups ? array('ID' => $group_id, 'name' => $group_id) : null; }
	public function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array()) { return $this->list_records(); }
	public function count() { return new rcube_result_set(1, ($this->list_page-1) * $this->page_size); }
	public function get_result() { return $this->result; }
	public function set_group($gid) {
        $this->group_id = $gid;
        $this->cache = null;
    }
	function create_group($name) { return false; }
	function delete_group($gid) { return false; }
	function rename_group($gid, $newname) { return $newname; }
	function add_to_group($group_id, $ids) { return false; }
	function remove_from_group($group_id, $ids) { return false; }

}
