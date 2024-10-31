<?php
/*
* Plugin Name: Random Ads by Aarvis
* Plugin URI: http://www.aarvis.com
* Description: This is a simple plugin to insert Ads to your website. As the name says you can show random Ads within your post content
* Version: 1.0.8
* Author: Subhash Bhaskaran
* Author URI: http://www.aarvis.com
*/

// create deafault settings on plugin activation
function RAA_create_DB_N_options()
{
	global $wpdb;
	$RAA_DbVesion = '1.0.1';
	$RAA_table_name = "wp_RAA_Ad_Units"; 
			
			$sql = " CREATE TABLE " . $RAA_table_name . " (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
				  AdUnit TEXT,
				  PRIMARY KEY  (id)
				) " . $STP_charset_collate . ";";
				
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
				
	
	
	add_option( 'RAA_show_cnt', '3' );
	add_option( 'RAA_ChkRandom', 'Y' );
	add_option( 'RAA_Show_Mod', 'para' );
	add_option( 'RAA_ParaCount', '2' );
	add_option( 'RAA_LetterCount', '500' );
	
	if (get_option( 'RAA_DbVesion' )) 
	{
		update_option ('RAA_DbVesion' , $RAA_DbVesion);
	}
	else
	{
		add_option( 'RAA_DbVesion', $RAA_DbVesion );
	}
	//Create Adunits on plugin activation
	
	RAA_InsertAdunits ('1','');
	RAA_InsertAdunits ('2','');
	RAA_InsertAdunits ('3','');
	RAA_InsertAdunits ('4','');
	RAA_InsertAdunits ('5','');
	RAA_InsertAdunits ('6','');
	
}

// Common function to insert adunits into DB.
	function RAA_InsertAdunits ($id , $unit_text )
	{
		global $wpdb;
		$RAA_table_name = "wp_RAA_Ad_Units";
		
		$wpdb->insert( $RAA_table_name, 
					array( 
						'id' => $id, 
						'AdUnit' => $unit_text,
						) 
					);	
	}
	
// Common function to update adunits.	
	function RAA_UpdateAdunits ($id , $unit_text )
	{
		global $wpdb;
		$RAA_table_name = "wp_RAA_Ad_Units";
		
		$wpdb->update( 
					$RAA_table_name, 
					array( 
						'AdUnit' => $unit_text
						) ,
						array('id' => $id)
		);	
	}
// Get the value of a specific ad unit for display	
	function RAA_GetAdUnits ($id)
	
	{
		global $wpdb;
		$RAA_SQL = "SELECT AdUnit FROM wp_RAA_Ad_Units where id = ". $id;
		$RAA_Adunit = $wpdb->get_row( $RAA_SQL );
		return $RAA_Adunit->AdUnit;
	}

// Delete settings on plugin de-activation
function RAA_delete_DB_N_options()
{
	
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS wp_RAA_Ad_Units" );
    
	delete_option( 'RAA_show_cnt' );
	delete_option( 'RAA_ChkRandom' );
	delete_option( 'RAA_Show_Mod' );
	delete_option( 'RAA_ParaCount' );
	delete_option( 'RAA_LetterCount' );
	
}
// register plugin ctivation and de-activation hooks
register_activation_hook( __FILE__, 'RAA_create_DB_N_options' );
register_deactivation_hook( __FILE__, 'RAA_delete_DB_N_options' );



// Display admin menu
function RAA_top_menu()
	{
		add_menu_page('Random Ads - Aarvis', 'Random Ads - Aarvis', 'manage_options', __FILE__, 'RAA_render_list_page', 'dashicons-exerpt-view');
	}
	add_action('admin_menu','RAA_top_menu');
	
	
// register plugin style sheet
wp_register_style( 'RAA_Style', plugins_url( 'css/RAA_Style.css', __FILE__ ), false, time() );
wp_enqueue_style( 'RAA_Style' );	
	
	
//supporting function to generate the display content 
add_filter( 'the_content', 'RAA_show_random_ads' );
	
