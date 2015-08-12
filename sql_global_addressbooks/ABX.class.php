<?php

	abstract class ABX extends rcube_addressbook {

		protected $mask = array();
		protected $show = array();
		protected $hide = array();
		protected $db;

		private $filter, $result;

		public $name, $id, $group_id;
		public $primary_key = 'ID';
		public $readonly = true;
		public $groups = FALSE;

		public function __construct(&$config, $name) {
			$this->db   = rcube::get_instance()->db;
			$this->id   = md5($name);
			$this->name = $name;
			$this->ready= true;

			$config['ABX'][$this->id] = $this;
		}

		protected function addSQL($query, $Found=array()) {
			$this->db->query($query);
			while ($ret = $this->db->fetch_assoc()) {
				$Found[]= $ret[0];
			}
			return $Found;
		}

		public function addShowSQL($query) {
			$toAdd = $this->addSQL($query);
			$this->addShow($toAdd);
			return count($toAdd);
		}

		public function addHideSQL($query) {
			$toAdd = $this->addSQL($query);
			$this->addHide($toAdd);
			return count($toAdd);
		}

		public function addShow($entry) {
			if (is_array($entry)) {
				$this->show += $entry;
			} else {
				$this->show[]= $entry;
			}
		}

		public function addHide($entry) {
			if (is_array($entry)) {
				$this->hide += $entry;
			} else {
				$this->hide[]= $entry;
			}
		}

		public function addFilter($entry) {
			if (is_array($entry)) {
				$this->mask += $entry;
			} else {
				$this->mask[]= $entry;
			}
		}

		public function valid($email) {
			$arr = array( $email, end(explode('@', $email, 2)) );

			if ( (count($this->show) &&
			     !array_intersect( $arr, $this->show ) ) ||
			    !!array_intersect( $arr, $this->hide ) ) {
				return FALSE;
			}

			return TRUE;
		}

		abstract public function list_records();

		//
		//
		//

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

		public function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array()) {
			if (!is_array($fields)) { $fields = array($fields); }
	        if (!is_array($required) && !empty($required)) { $required = array($required); }


	        $db = rcube::get_instance()->db;
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

		function list_groups($search = null, $mode=0) {
			if (!$this->groups) { return array(); }
			$rc = rcmail::get_instance();
			$cf = $rc->config->get('_sql_gb_data_allowed', array('*'));
			$fc = $rc->config->get('_sql_gb_data_hidden', array());

			if ($search) {
				switch (intval($mode)) {
		            case 1:
		                $x = $rc->db->ilike('domain', $search);
		                break;
		            case 2:
		                $x = $rc->db->ilike('domain', $search . '%');
		                break;
		            default:
		                $x = $rc->db->ilike('domain', '%' . $search . '%');
	            }
	            $x = ' WHERE ' . $x . ' ';
			} else { $x = ' '; }

			if ($cf === array('*')) {
				$cf = array();
				$rc->db->query("SELECT domain FROM global_addressbook {$x} GROUP BY domain");
				while ($ret = $rc->db->fetch_assoc()) {$cf[] = $ret['domain']; }
			}

			$co = array();
			foreach (array_diff($cf, $fc) as $v) { $co[] = array('ID' => $v, 'name' => $v); }
	        //file_put_contents('/var/www/test.log', print_r([$co, $search, $mode, $this->groups, $this->name, $this->group_id], true));
			return $co;

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



	class ABX_Global extends ABX {

		public function list_records($cols=null, $subset=0) {
			$this->result = $this->count();

			if (empty($this->group_id)) {
				$this->db->query('SELECT * FROM global_addressbook');
			} else {
				$x = $this->filter ? (' (' . $this->filter . ') AND '):' ';
				$this->db->query("SELECT * FROM global_addressbook WHERE {$x} domain=?", $this->group_id);
			}

			while ($ret = $this->db->fetch_assoc()) {
				$ret['email'] = explode(',', $ret['email']);
				//$names = explode(' ', $ret['name']);
				//$ret['surname'] = array_push($names);
				//$ret['firsname']= implode(' ', $names);
				$this->result->add($ret);
			}
			return $this->result;

		}

	}

	class ABX_Domain extends ABX {

		public function list_records() {}

	}