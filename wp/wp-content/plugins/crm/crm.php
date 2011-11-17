<?php
/*
Plugin Name: CRM
Plugin URI: http://cregy.co.uk/
Description: A simple CRM plugin.
Version: 1.1
Author: Best Web Outsourcing and Cregy
Author URI: http://cregy.co.uk/
Original code taken from a plugin developed by:
Author: Sam Wilson
Author URI: http://samwilson.id.au/
*/ 
ob_start();
include_once('notes.php');
include_once('reminder.php');
$crm_version = '1.0.0';

add_action('admin_head', 'crm_adminhead');
function crm_adminhead() {
	if ( $_GET['page'] == 'crm/crm.php' ) {
		?>
		<style type="text/css">
		.wrap h2 {margin:1em 0 0 0}
		form.crm div.line {width:95%; margin:auto}
		form.crm div.input {float:left}
		form.crm div.input label {font-size:smaller; margin:0}
		form.crm div.input input, form div.input textarea {width:100%; margin:0}
		form .submit {clear:both;border:0; text-align:right}
		table#crm-table {border-collapse:collapse}
		table#crm-table th {text-align:left}
		table#crm-table tr td {border:2px solid #e5f3ff; margin:0}
		table#crm-table tr:hover td {cursor:pointer}
		form.crm tr input {width:95%; border-color:#e5f3ff; background-color: white}
		<?php echo _crm_getAddressCardStyle() ?>
		</style>
		<?php
	}
}

add_action('admin_menu', 'crm_menus');
function crm_menus() {
	$toplevelmenu = get_option('crm_toplevelmenu');
	// The following menus have to be added in different orders depending on
	// whether the CRM is a top-level menu or not.  I'm not sure why!
	if ($toplevelmenu=='yes') {
		add_menu_page(__('CRM'), 'CRM', 4, 'crm/crm.php', 'crm_main');
	    add_submenu_page('crm/crm.php', 'Options', 'Options', 4, 'crm_options', 'crm_options');
	    $crm_basefile = "admin.php";
	} else {
	    add_options_page('CRM Options', 'CRM', 4, 'crm/crm.php', 'crm_options');
	    add_management_page('CRM', 'CRM', 4, 'crm/crm.php', 'crm_main');
	    $crm_basefile = "edit.php";
	}
}

function crm_options() {
	$toplevelmenu = get_option('crm_toplevelmenu');
	$yes_checked = '';
	$no_checked = '';
	if ($toplevelmenu=='yes') {
		$yes_checked = ' checked';
	} else {
		$no_checked = ' checked';
	}
	?>
	<div class="wrap">
	<form class="crm" method="post" action="options.php">
	<?php wp_nonce_field('update-options');
	if ($toplevelmenu=='yes') {
		echo '<p><em>';
		_e("Note: Because changing this option will move this page, after
		changing it you will be presented with an error.  Just click &lsquo;back&rsquo; and then
		navigate to the 'Manage &raquo; CRM' or 'Options &raquo; CRM' tab.");
		echo '</em></p>';
	} ?>
	<p>
	  <?php _e('Give CRM its own top-level menu item? '); ?>
	  <input type="radio" name="crm_toplevelmenu" value="yes"<?php echo $yes_checked ?> /><?php _e(Yes) ?>
	  <input type="radio" name="crm_toplevelmenu" value="no"<?php echo $no_checked ?> /><?php _e(No) ?>
	  <input type="hidden" name="action" value="update" />
	  <input type="hidden" name="page_options" value="crm_toplevelmenu" />
	</p>
	<p class="submit">
	<input type="submit" name="submit" value="<?php _e('Update Options'); ?> &raquo" />
	</p>
	</form>
	</div>
	<?php
}

/**
 * Outputs the main administration screen, and handles installing/upgrading, saving, and deleting.
 */
function crm_main() {
    global $wpdb, $crm_version, $crm_basefile;
    $show_main = true;
 
    if ( $_POST['new'] ) _crm_insertNewFromPost();
	if ( $_GET['action'] == 'delete' ) $show_main = _crm_deleteAddress( $_GET['id'] );
    if ( $_GET['action'] == 'edit' ) $show_main = _crm_editAddress( $_GET['id'] );
	
     if ( $_GET['action'] == 'notes_delete' ) $show_main = _notes_deleteNotes( $_GET['id'] );
     if ( $_GET['action'] == 'notes_edit' ) $show_main = _notes_editNotes( $_GET['id'] );
	 if ( $_POST['new_note'] ) _notes_insertNewFromPost();
	 if ( $_GET['shownote'] == 'shownote')
	 {
	 	$show_main = notes_main( $_GET['crmid'] ); 
	 	
	 }
	 
	 if ( $_GET['action'] == 'reminder_delete' ) $show_main = _reminder_deleteReminder( $_GET['id'] );
     if ( $_GET['action'] == 'reminder_edit' ) $show_main = _reminder_editReminder( $_GET['id'] );
	 if ( $_POST['new_reminder'] ) _reminder_insertNewFromPost();
	 if ( $_GET['showreminder'] == 'showreminder')
	 {
	 	$show_main = reminder_main($_GET['crmid']); 
	 	
	 }
	  if ( $_GET['exportcsv'] == 'exportcsv')
	 {
	 	
		include_once('exportemail.php');
		exportemail();
	 }
	
	if ( $_GET['shownote'] == 'shownote' || $_GET['showreminder'] == 'showreminder')
		$show_main = false;
    if ($show_main) {
    
    	// Make sure CRM is installed or upgraded.
        $table_name = $wpdb->prefix."crm";
        If ($wpdb->get_var("SHOW TABLES LIKE '$table_name'")!=$table_name
            || get_option("crm_version")!=$crm_version ) {
            // Call the install function here rather than through the more usual
            // activate_blah.php action hook so the user doesn't have to worry about
            // deactivating then reactivating the plugin.  Should happen seemlessly.
            _crm_install();
            _crm_outputMessage( sprintf(__('The CRM plugin (version %s) has been installed or upgraded.'), get_option("crm_version")) );
        } ?>
                
        <div class="wrap">
        <div style="text-align:center; width:47%; float:left;">
	      
	        <p style="font-size:110%" align="left">
            <strong><a href="<?=$crm_basefile?>?page=crm/crm.php&showreminder=showreminder"><img  style="vertical-align:middle;" alt="Reminder List" title="Reminder List" align="top" src="../wp-content/plugins/crm/mailreminder.png" /></a>&nbsp;&nbsp;<a href="<?=$crm_basefile?>?page=crm/crm.php&exportcsv=exportcsv&q=<?=$_GET['q']?>&tab=<?=$_GET['tab']?>"><img  alt="Export to CSV" title="Export to CSV" align="top" src="../wp-content/plugins/crm/export.png" style="vertical-align:middle;"/></a></strong>
             &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;<strong><a href="#new"><?php _e('Add new Contact &darr;'); ?></a></strong>
			

        </div>
        <div id="contact-info" style="border:10px solid #E5F3FF; margin:0 0 0 50%; padding:5px; width:47%">
        	<em><?php _e('Select an Contact from below to see its details displayed here.'); ?></em>
        </div>
           <br /><br />
            <div style="width:100%;" ><h2 style="margin:0 0 0 0;"><?php _e('CRM'); ?></h2></div>
<?php
if($_GET['tab'] == "Supplier")
	$sclass = "current";
if($_GET['tab'] == "Client")
	$cclass = "current";
if($_GET['tab'] == "Opportunity")
	$oclass = "current";
if($_GET['tab'] == "")
	$aclass = "current";
?>
<ul class="subsubsub">
<li><a href="<?=$crm_basefile?>?page=crm/crm.php" class="<?=$aclass?>"> All </a> |</li>
<li><a href="<?=$crm_basefile?>?page=crm/crm.php&tab=Supplier" class="<?=$sclass?>"> Supplier </a> |</li>
<li><a href="<?=$crm_basefile?>?page=crm/crm.php&tab=Client" class="<?=$cclass?>">Client </a> |</li>
<li><a href="<?=$crm_basefile?>?page=crm/crm.php&tab=Opportunity" class="<?=$oclass?>">Opportunity </a></li>
</ul><br /><br />

            <div style="width:70%;margin:0 0 0 0;" align="left">
               <form class="crm" action="<?php echo $crm_basefile; ?>?page=crm/crm.php" method="get">
	        	<div style="display:none">
	        		<input type="hidden" name="page" value="crm/crm.php" />
	        		<input type="hidden" name="action" value="search" />
	        	</div>
	        	<p>
	        		<?php _e("Filter messages by search term:"); ?>
	        		<input type="text" name="q" value="<?=stripslashes($_GET['q'])?>" /><input type="submit" value="<?php _e('Search&hellip;'); ?>" />
	        	</p>
	        </form></p>
            </div>
       
        <script type="text/javascript">
        /* <![CDATA[ */
        function click_contact(row, id) {
            document.getElementById('contact-info').innerHTML=document.getElementById('contact-'+id+'-info').innerHTML;
        }
		 
		/* ]]> */
        </script>
        <table style="width:100%; margin:auto" id="crm-table">
            <tr style="background-color:#E5F3FF">
                <?php 
				if($_GET['tab'] == "Opportunity")
				{
				echo '<th>'.__('Name').'</th><th>'.__('Category').'</th><th>'.__('Sales Ranking').'</th>
				<th>'.__('Organisation').'</th><th>'.__('Email address').'</th><th>'.__('Phone number').'</th><th>'.__('Rung').'</th><th>'.__('Note').'</th>'; 
				}
				else
				{
				echo '<th>'.__('Name').'</th><th>'.__('Category').'</th>
				<th>'.__('Organisation').'</th><th>'.__('Email address').'</th><th>'.__('Phone number').'</th><th>'.__('Rung').'</th><th>'.__('Note').'</th>'; 
				}
				
				?>
                
            </tr>
            <?php
			if(trim($_GET['tab']) != "")
			{
				$sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE
					  category LIKE '%".$wpdb->escape($_GET['tab'])."%'
	            	  ORDER BY first_name";
			}
            elseif ($_GET['action']=='search') {
	            $sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE
	            	first_name LIKE '%".$wpdb->escape($_GET['q'])."%'
	            	OR surname LIKE '%".$wpdb->escape($_GET['q'])."%'
					OR category LIKE '%".$wpdb->escape($_GET['q'])."%'
	            	OR organisation LIKE '%".$wpdb->escape($_GET['q'])."%'
	            	OR email LIKE '%".$wpdb->escape($_GET['q'])."%'
	            	OR phone LIKE '%".$wpdb->escape($_GET['q'])."%'
	            	OR notes LIKE '%".$wpdb->escape($_GET['q'])."%'
					OR rung LIKE '%".$wpdb->escape($_GET['q'])."%'
	            	ORDER BY first_name";
            } else {
	            $sql = "SELECT * FROM ".$wpdb->prefix."crm ORDER BY first_name";
            }
            $results = $wpdb->get_results($sql);
            foreach ($results as $row) {
                if($_GET['tab'] == "Opportunity")
				{
				echo"<tr>
                    <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->first_name." ".$row->surname)."&nbsp;</td><!-- nbsp is to stop collapse -->
                    <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->category)."</td>
					 <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->rank)."</td>
					<td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->organisation)."</td>
                    <td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->email)."</td>
                    <td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->phone)."</td>
					<td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->rung)."</td>
					<td><a href='$crm_basefile?page=crm/crm.php&shownote=shownote&crmid=".$row->id."'>Add/View</a></td>
                </tr>";
				}
				else
				{
					echo"<tr>
                    <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->first_name." ".$row->surname)."&nbsp;</td><!-- nbsp is to stop collapse -->
                    <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->category)."</td>
					<td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->organisation)."</td>
                    <td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->email)."</td>
                    <td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->phone)."</td>
					<td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->rung)."</td>
					<td><a href='$crm_basefile?page=crm/crm.php&shownote=shownote&crmid=".$row->id."'>Add/View</a></td>
                	</tr>";
				}
            } ?>
        </table>
        <?php foreach ($results as $row) {
            echo "<div class='address-label' id='contact-".$row->id."-info' style='display:none'>\n".
            	 "    <p style='text-align:center'>\n".
            	 "        <a href='$crm_basefile?page=crm/crm.php&action=edit&id=".$row->id."'>".__('[Edit]')."</a>\n".
            	 "        <a href='$crm_basefile?page=crm/crm.php&action=delete&id=".$row->id."'>".__('[Delete]')."</a>\n".
            	 "    </p>\n".
            	 _crm_getAddressCard($row, "    ").
            	 "</div>";
        } ?>
        
        <h2 style="margin-bottom:1em"><a name="new"></a>Add Contact</h2>
        <form class="crm" action="<?php echo $crm_basefile; ?>?page=crm/crm.php" method="post">
        <?php echo _crm_getaddressform(); ?>
        <p class="submit">
            <input type="submit" name="new" value="<?php _e('Add Contact &raquo;'); ?>" />
        </p>
        </form>
        </div><?php
    }
}

