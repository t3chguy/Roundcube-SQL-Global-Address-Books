<?php

	abstract class MultiBook_Helper extends rcube_addressbook {

		public $name, $id, $result, $group_id, $groups = false;
		public $primary_key = 'ID';
		public $readonly = true;

		protected $cloak= array();
		protected $show = array();
		protected $hide = array();
		protected $user, $filter, $db;

		public function __construct(&$config, $name) {
			$this->user = rcmail::get_instance()->user;
			$this->db   = rcube::get_instance()->db;
			$this->id   = md5($name);
			$this->name = $name;
			$this->ready= true;

			$config['MultiBook'][$this->id] = $this;
		}

		public function reset() {
			$this->result = null;
			$this->filter = null;
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
			foreach ((array)$entry as $single) {
				$this->show[] = str_replace('%d', $this->user->get_username('domain'), $single);
			}
		}

		public function addHide($entry) {
			foreach ((array)$entry as $single) {
				$this->hide[] = str_replace('%d', $this->user->get_username('domain'), $single);
			}
		}

		public function addCloak($entry) {
			foreach ((array)$entry as $single) {
				$this->cloak[]= str_replace('%d', $this->user->get_username('domain'), $single);
			}
		}

		protected function checkCloak($ret) {
			$ret['email'] = explode(',', $ret['email']);

			foreach ($ret['email'] as $email) {
				$spl = explode('@', $email, 2);
				$arr = array( $email, $spl[0] . '@', $spl[1] );
				if (!!array_intersect( $arr, $this->cloak )) return false;
			}

			$names = explode(' ', $ret['name']);
			$ret['surname'] = array_pop($names);
			$ret['firsname']= implode(' ', $names);
			return $ret;
		}

		public function valid($email) {
			$spl = explode('@', $email, 2);
			$arr = array( $email, $spl[0] . '@', $spl[1] );

			if ( (count($this->show) &&
			     !array_intersect( $arr, $this->show ) ) ||
			    !!array_intersect( $arr, $this->hide ) ) {
				return FALSE;
			}

			return TRUE;
		}

		// Boilerplate //
		public function get_group($group_id) { return $this->groups ? array('ID' => $group_id, 'name' => $group_id) : null; }
		public function count() { return new rcube_result_set(1, ($this->list_page-1) * $this->page_size); }
		public function remove_from_group($group_id, $ids) { return false; }
		public function set_search_set($filter) { $this->filter = $filter; }
		public function rename_group($gid, $newname) { return $newname; }
		public function add_to_group($group_id, $ids) { return false; }
		public function get_search_set() { return $this->filter; }
		public function get_result() { return $this->result; }
		public function create_group($name) { return false; }
		public function delete_group($gid) { return false; }
		public function get_name() { return $this->name; }
		public function set_group($gid) {
	        $this->group_id = $gid;
	        $this->cache = null;
	    }
		// E/Boilerpl8 //

		public function get_record($id, $assoc=false) {

			$this->db->query('SELECT * FROM MultiBook WHERE `ID`=?', $id);
			if ($record = $this->db->fetch_assoc()) {
				$record['email'] = explode(',', $record['email']);
				$this->result = new rcube_result_set(1);
				$this->result->add($record);
			}

			return $assoc && $record ? $record : $this->result;

		}

		public function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array(), $where=array()) {
			if (!is_array($fields)) { $fields = array($fields); }
	        if (!is_array($required) && !empty($required)) { $required = array($required); }

	        ;
	        $mode = intval($mode);
        	$AS   = self::SEPARATOR;
			$WS   = ' ';

	        foreach ($fields as $idx => $col) {

	        	if ($col == 'ID' || $col == $this->primary_key) {
	    			$ids     = !is_array($value) ? explode(',', $value) : $value;
	                $ids     = $this->db->array2list($ids, 'integer');
	                $where[] = 'c.' . $this->primary_key.' IN ('.$ids.')';
	                continue;
	            } else if ($col == '*') {
	        			$words = array();
	        			foreach (explode($WS, $value) as $word) {
	        				switch ($mode) {
	        					case 1: // Strict
	        						$words[]='(' . $this->db->ilike('name', $word . '%')
			                            . ' OR ' . $this->db->ilike('email',$word . '%')
			                            . ' OR ' . $this->db->ilike('name', '%' . $WS . $word . $WS . '%')
			                            . ' OR ' . $this->db->ilike('email','%' . $WS . $word . $WS . '%')
			                            . ' OR ' . $this->db->ilike('name', '%' . $WS . $word)
			                            . ' OR ' . $this->db->ilike('email','%' . $WS . $word). ')';
	        						break;

	        					case 2: // Prefix
	        						$words[]='(' . $this->db->ilike('name', $word . '%')
	                            		. ' OR ' . $this->db->ilike('email',$word . '%')
	                            		. ' OR ' . $this->db->ilike('name', '%' . $WS . $word . '%')
	                            		. ' OR ' . $this->db->ilike('email','%' . $WS . $word . '%') . ')';
									break;

	        					default: // Partial
	        						$words[]='(' . $this->db->ilike('name', '%' . $word . '%')
	        						    . ' OR ' . $this->db->ilike('email','%' . $word . '%') . ')';
	        						break;
	        				}
	        			}
	        			$where[] = '(' . join(' AND ', $words) . ')';
	        	//} else {
	        	} elseif ($col !== 'firstname' && $col !== 'surname') {
	        		$val = is_array($value) ? $value[$idx] : $value;

	        		switch ($mode) {
	                    case 1: // strict
	                        $where[] = '(' . $this->db->quote_identifier($col) . ' = ' . $this->db->quote($val)
	                            . ' OR ' . $this->db->ilike($col, $val . $AS . '%')
	                            . ' OR ' . $this->db->ilike($col, '%' . $AS . $val . $AS . '%')
	                            . ' OR ' . $this->db->ilike($col, '%' . $AS . $val) . ')';
	                        break;
	                    case 2: // prefix
	                        $where[] = '(' . $this->db->ilike($col, $val . '%')
	                            . ' OR ' . $this->db->ilike($col, $AS . $val . '%') . ')';
	                        break;
	                    default: // partial
	                        $where[] = $this->db->ilike($col, '%' . $val . '%');
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

		public function list_groups($search = null, $mode=0) { return array(); }
		//abstract function list_records($cols=null, $subset=0);

	}

	require 'MultiBook.Global.class.php';
	require 'MultiBook.Domain.class.php';