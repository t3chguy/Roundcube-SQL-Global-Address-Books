<?php

    class MultiBook_Domain extends MultiBook_Helper {

        public function list_records($cols=null, $subset=0) {
            $this->result = $this->count();

            $x = $this->filter ? (' (' . $this->filter . ') AND '):' ';
            $this->db->query("SELECT * FROM MultiBook WHERE {$x} domain=?",
                             $this->user->get_username('domain'));

            while ($ret = $this->db->fetch_assoc()) {
                $verfed = $this->checkCloak($ret);
                if ($verfed) $this->result->add($verfed);
            }
            return $this->result;

        }

    }