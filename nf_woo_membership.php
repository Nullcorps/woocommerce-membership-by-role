<?php
defined('ABSPATH') or die("No script kiddies please!");


/**
 * Plugin Name: WooCommerce Membership By Role
 * Author URI: https://github.com/Nullcorps
 * Description: Set a user role when order completes. Free membership plugin.
 * Version: 0.001
 * Author: NullCorps
 * Author URI: 
 * Text Domain:
 * Domain Path:
 * Network:
 * License: 
 */

 
 // search on SET THE PRODUCT IDS to find/change the magic membership id

$woo_member_magic_product_ids    = get_option( 'woo_member_magic_product_ids' );
$woo_member_debug                = get_option( 'woo_member_debug'  );
$woo_member_ignore_webhooks      = get_option( 'woo_member_ignore_webhooks');
$woo_member_check_users_pwd      = get_option( 'woo_member_check_users_pwd' );


$nl = "<BR>";
$wmdbg = false;


if ( $woo_member_debug == "on" )
   { $wmdbg = true; }

//// Remove billing phone (and set email field class to wide)
//add_filter( 'woocommerce_billing_fields', 'remove_billing_fields', 20, 1 );
//function remove_billing_fields($fields) {
//   $fields ['billing_last_name']['required'] = false; // To be sure "NOT required"
//   $fields ['billing_first_name']['label'] = "Name"; // To be sure "NOT required"
//   $fields ['billing_phone']['required'] = false; // To be sure "NOT required"
//   $fields ['billing_company']['required'] = false; // To be sure "NOT required"
//   $fields ['billing_address_1']['required'] = false; // To be sure "NOT required"
//   $fields ['billing_address_2']['required'] = false; // To be sure "NOT required"
//   $fields ['billing_city']['required'] = false; // To be sure "NOT required"
//   $fields ['billing_postcode']['required'] = false; // To be sure "NOT required"
//   $fields ['billing_state']['required'] = false; // To be sure "NOT required"
//   $fields ['billing_country']['required'] = false; // To be sure "NOT required"
//   $fields ['order_comments']['required'] = false; // To be sure "NOT required"
//   
//   unset( $fields ['billing_last_name'] ); // Remove billing phone field
//   unset( $fields ['billing_phone'] ); // Remove billing phone field
//   unset( $fields ['billing_company'] ); // Remove billing phone field
//   unset( $fields ['billing_address_1'] ); // Remove billing phone field
//   unset( $fields ['billing_address_2'] ); // Remove billing phone field
//   unset( $fields ['billing_city'] ); // Remove billing phone field
//   unset( $fields ['billing_postcode'] ); // Remove billing phone field
//   unset( $fields ['billing_state'] ); // Remove billing phone field
//   unset( $fields ['order_comments'] ); // Remove billing phone field
//   
//   $fields ['billing_email']['class'] = array('form-row-wide'); // Make the field wide
//
//   
//   return $fields;
//}
//
//
//// Remove shipping phone (optional)
//add_filter( 'woocommerce_shipping_fields', 'remove_shipping_fields', 20, 1 );
//function remove_shipping_fields($fields) {
//   $fields ['shipping_last_name']['required'] = false; // To be sure "NOT required"
//   $fields ['shipping_phone']['required'] = false; // To be sure "NOT required"
//   $fields ['shipping_company']['required'] = false; // To be sure "NOT required"
//   $fields ['shipping_address_1']['required'] = false; // To be sure "NOT required"
//   $fields ['shipping_address_2']['required'] = false; // To be sure "NOT required"
//   $fields ['shipping_city']['required'] = false; // To be sure "NOT required"
//   $fields ['shipping_postcode']['required'] = false; // To be sure "NOT required"
//   $fields ['shipping_state']['required'] = false; // To be sure "NOT required"
//   $fields ['shipping_country']['required'] = false; // To be sure "NOT required"
//   
//   unset( $fields ['shipping_phone'] ); // Remove shipping phone field
//   unset( $fields ['shipping_company'] ); // Remove shipping phone field
//   unset( $fields ['shipping_address_1'] ); // Remove shipping phone field
//   unset( $fields ['shipping_address_2'] ); // Remove shipping phone field
//   unset( $fields ['shipping_city'] ); // Remove shipping phone field
//   unset( $fields ['shipping_postcode'] ); // Remove shipping phone field
//   unset( $fields ['billing_state'] ); // Remove billing phone field
//   
//   return $fields;
//}
//
//
//
////add_filter( 'woocommerce_checkout_fields', 'remove_additional_billing_fields', 20, 1 );
//function remove_additional_billing_fields($fields) {
//   //$fields ['order_comments']['required'] = false; // To be sure "NOT required"
//   //unset( $fields ['order_comments'] ); // Remove billing phone field
//   unset( $fields['order']['order_comments']);
//   return $fields;
//}






