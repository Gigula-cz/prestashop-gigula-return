<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class GigulaReturn extends Module
{
    public function __construct()
    {
        $this->name = 'gigulareturn';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'gigula.cz';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Gigula - Odstoupení od smlouvy');
        $this->description = $this->l('Legislativní formulář pro snadné vrácení zboží do 14 dnů s potvrzením pro zákazníka a konfigurací v administraci.');
    }

    public function install()
    {
        return parent::install() 
            && $this->registerHook('displayFooter') 
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('displayOrderDetail')
            && Configuration::updateValue('GIGULA_RETURN_EMAIL', Configuration::get('PS_SHOP_EMAIL'))
            && Configuration::updateValue('GIGULA_RETURN_SUBJECT_ADMIN', 'Odstoupení od smlouvy (14 dnů) - {order_ref}')
            && Configuration::updateValue('GIGULA_RETURN_SUBJECT_CUST', 'Potvrzení přijetí odstoupení od smlouvy')
            && Configuration::updateValue('GIGULA_RETURN_INTRO_TEXT', 'Pomocí tohoto formuláře můžete snadno a rychle splnit zákonnou možnost elektronického odstoupení od smlouvy.');
    }

    public function uninstall()
    {
        return Configuration::deleteByName('GIGULA_RETURN_EMAIL')
            && Configuration::deleteByName('GIGULA_RETURN_SUBJECT_ADMIN')
            && Configuration::deleteByName('GIGULA_RETURN_SUBJECT_CUST')
            && Configuration::deleteByName('GIGULA_RETURN_INTRO_TEXT')
            && parent::uninstall();
    }

    // Administrační rozhraní v Back Office
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $email = strval(Tools::getValue('GIGULA_RETURN_EMAIL'));
            $subj_admin = strval(Tools::getValue('GIGULA_RETURN_SUBJECT_ADMIN'));
            $subj_cust = strval(Tools::getValue('GIGULA_RETURN_SUBJECT_CUST'));
            $intro = strval(Tools::getValue('GIGULA_RETURN_INTRO_TEXT'));

            if (empty($email) || !Validate::isEmail($email)) {
                $output .= $this->displayError($this->l('Zadejte platnou e-mailovou adresu.'));
            } else {
                Configuration::updateValue('GIGULA_RETURN_EMAIL', $email);
                Configuration::updateValue('GIGULA_RETURN_SUBJECT_ADMIN', $subj_admin);
                Configuration::updateValue('GIGULA_RETURN_SUBJECT_CUST', $subj_cust);
                Configuration::updateValue('GIGULA_RETURN_INTRO_TEXT', $intro, true);
                $output .= $this->displayConfirmation($this->l('Nastavení bylo úspěšně uloženo.'));
            }
        }

        return $output . $this->renderForm();
    }

    public function renderForm()
    {
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fieldsForm[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Konfigurace odstoupení od smlouvy (gigula.cz)'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('E-mail pro příjem oznámení (E-shop)'),
                    'name' => 'GIGULA_RETURN_EMAIL',
                    'required' => true,
                    'desc' => $this->l('Na tento e-mail budou chodit žádosti o vrácení zboží od zákazníků.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Předmět e-mailu pro e-shop'),
                    'name' => 'GIGULA_RETURN_SUBJECT_ADMIN',
                    'required' => true,
                    'desc' => $this->l('Předmět e-mailu doručeného administrátorovi. Zástupný kód {order_ref} bude nahrazen kódem objednávky.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Předmět e-mailu pro zákazníka'),
                    'name' => 'GIGULA_RETURN_SUBJECT_CUST',
                    'required' => true,
                    'desc' => $this->l('Zákazník obdrží toto potvrzení okamžitě po odeslání (klíčový legislativní krok).')
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Úvodní text nad formulářem'),
                    'name' => 'GIGULA_RETURN_INTRO_TEXT',
                    'cols' => 40,
                    'rows' => 4,
                    'required' => false,
                    'desc' => $this->l('Tento text se zobrazí zákazníkům hned pod nadpisem formuláře na webu.')
                )
            ),
            'submit' => array(
                'title' => $this->l('Uložit nastavení'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submit' . $this->name;

        // Načtení stávajících hodnot z databáze
        $helper->fields_value['GIGULA_RETURN_EMAIL'] = Configuration::get('GIGULA_RETURN_EMAIL') ?: Configuration::get('PS_SHOP_EMAIL');
        $helper->fields_value['GIGULA_RETURN_SUBJECT_ADMIN'] = Configuration::get('GIGULA_RETURN_SUBJECT_ADMIN') ?: 'Odstoupení od smlouvy (14 dnů) - {order_ref}';
        $helper->fields_value['GIGULA_RETURN_SUBJECT_CUST'] = Configuration::get('GIGULA_RETURN_SUBJECT_CUST') ?: 'Potvrzení přijetí odstoupení od smlouvy';
        $helper->fields_value['GIGULA_RETURN_INTRO_TEXT'] = Configuration::get('GIGULA_RETURN_INTRO_TEXT') ?: 'Pomocí tohoto formuláře můžete snadno a rychle splnit zákonnou možnost elektronického odstoupení od smlouvy.';

        return $helper->generateForm($fieldsForm);
    }

    // Zobrazení odkazu v patičce
    public function hookDisplayFooter($params)
    {
        try {
            $link = $this->context->link->getModuleLink('gigulareturn', 'form');
            $this->context->smarty->assign(array(
                'gigula_return_link' => $link
            ));
            
            return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
        } catch (\Exception $e) {
            return '';
        }
    }

    // Zobrazení odkazu v sekci Můj účet
    public function hookDisplayCustomerAccount($params)
    {
        try {
            $link = $this->context->link->getModuleLink('gigulareturn', 'form');
            $this->context->smarty->assign(array(
                'gigula_return_link' => $link
            ));
            
            return $this->display(__FILE__, 'views/templates/hook/customer_account.tpl');
        } catch (\Exception $e) {
            return '';
        }
    }

    // Zobrazení tlačítka přímo v detailu objednávky
    public function hookDisplayOrderDetail($params)
    {
        try {
            if (!isset($params['order']) || !is_object($params['order'])) {
                return '';
            }
            $order = $params['order'];
            $link = $this->context->link->getModuleLink('gigulareturn', 'form', array('order_ref' => $order->reference));
            $this->context->smarty->assign(array(
                'gigula_return_link' => $link
            ));
            
            return $this->display(__FILE__, 'views/templates/hook/order_detail.tpl');
        } catch (\Exception $e) {
            return '';
        }
    }
}