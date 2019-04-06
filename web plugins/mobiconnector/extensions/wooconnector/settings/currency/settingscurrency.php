<?php
$currency_code_options = get_woocommerce_currencies();
foreach ( $currency_code_options as $code => $name ) {
    $currency_code_options[ $code ] = $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')';
}
$currenkey = strtolower(get_woocommerce_currency());
$currencys = get_option('wooconnector_currency_settings');
if(!empty($currencys)){
    $currencys = unserialize($currencys);
    if(!empty($currencys[$currenkey])){
        $keyvalue = $currencys[$currenkey]['currency'];
        $woocommercecurrency = get_woocommerce_currency();
        if($keyvalue != $woocommercecurrency){
            unset($currencys);
            delete_option('wooconnector_currency_settings');
        }else{
            unset($currencys[$currenkey]);
        }
    }
}
$currenpostion = get_option( 'woocommerce_currency_pos' );
$symbols = WooConnectorListSymbolCurrency();
?>
<?php require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/tab.php'); ?>
<div class="wrap wooconnector-settings">
    <h1><?php echo __('Settings Currency','wooconnector')?></h1>
    <?php WooConnectorShowNoticeAdmin(); ?>
	<form method="POST" class="wooconnector-setting-form" action="?page=wooconnector" id="settings-form">
		<input type="hidden" name="wootask" value="savesettingcurrency"/>		
		<div id="woo-settings-body" class="wooconnector-full-width">
            <div id="woo-body" >
				<div id="woo-body-content">		
                    <div class="form-group">
                        <a class="btn btn-primary wooconnector-primary" id="wooconnector-add-currency">Add New</a>
                    </div>			
					<div class="form-group">
						<table id="wooconnector-table-currency">
                            <thead>
                                <tr>
                                    <th><?php echo __('Currency','wooconnector')?></th>
                                    <th><?php echo __('Rate / 1'.get_woocommerce_currency_symbol(WooConnectorGetBaseCurrency()),'wooconnector')?></th>
                                    <th><?php echo __('Currency position','wooconnector')?></th>
                                    <th><?php echo __('Thousand separator','wooconnector')?></th>
                                    <th><?php echo __('Decimal separator','wooconnector')?></th>
                                    <th><?php echo __('Number of decimals','wooconnector')?></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="wooconnector-table-currency-tr" data-index='1' data-currency="<?php echo strtolower(WooConnectorGetBaseCurrency()); ?>">
                                    <td>
                                        <span class="wooconnector-currency-settings-span"><?php echo $currency_code_options[WooConnectorGetBaseCurrency()];?></span>
                                        <input readonly type="hidden" class="wooconnector-currency-settings wooconnector-currency-currency" id="wooconnector_currency-<?php echo strtolower(WooConnectorGetBaseCurrency());?>" name="wooconnector_currency_settings[<?php echo strtolower(WooConnectorGetBaseCurrency());?>][currency]" value="<?php echo WooConnectorGetBaseCurrency();?>" />
                                    </td>
                                    <td>
                                        <span class="wooconnector-currency-settings-span">1</span>
                                        <input readonly type="hidden" class="wooconnector-base wooconnector-currency-settings wooconnector-currency-rate" id="wooconnector_currency-<?php echo strtolower(WooConnectorGetBaseCurrency());?>-rate" name="wooconnector_currency_settings[<?php echo strtolower(WooConnectorGetBaseCurrency());?>][rate]" value="1" /></td>
                                    <td>
                                        <span class="wooconnector-currency-settings-span"><?php echo WooconnectorChangePosition($currenpostion,strtolower(WooConnectorGetBaseCurrency()));?></span>
                                        <input readonly type="hidden" class="wooconnector-currency-settings wooconnector-currency-position" id="wooconnector_currency-<?php echo strtolower(WooConnectorGetBaseCurrency());?>-position" name="wooconnector_currency_settings[<?php echo strtolower(WooConnectorGetBaseCurrency());?>][position]" value="<?php echo $currenpostion; ?>" />
                                    </td>
                                    <td>
                                        <span class="wooconnector-currency-settings-span"><?php echo wc_get_price_thousand_separator();?></span>
                                        <input readonly type="hidden" class="wooconnector-base wooconnector-currency-settings wooconnector-currency-tseparator" id="wooconnector_currency-<?php echo strtolower(WooConnectorGetBaseCurrency());?>-tseparator" name="wooconnector_currency_settings[<?php echo strtolower(WooConnectorGetBaseCurrency());?>][thousand_separator]" value="<?php echo wc_get_price_thousand_separator();?>" /></td>
                                    <td>
                                        <span class="wooconnector-currency-settings-span"><?php echo wc_get_price_decimal_separator();?></span>
                                        <input readonly type="hidden" class="wooconnector-base wooconnector-currency-settings wooconnector-currency-dseparator" id="wooconnector_currency-<?php echo strtolower(WooConnectorGetBaseCurrency());?>-dseparator" name="wooconnector_currency_settings[<?php echo strtolower(WooConnectorGetBaseCurrency());?>][decimal_separator]" value="<?php echo wc_get_price_decimal_separator();?>" /></td>
                                    <td>
                                        <span class="wooconnector-currency-settings-span"><?php echo wc_get_price_decimals();?></span>
                                        <input readonly type="hidden" class="wooconnector-base wooconnector-currency-settings wooconnector-currency-ndecima" id="wooconnector_currency-<?php echo strtolower(WooConnectorGetBaseCurrency());?>-ndecima" name="wooconnector_currency_settings[<?php echo strtolower(WooConnectorGetBaseCurrency());?>][number_of_decimals]" value="<?php echo wc_get_price_decimals();?>" /></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <?php
                                    if(!empty($currencys)){
                                        $index = 2;
                                        foreach($currencys as $key => $value){
                                ?>
                                    <tr class="wooconnector-table-currency-tr" data-index='<?php echo $index; ?>' data-shower="hidden" data-currency="<?php echo $key; ?>">
                                        <td>
                                            <span class="wooconnector-currency-settings-span"><?php echo $currency_code_options[strtoupper($value['currency'])];?></span>
                                            <select id="wooconnector_currency-<?php echo $key; ?>" class="wooconnector-hidden wooconnector-currency-settings wooconnector-currency-currency" name="wooconnector_currency_settings[<?php echo $key; ?>][currency]">
                                                <option value="-1">Default Selected</option>
                                                <?php
                                                    foreach($currency_code_options as $keyc => $valuec){
                                                ?>
                                                    <option <?php if($value['currency'] == $keyc){echo 'selected="selected"';}else{echo '';} ?> value="<?php echo $keyc; ?>"><?php echo $valuec; ?></option>
                                                <?php
                                                    }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <span class="wooconnector-currency-settings-span"><?php echo $value['rate']; ?></span>
                                            <input required="true"  type="input" class="wooconnector-hidden wooconnector-settings-rate wooconnector-currency-rate" id="wooconnector_currency-<?php echo $key; ?>-rate" name="wooconnector_currency_settings[<?php echo $key; ?>][rate]" value="<?php echo $value['rate']; ?>" /><input type="button" class="wooconnector-hidden wooconnector-button-getrate" title="Get rate by Google" value="Get"/></td>
                                        <td>
                                            <span class="wooconnector-currency-settings-span"><?php echo WooconnectorChangePosition($value['position'],$value['currency']); ?></span>
                                            <select class="wooconnector-hidden wooconnector-currency-settings wooconnector-currency-position" id="wooconnector_currency-<?php echo $key; ?>-position" name="wooconnector_currency_settings[<?php echo $key; ?>][position]">
                                                <option <?php if($value['position'] == 'left'){ echo 'selected="selected"';} ?> value="left"><?php echo WooconnectorChangePosition('left',$value['currency']); ?></option>
                                                <option <?php if($value['position'] == 'right'){ echo 'selected="selected"';} ?> value="right"><?php echo WooconnectorChangePosition('right',$value['currency']); ?></option>
                                                <option <?php if($value['position'] == 'left_space'){ echo 'selected="selected"';} ?> value="left_space"><?php echo WooconnectorChangePosition('left_space',$value['currency']); ?></option>
                                                <option <?php if($value['position'] == 'right_space'){ echo 'selected="selected"';} ?> value="right_space"><?php echo WooconnectorChangePosition('right_space',$value['currency']); ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <span class="wooconnector-currency-settings-span"><?php echo $value['thousand_separator'];?></span>
                                            <input required="true" type="input" class="wooconnector-hidden wooconnector-currency-settings wooconnector-currency-tseparator" id="wooconnector_currency-<?php echo $key; ?>-tseparator" name="wooconnector_currency_settings[<?php echo $key; ?>][thousand_separator]" value="<?php echo $value['thousand_separator'];?>" /></td>
                                        <td>
                                            <span class="wooconnector-currency-settings-span"><?php echo $value['decimal_separator'];?></span>
                                            <input required="true" type="input" class="wooconnector-hidden wooconnector-currency-settings wooconnector-currency-dseparator" id="wooconnector_currency-<?php echo $key; ?>-dseparator" name="wooconnector_currency_settings[<?php echo $key; ?>][decimal_separator]" value="<?php echo $value['decimal_separator'];?>" /></td>
                                        <td>
                                            <span class="wooconnector-currency-settings-span"><?php echo $value['number_of_decimals'];?></span>
                                            <input required="true" type="input" class="wooconnector-hidden wooconnector-currency-settings wooconnector-currency-ndecima" id="wooconnector_currency-<?php echo $key; ?>-ndecima" name="wooconnector_currency_settings[<?php echo $key; ?>][number_of_decimals]" value="<?php echo $value['number_of_decimals'];?>" />
                                        </td>
                                        <td>
                                             <a class="wooconnector-edit-currency" data-id="<?php echo $key; ?>" data-index="<?php echo $index; ?>" id="wooconnector_edit_currency-<?php echo $key; ?>" ><span class="dashicons dashicons-edit"></span></a>
                                        </td>
                                        <td>
                                            <a class="wooconnector-delete-currency" data-id="<?php echo $key; ?>" data-index="<?php echo $index; ?>" id="wooconnector_delete_currency-<?php echo $key; ?>" ><span class="dashicons dashicons-trash"></span></a>
                                        </td>
                                    </tr>
                                <?php
                                            $index++;
                                        }
                                    }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th><?php echo __('Currency','wooconnector')?></th>
                                    <th><?php echo __('Rate / 1'.get_woocommerce_currency_symbol(WooConnectorGetBaseCurrency()),'wooconnector')?></th>
                                    <th><?php echo __('Currency position','wooconnector')?></th>
                                    <th><?php echo __('Thousand separator','wooconnector')?></th>
                                    <th><?php echo __('Decimal separator','wooconnector')?></th>
                                    <th><?php echo __('Number of decimals','wooconnector')?></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="form-group">
                        <p class="wooconnector-p"><?php echo __("Use Google Finance's webservice (<a target='_blank' href='https://finance.google.com/finance'>https://finance.google.com/finance</a>) to update your currency exchange rates. 
However, please use caution: rates are provided as-is.",'wooconnector') ?></p>
                    </div>
                    <div class="form-group">
                        <button class="wooconnector-primary" id="wooconnector-save-currency"><?php echo __('Save','wooconnector')?></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>