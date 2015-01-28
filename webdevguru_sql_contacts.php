<?php
ini_set('display_errors', 1);
require_once(__DIR__ . '/webdevguru_sql_contacts_backend.php');

/**
 * Web Development Guru Specialised Global Addressbook Contacts Class!
 *
 * @author Michael Daniel Telatynski
 */
class webdevguru_sql_contacts extends rcube_plugin {

	public function init() {
		$this->add_hook('addressbooks_list', array($this, 'address_sources'));
		$this->add_hook('addressbook_get', array($this, 'get_address_book'));
		$this->load_config();

		//$config = rcmail::get_instance()->config;
		//$sources = (array) $config->get('autocomplete_addressbooks', array('sql'));
		//if (!in_array($this->abook_id, $sources)) {
		//	$sources[] = $this->abook_id;
		//	$config->set('autocomplete_addressbooks', $sources);
		//}
	}

	public function address_sources($p) {
		$p['sources'][rcmail::get_instance()->user->get_username('domain')] = array(
			'id' => rcmail::get_instance()->user->get_username('domain'),
			'name' => rcube::get_instance()->config->get('wdg_sql_name', 'Global Address Book'),
			'readonly' => true,
			'autocomplete' => true,
			'groups' => in_array(rcube::get_instance()->config->get('wdg_sql_mode', 4), array(2, 4), true)
		);

		if (rcube::get_instance()->config->get('wdg_sql_mode', 4) === 0) {
			foreach (rcube::get_instance()->config->get('wdg_sql_whitelist', array()) as $k => $wl) {
				if ($wl != rcmail::get_instance()->user->get_username('domain')) {
					$p['sources'][$wl] = array(
						'id' => $wl,
						'name' => $k,
						'readonly' => true,
						'autocomplete' => true,
						'groups' => false
					);
				}
			}
		}
		file_put_contents('/var/www/test.log', print_r([$p], true));
		return $p;
	}

	public function get_address_book($p) {
		if ($p['id'] === rcmail::get_instance()->user->get_username('domain')) {
			$p['instance'] = new wdg_sql_contacts_backend(rcmail::get_instance()->user->get_username('domain'), rcube::get_instance()->config->get('wdg_sql_mode', 4));
			return $p;
		}
		file_put_contents('/var/www/test.log', print_r([$p], true));
		$rconfig = rcube::get_instance()->config;
		if ($rconfig->get('wdg_sql_mode', 4) === 0 && in_array($p['id'], $rconfig->get('wdg_sql_whitelist', array()), true)) {
			$p['instance'] = new wdg_sql_contacts_backend($p['id'], 0);
		}

		return $p;
	}

}