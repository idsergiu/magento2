<?php

$template = Mage::getModel('core/email_template')->loadByCode('Share Wishlist');

if($template->getId()) { 
	$template->setTemplateText('<style type="text/css">
			body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
		</style>
		<div style="font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;">
			<table cellspacing="0" cellpadding="0" border="0" width="98%" style="margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;">
				<tr>
					<td align="center" valign="top">
					<!-- [ header starts here] -->
						<table cellspacing="0" cellpadding="0" border="0" width="650">
							<tr>
								<td valign="top">
									<p><a href="{{store url=""}}" style="color:#1E7EC8;"><img src="{{skin url="images/logo_email.gif"}}" alt="Magento" border="0"/></a></p></td>
							</tr>
						</table>

					<!-- [ middle starts here] -->
						<table cellspacing="0" cellpadding="0" border="0" width="650">
							<tr>
								<td valign="top">
								<p>Hey,<br/>
								Take a look at my wishlist from Magento Demo Store.</p> 

<p>{{var message}}</p>

								{{var items}}

								<br/>

<p><strong><a href="{{var addAllLink}}" style="color:#DC6809;">Add all items to shopping cart</a></strong> | <strong><a href="{{var viewOnSiteLink}}" style="color:#1E7EC8;">View all items in the store</a></strong></p>
								
								<p>Thank you,<br/><strong>{{var customer.name}}</strong></p>


								</td>
							</tr>
						</table>
					
					</td>
				</tr>
			</table>
			</div>');
	$template->save();
}

