<?php

	abstract class MultiBook_Helper {

		protected $filter = array();
		protected $show = array();
		protected $hide = array();
		protected $db;

		public $name, $id, $result, $groups = FALSE;

		public function __construct(&$config, $name) {
			$this->db   = rcube::get_instance()->db;
			$this->id   = md5($name);
			$this->name = $name;
			$this->ready= true;

			$config['MultiBook'][$this->id] = $this;
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
				$this->filter += $entry;
			} else {
				$this->filter[]= $entry;
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

		public function get_record($id, $assoc, $child) {

			$this->db->query('SELECT * FROM MultiBook WHERE `ID`=?', $id);
			if ($record = $this->db->fetch_assoc()) {
				$record['email'] = explode(',', $record['email']);
				$child->result = new rcube_result_set(1);
				$child->result->add($record);
			}

			return $assoc && $record ? $record : $child->result;

		}

		public function list_groups($search, $mode) { return array(); }
		abstract function list_records($cols, $subset, $child);

	}



	class MultiBook_Global extends MultiBook_Helper {

		public function list_records($cols, $subset, $child) {
			$child->result = $child->count();

			if (empty($child->group_id)) {
				$this->db->query('SELECT * FROM MultiBook');
			} else {
				$x = $child->filter ? (' (' . $child->filter . ') AND '):' ';
				$this->db->query("SELECT * FROM MultiBook WHERE {$x} domain=?", $child->group_id);
			}

			while ($ret = $this->db->fetch_assoc()) {
				$ret['email'] = explode(',', $ret['email']);
				//$names = explode(' ', $ret['name']);
				//$ret['surname'] = array_push($names);
				//$ret['firsname']= implode(' ', $names);
				$child->result->add($ret);
			}
			return $child->result;

		}

		function list_groups($search, $mode) {
			if (!$this->groups) { return array(); }
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

			$this->db->query("SELECT domain FROM MultiBook {$x} GROUP BY domain");
			while ($ret = $rc->db->fetch_assoc()) {
				$cf[] = array( 'ID' => $ret['domain'], 'name' => $ret['domain'] );
			}
			return $cf;
		}

	}

	class MultiBook_Domain extends MultiBook_Helper {

		public function list_records($cols, $subset, $child) {}

	}