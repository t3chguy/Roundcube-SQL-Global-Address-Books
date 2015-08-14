<?php

	class MultiBook_Backend extends rcube_addressbook {

		public $result, $group_id, $groups = false;
		public $primary_key = 'ID';
		public $readonly = true;

		private $filter, $spx;

		public function __construct($spx) {
			$this->groups= $spx->groups;
			$this->ready = true;
			$this->spx   = $spx;
		}

		public function reset() {
			$this->result = null;
			$this->filter = null;
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

		function get_group($group_id) { return $this->groups ? array('ID' => $group_id, 'name' => $group_id) : null; }
		public function count() { return new rcube_result_set(1, ($this->list_page-1) * $this->page_size); }
		public function list_records($cols=null, $subset=0) { return $this->spx->list_records($cols, $subset, $this); }
		public function list_groups($search = null, $mode=0) { return $this->spx->list_groups($search, $mode); }
		public function get_record($id, $assoc=false) { return $this->spx->get_record($id, $assoc, $this); }
		public function set_search_set($filter) { $this->filter = $filter; }
		public function get_search_set() { return $this->filter; }
		public function get_result() { return $this->result; }
		public function get_name() { return $this->name; }
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