<?php

$newOrderTemplate = Mage::getModel('core/email_template')->loadByCode('New order (HTML)');

if($newOrderTemplate->getId()) {
	$newOrderTemplate->setTemplateText('<style type="text/css">
body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
</style>

<div style="font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;">
            <table cellspacing="0" cellpadding="0" border="0" width="98%" style="margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;"">
             <tr>
                    <td align="center" valign="top">
                    <!-- [ header starts here] -->
                      <table cellspacing="0" cellpadding="0" border="0" width="650">
                          <tr>
                                <td valign="top">
                                 <a href="{{store url=""}}"><img src="{{skin url="images/logo_email.gif"}}" alt="Magento"  style="margin-bottom:10px;" border="0"/></a></td>
                           </tr>
                       </table>

                    <!-- [ middle starts here] -->
                      <table cellspacing="0" cellpadding="0" border="0" width="650">
                          <tr>
                                <td valign="top">
                             <p><strong>Hello {{var billing.name}}</strong>,<br/>
                                Thank you for your order from Magento Demo Store. Once your package ships we will send an email with a link to track your order. You can check the status of your order by <a href="{{store url="customer/account/"}}" style="color:#1E7EC8;">logging into your account</a>. If you have any questions about your order please contact us at <a href="mailto:dummyemail@magentocommerce.com" style="color:#1E7EC8;">dummyemail@magentocommerce.com</a> or call us at <nobr>(800) DEMO-NUMBER</nobr> Monday - Friday, 8am - 5pm PST.</p>
 <p>Your order confirmation is below. Thank you again for your business.</p>

                                <h3 style="border-bottom:2px solid #eee; font-size:1.05em; padding-bottom:1px; ">Your Order #{{var order.increment_id}} <small>(placed on {{var order.getCreatedAtFormated(\'long\')}})</small></h3>
                              <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                 <thead>
                                 <tr>
                                        <th align="left" width="48.5%" bgcolor="#d9e5ee" style="padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;">Billing
                                       Information:</th>
                                       <th width="3%"></th>
                                      <th align="left" width="48.5%" bgcolor="#d9e5ee" style="padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;">Payment
                                       Method:</th>
                                    </tr>
                                   </thead>
                                    <tbody>
                                 <tr>
                                        <td valign="top" style="padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;">{{var order.billing_address.getFormated(\'html\')}}</td>
                                      <td>&nbsp;</td>
                                     <td valign="top" style="padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;"> {{var order.payment.getHtmlFormated(\'private\'))}}</td>
                                 </tr>
                                   </tbody>
                                </table><br/>
                                               <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                 <thead>
                                 <tr>
                                        <th align="left" width="48.5%" bgcolor="#d9e5ee" style="padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;">Shipping
                                      Information:</th>
                                       <th width="3%"></th>
                                      <th align="left" width="48.5%" bgcolor="#d9e5ee" style="padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;">Shipping
                                      Method:</th>
                                    </tr>
                                   </thead>
                                    <tbody>
                                 <tr>
                                        <td valign="top" style="padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;">{{var order.shipping_address.getFormated(\'html\')}}</td>
                                     <td>&nbsp;</td>
                                     <td valign="top" style="padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;">{{var order.shipping_description}}</td>
                                   </tr>
                                   </tbody>
                                </table><br/>

{{var items_html}}<br/>
      {{var order.getEmailCustomerNote()}}
                                <p>Thank you again,<br/><strong>Magento Demo Store</strong></p>


                             </td>
                           </tr>
                       </table>

                    </td>
               </tr>
           </table>
            </div>
');
$newOrderTemplate->save();
}



