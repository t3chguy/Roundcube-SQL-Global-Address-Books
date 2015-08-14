<?php

	class MultiBook_Global extends MultiBook_Helper {

		public function list_records($cols=null, $subset=0) {
			$this->result = $this->count();

			if (empty($this->group_id)) {
				$x = $this->filter ? ('WHERE (' . $this->filter . ')'):'';
				$this->db->query("SELECT * FROM MultiBook {$x}");
			} else {
				$x = $this->filter ? (' (' . $this->filter . ') AND '):'';
				$this->db->query("SELECT * FROM MultiBook WHERE {$x} domain=?", $this->group_id);
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

		function list_groups($search = null, $mode=0) {
			if (!$this->groups) { return array(); }
			if ($search) {
				switch (intval($mode)) {
		            case 1:
		                $x = $this->db->ilike('domain', $search);
		                break;
		            case 2:
		                $x = $this->db->ilike('domain', $search . '%');
		                break;
		            default:
		                $x = $this->db->ilike('domain', '%' . $search . '%');
	            }
	            $x = ' WHERE ' . $x . ' ';
			} else { $x = ' '; }

			$this->db->query("SELECT domain FROM MultiBook {$x} GROUP BY domain");
			while ($ret = $this->db->fetch_assoc()) {
				$cf[] = array( 'ID' => $ret['domain'], 'name' => $ret['domain'] );
			}
			return $cf;
		}

	}