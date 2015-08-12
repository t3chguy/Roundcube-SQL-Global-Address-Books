<?php

	abstract class ABX {

		protected $show = array();
		protected $hide = array();

		protected $name, $db;

		public function __construct($name) {
			$this->db   = rcube::get_instance()->db;
			$this->name = $name;
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
			return length($toAdd);
		}

		public function addHideSQL($query) {
			$toAdd = $this->addSQL($query);
			$this->addHide($toAdd);
			return length($toAdd);
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

		public function valid($email) {
			$domain = explode('@', $email, 2);
			if (length($this->show)) {
				if (in_array($domain[1], $this->true, TRUE) ||
				    in_array($email,     $this->true, TRUE) ) {
					return TRUE;
				}
				return FALSE;
			} elseif (length($this->hide)) {
				if (in_array($domain[1], $this->hide, TRUE) ||
				    in_array($email,     $this->hide, TRUE) ) {
					return FALSE;
				}
			}
			return TRUE;
		}

		abstract public function get();

	}

	class ABX_Global extends ABX {



	}