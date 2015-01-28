<?php

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

		//$config = rcmail::get_instance()->config;
		//$sources = (array) $config->get('autocomplete_addressbooks', array('sql'));
		//if (!in_array($this->abook_id, $sources)) {
		//	$sources[] = $this->abook_id;
		//	$config->set('autocomplete_addressbooks', $sources);
		//}
	}

	public function address_sources($p) {
		$this->load_config();
		$p['sources']['company'] = array(
			'id' => 'company',
			'name' => rcube::get_instance()->config->get('wdg_sql_name', 'Global Address Book'),
			'readonly' => true,
			'autocomplete' => true,
			'groups' => in_array(rcube::get_instance()->config->get('wdg_sql_mode', 4), array(2, 4), true),
		);
		return $p;

		if (rcube::get_instance()->config->get('wdg_sql_mode', 4) === 0) {
			foreach (rcube::get_instance()->config->get('wdg_sql_whitelist', array()) as $wl) {

			}
		}
	}

	public function get_address_book($p) {
		if ($p['id'] === 'company') {
			$p['instance'] = new wdg_sql_contacts_backend();
			return $p;
		}
		$rconfig = rcube::get_instance()->config;
		if ($rconfig->get('wdg_sql_mode', 1) === 4 && in_array($p['id'], $rconfig->get('wdg_sql_whitelist', array()), true) {
			$p['instance'] = new wdg_sql_contacts_backend($p['id']);
		}

		return $p;
	}

}