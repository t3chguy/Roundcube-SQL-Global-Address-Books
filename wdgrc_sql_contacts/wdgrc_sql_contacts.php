<?php

require_once(__DIR__ . '/wdgrc_sql_contacts_backend.php');
/**
 * Specialised Global Addressbook Contacts Class!
 *
 * @author Michael Daniel Telatynski <postmaster@webdevguru.co.uk>
 */
class wdgrc_sql_contacts extends rcube_plugin {

	public function init() {
		$this->add_hook('addressbooks_list', array($this, 'address_sources'));
		$this->add_hook('addressbook_get', array($this, 'get_address_book'));
		$this->load_config();
		$config = rcmail::get_instance()->config;
	    $sources= (array) $config->get('autocomplete_addressbooks', array());

	    foreach (array_merge(array_column($config->get('_sql_supportbook', array()), 0), array('domain', 'global')) as $v) {
		    if (!in_array($v, $sources)) { $sources[] = $v; }
	    }

	    $config->set('autocomplete_addressbooks', $sources);
	}

	private function touchbook($id, $name, $groups=false) {
		return array(
			'id'           => $id,
			'name'         => $name,
			'groups'       => $groups,
			'readonly'     => true,
			'autocomplete' => true,
		);
	}

	private function wlbl($id, $domain) {
		$rc = rcmail::get_instance();
		$cf = $rc->config->get('_sql_' . $id . '_read_allowed', array('*'));
		$fc = $rc->config->get('_sql_' . $id . '_read_hidden', array(''));

		if (in_array($domain, $fc)) { return false; }
		if ($cf === array('*') || in_array($domain, $cf)) { return true; }
		return false;

	}

	public function address_sources($p) {
		$rc     = rcmail::get_instance();
		$dm     = $rc->user->get_username('domain');
		$xc     = $rc->config;

		if (($gb = $xc->get('_sql_globalbook', false)) && $this->wlbl('gb', $dm)) {
			$p['sources']['global'] = $this->touchbook('global', $gb, $xc->get('_sql_globalbook_gp', true));
		}

		if (($db = $xc->get('_sql_domainbook', false)) && $this->wlbl('db', $dm)) {
			$p['sources']['domain'] = $this->touchbook('domain', $db);
		}

		if ($sb = $xc->get('_sql_supportbook', array())) {
			foreach ($sb as $csb) {
				$csbn = array_shift($csb);
				if (!in_array($dm, $csb)) { $p['sources'][$csbn]= $this->touchbook($csbn, $csbn);
				}
			}
		}

		return $p;
	}

	public function get_address_book($p) {

		if (in_array($p['id'], array_column(rcmail::get_instance()->config->get('_sql_supportbook', array()), 0))) {
			$p['instance'] = new wdgrc_sql_contacts_backend($p['id']);
		} elseif ($p['id'] === 'global') {
			$p['instance'] = new wdgrc_sql_contacts_backend('global');
			$p['instance']->groups = rcmail::get_instance()->config->get('_sql_globalbook_gp', true);
		} elseif ($p['id'] === 'domain') { $p['instance'] = new wdgrc_sql_contacts_backend('domain'); }

		return $p;
	}

}