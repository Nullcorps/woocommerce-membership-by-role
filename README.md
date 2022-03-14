# woocommerce-membership-by-role
Lets you define a "magic" mambership product in woo (by id), then when this product is bought, after checkout it assigns a premium_member role to that user. Use that along with restrict_content or my guestshortcode plugins to restrict content and build a membership site. 

- You can now set the id of the "magic" product in the admin settings. 

- moved the bit which removes all the billing/address fields to a separate plugin - see "woocommerce-remove-address-fields" in my repos

- Added some functionality to make things work with the CCBill plugin which didn't seem to want to work wiht ccbill webhooks. This manually sets the order status to "processing" (effectively 'done'). It's not pretty but seems to work reliably.

DONE - Admin bit with the magic product id/s isn't implemented yet 