function _crm_outputMessage($message) {
	?>
	<div id="message" class="updated fade">
	  <p><strong><?php echo $message ?></strong></p>
	</div>
	<?php
}

function _crm_insertNewFromPost() {
	global $wpdb, $crm_basefile;
	if($wpdb->escape($_POST['category']) == "Opportunity")
	{
		if($wpdb->escape($_POST['rank']) == "Client")
			$_POST['category'] = "Client";
		$sql = "INSERT INTO ".$wpdb->prefix."crm SET
		organisation  = '".$wpdb->escape($_POST['organisation'])."',
		first_name    = '".$wpdb->escape($_POST['first_name'])."',
		surname       = '".$wpdb->escape($_POST['surname'])."',
		category      = '".$wpdb->escape($_POST['category'])."',
		email         = '".$wpdb->escape($_POST['email'])."',
		website       = '".$wpdb->escape($_POST['website'])."',
		address_line1 = '".$wpdb->escape($_POST['address_line1'])."',
		address_line2 = '".$wpdb->escape($_POST['address_line2'])."',
		suburb        = '".$wpdb->escape($_POST['suburb'])."',
		postcode      = '".$wpdb->escape($_POST['postcode'])."',
		state         = '".$wpdb->escape($_POST['state'])."',
		country       = '".$wpdb->escape($_POST['country'])."',
		phone         = '".$wpdb->escape($_POST['phone'])."',
		rank         = '".$wpdb->escape($_POST['rank'])."',
		two_first_name    = '".$wpdb->escape($_POST['two_first_name'])."',
		two_surname       = '".$wpdb->escape($_POST['two_surname'])."',
		two_organisation  = '".$wpdb->escape($_POST['two_organisation'])."',
		two_email         = '".$wpdb->escape($_POST['two_email'])."',
		two_website       = '".$wpdb->escape($_POST['two_website'])."',
		two_address_line1 = '".$wpdb->escape($_POST['two_address_line1'])."',
		two_address_line2 = '".$wpdb->escape($_POST['two_address_line2'])."',
		two_suburb        = '".$wpdb->escape($_POST['two_suburb'])."',
		two_postcode      = '".$wpdb->escape($_POST['two_postcode'])."',
		two_state         = '".$wpdb->escape($_POST['two_state'])."',
		two_country       = '".$wpdb->escape($_POST['two_country'])."',
		two_phone         = '".$wpdb->escape($_POST['two_phone'])."',
		notes         = '".$wpdb->escape($_POST['notes'])."'";
	}
	else
	{
		$sql = "INSERT INTO ".$wpdb->prefix."crm SET
		organisation  = '".$wpdb->escape($_POST['organisation'])."',
		first_name    = '".$wpdb->escape($_POST['first_name'])."',
		surname       = '".$wpdb->escape($_POST['surname'])."',
		category      = '".$wpdb->escape($_POST['category'])."',
		email         = '".$wpdb->escape($_POST['email'])."',
		website       = '".$wpdb->escape($_POST['website'])."',
		address_line1 = '".$wpdb->escape($_POST['address_line1'])."',
		address_line2 = '".$wpdb->escape($_POST['address_line2'])."',
		suburb        = '".$wpdb->escape($_POST['suburb'])."',
		postcode      = '".$wpdb->escape($_POST['postcode'])."',
		state         = '".$wpdb->escape($_POST['state'])."',
		country       = '".$wpdb->escape($_POST['country'])."',
		phone         = '".$wpdb->escape($_POST['phone'])."',
		two_first_name    = '".$wpdb->escape($_POST['two_first_name'])."',
		two_surname       = '".$wpdb->escape($_POST['two_surname'])."',
		two_organisation  = '".$wpdb->escape($_POST['two_organisation'])."',
		two_email         = '".$wpdb->escape($_POST['two_email'])."',
		two_website       = '".$wpdb->escape($_POST['two_website'])."',
		two_address_line1 = '".$wpdb->escape($_POST['two_address_line1'])."',
		two_address_line2 = '".$wpdb->escape($_POST['two_address_line2'])."',
		two_suburb        = '".$wpdb->escape($_POST['two_suburb'])."',
		two_postcode      = '".$wpdb->escape($_POST['two_postcode'])."',
		two_state         = '".$wpdb->escape($_POST['two_state'])."',
		two_country       = '".$wpdb->escape($_POST['two_country'])."',
		two_phone         = '".$wpdb->escape($_POST['two_phone'])."',
		notes         = '".$wpdb->escape($_POST['notes'])."'";
	}	
	$wpdb->query($sql);
	_crm_outputMessage(__('The Contact has been added.'));
}

