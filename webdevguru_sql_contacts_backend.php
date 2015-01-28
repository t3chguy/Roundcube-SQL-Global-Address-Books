<?php

/**
 * Web Development Guru Specialised Global Addressbook Contacts Class!
 *
 * @author Michael Daniel Telatynski
 */
class wdg_sql_contacts_backend extends rcube_addressbook {

	public $primary_key = 'ID';
	public $readonly = true;
	public $groups = true;
	public $group_id;

	private $filter, $result, $name;

	public function __construct($name='company') {
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
		$db->query('SELECT ID, name, firstname, surname, email FROM global_addressbook WHERE `ID`=?', $id);
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
			$db->query('SELECT ID, name, firstname, surname, email FROM global_addressbook');
		} else {
			$db->query('SELECT ID, name, firstname, surname, email FROM global_addressbook WHERE domain=?', $this->group_id);
		}
		while ($ret = $db->fetch_assoc()) { $this->result->add($ret); }
		return $this->result;
	}

	function list_groups($search = null, $mode=0) {
		$db = rcube::get_instance()->db;
		$db->query('SELECT domain FROM global_addressbook GROUP BY domain');
		while ($ret = $db->fetch_assoc()) { $grps[] = ['ID' => $ret['domain'], 'name' => $ret['domain']]; }
		return $grps;

	}
	function get_group($group_id) { return $this->groups ? ['ID' => $group_id, 'name' => $group_id] : null; }
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
