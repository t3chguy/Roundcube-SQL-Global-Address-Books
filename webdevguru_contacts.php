<?php

require_once(__DIR__ . '/webdevguru_contacts_backend.php');

/**
 * Web Development Guru Specialised Global Addressbook Contacts Class!
 *
 * @author Michael Daniel Telatynski
 */
class webdevguru_contacts extends rcube_plugin {
	private $abook_id = 'webdevguru';
	private $abook_name = 'Global Address Book';

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
		$p['sources']['company'] = array(
			'id' => 'company',
			'name' => 'Global Address Book',
			'readonly' => true,
			'autocomplete' => true,
			'groups' => true,
		);
		return $p;
	}

	public function get_address_book($p) {
		if ($p['id'] === 'company') {
			$p['instance'] = new wdg_contacts_backend();
		}

		return $p;
	}

}