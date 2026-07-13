<?php
class GigulaReturnFormModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $success = false;
        $errors = array();
        
        $order_ref = Tools::getValue('order_ref', '');
        
        $customer_email = '';
        if ($this->context->customer->isLogged()) {
            $customer_email = $this->context->customer->email;
        }

        if (Tools::isSubmit('submit_return')) {
            $order_ref = trim(Tools::getValue('order_ref'));
            $email = trim(Tools::getValue('customer_email'));
            $items = trim(Tools::getValue('returned_items'));
            $ack = Tools::getValue('legal_ack');

            if (empty($order_ref) || empty($email) || empty($items) || !$ack) {
                $errors[] = $this->module->l('Vyplňte všechna povinná pole a potvrďte 14denní lhůtu.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = $this->module->l('Zadejte platný e-mail.');
            } else {
                // Načtení dat z konfigurace modulu v databázi
                $shop_email = Configuration::get('GIGULA_RETURN_EMAIL') ?: Configuration::get('PS_SHOP_EMAIL');
                $shop_name = Configuration::get('PS_SHOP_NAME');
                
                $subject_admin_template = Configuration::get('GIGULA_RETURN_SUBJECT_ADMIN') ?: 'Odstoupení od smlouvy (14 dnů) - {order_ref}';
                $subject_admin = str_replace('{order_ref}', $order_ref, $subject_admin_template);
                
                $subject_cust = Configuration::get('GIGULA_RETURN_SUBJECT_CUST') ?: 'Potvrzení přijetí odstoupení od smlouvy';

                $vars = array(
                    '{order_ref}' => htmlspecialchars($order_ref),
                    '{customer_email}' => htmlspecialchars($email),
                    '{returned_items}' => nl2br(htmlspecialchars($items)),
                    '{shop_name}' => htmlspecialchars($shop_name)
                );

                // 1. Odeslání e-mailu pro e-shop
                $res_admin = $this->sendEmail($shop_email, $subject_admin, $vars, 'return_admin', $email);

                // 2. Okamžité potvrzení pro zákazníka
                $res_cust = $this->sendEmail($email, $subject_cust, $vars, 'return_customer', $shop_email);

                if (!$res_admin || !$res_cust) {
                    PrestaShopLogger::addLog('GigulaReturn: Chyba při odesílání e-mailů. Status Admin: ' . ($res_admin ? 'OK' : 'CHYBA') . ', Status Zákazník: ' . ($res_cust ? 'OK' : 'CHYBA'), 3);
                }

                $success = true;
            }
        }

        $intro_text = Configuration::get('GIGULA_RETURN_INTRO_TEXT') ?: 'Pomocí tohoto formuláře můžete snadno a rychle splnit zákonnou možnost elektronického odstoupení od smlouvy.';

        $this->context->smarty->assign(array(
            'success' => $success,
            'errors' => $errors,
            'order_ref' => $order_ref,
            'customer_email' => $customer_email,
            'intro_text' => $intro_text,
            'action' => $this->context->link->getModuleLink('gigulareturn', 'form')
        ));

        $this->setTemplate('module:gigulareturn/views/templates/front/form.tpl');
    }

    /**
     * Bezpečné odeslání e-mailu s automatickým nouzovým fallbackem na PHP mail()
     */
    private function sendEmail($to, $subject, $vars, $template_name, $customer_email = null)
    {
        $lang_id = (int)$this->context->language->id;
        $shop_email = Configuration::get('GIGULA_RETURN_EMAIL') ?: Configuration::get('PS_SHOP_EMAIL');
        $shop_name = Configuration::get('PS_SHOP_NAME') ?: 'E-shop';
        $template_path = _PS_MODULE_DIR_ . 'gigulareturn/mails/';

        // Dynamická kontrola a tvorba chybějících jazykových složek (Fallback)
        $iso = Language::getIsoById($lang_id);
        if (!$iso) {
            $iso = 'cs';
        }
        $target_dir = $template_path . $iso . '/';
        if (!file_exists($target_dir)) {
            @mkdir($target_dir, 0755, true);
        }
        
        // Pokud v cílové složce chybí šablony, zkopírujeme tam české jako univerzální fallback
        if (!file_exists($target_dir . $template_name . '.html')) {
            $source_dir = $template_path . 'cs/';
            if (file_exists($source_dir . $template_name . '.html')) {
                @copy($source_dir . $template_name . '.html', $target_dir . $template_name . '.html');
                @copy($source_dir . $template_name . '.txt', $target_dir . $template_name . '.txt');
                @copy($source_dir . 'index.php', $target_dir . 'index.php');
            }
        }

        // Pokus o odeslání přes PrestaShop Mail::Send
        $result = false;
        try {
            $result = Mail::Send(
                $lang_id,
                $template_name,
                $subject,
                $vars,
                $to,
                null,
                $shop_email,
                $shop_name,
                null,
                null,
                $template_path,
                false,
                null,
                null,
                $customer_email ?: $shop_email
            );
        } catch (Exception $e) {
            PrestaShopLogger::addLog('GigulaReturn výjimka při odesílání: ' . $e->getMessage(), 3);
        }

        // NOUZOVÝ PLÁN: Pokud PrestaShop selhal, použijeme přímou PHP funkci mail()
        if (!$result) {
            $html_file = $target_dir . $template_name . '.html';
            $txt_file = $target_dir . $template_name . '.txt';
            
            $html_content = '';
            if (file_exists($html_file)) {
                $html_content = file_get_contents($html_file);
                foreach ($vars as $key => $val) {
                    $html_content = str_replace($key, $val, $html_content);
                }
            }
            
            $txt_content = '';
            if (file_exists($txt_file)) {
                $txt_content = file_get_contents($txt_file);
                foreach ($vars as $key => $val) {
                    $txt_content = str_replace($key, $val, $txt_content);
                }
            }

            if (!empty($html_content)) {
                $boundary = md5(uniqid(time()));
                
                // Sestavení hlaviček pro spolehlivé doručení (UTF-8, SPF Friendly)
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "From: =?UTF-8?B?" . base64_encode($shop_name) . "?= <" . $shop_email . ">\r\n";
                if ($customer_email) {
                    $headers .= "Reply-To: <" . $customer_email . ">\r\n";
                }
                $headers .= "Content-Type: multipart/alternative; boundary=\"" . $boundary . "\"\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

                // Multipart tělo (TXT + HTML)
                $body = "--" . $boundary . "\r\n";
                $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $txt_content . "\r\n\r\n";
                
                $body .= "--" . $boundary . "\r\n";
                $body .= "Content-Type: text/html; charset=UTF-8\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $html_content . "\r\n\r\n";
                $body .= "--" . $boundary . "--";

                $subject_encoded = "=?UTF-8?B?" . base64_encode($subject) . "?=";
                $result = @mail($to, $subject_encoded, $body, $headers);
                
                PrestaShopLogger::addLog('GigulaReturn: Nativní Mail::Send selhal, odesláno přes PHP mail() zálohu. Stav: ' . ($result ? 'ÚSPĚCH' : 'CHYBA'), 2);
            }
        }

        return $result;
    }
}