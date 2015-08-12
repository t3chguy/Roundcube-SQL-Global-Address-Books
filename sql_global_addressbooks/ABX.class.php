<?php

	abstract class ABX {

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

	}



	class ABX_Global extends ABX {

		public function list_records($cols, $subset, $child) {
			$this->result = $child->count();

			if (empty($child->group_id)) {
				$this->db->query('SELECT * FROM global_addressbook');
			} else {
				$x = $child->filter ? (' (' . $child->filter . ') AND '):' ';
				$this->db->query("SELECT * FROM global_addressbook WHERE {$x} domain=?", $child->group_id);
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

		public function list_records($cols=null, $subset=0) {}

	}