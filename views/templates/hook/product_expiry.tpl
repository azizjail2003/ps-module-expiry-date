{*
* 2007-2026 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Jail Abdelaziz <developer@nutrisport.com>
*  @copyright 2007-2026 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if isset($expiry_date) && $expiry_date}
  <div class="product-expiry-info {if $is_expired}text-danger font-weight-bold{else}text-warning{/if} mt-2 mb-2 p-2 border rounded" style="background-color: #fcf8e3; border-color: #faebcc;">
    <i class="material-icons">timer</i>
    <span>
      {if $is_expired}
        {l s='Attention : Date de péremption dépassée le' mod='ps_module_expiry_date'} <strong>{$expiry_date|date_format:"%d/%m/%Y"}</strong>
      {else}
        {l s='Expire le :' mod='ps_module_expiry_date'} <strong>{$expiry_date|date_format:"%d/%m/%Y"}</strong>
      {/if}
    </span>
  </div>
{/if}
