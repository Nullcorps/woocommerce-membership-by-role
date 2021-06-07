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
$woo_member_disable_redirect     = get_option( 'woo_member_disable_redirect'  );
$woo_member_debug                = get_option( 'woo_member_debug'  );


$nl = "<BR>";
$wmdbg = false;


if ( $woo_member_debug == "on" )
   { $wmdbg = true; }

// Remove billing phone (and set email field class to wide)
add_filter( 'woocommerce_billing_fields', 'remove_billing_fields', 20, 1 );
function remove_billing_fields($fields) {
   $fields ['billing_last_name']['required'] = false; // To be sure "NOT required"
   $fields ['billing_first_name']['label'] = "Name"; // To be sure "NOT required"
   $fields ['billing_phone']['required'] = false; // To be sure "NOT required"
   $fields ['billing_company']['required'] = false; // To be sure "NOT required"
   $fields ['billing_address_1']['required'] = false; // To be sure "NOT required"
   $fields ['billing_address_2']['required'] = false; // To be sure "NOT required"
   $fields ['billing_city']['required'] = false; // To be sure "NOT required"
   $fields ['billing_postcode']['required'] = false; // To be sure "NOT required"
   $fields ['billing_state']['required'] = false; // To be sure "NOT required"
   $fields ['billing_country']['required'] = false; // To be sure "NOT required"
   $fields ['order_comments']['required'] = false; // To be sure "NOT required"
   
   unset( $fields ['billing_last_name'] ); // Remove billing phone field
   unset( $fields ['billing_phone'] ); // Remove billing phone field
   unset( $fields ['billing_company'] ); // Remove billing phone field
   unset( $fields ['billing_address_1'] ); // Remove billing phone field
   unset( $fields ['billing_address_2'] ); // Remove billing phone field
   unset( $fields ['billing_city'] ); // Remove billing phone field
   unset( $fields ['billing_postcode'] ); // Remove billing phone field
   unset( $fields ['billing_state'] ); // Remove billing phone field
   unset( $fields ['order_comments'] ); // Remove billing phone field
   
   $fields ['billing_email']['class'] = array('form-row-wide'); // Make the field wide

   
   return $fields;
}


// Remove shipping phone (optional)
add_filter( 'woocommerce_shipping_fields', 'remove_shipping_fields', 20, 1 );
function remove_shipping_fields($fields) {
   $fields ['shipping_last_name']['required'] = false; // To be sure "NOT required"
   $fields ['shipping_phone']['required'] = false; // To be sure "NOT required"
   $fields ['shipping_company']['required'] = false; // To be sure "NOT required"
   $fields ['shipping_address_1']['required'] = false; // To be sure "NOT required"
   $fields ['shipping_address_2']['required'] = false; // To be sure "NOT required"
   $fields ['shipping_city']['required'] = false; // To be sure "NOT required"
   $fields ['shipping_postcode']['required'] = false; // To be sure "NOT required"
   $fields ['shipping_state']['required'] = false; // To be sure "NOT required"
   $fields ['shipping_country']['required'] = false; // To be sure "NOT required"
   
   unset( $fields ['shipping_phone'] ); // Remove shipping phone field
   unset( $fields ['shipping_company'] ); // Remove shipping phone field
   unset( $fields ['shipping_address_1'] ); // Remove shipping phone field
   unset( $fields ['shipping_address_2'] ); // Remove shipping phone field
   unset( $fields ['shipping_city'] ); // Remove shipping phone field
   unset( $fields ['shipping_postcode'] ); // Remove shipping phone field
   unset( $fields ['billing_state'] ); // Remove billing phone field
   
   return $fields;
}



//add_filter( 'woocommerce_checkout_fields', 'remove_additional_billing_fields', 20, 1 );
function remove_additional_billing_fields($fields) {
   //$fields ['order_comments']['required'] = false; // To be sure "NOT required"
   //unset( $fields ['order_comments'] ); // Remove billing phone field
   unset( $fields['order']['order_comments']);
   return $fields;
}






