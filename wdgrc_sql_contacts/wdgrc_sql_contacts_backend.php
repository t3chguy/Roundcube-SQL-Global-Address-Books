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
			$sql_arr['email'] = explode(',', $sql_arr['email']);
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
					$fc = rcmail::get_instance()->config->get('_sql_gb_data_hidden', array());
					if ($cf === array('*')) {
						$cf = array();
					} else { $x[] = 'domain IN (' . $db->array2list($cf) . ')'; }
					if ($this->filter)  { $x[] = '(' . $this->filter .')'; }
					if (count($fc) > 0) { $x[] = 'domain NOT IN (' . $db->array2list($fc) . ')'; }
					$x = count($x) > 0 ? (' WHERE ' . implode(' AND ', $x)):'';
					$db->query('SELECT * FROM global_addressbook' . $x);
					break;

				case 'domain':
					$x = $this->filter ? (' (' . $this->filter . ') AND '):' ';
					$db->query('SELECT * FROM global_addressbook WHERE' . $x . 'domain=?', rcmail::get_instance()->user->get_username('domain'));
					break;

				default:
					$d = rcmail::get_instance()->config->get('_sql_supportbook', array());
					$f = array_flip(wdgrc_sql_contacts::ac($d, 0));
					array_shift($z = $d[$f[$this->name]]);
					if ($this->filter) { $x[] = '(' . $this->filter .')'; }
					if (count($z) > 0) { $x[] = 'domain IN (' . $db->array2list($z) . ')'; }
					$x = count($x)> 0 ? (' WHERE ' . implode(' AND ', $x)):'';
					$db->query('SELECT * FROM global_addressbook' . $x);
			}

		} else {
			$x = $this->filter ? (' (' . $this->filter . ') AND '):' ';
			$db->query('SELECT * FROM global_addressbook WHERE' . $x . 'domain=?', $this->group_id);
		}

		while ($ret = $db->fetch_assoc()) {
			$ret['email'] = explode(',', $ret['email']);
			$this->result->add($ret);
		}
		return $this->result;

	}

	function list_groups($search = null, $mode=0) {
		if (!$this->groups) { return array(); }
		$rc = rcmail::get_instance();
		$cf = $rc->config->get('_sql_gb_data_allowed', array('*'));
		$fc = $rc->config->get('_sql_gb_data_hidden', array());

		if ($cf === array('*')) {
			$cf = array();
			$db = rcube::get_instance()->db;
			$db->query('SELECT domain FROM global_addressbook GROUP BY domain');
			while ($ret = $db->fetch_assoc()) {$cf[] = $ret['domain']; }
		}
		foreach (array_diff($cf, $fc) as $v) { $co[] = ['ID' => $v, 'name' => $v]; }
		return $co;

	}

	public function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array()) {
		if (!is_array($fields)) { $fields = array($fields); }
        if (!is_array($required) && !empty($required)) { $required = array($required); }


        $db = rcube::get_instance()->db;
        $where = $and_where = array();
        $mode = intval($mode);
        $WS = ' ';

        foreach ($fields as $idx => $col) {

        	if ($col == 'ID' || $col == $this->primary_key) {
    			$ids     = !is_array($value) ? explode(self::SEPARATOR, $value) : $value;
                $ids     = $db->array2list($ids, 'integer');
                $where[] = 'c.' . $this->primary_key.' IN ('.$ids.')';
                continue;
            } else if ($col == '*') {
        			$words = array();
        			foreach (explode($WS, rcube_utils::normalize_string($value)) as $word) {
        				switch ($mode) {
        					case 1: // Strict
        						$words[] = '(' . $db->ilike('words', $word . '%')
		                            . ' OR ' . $db->ilike('words', '%' . $WS . $word . $WS . '%')
		                            . ' OR ' . $db->ilike('words', '%' . $WS . $word) . ')';
        						break;

        					case 2: // Prefix
        						$words[] = '(' . $db->ilike('words', $word . '%')
                            		. ' OR ' . $db->ilike('words', '%' . $WS . $word . '%') . ')';
								break;

        					default: // Partial
        						$words[] = $db->ilike('words', '%' . $word . '%');
        						break;
        				}
        			}
        			$where[] = '(' . join(' AND ', $words) . ')';
        	} else {

        	}

        	/*foreach ($required as $col) {
	            $and_where[] = $db->quote_identifier($col).' <> '.$db->quote('');
	        }*/

        //file_put_contents('/var/www/test.log', print_r([$fields, $value, $where], true));
			/*if (!empty($where)) {
	            // use AND operator for advanced searches
	            $where = join(is_array($value) ? ' AND ' : ' OR ', $where);
	        }*/
	        /*if (!empty($and_where)) {
	            $where = ($where ? "($where) AND " : '') . join(' AND ', $and_where);
	        }*/

	        if (!empty($where)) {
	            $this->set_search_set($where);
	            if ($select)
	                $this->list_records(null, 0, $nocount);
	            else
	                $this->result = $this->count();
	        }

        }


		return $this->list_records();
	}
	function get_group($group_id) { return $this->groups ? array('ID' => $group_id, 'name' => $group_id) : null; }
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