/**
 * Edit a single Contact.
 *
 * @param int $id The ID of the Contact to be edited.
 * @return bool Whether or not any more content should be added to the page after calling this.
 */
function _crm_editAddress($id) {
	global $wpdb, $crm_basefile;
	$sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE id='".$wpdb->escape($id)."'";
	$row = $wpdb->get_row($sql);
	if ( $_POST['save'] ) {
		if($wpdb->escape($_POST['category']) == "Opportunity")
		{
			if($wpdb->escape($_POST['rank']) == "Client")
				$_POST['category'] = "Client";
			$wpdb->query("UPDATE ".$wpdb->prefix."crm SET
				first_name    = '".$wpdb->escape($_POST['first_name'])."',
				surname       = '".$wpdb->escape($_POST['surname'])."',
				category      = '".$wpdb->escape($_POST['category'])."',
				organisation  = '".$wpdb->escape($_POST['organisation'])."',
				email         = '".$wpdb->escape($_POST['email'])."',
				phone         = '".$wpdb->escape($_POST['phone'])."',
				address_line1 = '".$wpdb->escape($_POST['address_line1'])."',
				address_line2 = '".$wpdb->escape($_POST['address_line2'])."',
				suburb        = '".$wpdb->escape($_POST['suburb'])."',
				postcode      = '".$wpdb->escape($_POST['postcode'])."',
				state         = '".$wpdb->escape($_POST['state'])."',
				country       = '".$wpdb->escape($_POST['country'])."',
				notes         = '".$wpdb->escape($_POST['notes'])."',
				website       = '".$wpdb->escape($_POST['website'])."',
				two_first_name    = '".$wpdb->escape($_POST['two_first_name'])."',
				two_surname       = '".$wpdb->escape($_POST['two_surname'])."',
				two_organisation  = '".$wpdb->escape($_POST['two_organisation'])."',
				two_email         = '".$wpdb->escape($_POST['two_email'])."',
				two_website       = '".$wpdb->escape($_POST['two_website'])."',
				two_address_line1 = '".$wpdb->escape($_POST['two_address_line1'])."',
				two_address_line2 = '".$wpdb->escape($_POST['two_address_line2'])."',
				two_suburb        = '".$wpdb->escape($_POST['two_suburb'])."',
				two_postcode      = '".$wpdb->escape($_POST['two_postcode'])."',
				two_state         = '".$wpdb->escape($_POST['two_state'])."',
				two_country       = '".$wpdb->escape($_POST['two_country'])."',
				two_phone         = '".$wpdb->escape($_POST['two_phone'])."',
				rank          = '".$wpdb->escape($_POST['rank'])."'
				WHERE id ='".$wpdb->escape($_GET['id'])."'");
		}
		else
		{
			$wpdb->query("UPDATE ".$wpdb->prefix."crm SET
				first_name    = '".$wpdb->escape($_POST['first_name'])."',
				surname       = '".$wpdb->escape($_POST['surname'])."',
				category      = '".$wpdb->escape($_POST['category'])."',
				organisation  = '".$wpdb->escape($_POST['organisation'])."',
				email         = '".$wpdb->escape($_POST['email'])."',
				phone         = '".$wpdb->escape($_POST['phone'])."',
				address_line1 = '".$wpdb->escape($_POST['address_line1'])."',
				address_line2 = '".$wpdb->escape($_POST['address_line2'])."',
				suburb        = '".$wpdb->escape($_POST['suburb'])."',
				postcode      = '".$wpdb->escape($_POST['postcode'])."',
				state         = '".$wpdb->escape($_POST['state'])."',
				country       = '".$wpdb->escape($_POST['country'])."',
				notes         = '".$wpdb->escape($_POST['notes'])."',
				two_first_name    = '".$wpdb->escape($_POST['two_first_name'])."',
				two_surname       = '".$wpdb->escape($_POST['two_surname'])."',
				two_organisation  = '".$wpdb->escape($_POST['two_organisation'])."',
				two_email         = '".$wpdb->escape($_POST['two_email'])."',
				two_website       = '".$wpdb->escape($_POST['two_website'])."',
				two_address_line1 = '".$wpdb->escape($_POST['two_address_line1'])."',
				two_address_line2 = '".$wpdb->escape($_POST['two_address_line2'])."',
				two_suburb        = '".$wpdb->escape($_POST['two_suburb'])."',
				two_postcode      = '".$wpdb->escape($_POST['two_postcode'])."',
				two_state         = '".$wpdb->escape($_POST['two_state'])."',
				two_country       = '".$wpdb->escape($_POST['two_country'])."',
				two_phone         = '".$wpdb->escape($_POST['two_phone'])."',
				website       = '".$wpdb->escape($_POST['website'])."',
				rank          = ''
				WHERE id ='".$wpdb->escape($_GET['id'])."'");
		}
		_crm_outputMessage(__('The Contact has been updated.'));
		return true;
	} else {
		?><div class="wrap">
		<h2 style="margin-bottom:1em"><?php _e('Edit Address'); ?></h2>
		<form action="<?php echo $crm_basefile; ?>?page=crm/crm.php&action=edit&id=<?php echo $row->id; ?>"
			  method="post" class="crm">
		<?php echo _crm_getaddressform($row); ?>
		<p class="submit">
			<a href='<?php echo $crm_basefile; ?>?page=crm/crm.php'><?php _e('[Cancel]'); ?></a>
			<input type="submit" name="save" value="<?php _e('Save &raquo;'); ?>" />
		</p>
		</form>
		</div><?php
		return false;
	}
}

/**
 * Delete a single Contact from the database.
 *
 * @param int $id The ID of the Contact to be deleted.
 * @return bool Whether or not any more content should be added to the page after calling this.
 */
function _crm_deleteAddress($id) {
	global $wpdb, $crm_basefile;
	$sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE id='".$wpdb->escape($id)."'";
	$row = $wpdb->get_row($sql);
	if ($_GET['confirm']=='yes') {
		$wpdb->query("DELETE FROM ".$wpdb->prefix."crm WHERE id='".$wpdb->escape($id)."'");
		_crm_outputMessage(__('The Contact has been deleted.'));
		return true;
	} else {
		echo  "<div class='wrap'>".
			  "    <p style='text-align:center'>".__('Are you sure you want to delete this Contact?')."</p>\n".
			  "    <div style='border:1px solid black; width:50%; margin:1em auto; padding:0.7em'>\n".
			  _crm_getAddressCard($row, "        ").
			  "    </div>\n".
			  "    <p style='text-align:center; font-size:1.3em'>\n".
			  "        <a href='$crm_basefile?page=crm/crm.php&action=delete&id=".$row->id."&confirm=yes'>\n".
			  "            <strong>".__('[Yes]')."</strong>\n".
			  "        </a>&nbsp;&nbsp;&nbsp;&nbsp;\n".
			  "	       <a href='$crm_basefile?page=crm/crm.php'>".__('[No]')."</a>\n".
			  "    </p>\n".
			  "</div>\n";
		return false;
	}
}

function _crm_getaddressform($data='null') {
	
	// Set default values (the website field is the only one with a default value).
    if ($data=='null') $website = 'http://'; else $website = $data->website;
	if ($data=='null') $two_website = 'http://'; else $two_website = $data->two_website;

	if($data->rung == "No")
		$no = "checked";
	else
		$no = "";
	if($data->rung == "Yes")
		$yes = "checked";
	else
		$yes = "";
	
	$dis = "none";	
	if($data->category == "Supplier")
		$ssel = "selected";
	elseif($data->category == "Client")
		$csel = "selected";
	elseif($data->category == "Opportunity")
	{
		$osel = "selected";
		$dis = "block";
	}	
	if($data->rank == "Ready to buy")
		$se1l = "selected";
	elseif($data->rank == "Looking for Quotes")
		$se12 = "selected";
	elseif($data->rank == "Buying 6 months")
		$se13 = "selected";
	elseif($data->rank == "First Contact")
		$se14 = "selected";
	elseif($data->rank == "No longer interested")
		$se15 = "selected";
	elseif($data->rank == "Client")
		$se16 = "selected";

    $out = '
           	<div style="width:99%; float:left">

			<div class="input" style="width:50%">
                <label for="first_name">'.__('Category:').'</label>
                <select name="category" style="width:100%" onchange="if(this.value==\'Opportunity\'){getElementById(\'rank\').style.display=\'block\';}else{getElementById(\'rank\').style.display=\'none\';}">			
				<option  value="Supplier" '.$ssel.'>Supplier</option>
				<option  value="Client" '.$csel.'>Client</option>
				<option  value="Opportunity" '.$osel.'>Opportunity</option>
				</select>
			</div>
			<div class="input" style="width:50%;display:'.$dis.'" id="rank">
                <label for="rank">'.__('Sales Ranking:').'</label>
                <select name="rank" style="width:100%">			
				<option value="Ready to buy" '.$se1l.'>Ready to buy</option>
				<option value="Looking for Quotes" '.$se12.'>Looking for Quotes</option>
				<option  value="Buying 6 months" '.$se13.'>Buying 6 months</option>
				<option value="First Contact" '.$se14.'>First Contact</option>
				<option value="No longer interested" '.$se15.'>No longer interested</option>
				<option value="Client" '.$se16.' >Client</option>
				</select>
            </div>
			</div>
	
	<div style="width:50%; float:left"> 
	<div style="width:99%; float:left">
        <a>'.__('Primary Contact details:').'</a>
    </div>
	<div style="width:50%; float:left">
           
		<div class="line">
            <div class="input" style="width:50%">
                <label for="first_name">'.__('First name:').'</label>
                <input type="text" name="first_name" value="'.stripslashes($data->first_name).'" />
            </div>
            <div class="input" style="width:50%">
                <label for="surname">'.__('Surname:').'</label>
                <input type="text" name="surname" value="'.stripslashes($data->surname).'" />
            </div>
        </div>
       
		 <div class="line">
            <div class="input" style="width:100%">
                <label for="email">'.__('Organisation:').'</label>
                <input type="text" name="organisation" value="'.stripslashes($data->organisation).'" />
            </div>
        </div>
        <div class="line">
            <div class="input" style="width:100%">
                <label for="email">'.__('Email Address:').'</label>
                <input type="text" name="email" value="'.stripslashes($data->email).'" />
            </div>
        </div>
        <div class="line">
            <div class="input" style="width:100%">
                <label for="phone">'.__('Phone:').'</label>
                <input type="text" name="phone" value="'.stripslashes($data->phone).'" />
            </div>
        </div>
		  <!--div class="line">
            <div class="input" style="width:100%">
                <table><tr><td colspan="2"><label for="rung">'.__('Rung:').'</label></td></tr>
				<tr>
				<td nowrap><input type="radio" name="rung" value="No" '.$no.'">No</td>
				<td nowrap style="padding-left:30px;"><input type="radio" name="rung" value="Yes" '.$yes.'">Yes</td>
				</tr>
				</table>
			
            </div>
        </div-->
        <div class="line">
            <div class="input" style="width:100%">
                <label for="website">'.__('Website:').'</label>
                <input type="text" name="website" value="'.stripslashes($website).'" />
            </div>
        </div>
        </div>
        <div style="width:50%; float:right">
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="address_line1">'.__('Address Line 1:').'</label>
                    <input type="text" name="address_line1" value="'.stripslashes($data->address_line1).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="address_line2">'.__('Address Line 2:').'</label>
                    <input type="text" name="address_line2" value="'.stripslashes($data->address_line2).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:70%">
                    <label for="suburb">'.__('Suburb:').'</label>
                    <input type="text" name="suburb" value="'.stripslashes($data->suburb).'" />
                </div>
                <div class="input" style="width:30%">
                    <label for="postcode">'.__('Postcode:').'</label>
                    <input type="text" name="postcode" value="'.stripslashes($data->postcode).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="state">'.__('State or Territory:').'</label>
                    <input type="text" name="state" value="'.stripslashes($data->state).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="country">'.__('Country:').'</label>
                    <input type="text" name="country" value="'.stripslashes($data->country).'" />
                </div>
            </div>
        </div>
	</div>
	<div style="width:50%; float:left">
			<div style="width:99%; float:left">
        <a>'.__('Secondary Contact details:').'</a>
    </div>
	<div style="width:50%; float:left">
           
		<div class="line">
            <div class="input" style="width:50%">
                <label for="first_name">'.__('First name:').'</label>
                <input type="text" name="two_first_name" value="'.stripslashes($data->two_first_name).'" />
            </div>
            <div class="input" style="width:50%">
                <label for="surname">'.__('Surname:').'</label>
                <input type="text" name="two_surname" value="'.stripslashes($data->two_surname).'" />
            </div>
        </div>
       
		 <div class="line">
            <div class="input" style="width:100%">
                <label for="email">'.__('Position:').'</label>
                <input type="text" name="two_organisation" value="'.stripslashes($data->two_organisation).'" />
            </div>
        </div>
        <div class="line">
            <div class="input" style="width:100%">
                <label for="email">'.__('Email Address:').'</label>
                <input type="text" name="two_email" value="'.stripslashes($data->two_email).'" />
            </div>
        </div>
        <div class="line">
            <div class="input" style="width:100%">
                <label for="phone">'.__('Phone:').'</label>
                <input type="text" name="two_phone" value="'.stripslashes($data->two_phone).'" />
            </div>
        </div>
		
        <div class="line">
            <div class="input" style="width:100%">
                <label for="website">'.__('Website:').'</label>
                <input type="text" name="two_website" value="'.stripslashes($two_website).'" />
            </div>
        </div>
        </div>
        <div style="width:50%; float:right">
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="address_line1">'.__('Address Line 1:').'</label>
                    <input type="text" name="two_address_line1" value="'.stripslashes($data->two_address_line1).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="address_line2">'.__('Address Line 2:').'</label>
                    <input type="text" name="two_address_line2" value="'.stripslashes($data->two_address_line2).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:70%">
                    <label for="suburb">'.__('Suburb:').'</label>
                    <input type="text" name="two_suburb" value="'.stripslashes($data->two_suburb).'" />
                </div>
                <div class="input" style="width:30%">
                    <label for="postcode">'.__('Postcode:').'</label>
                    <input type="text" name="two_postcode" value="'.stripslashes($data->two_postcode).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="state">'.__('State or Territory:').'</label>
                    <input type="text" name="two_state" value="'.stripslashes($data->two_state).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="country">'.__('Country:').'</label>
                    <input type="text" name="two_country" value="'.stripslashes($data->two_country).'" />
                </div>
            </div>
        </div>
	</div>
		<div class="line" style="width:99%">
			<div class="input" style="width:100%">
				<label for="notes">'.__('Notes:').'</label>
				<textarea name="notes" rows="3">'.stripslashes($data->notes).'</textarea>
			</div>
        </div>';
    return $out;
}

function _crm_install() {
    global $wpdb, $crm_version;
    $table_name = $wpdb->prefix."crm";
	$table_name1 = $wpdb->prefix."notes";
	$table_name2 = $wpdb->prefix."reminder";
	
    $sql = "
	DROP TABLE IF EXISTS " . $table_name . ";
	CREATE TABLE " . $table_name . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name tinytext NOT NULL,
        surname tinytext NOT NULL,
        organisation tinytext NOT NULL,
		category VARCHAR(50) NOT NULL,
        email tinytext NOT NULL,
        phone tinytext NOT NULL,
        address_line1 tinytext NOT NULL,
        address_line2 tinytext NOT NULL,
        suburb tinytext NOT NULL,
        postcode tinytext NOT NULL,
        state tinytext NOT NULL,
        country tinytext NOT NULL, 
        website VARCHAR(55) NOT NULL, 
		two_first_name tinytext NOT NULL,
        two_surname tinytext NOT NULL,
        two_organisation tinytext NOT NULL,
        two_email tinytext NOT NULL,
        two_phone tinytext NOT NULL,
        two_address_line1 tinytext NOT NULL,
        two_address_line2 tinytext NOT NULL,
        two_suburb tinytext NOT NULL,
        two_postcode tinytext NOT NULL,
        two_state tinytext NOT NULL,
        two_country tinytext NOT NULL, 
        two_website VARCHAR(55) NOT NULL,
        notes tinytext NOT NULL,
		rung int(11) NOT NULL,
		rank VARCHAR(50) NOT NULL,
        PRIMARY KEY  (id)
    );
	DROP TABLE IF EXISTS " . $table_name1 . ";
	CREATE TABLE " . $table_name1 . " (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `crmid` int(11) NOT NULL,
	  `notes` text,
	  `keyword` varchar(20) NOT NULL,
	  `date` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	);
	DROP TABLE IF EXISTS " . $table_name2 . ";
	CREATE TABLE " . $table_name2 . " (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `crmid` int(11) NOT NULL,
	  `notes` text NOT NULL,
	  `date` datetime NOT NULL,
	  `sent` enum('0','1') NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	);";
    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	
    dbDelta($sql);
    update_option('crm_version', $crm_version);
}

/**
 * For other plugins, etc., to use.
 */
function crm_getselect($name, $sel_id=false) {
    global $wpdb;
    $out = "<select name='$name'>";
    $rows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."crm ORDER BY first_name, organisation");
    foreach($rows as $row) {
		if ($row->id==$sel_id) {
			$selected = " selected";
		} else {
			$selected = "";
		}
        $out .= "<option$selected value='$row->id'>$row->first_name $row->surname";
        if (!empty($row->organisation)) {
        	$out .= " ($row->organisation)";
        }
        $out .= "</option>";
    }
    $out .= "</select>";
    return $out;
}

