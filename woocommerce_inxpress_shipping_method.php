<?php
/*
Plugin Name: InXpress Shipping Extension
Plugin URI: http://inxpress.com/
Description: InXpress Shipping Extension
Version: 1.0.0
Author: InXpress
Author URI: http://inxpress.com/
*/
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	// ################ -Section for creating database tables for inxpress extension- START ############## //
	
	global $inxpress_db_version;
	$inxpress_db_version = '1.0';
	
	function inxpress_install_dhl() {
		
		global $wpdb;
		global $inxpress_db_version;
	
		$table_name = $wpdb->prefix . 'inxpress_dhl';
	
		/*
		 * We'll set the default character set and collation for this table.
		* If we don't do this, some characters could end up being converted
		* to just ?'s when saved in our table.
		*/
		$charset_collate = '';
	
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
	
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
	
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
					  `id_inxpress_dhl` int(11) NOT NULL AUTO_INCREMENT,
					  `supplies` varchar(255),
					  `length` float(11,2),
					  `width` float(11,2),
					  `height` float(11,2),
					  PRIMARY KEY (`id_inxpress_dhl`)
					) ENGINE=InnoDB $charset_collate;";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	
		add_option( 'inxpress_db_version', $inxpress_db_version );
	}
	
	function inxpress_install_data_dhl() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'inxpress_dhl';
	
		$query1="INSERT INTO `$table_name` (`id_inxpress_dhl`, `supplies`, `length`, `width`, `height`) VALUES
					(1, 'Express Envelope', '12.6', '9.4', '1'),
					(2, 'Express Legal Envelope', '15', '9.4', '1'),
					(3, 'Small Padded Pouch', '9.8', '12', '1'),
					(4, 'Large Padded Pouch', '11.9', '14.8', '1'),
					(5, 'Standard Flyer (Small Express Pack)', '11.8', '15.7', '1'),
					(6, 'Large Flyer (Large Express Pack)', '15', '18.7', '1'),
					(7, 'Box #2 Cube', '10.8', '5.8', '5.9'),
					(8, 'Box #2 Small', '12.5', '11.1', '1.5'),
					(9, 'Box #2 Medium', '13.2', '12.6', '2.0'),
					(10, 'Box #3 Large', '17.5', '12.5', '3.0'),
					(11, 'Box #3 Small Tri-Tube', '5', '5', '25'),
					(12, 'Box #4 Large Tri-Tube', '38.4', '6.9', '6.9');";
		
		$wpdb->query($query1);		
		
	}	
	
	function inxpress_install_variant() {
	
		global $wpdb;
		global $inxpress_db_version;
	
		$table_name = $wpdb->prefix . 'inxpress_variant';
	
		$charset_collate = '';
	
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
	
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
	
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
				  `id_inxpress_variant` int(11) NOT NULL AUTO_INCREMENT,
				  `product_id` int(11) DEFAULT NULL,`variant` varchar(255), `length` float(11,2),`width` float(11,2),`height` float(11,2),`dim_weight` float(11,2),`variable` varchar(255),
				   PRIMARY KEY (`id_inxpress_variant`)
				) ENGINE=InnoDB $charset_collate;";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );	
		
	}
		//activate
	register_activation_hook( __FILE__, 'inxpress_install_dhl' );
	register_activation_hook( __FILE__, 'inxpress_install_data_dhl' );
	register_activation_hook( __FILE__, 'inxpress_install_variant' );
	register_activation_hook( __FILE__, 'inxpress_activate_file' );
	
	function inxpress_activate_file()
	{
		$fileLocation1 = ABSPATH. "inxpress_activate.php";
		$newfile1 = fopen($fileLocation1,"wb");		
		$filetext = '<?php

		require("wp-blog-header.php");
		
		global $current_user;
			
		$current_user = get_userdata( $current_user->data->ID );
		
		$inxpress_config = get_option( "INEXPRESS_CONIG" );
		
		try
		{
		
			if(isset($_POST["key"]))
			{		
						
				if($_POST["key"] == $inxpress_config)				
				{
					
					$inxpress_settings = ( get_option("woocommerce_inxpress_shipping_settings"));
					
					//Configuration::updateValue("INXPRESS_STATUS", 2);
					//Configuration::updateValue("INXPRESS_DHL_ACCOUNT", $_POST["account_no"]);
					//Configuration::updateValue("INXPRESS_ACCOUNT", $_POST["inxpress_account_no"]);
					//Configuration::updateValue("INXPRESS_GATEWAY", "http://www.ixpapi.com/ixpapp/rates.php");
		
					$inxpress_settings["enabled"] = "yes";
					$inxpress_settings["acc_num"] = $_POST["account_no"];
					$inxpress_settings["inxpress_acc_num"] = $_POST["inxpress_account_no"];
								
					update_option("woocommerce_inxpress_shipping_settings", $inxpress_settings );
					
					echo "Configuration Data Has Been Saved Successfully!!!!"; die;
				} else {
					echo "key not matched! your key: ".$_POST["key"]." my key: ".$inxpress_config; die;
				}
			}
			else
			{
				echo "Key Is Empty"; die;
			}
		}
		catch(Exception $e)
		{
			echo $e; die;
		}';
		
		fwrite($newfile1, $filetext);
	}
	
	//deactivate file
	register_deactivation_hook( __FILE__, 'inxpress_deactivate_file' );
	function inxpress_deactivate_file()
	{
		$fileLocation = ABSPATH. "inxpress_activate.php";
		unlink($fileLocation);
	}
	// ################ -Section for creating database tables for inxpress extension- END ############## //
	
	/**
	 * inxpress_activation_check
	 *
	 * function to be called at the time of pluygin activation to make the configuration for the extension
	 *
	 */
	function inxpress_activation_check(){
	
		global $current_user;
			
		$current_user = get_userdata( $current_user->data->ID );
	
		if( !isset($current_user->first_name) ||
		!isset($current_user->last_name) ||
		!isset($current_user->data->user_email) ||
		($current_user->first_name == '') ||
		($current_user->last_name == '') ||
		($current_user->data->user_email == '')
		) {
			deactivate_plugins(basename(__FILE__)); // Deactivate ourself			
			wp_die("Sorry, but you can't run this plugin until you fill in your first name and last name in update profile fields. Update your profile to fill first name and last name and then activate this plugin.<br /> <a href='javascript: window.history.back();'>Click Here to go Back</a>");
	
		} else {
			//check if the configuration is not done for inxpress..
			//$inxpress_config = get_user_option( 'INEXPRESS_CONIG', get_current_user_id() );			
			$inxpress_config = get_option( 'INEXPRESS_CONIG' );			
			if (  $inxpress_config == '' || (!$inxpress_config)) {				
				
				$firstname = $current_user->first_name;
				$lastname = $current_user->last_name;
				$shopname = 'woocommerce_shop';
				$website = site_url();
				$email = $current_user->data->user_email;				
	
				$key1=md5(substr($website,8,6)."_".time().substr($email,2,5));
	
				$url = 'http://inxpressaz.force.com/leadcreation?cmp=woocommerce_shop&fn='.$current_user->first_name.'&ln='.$current_user->last_name.'&em='.$current_user->data->user_email;
	
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
				$data = curl_exec ($ch);
	
				update_option( 'INEXPRESS_CONIG', $key1 );
	
				$post_string = '';
				$params = array(
						'firstname'=>$firstname,
						'lastname'=>$lastname,
						'company'=>$shopname,
						'email'=>$email,
						'website'=>$website,
						'framework'=>3,
						'key'=>$key1,
				);
				update_option( 'INEXPRESS_CARRIER_KEY', $key1);
				foreach($params as $key=>$value) { $post_string .= $key.'='.$value.'&'; }
				$post_string = rtrim($post_string, '&');
	
				$url="http://www.ixpapi.com/ixpadmin/control/index.php/downloadInfo/create";
	
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
	
				$result = curl_exec($ch);
				curl_close($ch);
	
			}
		}
	}
	
	register_activation_hook(__FILE__, 'inxpress_activation_check');		
	
	function inxpress_shipping_method_init() {
		if ( ! class_exists( 'WC_inxpress_shipping_method' ) ) {
			
			/**
			 * 
			 * @name WC_inxpress_shipping_method
			 * 
			 * @property contain all the needed method definitions for InXress Shipping Fee Calculation
			 *
			 */
			class WC_inxpress_shipping_method extends WC_Shipping_Method {
				
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {					
					$this->id                 = 'inxpress_shipping'; // Id for your shipping method. Should be uunique.
					$this->method_title       = __( 'InXpress' );  // Title shown in admin
					$this->method_description = __( 'Using InXpress Shipping method' ); // Description shown in admin
 
					$this->enabled            = $this->settings['enabled']; // This can be added as an setting but for this example its forced enabled
					$this->title              = $this->settings['title']; // This can be added as an setting but for this example its forced.
 
					$this->init();
				}										
				
				
				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() {
					// Load the settings API					
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
					
					// Define user set variables
					$this->title = $this->get_option( 'title' );
					$this->enabled = $this->get_option( 'enabled' );
					$this->acc_num = $this->get_option( 'acc_num' );
					$this->inxpress_acc_num = $this->get_option( 'inxpress_acc_num' );
					$this->inxpress_handling_type = $this->get_option( 'inxpress_handling_type' );
					$this->inxpress_handling_applied = $this->get_option( 'inxpress_handling_applied' );
					$this->inxpress_handling_fee = $this->get_option( 'inxpress_handling_fee' );					
					$this->inxpress_countries = $this->get_option( 'inxpress_countries' );
					
					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
										
				}
				
				/**
				 * Initialise Gateway Settings Form Fields
				 */
				function init_form_fields() {									
					$this->form_fields = array(
							'enabled' => array(
									'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
									'type' 			=> 'checkbox',
									'label' 		=> __( 'Enable this shipping method', 'woocommerce' ),
									'default' 		=> 'no',
							),
							'title' => array(
									'title' => __( 'Title', 'woocommerce' ),
									'type' => 'text',
									'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
									'default' => __( 'DHL Express', 'woocommerce' )
							),
							'acc_num' => array(
									'title' => __( 'Account Number', 'woocommerce' ),
									'type' => 'text',
									'description' => __( 'This controls the account number of the admin user.', 'woocommerce' ),
									'default' => __( '', 'woocommerce' )
							),
							'inxpress_acc_num' => array(
									'title' => __( 'InXpress Account Number', 'woocommerce' ),
									'type' => 'text',
									'description' => __( 'This controls the account number of the admin user.', 'woocommerce' ),
									'default' => __( '', 'woocommerce' )
							),
							'inxpress_handling_type' => array(
									'title' => __( 'Calculate Handling Fee', 'woocommerce' ),
									'type' => 'select',
									'options' => array(
													'1' => __('Fixed', 'woocommerce'),
													'2' => __('Percent', 'woocommerce')
												),
									'description' => __( '', 'woocommerce' ),
									'default' => __( '', 'woocommerce' )
							),
							'inxpress_handling_applied' => array(
									'title' => __( 'Handling Applied', 'woocommerce' ),
									'type' => 'select',
									'options' => array(
													'1' => __('Per Order', 'woocommerce'),
													'2' => __('Per Package', 'woocommerce')
												),
									'description' => __( '', 'woocommerce' ),
									'default' => __( '', 'woocommerce' )
							),
							'inxpress_handling_fee' => array(
									'title' => __( 'Handling Fee', 'woocommerce' ),
									'type' => 'text',
									'description' => __( '', 'woocommerce' ),
									'default' => __( '', 'woocommerce' )
							),
							'inxpress_countries' => array(
									'title' => __( 'Ship to Applicable Countries', 'woocommerce' ),
									'type' 			=> 'multiselect',
									'class'			=> 'chosen_select',
									'css'			=> 'width: 450px;',
									'default' 		=> '',
									'options'		=> WC()->countries->get_shipping_countries(),
									'custom_attributes' => array(
													'data-placeholder' => __( 'Select some countries', 'woocommerce' )
													)									
							),
					);
				}
 
				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package ) {
					$inxpress_settings = ( get_option('woocommerce_inxpress_shipping_settings'));										
					if(isset($inxpress_settings['enabled']) && ($inxpress_settings['enabled'] == 'yes')){													
						$rate = array(	
							'id' => $this->id,													
							'calc_tax' => 'per_item',	
							'country' => $package['destination']['country'],	
							'zip' => $package['destination']['postcode']	
						);	
						 	
						// Register the rate	
						$this->add_rate( $rate );					}
				}
				
				
				/**
				 * calcRate_old
				 */
				public function calcRate_old($account,$code,$country,$weight,$length,$width,$height,$zip,$pro_weight)				
				{
					$inxpress_settings = ( get_option('woocommerce_inxpress_shipping_settings'));					
					if(isset($inxpress_settings['enabled']) && ($inxpress_settings['enabled'] == 'yes')){  	
				    	
						echo "weight: ".$weight." l: ".$length." w: ".$width." h: ".$height;
						
						if(($pro_weight>$weight)||($length==0)||($width==0)||($height==0))	
				    	{	
				    		$url = 'http://www.ixpapi.com/ixpapp/rates.php?acc='.$account.'&dst='.$country.'&prd='.$code.'&wgt='.$weight.'&pst='.$zip;	
				    	}	
				    	else 	
				    	{	
				    		$url = 'http://www.ixpapi.com/ixpapp/rates.php?acc='.$account.'&dst='.$country.'&prd='.$code.'&wgt='.$weight.'&pst='.$zip.'&pcs='.$length.'|'.$width.'|'.$height.'|'.$pro_weight;	
				    	}		
				    	echo $url;
						$ch = curl_init();	
						curl_setopt($ch, CURLOPT_URL,$url);	
						curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);	
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);	
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);	
						$data = curl_exec ($ch);	
						curl_close ($ch); 	
						$xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $data);	
						$xml = simplexml_load_string($xml);	
						$json = json_encode($xml);	
						$responseArray = json_decode($json,true);	
							
						if(isset($responseArray['totalCharge']))	
						{	
							$response=array();	
							$response['price']=$responseArray['totalCharge'];	
							$response['days']=$responseArray['info']['baseCountryTransitDays'];	
							
							return $response;	
						}	
						else 	
						{	
							return false;	
						}	
							
				    }
				}
				
				/**
				 * calcRate
				 * 
				 * @property used to calculate the exact rate for any shipping method according to product weight and zip code with country code
				 * 
				 */
				public function calcRate($account,$country,$final_weight,$final_lbh,$zip,$final_pro_weight)
				{
					$inxpress_settings = ( get_option('woocommerce_inxpress_shipping_settings'));
					if(isset($inxpress_settings['enabled']) && ($inxpress_settings['enabled'] == 'yes')){

						if($final_weight>0.5) {
								
							$code='P';
						}
						else if($final_weight!=0 && $final_weight<=0.5)
						{
							$code='X';
						}
						
						$final_lbh = rtrim($final_lbh, ';');
						
						if(($final_pro_weight>$final_weight)||($final_lbh == ''))
						{
							$url = 'http://www.ixpapi.com/ixpapp/rates.php?acc='.$account.'&dst='.$country.'&prd='.$code.'&wgt='.$final_weight.'&pst='.$zip;
						}
						else
						{
							$url = 'http://www.ixpapi.com/ixpapp/rates.php?acc='.$account.'&dst='.$country.'&prd='.$code.'&wgt='.$final_weight.'&pst='.$zip.'&pcs='.$final_lbh;
						}
						//echo $url;
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL,$url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
						$data = curl_exec ($ch);
						curl_close ($ch);
						$xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $data);
						$xml = simplexml_load_string($xml);
						$json = json_encode($xml);
						$responseArray = json_decode($json,true);
							
						if(isset($responseArray['totalCharge']))
						{
							$response=array();
							$response['price']=$responseArray['totalCharge'];
							$response['days']=$responseArray['info']['baseCountryTransitDays'];
								
							return $response;
						}
						else
						{
							return false;
						}
							
					}
				}
				
			    /**
			     * add_rate
			     * 
			     * function to calculate the rate of product weight wise and also the transit days
			     * 
			     * @see WC_Shipping_Method::add_rate()
			     */
				function add_rate( $args = array() ) {
					
					$inxpress_settings = ( get_option('woocommerce_inxpress_shipping_settings'));
					
						$total_pro = 0;	
						$trans_days = 0;	
											
						$defaults = array(	
								'id'     => '',      // ID for the rate	
								'label' => $this->title,      // Label for the rate	
								'cost'     => '0',      // Amount or array of costs (per item shipping)	
								'taxes'   => '',      // Pass taxes, nothing to have it calculated for you, or 'false' to calc no tax	
								'calc_tax'  => 'per_order'  // Calc tax per_order or per_item. Per item needs an array of costs	
						);	
						
						$args = wp_parse_args( $args, $defaults );	
															
						extract( $args );	
											
						// Id and label are required	
						if ( ! $id || ! $label ) return;	
							
										
						// Handle cost	
						$total_cost = ( is_array( $cost ) ) ? array_sum( $cost ) : $cost;	
							
						$countries_selected = $this->inxpress_countries;	
							
						if(($countries_selected=='')||(in_array($country,$countries_selected)))	
						{	
							
							$final_pro_weight = 0.0;
							$final_weight = 0.0;
							$final_lbh = '';
							
							if($cost == '' || $cost == 0){	
								$cart = WC()->cart->get_cart();	
																		
								foreach ( $cart as $product ) {	
										
									wp_cache_flush();
									
									clean_post_cache( $product['product_id'] );
			
									$dimweight = $this->getDimweight($product['product_id']);	
																	
									$product['weight'] = get_post_meta( $product['product_id'], '_weight', true );	
																	
									if(!empty($dimweight))	
									{	
										$weight=($dimweight['dim_weight'] > $product['weight'] )? $dimweight['dim_weight'] : $product['weight'];	
										
										$variable = $dimweight['variable'];	
										
										$final_lbh .=  $dimweight['length'].'|'.$dimweight['width'].'|'.$dimweight['height'].'|'.$product['weight'].';';
									}	
									else	
									{	
										$weight=$product['weight'];	
									}
									
									$code='';
									if($weight>0.5) {
											
										$code='P';
									}
									else if($weight!=0&&$weight<=0.5)
									{
										$code='X';
									}
									
									if(!empty($dimweight))
									{
											
										if((isset($variable))&&($variable!=''&&$variable!=0))
										{
											if($variable>=$product['quantity'])
											{
												$final_weight += $weight;
												//$price=$this->calcRate($this->acc_num,$code,$country,$weight,$dimweight['length'],$dimweight['width'],$dimweight['height'],$zip,$product['weight']);
											}
											else if($variable<$product['quantity'])
											{
												$qty=ceil(($product['quantity'])/$variable);
												$weight=$weight*$qty;
												
												$final_weight += $weight;
												//$price=$this->calcRate($this->acc_num,$code,$country,$weight,$dimweight['length'],$dimweight['width'],$dimweight['height'],$zip,$product['weight']);
											}
										}
										else
										{
											$weight=$weight*$product['quantity'];
											
											$final_weight += $weight;
											//$price=$this->calcRate($this->acc_num,$code,$country,$weight,$dimweight['length'],$dimweight['width'],$dimweight['height'],$zip,$product['weight']);
												
										}
											
											
									}
									else
									{
										$weight=$weight*$product['quantity'];
										
										$final_weight += $weight;
										//$price=$this->calcRate($this->acc_num,$code,$country,$weight,0,0,0,$zip,$product['weight']);
									}
									
									
									$final_pro_weight +=  $product['weight'];								
									
									
									//$price=$this->calcRate($this->acc_num,$code,$country,$weight,$dimweight['length'],$dimweight['width'],$dimweight['height'],$zip,$product['weight']);
									
									if($price)
									{
										$shippingPrice=($shippingPrice+$price['price']);
									}
										
															
							}	
							
							$price=$this->calcRate($this->acc_num,$country,$final_weight,$final_lbh,$zip,$final_pro_weight);
							
							//print_r($price);
							
							if($price)
							{
								$shippingPrice=($shippingPrice+$price['price']);
							}

							$shippingPrice = $this->getFinalPriceWithHandlingFee($cart,$shippingPrice,$country);
								
							$total_cost = $shippingPrice;
							
							$trans_days = $price['days'];
							$defaults['label'] = $this->title." (Transit Days: ".$trans_days." ) ";
							
							$label = $defaults['label'];
								
							if($shippingPrice!=0)
							{
							
								$this->title .= __(' (Transit Days: ').$trans_days.' ) ';
									
								$this->rates[] = new WC_Shipping_Rate( $id, $label, $total_cost, $taxes, $this->id );
							
							}
							
							
		
						}	
					}					
				}

				/**
				 * getDimweight
				 * 
				 * @property get the dimensional weight of any product from database
				 * 
				 */
				public function getDimweight($id)
				{
					try
					{
						global $wpdb;
						$table_name = $wpdb->prefix . 'inxpress_variant';
						$query='SELECT * FROM `'.$table_name.'` WHERE `product_id`='.$id;
						$variant=$wpdb->get_row($query, 'ARRAY_A');
						
						return $variant;
					}
					catch(Exception $e)
					{
						return false;
					}
				}
				
				/**
				 * getFinalPriceWithHandlingFee
				 * 
				 * @property calculate the final rate includign the handling fee if provided by the admin
				 * 
				 */
				public function getFinalPriceWithHandlingFee($products,$shipping_price,$country)
				{
					$countries_selected = $this->inxpress_countries;
					$handling_fee = $this->inxpress_handling_fee;
					$handling_type = $this->inxpress_handling_type;
					$handling_applied = $this->inxpress_handling_applied;
					
					if(($countries_selected=='')||(in_array($country,$countries_selected)))
					{
						if((isset($handling_fee))&&($handling_fee!='')&&($handling_type=='1'))
						{
							if((isset($handling_applied))&&($handling_applied!='')&&($handling_applied=='1'))
							{
								$final_price=$shipping_price+$handling_fee;
								return $final_price;
							}
							else if((isset($handling_applied))&&($handling_applied!='')&&($handling_applied=='2'))
							{
								$final_price=$shipping_price+((count($products))*$handling_fee);
								return $final_price;
							}
							else
							{
								return $shipping_price;
							}
						}
						else if((isset($handling_fee))&&($handling_fee!='')&&($handling_type=='2'))
						{
							if((isset($handling_applied))&&($handling_applied!='')&&($handling_applied=='1'))
							{
								$final_price=$shipping_price+(($shipping_price/100)*$handling_fee);
								return $final_price;
							}
							else if((isset($handling_applied))&&($handling_applied!='')&&($handling_applied=='2'))
							{
								$final_price=$shipping_price+((count($products))*(($shipping_price/100)*$handling_fee));
								return $final_price;
							}
							else
							{
								return $shipping_price;
							}
						}
						else
						{
							return $shipping_price;
						}
					}
					else
					{
						return 0;
					}
				}
				
				
			}
		}
	}
	
	add_action( 'woocommerce_shipping_init', 'inxpress_shipping_method_init' );
		 
	function add_inxpress_shipping_method( $methods ) {
		$methods[] = 'WC_inxpress_shipping_method';
		return $methods;
	}
 
	add_filter( 'woocommerce_shipping_methods', 'add_inxpress_shipping_method' );
		
	
	// ################ -Section for creating submenus for dimensional weight and DHL boxes- START ############## //	
	// creating admin submenu pages..
	add_action('admin_menu', 'inxpress_manage_dimensional_weight');	
	add_action('admin_menu', 'inxpress_manage_DHL_boxes');	
	add_action('admin_menu', 'inxpress_add_DHL_boxes');	
	add_action('admin_menu', 'inxpress_add_dimensional_weight');	
	add_action('admin_menu', 'inxpress_edit_dimensional_weight');
	
	
	function inxpress_manage_dimensional_weight() {
		add_submenu_page( 'edit.php?post_type=product', 'Manage Dimensional Weight', 'Manage Dimensional Weight', 'administrator', 'inxpress_manage_dimensional_weight', 'inxpress_manage_dimensional_weight_function');		
	}
	
	function inxpress_manage_DHL_boxes() {
		add_submenu_page( 'edit.php?post_type=product', 'Manage DHL Boxes', 'Manage DHL Boxes', 'administrator', 'inxpress_manage_DHL_boxes', 'inxpress_manage_DHL_boxes_function');
	}
	
	function inxpress_add_DHL_boxes() {
		add_submenu_page( null, 'Add DHL Boxes', 'Add DHL Boxes', 'administrator', 'inxpress_add_DHL_boxes', 'inxpress_add_DHL_boxes_function');
	}
	
	function inxpress_add_dimensional_weight() {
		add_submenu_page( null, 'Import CSV for Dimensional Weight', 'Import CSV for Dimensional Weight', 'administrator', 'inxpress_add_dimensional_weight', 'inxpress_add_dimensional_weight_function');
	}
	
	function inxpress_edit_dimensional_weight() {
		add_submenu_page( NULL, 'Edit Dimensional Weight', 'Edit Dimensional Weight', 'administrator', 'inxpress_edit_dimensional_weight', 'inxpress_edit_dimensional_weight_function');
	}
	
	
	/**
	 * inxpress_manage_DHL_boxes_function
	 * 
	 * @property managing dhl boxes (Add | Edit | Delete)
	 */
	function inxpress_manage_DHL_boxes_function() {	
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'inxpress_dhl';
		
		if(isset($_GET['delete_box_id']) && ($_GET['delete_box_id'] != '')){
			$wpdb->delete( $table_name, array( 'id_inxpress_dhl' => $_GET['delete_box_id'] ), array( '%d' ) );
			?>
			<div class="updated">
		        <p><?php echo __( 'DHL Box Deleted successfully.' ); ?></p>
		    </div>
		    <?php
		}		
		
		$dhl_boxes = $wpdb->get_results(
				"
				SELECT *
				FROM `$table_name`				
				"
		);	
		
		?>
		<div class="wrap"><div id="icon-tools" class="icon32"></div>
		<h2>
			Manage DHL Boxes 
			<a class="add-new-h2" href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_add_DHL_boxes" ); ?>">Add DHL Box</a>
		</h2>
		<table class="wp-list-table widefat fixed posts">
			<thead>
				<tr>
					<th style="width: 3.2em;" class="manage-column column-cb check-column" id="cb" scope="col">			
						<span style="margin-left: 8px;">ID</span>
					</th>
					<th style="" class="manage-column column-cb" id="cb" scope="col">			
						<span>Box Name</span>
					</th>
					<th style="" class="manage-column column-cb" id="cb" scope="col">			
						<span>Length</span>
					</th>
					<th style="" class="manage-column column-cb" id="cb" scope="col">			
						<span>Width</span>
					</th>
					<th style="" class="manage-column column-cb" id="cb" scope="col">			
						<span>Height</span>
					</th>					
				</tr>		
			</thead>
			
			<tfoot>
				<tr>
					<th style="width: 3.2em;" class="manage-column column-cb check-column" id="cb" scope="col">			
						<span style="margin-left: 8px;">ID</span>
					</th>
					<th style="" class="manage-column column-cb" id="cb" scope="col">			
						<span>Box Name</span>
					</th>
					<th style="" class="manage-column column-cb" id="cb" scope="col">			
						<span>Length</span>
					</th>
					<th style="" class="manage-column column-cb" id="cb" scope="col">			
						<span>Width</span>
					</th>
					<th style="" class="manage-column column-cb" id="cb" scope="col">			
						<span>Height</span>
					</th>					
				</tr>		
			</tfoot>
		
			<tbody id="the-list">
			<?php 
			if ( $dhl_boxes )
			{
				foreach ( $dhl_boxes as $box )
				{
					?>
					<tr>
						<th><?php echo $box->id_inxpress_dhl; ?></th>
						<td class="column-title">
							<strong><a class="row-title" href=""><?php echo $box->supplies; ?></a></strong>
							<div class="row-actions">
								<span class="edit"><a title="Edit this item" href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_add_DHL_boxes&box_id=$box->id_inxpress_dhl" ); ?>">Edit</a> | </span>								
								<span class="trash"><a href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_manage_DHL_boxes&delete_box_id=$box->id_inxpress_dhl" ); ?>" title="Delete this item" class="submitdelete">Delete</a></span>	
							</div>							
						</td>
						<td><?php echo $box->length; ?></td>
						<td><?php echo $box->width; ?></td>
						<td><?php echo $box->height; ?></td>						
					</tr>
					<?php 
				}
			}
			else
			{
			?>
					<tr><td colspan="5"><h2>No DHL box Found</h2></td></tr>
					<?php
			}
			
			?>
			</tbody>
		</table>
		</div>
		<?php			
	}
	
	/**
	 * inxpress_add_DHL_boxes_function
	 *
	 * @property managing dhl boxes (Add)
	 */
	function inxpress_add_DHL_boxes_function(){
		global $wpdb;
		$row_box = '';
		$table_name = $wpdb->prefix . 'inxpress_dhl';
		
		if(isset($_POST['submit_dhl'])){
			
			//save to database..
			$name = isset($_POST['dhl_box_name']) ? $_POST['dhl_box_name']: '';
			$length = isset($_POST['dhl_box_length']) ? $_POST['dhl_box_length']: '';
			$width = isset($_POST['dhl_box_width']) ? $_POST['dhl_box_width']: '';
			$height = isset($_POST['dhl_box_height']) ? $_POST['dhl_box_height']: '';
			
			$msg = '';
			
			if($name != '' && $length != ''  && $width != '' && $height != ''){

				if(isset($_POST['hdn_box_id']) && ($_POST['hdn_box_id']!= '')){
					$wpdb->update(
							$table_name,
							array(
									'supplies' => $name,
									'length' => $length,
									'width' => $width,
									'height' => $height
							),
							array( 'id_inxpress_dhl' => $_POST['hdn_box_id'] ),
							array(
									'%s',
									'%f',
									'%f',
									'%f'
							),
							array( '%d' )
					);
		
					$msg = 'Updated';

				} else {	
					$wpdb->insert(
							$table_name,
							array(
									'supplies' => $name,
									'length' => $length,
									'width' => $width,
									'height' => $height
							),
							array(
									'%s',
									'%f',
									'%f',
									'%f'
							)
					);

					$msg = 'Added';
					
				}
				?>
				<div class="updated">
			        <p><?php echo __( "Congrats! $msg successfully." ); ?></p>
			    </div>
				<?php	
			} else {
				?>
				<div class="error">
			        <p><?php echo __( 'Please fill all the fields!' ); ?></p>
			    </div>
			    <?php
			}
			
		}
		
		if(isset($_GET['box_id']) && ($_GET['box_id'] != '' )){
			$row_box = $wpdb->get_row("SELECT * FROM $table_name WHERE id_inxpress_dhl = ".$_GET['box_id']);
		}
		
		$dhl_boxes = $wpdb->get_results(
				"
				SELECT *
				FROM `$table_name`
				"
		);
		
		?>
		<div class="wrap"><div id="icon-tools" class="icon32"></div>
			<h2>
				<?php if(isset($row_box->supplies)): echo 'Edit'; else: echo 'Add'; endif; ?> DHL Box			
			</h2>
			<form class="validate" id="createuser" name="createuser" method="post" action="">
				<table class="form-table">
					<tbody>
						
						<tr class="form-field form-required">
							<th scope="row"><label for="user_login">Name <span class="description">(required)</span></label></th>
							<td>
								<input type="text" value="<?php if(isset($row_box->supplies)): echo $row_box->supplies; endif; ?>" id="dhl_box_name" name="dhl_box_name">
								<input type="hidden" name="hdn_box_id" value="<?php if(isset($row_box->id_inxpress_dhl)): echo $row_box->id_inxpress_dhl; endif; ?>" />
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="email">Length <span class="description">(required)</span></label></th>
							<td><input type="text" value="<?php if(isset($row_box->length)): echo $row_box->length; endif; ?>" id="dhl_box_length" name="dhl_box_length"></td>
						</tr>
						<tr class="form-field">
							<th scope="row"><label for="first_name">Width <span class="description">(required)</span></label></th>
							<td><input type="text" value="<?php if(isset($row_box->width)): echo $row_box->width; endif; ?>" id="dhl_box_width" name="dhl_box_width"></td>
						</tr>
						<tr class="form-field">
							<th scope="row"><label for="last_name">Height <span class="description">(required)</span></label></th>
							<td><input type="text" value="<?php if(isset($row_box->height)): echo $row_box->height; endif; ?>" id="dhl_box_height" name="dhl_box_height"></td>
						</tr>					
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" value="<?php if(isset($row_box->supplies)): echo 'Update'; else: echo 'Add'; endif; ?> Box" class="button button-primary" id="submit_dhl" name="submit_dhl">
					<a href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_manage_DHL_boxes"); ?>" class="button button-primary">Back to list</a>
				</p>
			</form>
		</div>
		<?php				
	}
	
	
	/**
	 * inxpress_manage_dimensional_weight_function
	 *
	 * @property managing dhl boxes (Add | Edit | Delete)
	 */
	function inxpress_manage_dimensional_weight_function() {	
		global $wpdb;
	
		$table_name = $wpdb->prefix . 'inxpress_variant';
		
		if(isset($_GET['delete_dim_id']) && ($_GET['delete_dim_id'] != '')){

			$qry_del_meta = "SELECT * FROM `$table_name` WHERE `id_inxpress_variant` = ".$_GET['delete_dim_id'];

			
			$res_del_meta = $wpdb->get_row($qry_del_meta);
			
			if(isset($res_del_meta->product_id)){

				update_post_meta($res_del_meta->product_id, '_length', '0.0');
				update_post_meta($res_del_meta->product_id, '_width', '0.0');
				update_post_meta($res_del_meta->product_id, '_height', '0.0');				
			}

			$wpdb->delete( $table_name, array( 'id_inxpress_variant' => $_GET['delete_dim_id'] ), array( '%d' ) );
			?>
			<div class="updated">
		        <p><?php echo __( 'Deleted successfully.' ); ?></p>
		    </div>
		    <?php
		}
	
		$dhl_boxes = $wpdb->get_results(
				"
				SELECT *
				FROM `$table_name`
				"
		);		
	
		?>
			<div class="wrap"><div id="icon-tools" class="icon32"></div>
			<h2>
				Manage Dimensional Weight 
				<a class="add-new-h2" href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_add_dimensional_weight" ); ?>">Import CSV</a>
			</h2>
			<table class="wp-list-table widefat fixed posts">
				<thead>
					<tr>
						<th style="width: 3.2em;" class="manage-column column-cb check-column" id="cb" scope="col">			
							<span style="margin-left: 8px;">ID</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Product ID</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Product Name</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Length</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Width</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Height</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Dim Weight</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Variable</span>
						</th>					
					</tr>		
				</thead>
				
				<tfoot>
					<tr>
						<th style="width: 3.2em;" class="manage-column column-cb check-column" id="cb" scope="col">			
							<span style="margin-left: 8px;">ID</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Product ID</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Product Name</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Length</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Width</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Height</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Dim Weight</span>
						</th>
						<th style="" class="manage-column column-cb" id="cb" scope="col">			
							<span>Variable</span>
						</th>				
					</tr>		
				</tfoot>
			
				<tbody id="the-list">
				<?php 
				if ( $dhl_boxes )
				{
					foreach ( $dhl_boxes as $box )
					{
						?>
						<tr>
							<th><?php echo $box->id_inxpress_variant; ?></th>
							<th><?php echo $box->product_id; ?></th>
							<td class="column-title">
								<strong><a class="row-title" href=""><?php echo $box->variant; ?></a></strong>
								<div class="row-actions">
									<span class="edit"><a title="Edit this item" href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_edit_dimensional_weight&dim_id=$box->id_inxpress_variant" ); ?>">Edit</a> | </span>								
									<span class="trash"><a href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_manage_dimensional_weight&delete_dim_id=$box->id_inxpress_variant" ); ?>" title="Delete this item" class="submitdelete">Delete</a></span>	
								</div>							
							</td>
							<td><?php echo $box->length; ?></td>
							<td><?php echo $box->width; ?></td>
							<td><?php echo $box->height; ?></td>	
							<td><?php echo $box->dim_weight; ?></td>	
							<td><?php echo $box->variable; ?></td>						
						</tr>
						<?php 
					}
				}
				else
				{
				?>
						<tr><td colspan="5"><h3>No Dimensional Weight for any product Found.</h3></td></tr>
						<?php
				}
				
				?>
				</tbody>
			</table>
			</div>
			<?php				
		}
	
		
		/**
		 * inxpress_manage_dimensional_weight_function
		 *
		 * @property managing dhl boxes (Add | Edit | Delete)
		 */
		function inxpress_add_dimensional_weight_function() {
			global $wpdb;
			$row_box = '';
			$table_name = $wpdb->prefix . 'inxpress_variant';
			
			if(isset($_POST['submit_dhl'])){
				
				if(isset($_FILES['file']['name']) && ($_FILES['file']['name'] != '') ){
					
					$handle = fopen($_FILES["file"]["tmp_name"], "r");
					$data = fgetcsv($handle, 4000, ",");
					$indexes=array();
					foreach($data as $key=>$val)
					{
						$indexes[$val]=$key;
					}
					if(!isset($indexes['length'])||!isset($indexes['width'])||!isset($indexes['height'])||!isset($indexes['id']))
					{
						die('Some required attributes are missing.');
							
					}
					$count=0;
					$lbh_check=0;
					$success=0;
					$msg='';$smsg='';
					while(($data = fgetcsv($handle, 4000, ",")) !== FALSE)
					{
						$data1=array();
						
						//print_r($data);
											
						$post_type = get_post_type( $data[$indexes['id']]);
						
						$post_title = get_the_title( $data[$indexes['id']] );
						
						//echo "post title: ".$post_title."----";
						
						if(($post_title != '') && ($post_type == 'product'))
						{
							if(($data[$indexes['length']])==''||($data[$indexes['width']])==''||($data[$indexes['height']])=='')
							{
					
								$lbh_check++;
					
							}
							else
							{
								$dim_weight=ceil((((float)$data[$indexes['length']])*((float)$data[$indexes['width']])*((float)$data[$indexes['height']]))/139);
								
								$query='SELECT `id_inxpress_variant` FROM `'.$table_name.'` WHERE `product_id`='.$data[$indexes['id']];
								
								$variant = $wpdb->get_row($query);
								
								if(!empty($variant))
								{
									$query='UPDATE `'.$table_name.'` SET `length`="'.$data[$indexes['length']].'",`width`="'.$data[$indexes['width']].'",`height`="'.$data[$indexes['height']].'",`dim_weight`="'.$dim_weight.'" WHERE `product_id`='.$data[$indexes['id']];
									$wpdb->query($query);
									
									update_post_meta($data[$indexes['id']], '_length', $data[$indexes['length']]);
									update_post_meta($data[$indexes['id']], '_width', $data[$indexes['width']]);
									update_post_meta($data[$indexes['id']], '_height', $data[$indexes['height']]);
									
									$success++;
								}
								else
								{
									$query ='INSERT INTO `'.$table_name.'`
											( `id_inxpress_variant`,`product_id`, `variant`,`length`,`width`,`height`,`dim_weight`,`variable`)
											VALUES
											("","'.$data[$indexes['id']].'","'.$post_title.'","'.$data[$indexes['length']].'","'.$data[$indexes['width']].'","'.$data[$indexes['height']].'","'.$dim_weight.'","")';
					
									$wpdb->query($query);
									
									update_post_meta($data[$indexes['id']], '_length', $data[$indexes['length']]);
									update_post_meta($data[$indexes['id']], '_width', $data[$indexes['width']]);
									update_post_meta($data[$indexes['id']], '_height', $data[$indexes['height']]);
									
									$success++;
								}
							}
					
						}
						else
						{
							$count++;
						}
							
						if($count!=0)
						{
							$msg = $count.' Id\'s of csv are not matching to shop products..';
						}
						if($lbh_check!=0)
						{
							$msg .= "<br />". $lbh_check.' Row\'s of csv have not valid LBH value..';
						}
						if($success!=0)
						{
							$smsg = 'Csv Imported Successfully.';
						}
						
					}
					
					if($msg != ''){
					?>
						<div class="error">
					        <p><?php echo __( $msg ); ?></p>
					    </div>
					<?php
					}
					
					if($smsg != ''){
					?>
						<div class="updated">
					        <p><?php echo __( $smsg ); ?></p>
					    </div>
					<?php
					}
				} else {
					?>
					<div class="error">
				        <p><?php echo __( 'Please browse a file to import!' ); ?></p>
				    </div>
				    <?php
				}
				
			}
					
			?>
			<div class="wrap"><div id="icon-tools" class="icon32"></div>
				<h2>
					Import CSV
				</h2>
				<form class="validate" id="createuser" name="createuser" method="post" action="" enctype="multipart/form-data">
					<table class="form-table">
						<tbody>
							<tr class="form-field form-required">
								<th scope="row"><label for="email">File <span class="description">(required)</span></label></th>
								<td><input id="inxp_inp" type="file" name="file"></td>
							</tr>											
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" value="Import CSV" class="button button-primary" id="submit_dhl" name="submit_dhl">
						<a href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_manage_dimensional_weight"); ?>" class="button button-primary">Back to list</a>
					</p>
				</form>
			</div>
		<?php				
	}
	
	/**
	 * inxpress_edit_dimensional_weight_function
	 *
	 * @property managing dhl boxes (Edit)
	 */
	function inxpress_edit_dimensional_weight_function() {
		global $wpdb;
		$row_box = '';
		$table_name_dim = $wpdb->prefix . 'inxpress_variant';
		$table_name_dhl = $wpdb->prefix . 'inxpress_dhl';
		
		if(isset($_POST['submit_variant'])){

			//doing update..
			if(isset($_POST['hdn_dim_id']) && ($_POST['hdn_dim_id'] != '')){			
			$query='UPDATE `'.$table_name_dim.'` 
					SET 
						`length`="'.$_POST['length'].'",
						`width`="'.$_POST['width'].'",
						`height`="'.$_POST['height'].'",
						`dim_weight`="'.$_POST['dim_weight'].'",
						`variable` = "'.$_POST['variable'].'",
						`variant` = "'.$_POST['variant'].'"  
					WHERE 
						`id_inxpress_variant`='.$_POST['hdn_dim_id'];
			$wpdb->query($query);
			
			update_post_meta($_POST['product_id'], '_length', $_POST['length']);
			update_post_meta($_POST['product_id'], '_width', $_POST['width']);
			update_post_meta($_POST['product_id'], '_height', $_POST['height']);
			
			?>
			<div class="updated">
		        <p><?php echo __('Updated Successfully!'); ?></p>
		    </div>
			<?php 
			} else {
				$query='INSERT INTO `'.$table_name_dim.'`
					SET
						`length`="'.$_POST['length'].'",
						`width`="'.$_POST['width'].'",
						`height`="'.$_POST['height'].'",
						`dim_weight`="'.$_POST['dim_weight'].'",
						`variable` = "'.$_POST['variable'].'",
						`product_id` = "'.$_POST['product_id'].'",
						`variant` = "'.$_POST['variant'].'" 
				';
					
				$wpdb->query($query);
					
				update_post_meta($_POST['product_id'], '_length', $_POST['length']);
				update_post_meta($_POST['product_id'], '_width', $_POST['width']);
				update_post_meta($_POST['product_id'], '_height', $_POST['height']);
					
				?>
				<div class="updated">
			        <p><?php echo __('Added Successfully!', 'woocommerce'); ?></p>
			    </div>
				<?php 
			}
		}
				
		if(isset($_GET['dim_id']) && ($_GET['dim_id'] != '' )){
			$row_dim = $wpdb->get_row("SELECT * FROM $table_name_dim WHERE id_inxpress_variant = ".$_GET['dim_id']);
		}
		
		if(isset($_GET['product_id']) && ($_GET['product_id'] != '' )){
			$row_dim = $wpdb->get_row("SELECT * FROM $table_name_dim WHERE product_id = ".$_GET['product_id']);
		}
		
		$dhl_boxes = $wpdb->get_results(
				"
				SELECT *
				FROM `$table_name_dhl`
				"
		);
		
		?>
		<script type="text/javascript">	
			jQuery('document').ready(function()
				{
					jQuery('.tdvariants a').click(function(){
						jQuery(this).hide();
						jQuery(this).parent().find('input[type=text]').attr('value',jQuery(this).text());
						jQuery(this).parent().find('input[type=text]').css('display','block');
					});
					jQuery('.tdvariants input').blur(function(){
						updateValues();
					});
				});		
			
			function validate()
			{
				if((jQuery('#length').val()=='')||(jQuery('#width').val()=='')||(jQuery('#height').val()==''))
				{
					alert('Length, Width and Height are required');
					return false;
				}
				else
				{
					variant_form.submit();
				}
			}
			function updateValues()
			{
				length=parseFloat(jQuery('#length').val());
				width=parseFloat(jQuery('#width').val());
				height=parseFloat(jQuery('#height').val());
				dim_weight=Math.ceil((length*width*height)/139);
				jQuery('.length a').html(length);
				jQuery('.width a').html(width);
				jQuery('.height a').html(height);
				jQuery('.dim_weight a').html(dim_weight);
				jQuery('#dim_weight').val(dim_weight);
			}
			function selectvariant(id)
			{
				
				length=parseFloat(jQuery('#length_'+id).html());
				width=parseFloat(jQuery('#width_'+id).html());
				height=parseFloat(jQuery('#height_'+id).html());
				jQuery('#length').val(length);
				jQuery('.length a').html(length);
				jQuery('#width').val(width);
				jQuery('.width a').html(width);
				jQuery('#height').val(height);
				jQuery('.height a').html(height);
				dim_weight=Math.ceil((length*width*height)/139);
				jQuery('#dim_weight').val(dim_weight);
				jQuery('.dim_weight a').html(dim_weight);

				
			}
		</script>
		<div class="wrap"><div id="icon-tools" class="icon32"></div>
			<h2>
				<?php if(isset($row_dim->id_inxpress_variant)): echo 'Edit'; else: echo 'Add'; endif; ?> Dimensional Weight			
			</h2>
			<form class="validate" id="createuser" name="createuser" method="post" action="">
				<table class="wp-list-table widefat fixed posts">					
					<tbody>					
						<tr>
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Variant</strong></span>
							</th>							
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Length</strong></span>
							</th>
							<th style=";" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Width</strong></span>
							</th>
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Height</strong></span>
							</th>	
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Dim Weight</strong></span>
							</th>
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Variable</strong></span>
							</th>
							<th colspan="2" style="" class="manage-column column-cb" id="cb" scope="col">			
								<span>&nbsp;</span>
							</th>				
						</tr>	
						<tr>							
							<td>
								<?php if(isset($row_dim->product_id) && ($row_dim->product_id != '')): 
										echo get_the_title($row_dim->product_id);
									elseif(isset($_GET['product_id']) && ($_GET['product_id'] != '')):
										echo get_the_title($_GET['product_id']);
									endif;
								?>
								<input type="hidden" name="variant" value="<?php if(isset($row_dim->product_id) && ($row_dim->product_id != '')): 
										echo get_the_title($row_dim->product_id);
									elseif(isset($_GET['product_id']) && ($_GET['product_id'] != '')):
										echo get_the_title($_GET['product_id']);
									endif; ?>" />
								<input type="hidden" name="hdn_dim_id" value="<?php if(isset($row_dim->id_inxpress_variant)): echo $row_dim->id_inxpress_variant; endif; ?>" />
								<input type="hidden" name="product_id" value="<?php if(isset($row_dim->product_id)): echo $row_dim->product_id; elseif(isset($_GET['product_id']) && ($_GET['product_id'] != '')): echo $_GET['product_id'];  endif; ?>" />
							</td>
							<td class="tdvariants"><input type="text" size="7" value="<?php if(isset($row_dim->length)): echo $row_dim->length; endif; ?>" id="length" name="length"></td>
							<td class="tdvariants"><input type="text" size="7" value="<?php if(isset($row_dim->width)): echo $row_dim->width; endif; ?>" id="width" name="width"></td>
							<td class="tdvariants"><input type="text" size="7" value="<?php if(isset($row_dim->height)): echo $row_dim->height; endif; ?>" id="height" name="height"></td>
							<td><input type="text" size="7" readonly="readonly" value="<?php if(isset($row_dim->dim_weight)): echo $row_dim->dim_weight; endif; ?>" id="dim_weight" name="dim_weight"></td>
							<td><input type="text" size="7" value="<?php if(isset($row_dim->variable)): echo $row_dim->variable; endif; ?>" id="dhl_box_variable" name="variable"></td>
							<td colspan="2">
								<input type="submit" onclick="validate();" value="<?php if(isset($row_dim->id_inxpress_variant)): echo 'Update'; else: echo 'Add'; endif; ?>" class="button button-primary" id="submit_variant" name="submit_variant">
								<a href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_manage_dimensional_weight"); ?>" class="button button-primary">Back to List</a>
							</td>
						</tr>
						<tr>
							<td colspan="8"><hr></td>
						</tr>
						
						<tr>							
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Supplies</strong></span>
							</th>
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Length</strong></span>
							</th>
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Width</strong></span>
							</th>
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Height</strong></span>
							</th>	
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span><strong>Action</strong></span>
							</th>
							<th style="" class="manage-column column-cb" id="cb" scope="col">			
								<span>&nbsp;</span>
							</th>
							<th style="" colspan="2" class="manage-column column-cb" id="cb" scope="col">			
								<span>&nbsp;</span>
							</th>				
						</tr>		
					
						<?php 
						if ( $dhl_boxes )
						{
							foreach ( $dhl_boxes as $box )
							{
								?>
						<tr>							
							<td class="column-title">
								<?php echo $box->supplies; ?>															
							</td>
							<td id="length_<?php echo $box->id_inxpress_dhl; ?>"><?php echo $box->length; ?></td>
							<td id="width_<?php echo $box->id_inxpress_dhl; ?>"><?php echo $box->width; ?></td>
							<td id="height_<?php echo $box->id_inxpress_dhl; ?>"><?php echo $box->height; ?></td>
							<td><a href="javascript:void(0)" onclick="selectvariant(<?php echo $box->id_inxpress_dhl; ?>);" class="button button-primary">Select</a></td>
							<td>&nbsp;</td>
							<td colspan="2">&nbsp;</td>
													
						</tr>
								<?php 
							}
						}
						?>
									
					</tbody>
				</table>				
			</form>
		</div>
		<?php 				
	}	
	// ################ -Section for creating submenus for dimensional weight and DHL boxes- END ############## //
	
	
	// ################ -Section for updating plugin table values on post save or update- START ############## //
	function inxpress_update_rows( $post_id ) {
		
		global $wpdb;
		$table_name_dim = $wpdb->prefix . 'inxpress_variant';
	
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) )
			return;
	
		$post_type = get_post_type( $post_id );
		
		if($post_type == 'product'){
			$length = get_post_meta($post_id, '_length', true);
			$width = get_post_meta($post_id, '_width', true);
			$height = get_post_meta($post_id, '_height', true);
			
			$dim_weight=ceil((((float)$length)*((float)$width)*((float)$height))/139);
			
			$query='UPDATE `'.$table_name_dim.'`
					SET
						`length`="'.$length.'",
						`width`="'.$width.'",
						`height`="'.$height.'",
						`dim_weight` = "'.$dim_weight.'"						
					WHERE
						`product_id`='.$post_id;
			$res = $wpdb->query($query);
			//echo "result: ".$res; die;
		}

	}
	
	add_action( 'save_post', 'inxpress_update_rows' );	
	// ################ -Section for updating plugin table values on post save or update- END ############## //
	
	
	function inxpress_add_meta_box() {	
		
		add_meta_box(
		'inxpress_meta_manage_dimensions',
		__( 'Manage Dimensional Weight', 'woocommerce' ),
		'inxpress_add_meta_box_callback',
		'product',
		'side',
		'default'
		);

	}
	add_action( 'add_meta_boxes', 'inxpress_add_meta_box' );
	
	function inxpress_add_meta_box_callback($post) {

		//print_r($post); die;
		?>
		<a href="<?php echo admin_url( "edit.php?post_type=product&page=inxpress_edit_dimensional_weight&product_id=$post->ID" ); ?>">Manage Dimensional Weight</a>
		<?php 
	}		
	
}
