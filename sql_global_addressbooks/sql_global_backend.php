<?php

/**
 * Specialised Global Addressbook Contacts Class Backend!
 *
 * @author Michael Daniel Telatynski <postmaster@webdevguru.co.uk>
 * @copyright 2015 Web Development Guru
 * @license http://bit.ly/16ABH2R
 * @license MIT
 *
 * @version 2.5.0
 */
class sql_global_backend extends rcube_addressbook {

	public $group_id, $groups = false;
	public $primary_key = 'ID';
	public $readonly = true;

	private $filter, $result, $name, $db;

	public function __construct($name) {
		$this->ready = true;
		$this->name  = $name;
		$dbtype = rcmail::get_instance()->config->get('_database_type', '');
		if (empty($dbtype)) {
			// use roundcube native database
			$this->db = rcube::get_instance()->db;
		} else {
			// setup connection to external database
			$dbhost = rcmail::get_instance()->config->get('_database_host', '');
			$dbport = rcmail::get_instance()->config->get('_database_port', '');
			$dbuser = rcmail::get_instance()->config->get('_database_user', 'roundcube');
			$dbpass = rcmail::get_instance()->config->get('_database_pass', '');
			$dbname = rcmail::get_instance()->config->get('_database_name', 'roundcubemail');
			if (!empty($dbport)) { $dbport = ':'.$dbport; }
			$dsn = $dbtype.'://'.$dbuser.':'.$dbpass.'@'.$dbhost.$dbport.'/'.$dbname;
			$this->db = rcube_db::factory($dsn);
		}
	}

	public function get_name() { return $this->name; }
	public function set_search_set($filter) { $this->filter = $filter; }
	public function get_search_set() { return $this->filter; }

	public function reset() {
		$this->result = null;
		$this->filter = null;
	}

	public function get_record($id, $assoc=false) {
		$db = $this->db;
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
		$db = $this->db;

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
					$f = array_flip(sql_global_addressbooks::ac($d, 0));
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
			//$names = explode(' ', $ret['name']);
			//$ret['surname'] = array_push($names);
			//$ret['firsname']= implode(' ', $names);
			$this->result->add($ret);
		}
		return $this->result;

	}

	function list_groups($search = null, $mode=0) {
		if (!$this->groups) { return array(); }
		$rc = rcmail::get_instance();
		$cf = $rc->config->get('_sql_gb_data_allowed', array('*'));
		$fc = $rc->config->get('_sql_gb_data_hidden', array());

		if ($search) {
			switch (intval($mode)) {
	            case 1:
	                $x = $db->ilike('domain', $search);
	                break;
	            case 2:
	                $x = $db->ilike('domain', $search . '%');
	                break;
	            default:
	                $x = $db->ilike('domain', '%' . $search . '%');
            }
            $x = ' WHERE ' . $x . ' ';
		} else { $x = ' '; }

		if ($cf === array('*')) {
			$cf = array();
			$db->query('SELECT domain FROM global_addressbook' . $x . 'GROUP BY domain');
			while ($ret = $db->fetch_assoc()) {$cf[] = $ret['domain']; }
		}

		$co = array();
		foreach (array_diff($cf, $fc) as $v) { $co[] = array('ID' => $v, 'name' => $v); }
        //file_put_contents('/var/www/test.log', print_r([$co, $search, $mode, $this->groups, $this->name, $this->group_id], true));
		return $co;

	}

	public function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array()) {
		if (!is_array($fields)) { $fields = array($fields); }
        if (!is_array($required) && !empty($required)) { $required = array($required); }


        $db = $this->db;
        $where = array();
        $mode = intval($mode);
        $WS = ' ';

        foreach ($fields as $idx => $col) {

        	if ($col == 'ID' || $col == $this->primary_key) {
    			$ids     = !is_array($value) ? explode(',', $value) : $value;
                $ids     = $db->array2list($ids, 'integer');
                $where[] = 'c.' . $this->primary_key.' IN ('.$ids.')';
                continue;
            } else if ($col == '*') {
        			$words = array();
        			foreach (explode($WS, rcube_utils::normalize_string($value)) as $word) {
        				switch ($mode) {
        					case 1: // Strict
        						$words[]='(' . $db->ilike('name', $word . '%')
		                            . ' OR ' . $db->ilike('email',$word . '%')
		                            . ' OR ' . $db->ilike('name', '%' . $WS . $word . $WS . '%')
		                            . ' OR ' . $db->ilike('email','%' . $WS . $word . $WS . '%')
		                            . ' OR ' . $db->ilike('name', '%' . $WS . $word)
		                            . ' OR ' . $db->ilike('email','%' . $WS . $word). ')';
        						break;

        					case 2: // Prefix
        						$words[]='(' . $db->ilike('name', $word . '%')
                            		. ' OR ' . $db->ilike('email',$word . '%')
                            		. ' OR ' . $db->ilike('name', '%' . $WS . $word . '%')
                            		. ' OR ' . $db->ilike('email','%' . $WS . $word . '%') . ')';
								break;

        					default: // Partial
        						$words[]='(' . $db->ilike('name', '%' . $word . '%')
        						    . ' OR ' . $db->ilike('email','%' . $word . '%') . ')';
        						break;
        				}
        			}
        			$where[] = '(' . join(' AND ', $words) . ')';
        	//} else {
        	} elseif ($col !== 'firstname' && $col !== 'surname') {
        		$val = is_array($value) ? $value[$idx] : $value;

        		switch ($mode) {
                    case 1: // strict
                        $where[] = '(' . $db->quote_identifier($col) . ' = ' . $db->quote($val)
                            . ' OR ' . $db->ilike($col, $val . $AS . '%')
                            . ' OR ' . $db->ilike($col, '%' . $AS . $val . $AS . '%')
                            . ' OR ' . $db->ilike($col, '%' . $AS . $val) . ')';
                        break;
                    case 2: // prefix
                        $where[] = '(' . $db->ilike($col, $val . '%')
                            . ' OR ' . $db->ilike($col, $AS . $val . '%') . ')';
                        break;
                    default: // partial
                        $where[] = $db->ilike($col, '%' . $val . '%');
                }
        	}

	        if (!empty($where)) {
	            $this->set_search_set(join(is_array($value) ? ' AND ' : ' OR ', $where));
	            /*if ($select) {
	                $this->list_records(null, 0, $nocount);
	            } else { $this->result = $this->count(); */
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
