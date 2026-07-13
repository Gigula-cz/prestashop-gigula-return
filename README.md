Gigula.cz Odstoupení od smlouvy (gigulareturn)

Bezpečný, ultra lehký a legislativně neprůstřelný open-source modul pro elektronické odstoupení od smlouvy do 14 dnů na platformě PrestaShop (1.7.x a 8.x). Vyvinuto s láskou k české e-commerce komunitě týmem gigula.cz.

🇨🇿 Proč tento modul potřebujete?

Evropská legislativa (tzv. „tlačítková novela“) ukládá e-shopům přísné podmínky pro odstoupení od smlouvy do 14 dnů. Běžný kontaktní formulář nebo statické PDF ke stažení již při kontrole České obchodní inspekce (ČOI) nemusí obstát.

Zákazník musí mít možnost odstoupit elektronicky a ihned po odeslání obdržet automatické potvrzení na trvalém nosiči dat (e-mailu).

Klíčové vlastnosti modulu:

Kompletní administrace (Back Office): Přímo v administraci modulu nastavíte e-mail příjemce (e-shopu), předměty zpráv a úvodní texty bez zásahu do kódu.

Automatické potvrzení pro zákazníka: Splňuje klíčový legislativní krok – okamžité odeslání kopie žádosti zákazníkovi.

Inteligentní Reply-To hlavičky: E-maily procházejí přísnými SPF/DMARC filtry, odesílatel je váš e-shop. V poštovním klientovi (např. Thunderbird, Outlook) ale stačí kliknout na "Odpovědět" a píšete rovnou zákazníkovi.

Nouzový mailer fallback: Pokud na hostingu selže standardní odesílání přes jádro PrestaShopu, modul automaticky přepne na nativní PHP mailer, aby e-mail za každou cenu dorazil.

Žádná zátěž pro e-shop: ZIP má pouze ~14 KB. Žádné zbytečné SQL dotazy, žádné zpomalení webu.

🚀 Instalace

Stáhněte si instalační ZIP balíček z gigula.cz (případně si zabalte složku gigulareturn z tohoto repozitáře).

V administraci PrestaShopu přejděte do sekce Moduly > Správce modulů (Modules > Module Manager).

Vpravo nahoře klikněte na Nahrát modul (Upload a module) a vyberte stažený ZIP.

Po úspěšné instalaci klikněte na Konfigurovat (Configure) a nastavte požadované e-maily a texty.

Přejděte do Nástroje > Výkon (Advanced Parameters > Performance) a klikněte na Vyčistit mezipaměť (Clear cache), aby se správně vykreslily odkazy v šabloně.

⚙️ Kompatibilita

PrestaShop: 1.7.0.0 až 1.7.8.x & 8.0.x až 8.2.x (a novější)

PHP: 7.1 až 8.2+

Šablona: Plně kompatibilní s výchozí šablonou Classic i jakýmikoliv zakázkovými tématy (díky standardním hookům displayFooter, displayCustomerAccount a displayOrderDetail).

📝 Licence & Podpora

Tento modul je uvolněn jako open-source pod licencí GNU GPLv3. Můžete jej zcela zdarma používat, upravovat a nasazovat na weby svých klientů.

Pokud se vám naše práce líbí, budeme rádi za hvězdičku (star) ⭐ u tohoto repozitáře nebo sdílení na LinkedIn.

Potřebujete odbornou instalaci nebo rozvoj e-shopu?

Pokud nechcete ztrácet čas konfigurací, naši specialisté vám modul odborně nainstalují a otestují přímo na vašem webu za fixní poplatek 990 Kč bez DPH. Napište nám na marketing@gigula.cz nebo navštivte náš web www.gigula.cz.

🇬🇧 English Description

A lightweight, secure, and legally compliant open-source PrestaShop module for electronic contract withdrawal (14-day return form) compatible with PrestaShop 1.7.x and 8.x.

Key Features:

Back Office Configuration: Easily set up recipient email, email subjects, and intro texts directly in the module configuration.

Instant Customer Confirmation: Automatically sends an immediate copy of the withdrawal request to the customer (required by EU law).

Smart Reply-To Headers: Emails are SPF/DMARC safe. Clicking "Reply" in your email client automatically addresses the customer.

Emergency Mailer Fallback: Automatically switches to native PHP mailer if PrestaShop's core mailing system fails on your hosting.

Installation:

Download the ZIP file from gigula.cz or package the gigulareturn folder from this repo.

Upload it via PrestaShop Module Manager.

Configure your settings and Clear Cache in Advanced Parameters > Performance.
