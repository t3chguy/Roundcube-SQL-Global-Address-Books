<?php
ini_set('display_errors', 1);
/**
 * Web Development Guru Specialised Global Addressbook Contacts Class!
 *
 * @author Michael Daniel Telatynski
 */
class wdg_sql_contacts_backend extends rcube_addressbook {

	public $primary_key = 'ID';
	public $readonly = true;
	public $groups, $group_id, $mode;

	private $filter, $result, $name;

	public function __construct($name='company', $mode) {
		$this->groups= in_array($mode, array(2, 4), true);
		$this->ready = true;
		$this->mode  = $mode;
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
		if (empty($this->group_id) && $this->mode > 2) {
			$db->query('SELECT ID, name, firstname, surname, email FROM global_addressbook');
		} elseif ($this->mode === 1) {
			$xtra = array_reduce(rcube::get_instance()->config->get('wdg_sql_whitelist', array()), function ($carry, $item) {
				return $carry . ' OR domain=?'
			});
			$xtrb = array(array('SELECT ID, name, firstname, surname, email FROM global_addressbook WHERE domain=?' . $xtra, $this->group_id));
			call_user_func_array(array($db, 'query'), array_merge($xtrb, array_values(rcube::get_instance()->config->get('wdg_sql_whitelist', array()))));
			//$db->query('SELECT ID, name, firstname, surname, email FROM global_addressbook WHERE domain=?', $this->group_id);
		} else {
			$db->query('SELECT ID, name, firstname, surname, email FROM global_addressbook WHERE domain=?', $this->group_id);
		}
		while ($ret = $db->fetch_assoc()) { $this->result->add($ret); }
		return $this->result;
	}

	function list_groups($search = null, $mode=0) {
		if (!$this->groups) { return array(); }
		if ($this->mode === 2) {
			$arr = array_merge(array(
				rcube::get_instance()->config->get('wdg_sql_name', 'Global Address Book') => rcmail::get_instance()->user->get_username('domain')
			),  rcube::get_instance()->config->get('wdg_sql_whitelist', array()));
			foreach ($arr as $key => $val) {
				if (is_int($key)) { $key = $val; }
				$grps[] = array('ID' => $val, 'name' => $key);
			}
			return $grps;
		}
		$db = rcube::get_instance()->db;
		$db->query('SELECT domain FROM global_addressbook GROUP BY domain');
		while ($ret = $db->fetch_assoc()) { $grps[] = array('ID' => $ret['domain'], 'name' => $ret['domain']); }
		return $grps;

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