function woo_postpay_membership2($orderid)
   {
	global $nl;
	global $woo_member_magic_product_ids;
   global $woo_member_debug;
   
   //$out = "in Woo postpay membership2:" . $nl;
	
	//$orderid = "";
	
	//if (isset($_GET['orderid']))
	//	{ $orderid = $_GET['orderid']; }
	
	
	if (is_numeric($orderid))
		{
 
		//$out .= "Get the order yo" . $nl;
		$out .= "<span style=\"font-size: 20px;\">Order id: " . $orderid . "</span>&nbsp;";
		
		$order = wc_get_order( $orderid );
		$out .= " (" . $order->status . ")" . $nl;
		
		$out .= $nl;
		//$out .= print_r($order) . $nl;
		
		foreach( $order->get_items() as $item_id => $item )
			{
			// The product name
			$product_name = $item->get_name(); // … OR: $product->get_name();
			$out .= "<div style=\"font-size: 20px\">" . $product_name . " ";
			
			//Get the product ID
			$product_id = $item->get_product_id();
			$out .= " (id: " . $product_id . ")</div>";
			
         $magic_id = 1;
         
         if ( is_numeric($woo_member_magic_product_ids) )
            { $magic_id = $woo_member_magic_product_ids; }
         
         
         if ( $product_id == $magic_id )  // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< HERE IS WHEREYOU SET THE PRODUCT IDS WHIC ARE SPECIAL/MEMBERSHIPS
             {
            $out .= "Product is a membership, get duration and add to user field" . $nl . $nl;
            $userid = get_current_user_id();
            $out .= "Current user ID: " . $userid . $nl . $nl;
            
            if ( $userid == 0 )
               {
               echo "Missing user ID, form cannot complete. Please email support" . $nl;
               return;
               }
            
            // see if there's an existing expiry date
            $membership_expires = get_user_meta($userid, 'membership_expires', true);
            $membership_expires_order = get_user_meta($userid, 'membership_expires_order', true);
            $membership_expires_updated = get_user_meta($userid, 'membership_expires_updated', true);
            
            $out .= "Membership expires: " . $membership_expires . " - " . date('d/m/Y H:i:s', $membership_expires) . $nl;
            $out .= "Membership expires order: " . $membership_expires_order . $nl;
            $out .= "Membership expires updated: " . $membership_expires_updated . " - " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl;
            $out .= $nl;
            
            $product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
            
            $duration_days = $product->get_attribute( 'duration_days' );
            $out .= "Duration_days: " . $duration_days . $nl;
            $out .= $nl;
            
            if ($membership_expires <> "" && $membership_expires_order <> "" && $membership_expires_updated <> "" )
               {
               $out .= "There's already a membership expires date, check it's not a duplicate and then add to it and update the order & updated date." . $nl;
               $out .= $nl;
               //update_user_meta( $userid, 'membership_expires', 'herp de flerp' );
               if ($membership_expires_order < $orderid)
						{
						$out .= "This looks to be an additional renewal, extend the previous expiry date" . $nl;
						$current_time = time();
						$current_expires_time = $membership_expires;
						$out .= "Current membership expiry time: " . $current_expires_time . $nl;
						$out .= "Current membership expiry time (readable): " . date('d/m/Y H:i:s', $current_expires_time) . $nl;
						
						$new_expires_time = date(strtotime('+' . $duration_days . ' days', $current_expires_time));
						$out .= "New expires time: " . $new_expires_time . $nl;
						$out .= "New expires time (readable): " . date('d/m/Y H:i:s', $new_expires_time) . $nl;
						
						$out .= "Skipping updates for now till maths has been checked!" . $nl;
						update_user_meta( $userid, 'membership_expires', $new_expires_time );
						update_user_meta( $userid, 'membership_expires_order', $orderid );
						update_user_meta( $userid, 'membership_expires_updated', $current_time );
	 
						$out .= "Membership expiry details set" . $nl;
						
						$membership_expires = get_user_meta($userid, 'membership_expires', true);
						$membership_expires_order = get_user_meta($userid, 'membership_expires_order', true);
						$membership_expires_updated = get_user_meta($userid, 'membership_expires_updated', true);
						
						$out .= "Membership expires: " . $membership_expires . " - " . date('d/m/Y H:i:s', $membership_expires) . $nl;
						$out .= "Membership expires order: " . $membership_expires_order . $nl;
						$out .= "Membership expires updated: " . $membership_expires_updated . " - " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl; 
                  $out .= $nl;
						
						}
					else
						{
						$out .= "This expiry date looks to already have been set for this orderid or is a duplicate. Ignore" . $nl . $nl;
						}
					
					
					}
            else
               {
               $out .= "There's no membership expires date - add one, along with the order and updated date" . $nl;
               
               //$current_time = date('d/m/Y H:i:s', time());
               $current_time = time();
               $out .= "Current time: " . $current_time . $nl;
               $out .= "Current time (readable): " . date('d/m/Y H:i:s', $current_time) . $nl;
               $out .= $nl;
               
               $out .= "Duration to add: " . $duration_days . $nl;
               $out .= $nl;
               
               if ( !is_numeric($duration_days) )
                  {
                  $out .= "WARNING: MISSING duration_days ATTRIBUTE FROM MAGIC PRODUCT" . $nl; 
                  }
               else
                  {
                  $expires_time = date(strtotime('+' . $duration_days . ' days', $current_time));
                  $out .= "Expires time: " . $expires_time . $nl;
                  $out .= "Expires time (readable): " . date('d/m/Y H:i:s', $expires_time) . $nl;
                  update_user_meta( $userid, 'membership_expires', $expires_time );
                  update_user_meta( $userid, 'membership_expires_order', $orderid );
                  update_user_meta( $userid, 'membership_expires_updated', $current_time );
    
                  $out .= "Membership expiry details set" . $nl;
                  
                  $membership_expires = get_user_meta($userid, 'membership_expires', true);
                  $membership_expires_order = get_user_meta($userid, 'membership_expires_order', true);
                  $membership_expires_updated = get_user_meta($userid, 'membership_expires_updated', true);
                  
                  $out .= "Membership expires: " . $membership_expires . " - " . date('d/m/Y H:i:s', $membership_expires) . $nl; $nl;
                  $out .= "Membership expires order: " . $membership_expires_order . $nl;
                  $out .= "Membership expires updated: " . $membership_expires_updated . " - " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl; 
                  }
					}
               
               
            // ADD USER FIELD
            }
         
			$out .= nf_woo_update_membership($userid) . $nl;
			
			
			//$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
			//$out .= $product->get_image();
			
			//Get the WC_Product object
			//$product = $item->get_product();
			//$out .=  "description/fuid: " . $product->get_description() . $nl;
			//$fuid = $product->get_description();
			
			//print_r($product) . $nl;
			
			//Get the variation ID
			//$product_id = $item->get_variation_id();
		
			// The quantity
			//$product_qty = $item->get_quantity();
			//$out .= $nl . "Product description: " . $fuid . $nl;
         //$download = mf_checkout_get_mf_download_url($fuid);
			
			
         
         
         
			//$product_data = $item->get_data();
			//$out .= "description: " . $product_data['description'] . $nl;
			
			
			//Get the product SKU (using WC_Product method)
			//$sku = $product->get_sku();
			//$out .= $nl;
			}
		}
	return $out;

   //return do_shortcode($out);
   }
   
   


   
   
   