/**
 * For other plugins, etc., to use.
 */
function crm_getIdFromEmail($email) {
    global $wpdb;
    $sql = "SELECT id FROM ".$wpdb->prefix."crm where email='".$wpdb->escape($email)."'";
    $res = $wpdb->get_var($sql);
    return $res;
}

/**
 * For other plugins, etc., to use.
 */
function crm_getFullnameFromId($id) {
    global $wpdb;
	$sql = "SELECT CONCAT(first_name,' ',surname) FROM ".$wpdb->prefix."crm WHERE id='".$wpdb->escape($id)."'";
    $res = $wpdb->get_var($sql);
    return $res;
}

add_action('wp_head', 'crm_wphead');
function crm_wphead() {
	?>
    <style type="text/css">
      ol.crm-list {padding:0; margin:0}
      li.crm-item {list-style-type:none; border:1px solid #666; padding:3px; margin:0; clear:both}
      <?php echo _crm_getAddressCardStyle() ?>
    </style>
    
    <?php
} // end crm_wphead()

add_filter('the_content', 'crm_list');
function crm_list($content) {
    global $wpdb;
    $sql = "SELECT * FROM ".$wpdb->prefix."crm ORDER BY first_name";
    $results = $wpdb->get_results($sql);
    $out = "<ol class='crm-list'>\n\n";
    foreach ($results as $row) {
        $out .= "  <li class='crm-item'>\n"._crm_getAddressCard($row, "    ")."  </li>\n\n";
    }
    $out .= "</ol>\n";
    return preg_replace("/<crm \/>|<crm>.*<\/crm>/", $out, $content);
}

function _crm_getAddressCardStyle() {
	return "
      .crm-card p {margin:3px}
      .crm-card .name {font-size:1.2em; font-weight:bolder}
      .crm-card .avatar {float:right; margin:0 0 0 1em}
      .crm-card .address {display:block; margin:0 0.3em 1em 1em; width:38%; float:left; font-size:smaller}
      .crm-card .address span {}
      .crm-card .notes {font-size:smaller; padding:4px}
	";
}

/**
 * @param 
 * @return string HTML to go within a containing element.
 */
function _crm_getAddressCard($data, $pad="") {
	$out = "$pad<div class='crm-card vcard'>\n".
		"$pad    ".get_avatar($data->email)."\n".
		"$pad    <p>\n".
		_crm_getIfNotEmpty("$pad        <strong><span class='fn name'>%s</span></strong>\n", stripslashes($data->first_name." ".$data->surname)).
		_crm_getIfNotEmpty("$pad        <br><em><span class='org'>%s</span></em>\n", stripslashes($data->category)).		
		_crm_getIfNotEmpty("$pad        <span class='org'>(%s)</span>\n", stripslashes($data->rank)).
		_crm_getIfNotEmpty("$pad        <br><span class='org'>%s</span>\n", stripslashes($data->organisation)).
		_crm_getIfNotEmpty("$pad        <a class='email' href='mailto:%1\$s'>%1\$s</a><br />\n", stripslashes($data->email)).
		_crm_getIfNotEmpty("$pad        <span class='tel phone'>%s</span>\n", stripslashes($data->phone)).
		_crm_getIfNotEmpty("$pad        <a class='website url' href='%1\$s'>%1\$s</a>\n", stripslashes($data->website)).
		"$pad    </p>\n";
	if ( !empty($data->address_line1) || !empty($data->suburb) || !empty($data->postcode) || !empty($data->state) || !empty($data->country) ) {
		$out .= "$pad    <div class='address adr'>\n";
		if (!empty($data->address_line1) || !empty($data->address_line2)) {
			$out .= "$pad      <span class='street-address'>\n".
				_crm_getIfNotEmpty("$pad        <span class='address-line1'>%s</span><br />\n", stripslashes($data->address_line1)).
				_crm_getIfNotEmpty("$pad        <span class='address-line2'>%s</span><br />\n", stripslashes($data->address_line2)).
			"$pad      </span>\n";
		}
		$out .= _crm_getIfNotEmpty("$pad      <span class='suburb locality'>%s</span>\n", stripslashes($data->suburb)).
			_crm_getIfNotEmpty("$pad      <span class='postcode postal-code'>%s</span><br />\n", stripslashes($data->postcode)).
			_crm_getIfNotEmpty("$pad      <span class='state region'>%s</span>\n", stripslashes($data->state)).
			_crm_getIfNotEmpty("$pad      <span class='country country-name'>%s</span>\n", stripslashes($data->country)).
		"$pad    </div>\n";
	}
	$out .=  "$pad    <div style='clear:both'></div>\n$pad</div>\n";
	
	
	$out .= "$pad<div class='crm-card vcard'>\n".
		
		"$pad    <p>\n".
		_crm_getIfNotEmpty("$pad        <strong><span class='fn name'>%s</span></strong><br>", stripslashes($data->two_first_name." ".$data->two_surname)).
		_crm_getIfNotEmpty("$pad        <span class='org'>%s</span>\n", stripslashes($data->two_organisation)).
		_crm_getIfNotEmpty("$pad        <a class='email' href='mailto:%1\$s'>%1\$s</a><br />\n", stripslashes($data->two_email)).
		_crm_getIfNotEmpty("$pad        <span class='tel phone'>%s</span>\n", stripslashes($data->two_phone)).
		_crm_getIfNotEmpty("$pad        <a class='website url' href='%1\$s'>%1\$s</a>\n", stripslashes($data->two_website)).
		"$pad    </p>\n";
	if ( !empty($data->two_address_line1) || !empty($data->two_suburb) || !empty($data->two_postcode) || !empty($data->two_state) || !empty($data->two_country) ) {
		$out .= "$pad    <div class='address adr'>\n";
		if (!empty($data->two_address_line1) || !empty($data->two_address_line2)) {
			$out .= "$pad      <span class='street-address'>\n".
				_crm_getIfNotEmpty("$pad        <span class='address-line1'>%s</span><br />\n", stripslashes($data->two_address_line1)).
				_crm_getIfNotEmpty("$pad        <span class='address-line2'>%s</span><br />\n", stripslashes($data->two_address_line2)).
			"$pad      </span>\n";
		}
		$out .= _crm_getIfNotEmpty("$pad      <span class='suburb locality'>%s</span>\n", stripslashes($data->two_suburb)).
			_crm_getIfNotEmpty("$pad      <span class='postcode postal-code'>%s</span><br />\n", stripslashes($data->two_postcode)).
			_crm_getIfNotEmpty("$pad      <span class='state region'>%s</span>\n", stripslashes($data->two_state)).
			_crm_getIfNotEmpty("$pad      <span class='country country-name'>%s</span>\n", stripslashes($data->two_country)).
		"$pad    </div>\n";
	}
	$out .= _crm_getIfNotEmpty("$pad    <div class='notes note'>\n$pad    %s\n$pad    </div>\n", stripslashes($data->notes)).
		 "$pad    <div style='clear:both'></div>\n$pad</div>\n";
	return $out;
}

function _crm_getIfNotEmpty($format,$var) {
	if (!empty($var)) {
		return sprintf($format, $var);
	}
}

?>
