<?php

class usercss extends Module{

	public function __construct(){
		$this->name = 'usercss';
		$this->tab = 'back_office_features';
		$this->version = '1.0.0';
		$this->author = 'Jonathan Parisi';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->bootstrap = true;

		parent::__construct();

		/* Name and description shown in the section modules */

		$this->displayName = $this->l('Customer user CSS');
		$this->description = $this->l('Giver dig mulighed for at skrive custom CSS til alle brugere i prestashop backend');
	}

	public function install(){
		if (!parent::install()
			or !$this->registerHook('displayAdminOrder')
			or !$this->installDb())

			return false;
		return true;
	}

	public function unistall(){

		return parent::unistall();

	}
	public function installDb()
	{}

	public function hookDisplayAdminOrder($params){
		$shop_id = $this->context->shop->id;

		$profileId = 1;
		$profileId = $this->context->employee->id_profile;

		$this->html = '<style>'.Configuration::get('CUSTOM_CSS_'.$profileId).'</style>';


		return $this->html;

	}


	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name))
		{

			$profileId = $this->context->employee->id_profile;
			$profiles = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'profile');
			foreach ($profiles as $row){
				Configuration::updateValue('CUSTOM_CSS_'.$row["id_profile"], Tools::getValue('CUSTOM_CSS_'.$row["id_profile"]));
			}
			$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		return $output.$this->displayForm();
	}


	public function displayForm()
	{
		// Get default language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');



		$profileId = $this->context->employee->id_profile;
		$profiles = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'profile AS pp LEFT JOIN '._DB_PREFIX_.'profile_lang AS ppl ON ppl.id_profile = pp.id_profile');

		foreach ($profiles as $row){
			$fields_form[$row["id_profile"]]['form'] = array(
				'legend' => array(
					'title' => $this->l('CSS for '.$row["name"]),
				),
				'input' => array(
					array(
						'type' => 'textarea',
						'label' => $this->l('Custom CSS'),
						'name' => 'CUSTOM_CSS_'.$row["id_profile"],
						'rows' => 10,
						'required' => false
					)
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'class' => 'btn btn-default pull-right'
				)
			);
		}

		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		$profileId = $this->context->employee->id_profile;
		$profiles = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'profile');

		// Load current value
		foreach ($profiles as $row){
			$helper->fields_value['CUSTOM_CSS_'.$row["id_profile"]] = Configuration::get('CUSTOM_CSS_'.$row["id_profile"]);
		}

		return $helper->generateForm($fields_form);
	}

}

?>