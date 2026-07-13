{extends file='page.tpl'}

{block name='page_content'}
<div id="gigula-return-form-container" class="card card-block">
    {if $success}
        <div class="alert alert-success" role="alert">
            {l s='Vaše oznámení o odstoupení od smlouvy bylo úspěšně odesláno. Potvrzení o doručení jsme Vám obratem odeslali na Váš e-mail.' d='Modules.Gigulareturn.Shop'}
        </div>
    {else}
        {if !empty($errors)}
            <div class="alert alert-danger" role="alert">
                <ul>
                    {foreach from=$errors item=error}
                        <li>{$error}</li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        <div class="form-title-group" style="margin-bottom: 25px;">
            <h2 class="h1">{l s='Odstoupení od kupní smlouvy do 14 dnů' d='Modules.Gigulareturn.Shop'}</h2>
            <p class="text-muted">{$intro_text|escape:'html':'UTF-8'}</p>
        </div>

        <form action="{$action}" method="post" class="js-customer-form">
            <section>
                <div class="form-group row">
                    <label class="col-md-3 form-control-label required">
                        {l s='Kód / Číslo objednávky' d='Modules.Gigulareturn.Shop'}
                    </label>
                    <div class="col-md-6">
                        <input class="form-control" name="order_ref" type="text" value="{$order_ref}" required placeholder="Např. ABCDEFGHI">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 form-control-label required">
                        {l s='Váš e-mail' d='Modules.Gigulareturn.Shop'}
                    </label>
                    <div class="col-md-6">
                        <input class="form-control" name="customer_email" type="email" value="{$customer_email}" required placeholder="jmeno@domena.cz">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 form-control-label required">
                        {l s='Vracené zboží a množství' d='Modules.Gigulareturn.Shop'}
                    </label>
                    <div class="col-md-6">
                        <textarea class="form-control" name="returned_items" rows="4" required placeholder="Např. Tričko černé, velikost L - 1ks"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-9 offset-md-3">
                        <span class="custom-checkbox">
                            <label>
                                <input name="legal_ack" type="checkbox" value="1" required>
                                <span><i class="material-icons checkbox-checked">&#xE5CA;</i></span>
                                <strong>{l s='Prohlašuji, že toto odstoupení činím v zákonné lhůtě 14 dnů od převzetí zboží.' d='Modules.Gigulareturn.Shop'}</strong>
                            </label>
                        </span>
                    </div>
                </div>
            </section>

            <footer class="form-footer text-xs-left" style="margin-top: 20px;">
                <div class="row">
                    <div class="col-md-9 offset-md-3">
                        <button class="btn btn-primary" name="submit_return" type="submit">
                            {l s='Odeslat oznámení' d='Modules.Gigulareturn.Shop'}
                        </button>
                    </div>
                </div>
            </footer>
        </form>
    {/if}
</div>
{/block}