function RAA_show_random_ads($content)
	{
	if (!is_single()) return $content;
	
	$RAA_show_cnt = get_option('RAA_show_cnt');
    $RAA_ParaCount = get_option('RAA_ParaCount'); //Enter number of paragraphs to display ad after.
	$RAA_LetterCount = get_option('RAA_LetterCount');
	
	
	global $wpdb;
	
	if (get_option(RAA_ChkRandom) == "Y")
	{
		
		$RAA_strSQL = "SELECT AdUnit FROM wp_RAA_Ad_Units where AdUnit != '' and AdUnit IS NOT NULL ORDER BY rand() LIMIT " .$RAA_show_cnt;
	}
	else
	{
		$RAA_strSQL = "SELECT AdUnit FROM wp_RAA_Ad_Units where AdUnit != '' and AdUnit IS NOT NULL LIMIT " .$RAA_show_cnt;
	}
	 $RAA_AdUnits =  $wpdb->get_results($RAA_strSQL, ARRAY_N );
	 
	$RAA_UnitCount = sizeof($RAA_AdUnits);
	$RAA_Counter = 0 ;
	
	$wpdb->get_row;
	
	if (get_option(RAA_Show_Mod) == 'para' )
	{
		$content = explode("</p>", $content);
	}
	else
	{
		$content = str_split ($content , $RAA_LetterCount);
	}		
	    
	$new_content = '';
    for ($i = 0; $i < count($content); $i++) 
	{
        if ( !fmod($i,$RAA_ParaCount))	
		{
            if ( $RAA_UnitCount >0 && $RAA_Counter < $RAA_UnitCount && $i > 0)
			
			{
				$new_content.= '<div name = "mydiv" class="RAA_div">'; 
				$new_content.= stripslashes($RAA_AdUnits [ $RAA_Counter ] [0]);
				$new_content.= '</div>';
				
				$RAA_Counter = $RAA_Counter + 1;
			}
        }
		if (get_option(RAA_Show_Mod) == 'para' )
		{
			$new_content.= $content[$i] . "</p>";
		}
		else
		{
			$new_content.= $content[$i];
		}
	
        
    }

    return $new_content;
	}
	