add_action( 'show_user_profile', 'crf_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'crf_show_extra_profile_fields' );

function crf_show_extra_profile_fields( $user ) {
	?>
	<h3><?php esc_html_e( 'Personal Information', 'crf' ); ?></h3>

	<table class="form-table">
		<tr>
			<th><label for="year_of_birth"><?php esc_html_e( 'membership_expires', 'crf' ); ?></label></th>
			<td><?php echo esc_html( get_the_author_meta( 'membership_expires', $user->ID ) ); ?></td>
		</tr>
	</table>
	<?php
}










add_shortcode('nf_woo_membership_status','nf_woo_membership_status');
function nf_woo_membership_status($atts,$content = null)
   {
	global $nl;
	global $wmdbg;
   
	$isloggedin = is_user_logged_in();
	
   if ($qdbg) { $out .= "DEBUG IS: " . $wmdbg . $nl; }
   
	//$out .= "Membership status:" . $nl;
	//$out .= "Is user logged in: " . $isloggedin . $nl;
	
   $has_manual_premium = "";
   
	if (!$isloggedin)
		{
		$out .= "Please <a href=\"/wp-login.php\">log in</a> to see membership status" . $nl;	
		}
	else
		{
		$isuseradmin = nf_woo_is_user_admin();
		//$out .= "Is user admin: " . $isuseradmin . $nl;
		
		if ($isuseradmin)
			{ $out .= "User is admin - membership doesn't apply" . $nl; }
		else
			{
			$userid = get_current_user_id();
			
         $has_manual_premium = nf_woo_user_has_role($userid, "manual_premium");
         $has_premium = nf_woo_user_has_role($userid, "premium_subscriber");
         $out .= "Has premium: " . $has_premium . $nl;
         $out .= "Has manual premium: " . $has_manual_premium . $nl;
         
         
         if ( $has_premium && $has_manual_premium )
            {
            $out .= "Your account has manually set premium permissions, so will not expire." . $nl;
            return do_shortcode($out);
            }
         
			$membership_expires = get_user_meta($userid, 'membership_expires', true);
			$membership_expires_order = get_user_meta($userid, 'membership_expires_order', true);
			$membership_expires_updated = get_user_meta($userid, 'membership_expires_updated', true);
			
         if ($wmdbg)
            {
            $out .= "Membership expires: " . $membership_expires . " - " . date('d/m/Y H:i:s', $membership_expires) . $nl; $nl;
            $out .= "Order no of last membership purchase: " . $membership_expires_order . $nl;
            $out .= "Membership last updated: " . $membership_expires_updated . " - " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl; 
            }
         else
            {         
            $out .= "Membership expires: " . date('d/m/Y H:i:s', $membership_expires) . $nl; $nl;
            $out .= "Order no of last membership purchase: " . $membership_expires_order . $nl;
            $out .= "Membership last updated: " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl; 
            }
      
			$user_meta=get_userdata($userid);
			$user_roles=$user_meta->roles;
			
			//foreach ($user_roles as $user_role)
			//	{
			//	$out .= "Role: " . $user_role . $nl;
			//	}
			//echo $out;
			$out .= $nl;
         //echo $out;
			$out .= nf_woo_update_membership($userid);
			}
		}
	
	return do_shortcode($out);
   }
	
	
	
   
   
   

function nf_woo_user_has_role($user_id, $role_or_cap) {

    $u = new \WP_User( $user_id );
    //$u->roles Wrong way to do it as in the accepted answer.
    $roles_and_caps = $u->get_role_caps(); //Correct way to do it as wp do multiple checks to fetch all roles

    if( isset ( $roles_and_caps[$role_or_cap] ) and $roles_and_caps[$role_or_cap] === true )
       {
           return true;
       }
 }   
   
   
   
   
   
function nf_woo_update_membership($userid)
	{
	global $nl;
	global $wmdbg;
   
	if (!$userid)
		{ $userid = get_current_user_id(); }
	
	
   if ($wmdbg) { $out .= "Debug: " . $wmdbg . $nl; }
   if ($wmdbg) { $out .= "IN WOO UPDATE MEMBERSHIP" . $nl; }
	
	$out .= "<b>UserID: " . $userid . "</b>" . $nl;
	
	$current_time = time();
	if ($wmdbg)
      { $out .= "Current time: " . $current_time . " - " . date('d/m/Y H:i:s', $current_time) . $nl; }
   else
      { $out .= "Current time: " . date('d/m/Y H:i:s', $current_time) . $nl; }
	
	
	$membership_expires = get_user_meta($userid, 'membership_expires', true);
	$membership_expires_order = get_user_meta($userid, 'membership_expires_order', true);
	$membership_expires_updated = get_user_meta($userid, 'membership_expires_updated', true);
	
   
   if ($wmdbg)
      {
      $out .= "Membership expires: " . $membership_expires . " - " . date('d/m/Y H:i:s', $membership_expires) . $nl;
      $out .= "Membership expires order: " . $membership_expires_order . $nl;
      $out .= "Membership expires updated: " . $membership_expires_updated . " - " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl; 
      }
   else
      {
      $out .= "Membership expires: " . date('d/m/Y H:i:s', $membership_expires) . $nl;
      $out .= "Membership expires order: " . $membership_expires_order . $nl;
      $out .= "Membership expires updated: " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl; 
      }
   
	$expires_diff = $membership_expires - $current_time;
	$out .= $nl;
	$out .= "Expires difference: " . $expires_diff . " sec" . $nl; 	//" : " . date('', $expires_diff) . " days remaining" .  $nl;
	//$out .= "Something: " . $expires_diff->format("%a") . $nl;
	
	$days_remaining = round($expires_diff/60/60/24,1);
	$out .= "Membership days remaining: " . $days_remaining . $nl;
	
	
	$ispremium = false;
   
   $out .= $nl;
   $out .= "<b>Checking it's all set corrrectly:</b>" . $nl;
   
	if ($expires_diff > 0)
		{
		$out .= "User has days remaining as premium member, check roles are correct:" . $nl;
		$setpremium = true;
		}
	else
		{
		$out .= "User does not have days remaining as premium member, check roles are correct:" . $nl;
		$setpremium = false;
		}
	
	$user_meta=get_userdata($userid);
	$user_roles=$user_meta->roles;
	
	$ispremium = false;
	
	foreach ($user_roles as $user_role)
		{
		//$out .= "Role: " . $user_role . $nl;
		if ($user_role == "premium_subscriber")
			{
			$ispremium = true;
			}
		}
	
	$out .= "- Is user currently premium (by role)?: " . $ispremium . $nl;
	
	$out .= "- Should user be premium?: " . $setpremium . $nl;
	
	$u = new WP_User($userid);


	
	
	
	if ($ispremium)
		{
		if ($setpremium)
			{ $out .= $nl . "Nothing to do, user is already premium and should be. All good" . $nl; }
		else
			{
			$out .= $nl . "Remove premium role" . $nl;
			$u->remove_role( 'premium_subscriber' );
         
         $has_subscriber = nf_woo_user_has_role($userid, "subscriber");
         if ( !$has_subscriber )
            {
            $u->add_role( 'subscriber' );   
            }
			}
		}
	else
		{
		if ($setpremium)
			{
			$out .= "Set user as premium" . $nl;
			$u->add_role( 'premium_subscriber' );
			}
		else
			{ $out .= "Nothing to do. User is not premium and shouldn't be. All good" . $nl; }
		}
	
   $out .= $nl . $nl;
   
	return $out;
		
	}
	

	
	
   
   
	
function nf_woo_is_user_admin()
	{
	if (is_user_logged_in())
      {
      //$content .= "Hello";
      global $current_user;
      get_currentuserinfo();

      //echo 'Username: ' . $current_user->user_login . "<br>";
      //echo 'User email: ' . $current_user->user_email . "\n";
      //echo 'User first name: ' . $current_user->user_firstname . "\n";
      //echo 'User last name: ' . $current_user->user_lastname . "\n";
      //echo 'User display name: ' . $current_user->display_name . "\n";
      //echo 'User ID: ' . $current_user->ID . "<br>";
      foreach ( $current_user->roles as $role )
         {
			//echo $role;
			//if ($role == 'premium_subscriber') { $show = true; }
			if ($role == 'administrator') { $show = true; }
			}
      }
   if ($show == true)
		{ return true; }
	else
		{ return false; }
	}
   
   
   
   
   







   
   

add_action( 'woocommerce_thankyou', 'redirectcustom', 10);
  
function redirectcustom( $order_id )
	{
	global $nl;
   global $woo_member_ignore_webhooks;   
	$order = wc_get_order( $order_id );
   //echo $nl . "ATTEMPTING TO DO THE MEMBERSHIP STUFF" . $nl . $nl;
   
   //$url = '/wptest/?page_id=595&orderid=' . $order_id;
   $url = "/my-account/view-order/" . $order_id;
   
   $userid = get_current_user_id();
   $out .= "Current user ID: " . $userid . $nl . $nl;
   
   if ( $userid == 0 )
      {
      echo "Missing user ID, form cannot complete. Please email support" . $nl;
      return;
      }
   
   //if ( $order->status != 'failed' ) {
	if ( $order->status == 'completed' || $order->status == 'processing' || $woo_member_ignore_webhooks)
                                         // added "processing" bit for ccbill compatibility
		{
       
      if ( $woo_member_ignore_webhooks )
         {
         echo "Marking order as paid (ignore webhooks)" . $nl . $nl;
         $order->set_status('processing');
         $order->save();        
         }
		//echo "DOING THE MEMBERSHIP STUFF" . $nl;
      //echo "Processing order id: " . $order_id . $nl;
      $out = woo_postpay_membership2($order_id);
      echo $out;
      echo "<center><img src=\"/wp-content/plugins/woocommerce-membership-by-role/waiting_icon.gif\" style=\"width: 96px;\"></center>";
      echo $nl;
      echo "<center style=\"font-size: 20px; \">If you are not forwarded automatically, please click: ";
      echo "<a href=\"" . $url . "\">HERE</a></center>" . $nl;
      echo "<br><br>";
      echo "</div></div>";
      echo "
<script language=javascript>
setTimeout(\"location.href = '" . $url . "'\", 1500);
</script>";
      
      //echo "</body></html>";
      //wp_safe_redirect( $url );
      //exit;
		}
	else
		{
		echo "<div style=\"background-color: #ffffffff; padding: 12px;\">";
		echo "ORDER STATUS: " . $order->status . $nl;
		echo "Once this order is set to completed you will be able to download your clips.<br>You can refresh this page if you need to. It should take you to the order once it's approved.". $nl;
      echo "</div>";
		echo $nl;
		}
	}






add_action('woocommerce_order_details_after_order_table', 'action_order_details_after_order_table', 10, 4 );
function action_order_details_after_order_table( $order, $sent_to_admin = '', $plain_text = '', $email = '' ) {
    // Only on "My Account" > "Order View"
    if ( is_wc_endpoint_url( 'view-order' ) ) {
        //printf( '<p class="custom-text">' .
        //__("To cancel your license within the 30 day trial period click on %s" ,"woocommerce"),
        //'<strong>"' .__("Refund my entire order", "woocommerce") . '"</strong>.</p>' );
       printf("<div style=\"font-size: 18px; font-weight: bold; \">You can check the status and how long you've got left of your membership at any time by going to the <a href=\"/membership\">Membership status</a> page from the \"My account\" menu item.</div><br><br>");
    }
}





















// =============== ADMIN PAGE STUFF

if ( is_admin() )
   {  // admin actions
   //add_action( 'admin_menu', 'add_mymenu' );
   //add_options_page( 'P8-Statto', 'P8-Statto', 'administrator', 'P8-statto/settings.php', 'statto_admin_page', 'dashicons-tickets', 6  );
   add_action( 'admin_menu', 'woo_member_admin_menu' );
   add_action( 'admin_init', 'woo_member_register_settings' );
   }
   else
   {
   // non-admin enqueues, actions, and filters
   }

function woo_member_register_settings() { // whitelist options
  register_setting( 'Woo-member', 'woo_member_magic_product_ids' );
  register_setting( 'Woo-member', 'woo_member_debug' );
  register_setting( 'Woo-member', 'woo_member_ignore_webhooks' );
  register_setting( 'Woo-member', 'woo_member_check_users_pwd' );
  
}

//add_action( 'admin_menu', 'botfink_admin_menu' );

function woo_member_admin_menu() {
	// add_menu_page( 'My Top Level Menu Example', 'P8-Botfink', 'manage_options', 'myplugin/myplugin-admin-page.php', 'botfink_admin_page', 'dashicons-tickets', 6  );
   add_options_page( 'Woo-membership', 'Woo-membership', 'administrator', 'Woo-member/admin-page.php', 'woo_member_admin_page', 'dashicons-tickets', 6  );
}








function woo_member_admin_page(){
   global $nl;

	?>
	<div class="wrap">
		<h2>WooCommerce membership by role configuration</h2>


		<form method="post" action="options.php">
		<?php
		settings_fields( 'Woo-member' );
		do_settings_sections( 'Woo-member' );
		//add_settings_field( $id, $title, $callback, $page, $section, $args );
		?>
		  <table class="form-table" border=0>

        <tr valign="top">
        <th scope="row">"Magic" product ID:</th>
        <td><input type="text" size=50 name="woo_member_magic_product_ids" value="<?php echo esc_attr( get_option('woo_member_magic_product_ids') ); ?>" /><br>
        (Single value for now but implement for comma-separated list of product IDs to trigger adding a membership - look to duration attribute for length)
        </td>
        </tr>

        
        <tr valign="top">
        <th scope="row">Show debug info</th>
        <td><input type="checkbox" name="woo_member_debug" <?php if ( esc_attr( get_option('woo_member_debug') ) == "on" ) { echo "checked"; }; ?> />
        &nbsp;
        Should be disabled unless trying to trace an error. Do this as a label!
        </td>
        </tr>
        
        
        <tr valign="top">
        <th scope="row">Ignore CCbill webhooks</th>
        <td><input type="checkbox" name="woo_member_ignore_webhooks" <?php if ( esc_attr( get_option('woo_member_ignore_webhooks') ) == "on" ) { echo "checked"; }; ?> />
        &nbsp;
        Don't rely on webhooks from CCbill to set order status, order status is set to processing by the checkout process.
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Password for "check users" page:</th>
        <td><input type="text" size=50 name="woo_member_check_users_pwd" value="<?php echo esc_attr( get_option('woo_member_check_users_pwd') ); ?>" /><br>
        The password to use to allow automated access to the check_users page, to allow automating the downgrading of expired users.<br>
        <br>
        <strong><u>IMPORTANT: Make this password LONG since it's the only protection for that page - like 30-40 alphanumeric characters</u></strong>
        <br><br>
        You can use <a href="https://www.strongpasswordgenerator.org/" target="_blank">this site</a> to generate a strong password.<br>
        <br>
        Recommended settings:<br>
        - "Include alpha<br>
        - Include lower<br>
        - Include number<br>
        - DO NOT include symbol (as it will likely break the password because of character encoding<br>
        - Length: 30-40 characters
        
        To allow checking all users, make a new page and add a "custom html" block and add the following shortcode: [nf_woo_check_all_users]<br><br>
        
        To automate checking, set up a web-cron (e.g. from montastic.com which is free) to run e.g. one a week and hit the url of your chcking page,
        but add ?p=thepasswordyouenteredinthisfield to the end of the url.<br><br>
        
        So fo example you url ought to look a bit like this: https://yoursite.com/check-users?p=we9u34rj4fojifioje4ioerfioerfuhi<Br><Br>
        
        You can check whether it's working ok by opening a private browsing window (or log out so you're not admin) and hit that url, it should show you the check_users page.<br><br>
        
        If you remove the p=sfsdffsds bit or change it, it ought to show "page restricted".
        </td>
        </tr>
      
    </table>
		<?php submit_button(); ?>
      </form>
	</div>
	<?php

   echo "woo_member_magic_product_ids: "              . get_option( 'woo_member_magic_product_ids' )  . $nl;
   echo "woo_member_debug: "                          . get_option( 'woo_member_debug' )              . $nl;
   echo "woo_member_ignore_webhooks: "                . get_option( 'woo_member_ignore_webhooks' )    . $nl;
   echo "woo_member_check_users_pwd: "                . get_option( 'woo_member_check_users_pwd' )    . $nl;

   
   
}









add_shortcode('nf_woo_check_all_users','nf_woo_check_all_users');
function nf_woo_check_all_users($atts,$content = null)
   {
   global $nl;
   global $wmdbg;
   global $woo_member_check_users_pwd;

   $wmdbg = 1;
   $out = "";
    
   //$out .= "stored pwd: " . $woo_member_check_users_pwd . $nl;   
   $pwd = "";
   if ( isset($_GET['p']) )
      { $pwd = sanitize_text_field($_GET['p']); }
   
   $currentisadmin = nf_woo_is_user_admin();
   //$out .= "Current user is admin: " . $currentisadmin . $nl;
   
   if ( $currentisadmin || ($woo_member_check_users_pwd <> "" && $pwd == $woo_member_check_users_pwd) )
      {
      $out .= "Current user is admin or password matched" . $nl . $nl; 
      }
   else
      {
      $out .= "Sorry this page is restricted";
      return $out;
      }
   
   $out .= "Check all users, find those who are premium_subscriber but not admin or manual_premium: " . $nl;
   $out .= $nl;

   $users = get_users();
   
   foreach ( $users as $user )
      {
      //$out .= "<pre>" . print_r($user->data, true) . "</pre>";
      //$out .= "<pre>" . print_r($user->roles, true) . "</pre>";
      
      $ispremium = false;
      $ismanualpremium = false;
      $isadmin = false;
      
      foreach ( $user->roles as $role )
         {
         //$out .= $role . $nl;
         if ( $role == "administrator" )
            { $isadmin = true; }
         if ( $role == "premium_subscriber" )
            { $ispremium = true; }
         if ( $role == "manual_premium" )
            { $ismanualpremium = true; }
         }
      
      if ( $ispremium && !$isadmin && !$ismanualpremium )
         {
         $userid = $user->data->ID;      
         
         //$out .= "Do the thing" . $nl;
         //$out .= "<pre>" . print_r($user, true) . "</pre>";
         //$out .= $nl;
         
         $out .= "UserID: " . $userid . $nl;
         $out .= "Username: " . $user->data->user_login . $nl;
         $out .= "User email: " . $user->data->user_email . $nl;
         
         
         $membership_expires = get_user_meta($userid, 'membership_expires', true);
         $membership_expires_order = get_user_meta($userid, 'membership_expires_order', true);
         $membership_expires_updated = get_user_meta($userid, 'membership_expires_updated', true);
         
         
         if ($wmdbg)
            {
            $out .= "Membership expires: " . $membership_expires . " - " . date('d/m/Y H:i:s', $membership_expires) . $nl;
            $out .= "Membership expires order: " . $membership_expires_order . $nl;
            $out .= "Membership expires updated: " . $membership_expires_updated . " - " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl; 
            }
         else
            {
            $out .= "Membership expires: " . date('d/m/Y H:i:s', $membership_expires) . $nl;
            $out .= "Membership expires order: " . $membership_expires_order . $nl;
            $out .= "Membership expires updated: " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl; 
            }
         
         if ( strval($membership_expires) <> "" && strval($membership_expires_order) <> "" && strval($membership_expires_order) <> "" )
            {
            $out .= "Found all the required membership expiry stuff, should be ok to process" . $nl;
            $out .= nf_woo_update_membership($userid);
            }
         else
            {
            $out .= "<span style=\"color: red; font-weight: bold;\">This order appears to be missing membership expires stuff, please review manually. SKIPPING PROCESSING</span>" . $nl;
            }
         
         $out .= $nl . $nl;
         
         }
      }   
   
   
   //$out .= "<pre>" . print_r($users, true) . "</pre>";
   
   
   // users who have customer role
   // who are not admin
   
   // check status and modify as needed
   
   
   // add a password to admin settings so we can cron this page
   
   return $out;
   }