function woo_postpay_membership2($orderid)
   {
	global $nl;
	
   $out = "in Woo postpay membership2:" . $nl;
	
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
			
         if ($product_id == 1130)  // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< HERE IS WHEREYOU SET THE PRODUCT IDS WHIC ARE SPECIAL/MEMBERSHIPS
             {
            $out .= "Product is a membership, get duration and add to user field" . $nl;
            $userid = get_current_user_id();
            $out .= "Current user ID: " . $userid . $nl;
            
            // see if there's an existing expiry date
            $membership_expires = get_user_meta($userid, 'membership_expires', true);
            $membership_expires_order = get_user_meta($userid, 'membership_expires_order', true);
            $membership_expires_updated = get_user_meta($userid, 'membership_expires_updated', true);
            
            $out .= "Membership expires: " . $membership_expires . " - " . date('d/m/Y H:i:s', $membership_expires) . $nl;
            $out .= "Membership expires order: " . $membership_expires_order . $nl;
            $out .= "Membership expires updated: " . $membership_expires_updated . " - " . date('d/m/Y H:i:s', $membership_expires_updated) . $nl;
            
            
            $product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
            
            $duration_days = $product->get_attribute( 'duration_days' );
            $out .= "Duration_days: " . $duration_days . $nl;
            
            
            if ($membership_expires <> "" && $membership_expires_order <> "" && $membership_expires_updated <> "" )
               {
               $out .= "There's already a membership expires date, check it's not a duplicate and then add to it and updated the order & updated date" . $nl;
               //update_user_meta( $userid, 'membership_expires', 'herp de flerp' );
               if ($membership_expires_order < $orderid)
						{
						$out .= "This looks to be an additional renewal, extend the previous epiry date" . $nl;
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

						
						}
					else
						{
						$out .= "This expiry date looks to already have been set for this orderid or is a duplicate. Ignore" . $nl;
						}
					
					
					}
            else
               {
               $out .= "There's no membership expires date - add one, along with the order and updated date" . $nl;
               
               //$current_time = date('d/m/Y H:i:s', time());
               $current_time = time();
               $out .= "Current time: " . $current_time . $nl;
               $out .= "Current time (readable): " . date('d/m/Y H:i:s', $current_time) . $nl;
               
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
               
               
            // ADD USER FIELD
            }
         
			$out .= nf_woo_update_membership($userid) . $nl;
			
			
			$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
			$out .= $product->get_image();
			
			//Get the WC_Product object
			$product = $item->get_product();
			//$out .=  "description/fuid: " . $product->get_description() . $nl;
			$fuid = $product->get_description();
			
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
			$out .= $nl . $nl;
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
	
	
	
   
   
   
   
   
   
   
   
   
function nf_woo_update_membership($userid)
	{
	global $nl;
	global $wmdbg;
   
	if (!$userid)
		{ $userid = get_current_user_id(); }
	
	
   if ($wmdbg) { $out .= "Debug: " . $wmdbg . $nl; }
   if ($wmdbg) { $out .= "IN WOO UPDATE MEMBERSHIP" . $nl; }
	
	$out .= "UserID: " . $userid . $nl;
	
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
	
	if ($expires_diff > 0)
		{
		$out .= "User has days remaining as premium member, check roles are correct" . $nl;
		$setpremium = true;
		}
	else
		{
		$out .= "User does not have days remaining as premium member, check roles are correct" . $nl;
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
	
	$out .= "Is user currently premium (by role)?: " . $ispremium . $nl;
	
	$out .= "Should user be premium?: " . $setpremium . $nl;
	
	$u = new WP_User($userid);


	
	
	
	if ($ispremium)
		{
		if ($setpremium)
			{ $out .= "Nothing to do, user is already premium and should be. All good" . $nl; }
		else
			{
			$out .= "Remove premium role" . $nl;
			$u->remove_role( 'premium_subscriber' );
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
	$order = wc_get_order( $order_id );
   //echo $nl . "ATTEMPTING TO DO THE MEMBERSHIP STUFF" . $nl . $nl;
   
   //$url = '/wptest/?page_id=595&orderid=' . $order_id;
   $url = "/my-account/view-order/" . $order_id;
   
   //if ( $order->status != 'failed' ) {
	if ( $order->status == 'completed' )
		{
		echo "DO THE MEMBERSHIP STUFF" . $nl;
      echo "Order id: " . $order_id . $nl;
      $out = woo_postpay_membership2($order_id);
      echo $out;
      echo "The above would normally happen silently and then you'd be redirected to the url below: " . $nl;
      echo "<a href=\"" . $url . "\">HERE</a>" . $nl;
      echo "<br><br>";
      echo "</div></div>";
      //echo "</body></html>";
      //wp_safe_redirect( $url );
      //exit;
		}
	else
		{
		//echo "<div style=\"background-color: #ffffffff; padding: 12px;\">";
		//echo "ORDER STATUS: " . $order->status . $nl;
		//echo "Once this order is set to completed you will be able to download your clips.<br>You can refresh this page if you need to. It should take you to the order once it's approved.". $nl;
		//echo "</div>";
		//echo $nl;
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
  register_setting( 'Woo-membership', 'woo_member_magic_product_ids' );
  register_setting( 'Woo-member', 'woo_member_disable_redirect' );
  register_setting( 'Woo-member', 'woo_member_debug' );
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
        <th scope="row">"Magic" product IDs:</th>
        <td><input type="text" size=50 name="woo_member_magic_product_ids" value="<?php echo esc_attr( get_option('woo_member_magic_product_ids') ); ?>" /><br>
        (Single value or comma-separated list of product IDs to trigger adding a membership
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Disable redirect after processing</th>
        <td><input type="checkbox" name="woo_member_disable_redirect" <?php if ( esc_attr( get_option('woo_member_disable_redirect') ) == "on" ) { echo "checked"; }; ?> /><br>
        Enable this to allow further checkout postpay processes, which use the "woocommerce_thankyou" action/hook, to happen rather than redirecting to the order summary page
        </td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Show debug info</th>
        <td><input type="checkbox" name="woo_member_debug" <?php if ( esc_attr( get_option('woo_member_debug') ) == "on" ) { echo "checked"; }; ?> /><br>
        Should be disabled unless trying to trace an error.
        </td>
        </tr>
        

    </table>
		<?php submit_button(); ?>
      </form>
	</div>
	<?php

   echo "woo_member_magic_product_ids: "              . get_option( 'woo_member_magic_product_ids' )  . $nl;
   echo "woo_member_disable_redirect: "               . get_option( 'woo_member_disable_redirect' )   . $nl;
   echo "woo_member_debug: "                          . get_option( 'woo_member_debug' )              . $nl;

  
}