// call back funtion to render the admin page
function RAA_render_list_page()
{
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	$RAA_show_cnt = get_option('RAA_show_cnt');
	$RAA_ChkRandom = get_option('RAA_ChkRandom');
	$RAA_Show_Mod = get_option('RAA_Show_Mod');
	$RAA_ParaCount = get_option('RAA_ParaCount');
	$RAA_LetterCount = get_option('RAA_LetterCount');
		
	$RAA_Unit1 = RAA_GetAdUnits('1');
	$RAA_Unit2 = RAA_GetAdUnits('2');
	$RAA_Unit3 = RAA_GetAdUnits('3');
	$RAA_Unit4 = RAA_GetAdUnits('4');
	$RAA_Unit5 = RAA_GetAdUnits('5');
	$RAA_Unit6 = RAA_GetAdUnits('6');
	
	
	
	
	if (isset($_POST ['RAA_hdn_submit']) && ($_POST ['RAA_hdn_submit'] == 'Y'))
	{
		if ( isset( $_REQUEST['RAA_options_nonce'] ) ) 
		{
			if ( isset( $_REQUEST[ 'RAA_options_nonce' ] ) && wp_verify_nonce( $_REQUEST[ 'RAA_options_nonce' ], 'RAA_options_save' ) ) 
			{
   
			
				if 	(isset($_POST ['RAA_show_cnt']) && !empty($_POST ['RAA_show_cnt']))
				{	
					$RAA_show_cnt = intval( $_POST['RAA_show_cnt'] );
					if ( ! $RAA_show_cnt ) 
					{
						$RAA_show_cnt = '3';
					}
				}
				if	(isset($_POST ['RAA_ChkRandom']) && !empty($_POST ['RAA_ChkRandom']))
				{
					if ($_POST ['RAA_ChkRandom'] == 'on')
					{
						$RAA_ChkRandom = "Y";
						
					}
						
				}
				else
					{
						$RAA_ChkRandom = "N";
						
					}	
				
				if (isset($_POST ['RAA_Show_Mod']) && !empty($_POST ['RAA_Show_Mod']))
				{
					$RAA_Show_Mod = ($_POST ['RAA_Show_Mod']);
				}
				else
				{
					$RAA_Show_Mod = 'para';
				}
				
				if 	(isset($_POST ['RAA_ParaCount']) && !empty($_POST ['RAA_ParaCount']))
				{	
					$RAA_ParaCount = intval( $_POST['RAA_ParaCount'] );
					if ( ! $RAA_ParaCount ) 
					{
						$RAA_ParaCount = '2';
					}
				}
				
				if 	(isset($_POST ['RAA_LetterCount']) && !empty($_POST ['RAA_LetterCount']))
				{	
					$RAA_LetterCount = intval( $_POST['RAA_LetterCount'] );
					if ( ! $RAA_LetterCount ) 
					{
						$RAA_LetterCount = '100';
					}
				}
					
								
				if (isset($_POST ['RAA_Unit1']) && !empty($_POST ['RAA_Unit1']))
				{
					$RAA_Unit1 = apply_filters('the_content', $_POST ['RAA_Unit1']);
				}
				else
				{
					$RAA_Unit1 = '';
				}
				if (isset($_POST ['RAA_Unit2']) && !empty($_POST ['RAA_Unit2']))
				{
					$RAA_Unit2 = apply_filters('the_content', $_POST ['RAA_Unit2']);
				}
				else
				{
					$RAA_Unit2 = '';
				}
				if (isset($_POST ['RAA_Unit3']) && !empty($_POST ['RAA_Unit3']))
				{
					$RAA_Unit3 = apply_filters('the_content', $_POST ['RAA_Unit3']);
				}
				else
				{
					$RAA_Unit3 = '';
				}
				if (isset($_POST ['RAA_Unit4']) && !empty($_POST ['RAA_Unit4']))
				{
					$RAA_Unit4 = apply_filters('the_content', $_POST ['RAA_Unit4']);
				}
				else
				{
					$RAA_Unit4 = '';
				}
				if (isset($_POST ['RAA_Unit5']) && !empty($_POST ['RAA_Unit5']))
				{
					$RAA_Unit5 = apply_filters('the_content', $_POST ['RAA_Unit5']);
				}
				else
				{
					$RAA_Unit5 = '';
				}
				if (isset($_POST ['RAA_Unit6']) && !empty($_POST ['RAA_Unit6']))
				{
					$RAA_Unit6 = apply_filters('the_content', $_POST ['RAA_Unit6']);
				}
				else
				{
					$RAA_Unit6 = '';
				}
				
			
				RAA_UpdateAdunits('1' , $RAA_Unit1 );
				RAA_UpdateAdunits('2' , $RAA_Unit2 );
				RAA_UpdateAdunits('3' , $RAA_Unit3 );
				RAA_UpdateAdunits('4' , $RAA_Unit4 );
				RAA_UpdateAdunits('5' , $RAA_Unit5 );
				RAA_UpdateAdunits('6' , $RAA_Unit6 );
				
				update_option ('RAA_show_cnt' , $RAA_show_cnt);
				update_option ('RAA_ChkRandom' , $RAA_ChkRandom);
				update_option ('RAA_Show_Mod' , $RAA_Show_Mod);
				update_option ('RAA_ParaCount' , $RAA_ParaCount);
				update_option ('RAA_LetterCount' , $RAA_LetterCount);
				
				
				?>
				<div class="updated"><p><strong><?php _e('settings saved.', 'RAA-plugin' ); ?></strong></p></div>
				<?php
			}
			else
			{
				// Nonce could not be verified - bail
				wp_die( __( 'Invalid session or request. Please try againg', 'Random-Ads-by-aarvis' ), __( 'Error', 'Random-Ads-by-aarvis'  ), array(
				'response' 	=> 403,
				'back_link' => 'admin.php?page=' . 'Random-Ads-by-aarvis',
				) );
			}
					
		}
	}
	
		?>
	<h3><?php esc_attr_e('Random Ads Settings' , 'RAA-plugin' ); ?>  </h3><hr>
	
	<div><form name = "frm_RAA" method = "post">
				<div class="divTble">
				<div class="divTbleBody">
				<div class="divTbleRow">
				<input type = "hidden" name ="RAA_hdn_submit" value = "Y">
				
				<div class="divTbleRow">
				<div class="divTbleCell"><b><?php esc_attr_e('Ad Unit 1', 'RAA-plugin' ); ?></b></div><div class="divTbleCell"><textarea rows="3" cols="100" name ="RAA_Unit1"><?php esc_attr_e( stripslashes($RAA_Unit1), 'RAA-plugin' );?></textarea></div>
				<div class="divTbleCell"><?php esc_attr_e('', 'RAA-plugin' ); ?></div>
				</div>
				<div class="divTbleRow">
				<div class="divTbleCell"><b><?php esc_attr_e('Ad Unit 2', 'RAA-plugin' ); ?></b></div><div class="divTbleCell"><textarea rows="3" cols="100" name ="RAA_Unit2"><?php esc_attr_e( stripslashes( $RAA_Unit2 ), 'RAA-plugin' );?></textarea></div>
				<div class="divTbleCell"><?php esc_attr_e('', 'RAA-plugin' ); ?></div>
				</div>
				
				<div class="divTbleRow">
				<div class="divTbleCell"><b><?php esc_attr_e('Ad Unit 3', 'RAA-plugin' ); ?></b></div><div class="divTbleCell"><textarea rows="3" cols="100" name ="RAA_Unit3"><?php esc_attr_e( stripslashes( $RAA_Unit3), 'RAA-plugin' );?></textarea></div>
				<div class="divTbleCell"><?php esc_attr_e('', 'RAA-plugin' ); ?></div>
				</div>
				<div class="divTbleRow">
				<div class="divTbleCell"><b><?php esc_attr_e('Ad Unit 4', 'RAA-plugin' ); ?></b></div><div class="divTbleCell"><textarea rows="3" cols="100" name ="RAA_Unit4"><?php esc_attr_e( stripslashes( $RAA_Unit4 ) , 'RAA-plugin');?></textarea></div>
				<div class="divTbleCell"><?php esc_attr_e('', 'RAA-plugin' ); ?></div>
				</div>
				
				<div class="divTbleRow">
				<div class="divTbleCell"><b><?php esc_attr_e('Ad Unit 5', 'RAA-plugin' ); ?></b></div><div class="divTbleCell"><textarea rows="3" cols="100" name ="RAA_Unit5"><?php esc_attr_e( stripslashes( $RAA_Unit5 ), 'RAA-plugin' );?></textarea></div>
				<div class="divTbleCell"><?php esc_attr_e('', 'RAA-plugin' ); ?></div>
				</div>
				<div class="divTbleRow">
				<div class="divTbleCell"><b><?php esc_attr_e('Ad Unit 6', 'RAA-plugin' ); ?></b></div><div class="divTbleCell"><textarea rows="3" cols="100" name ="RAA_Unit6"><?php esc_attr_e( stripslashes( $RAA_Unit6), 'RAA-plugin' );?></textarea></div>
				<div class="divTbleCell"><?php esc_attr_e('', 'RAA-plugin' ); ?></div>
				</div>
				
				<div class="divTbleRow">
				<div class="divTbleCell"><b><?php esc_attr_e('Maximum Number of Ad. Units to show', 'RAA-plugin' ); ?></b></div><div class="divTbleCell"><input type="number"  style="width: 5em" min="1" max="6" name ="RAA_show_cnt" value = "<?php esc_attr_e( $RAA_show_cnt , 'RAA-plugin')?>" required/>
				<?php esc_attr_e('Minimum allowed number is 1 and Maximum allowed number is 6.', 'RAA-plugin' ); ?></div>
				<div class="divTbleCell"></div>
				</div>
				
				<div class="divTbleRow">
				<div class="divTbleCell"><b><?php esc_attr_e('Show random Ads' , 'RAA-plugin' ); ?> </b></div><div class="divTbleCell"><input type = "checkbox" name ="RAA_ChkRandom" <?php if($RAA_ChkRandom == 'Y') { echo 'checked';} ?>>
				<?php esc_attr_e('Check this option if you want to show Ads randomly. Otherwise Ads will be shown in the default order.', 'RAA-plugin' ); ?> 
				</div>
				<div class="divTbleCell"></div>
				</div>
				
				<div class="divTbleRow">
				<div class="divTbleCell"><strong><?php esc_attr_e('' , 'RAA-plugin' ); ?> </strong> </div><div class="divTbleCell"> <input type="radio" name ="RAA_Show_Mod" value = "para" <?php if($RAA_Show_Mod == 'para') { echo 'checked'; };?>><?php esc_attr_e('Show Ads by paragraph' , 'RAA-plugin' ); ?>
				<br><?php esc_attr_e('Show after every ' , 'RPA-plugin' ); ?><input type="number" min="1" max="10" style="width: 5em" name ="RAA_ParaCount" value = "<?php esc_attr_e( $RAA_ParaCount , 'RPA-plugin')?>" required/><?php esc_attr_e(' paragraph' , 'RAA-plugin' ); ?>
				</div>
				<div class="divTbleCell"></div>
				</div>
				<div class="divTbleRow">
				<div class="divTbleCell"><strong><?php esc_attr_e('' , 'RAA-plugin' ); ?> </strong> </div><div class="divTbleCell"> <input type="radio" name ="RAA_Show_Mod" value = "letter" <?php if($RAA_Show_Mod == 'letter') { echo 'checked'; };?>><?php esc_attr_e('Show Ads by number of letters' , 'RAA-plugin' ); ?>
				<br><?php esc_attr_e('Show after every ' , 'RPA-plugin' ); ?><input type="number" min="1" max="1000" style="width: 5em" name ="RAA_LetterCount" value = "<?php esc_attr_e( $RAA_LetterCount , 'RPA-plugin')?>" required/><?php esc_attr_e(' letters' , 'RAA-plugin' ); ?>
				</div>
				<div class="divTbleCell"></div>
				</div>
				
				<div class="divTbleRow">
				<?php wp_nonce_field( 'RAA_options_save', 'RAA_options_nonce' ); ?>
				<div class="divTbleCell"><input type = "submit" class="button-primary" id="RAA_btnSubmit" value = "<?php esc_attr_e('Save Settings' , 'RAA-plugin'); ?>"></div>
				<div class="divTbleCell"></div>
				</div>
				</div>
				</div>
		</form>
	</div>
	<?php
	
	
}
