<?php get_header(); ?>
<h1>Search results</h1>
<?php require('search.php'); ?>
<?php
wp_enqueue_media();
$post_id;
if(!empty($_POST)){
    if(isset($_POST['ua_wdm_add_auc']) && wp_verify_nonce($_POST['ua_wdm_add_auc'],'ua_wp_n_f')){
    $auction_title=(!empty($_POST["auction_title"])) ? ($_POST["auction_title"]):'';
    $auction_content=(!empty($_POST["auction_description"])) ? ($_POST["auction_description"]):'';
    $auction_excerpt=(!empty($_POST["auction_excerpt"])) ? ($_POST["auction_excerpt"]):'';
    
    $auc_end_tm = isset($_POST["end_date"]) ? strtotime($_POST["end_date"]) : 0;
    $blog_curr_tm = strtotime(date("Y-m-d H:i:s", time()));
    
    if(($auc_end_tm > $blog_curr_tm) && $auction_title!="" && $auction_content!=""){
        global $post_id;
        $is_update=false;
        $reactivate=false;
        
        //update auction mode
        if(isset($_POST["update_auction"]) && !empty($_POST["update_auction"]) /*&& !isset($_GET["reactivate"])*/){
            $post_id=$_POST["update_auction"];
            
            $args=array(
                        'ID'    => $post_id,
                        'post_title' => $auction_title,
                        'post_content' => $auction_content,
            'post_excerpt'  => $auction_excerpt
                        );
            wp_update_post( $args );
            $is_update = true;
            
        }
        //reactivate auction mode
        elseif(/*isset($_POST["update_auction"]) && !empty($_POST["update_auction"]) && */isset($_GET["reactivate"]))
        {
            $args = array(
            'post_title'    => wp_strip_all_tags( $auction_title ),//except for title all other fields are sanitized by wordpress
            'post_content'  => $auction_content,
            'post_type'     => 'ultimate-auction',
            'post_status'   => 'publish',
        'post_excerpt'  => $auction_excerpt
            );
            $post_id = wp_insert_post($args);
            $this->wdm_set_auction($post_id);
            $this->auction_id=$post_id;
            $reactivate = true;
        }
        //create/add auction mode
        else{
            $args = array(
                'post_title'    => wp_strip_all_tags( $auction_title ),//except for title all other fields are sanitized by wordpress
                'post_content'  => $auction_content,
                'post_type'     => 'ultimate-auction',
                'post_status'   => 'publish',
            'post_excerpt'  => $auction_excerpt
                );
            $post_id = wp_insert_post($args);
            $this->wdm_set_auction($post_id);
            $this->auction_id=$post_id;
            }
           
        if($post_id){
        $get_default_timezone = get_option('wdm_time_zone');
    
        if(!empty($get_default_timezone))
        {
        date_default_timezone_set($get_default_timezone);
        }
        
            echo '<div id="message" class="updated fade">';
            echo "<p><strong>";
            if($is_update)
        _e("Auction updated successfully.", "wdm-ultimate-auction");
            elseif($reactivate)
        {
        printf(__("Auction reactivated successfully. Auction id is %d", "wdm-ultimate-auction"), $post_id);
        update_post_meta($post_id, 'wdm-auth-key',md5(time().rand()));
        add_post_meta($post_id, 'wdm_creation_time', date("Y-m-d H:i:s", time()));
        }
            else
        {
        printf(__("Auction created successfully. Auction id is %d", "wdm-ultimate-auction"), $post_id);
        update_post_meta($post_id, 'wdm-auth-key',md5(time().rand()));
        add_post_meta($post_id, 'wdm_creation_time', date("Y-m-d H:i:s", time()));
        }
            echo "</strong></p></div>";
            
            $temp = term_exists('live', 'auction-status');
            wp_set_post_terms($post_id, $temp["term_id"], 'auction-status');
            
            //update options
        for($u=1; $u<=4; $u++)
        {
        update_post_meta($post_id, "wdm-image-".$u,$_POST["auction_image_".$u]);
        }
        
        update_post_meta($post_id, 'wdm-main-image',$_POST["auction_main_image"]);
            update_post_meta($post_id, 'wdm_listing_ends', $_POST["end_date"]);
            update_post_meta($post_id, 'wdm_opening_bid', round($_POST["opening_bid"], 2));
            update_post_meta($post_id, 'wdm_lowest_bid', round($_POST["lowest_bid"], 2));
            update_post_meta($post_id, 'wdm_buy_it_now', round($_POST["buy_it_now_price"], 2));
            update_post_meta($post_id, 'wdm_incremental_val', round($_POST["incremental_value"], 2));
            update_post_meta($post_id, 'wdm_payment_method', $_POST["payment_method"]);
        //if another bidding engine is active
        update_post_meta($post_id, 'wdm_bidding_engine', $_POST["bidding_engine"]);
            for($im=1; $im<=4; $im++)
            {
                if(get_post_meta($post_id,'wdm-main-image',true) == 'main_image_'.$im)
                    {
                        $main_image = get_post_meta($post_id,'wdm-image-'.$im,true);
                        update_post_meta($post_id, 'wdm_auction_thumb', $main_image);
                    }
            }
       
    }
    }
    elseif($auc_end_tm <= $blog_curr_tm)
    {
        
    ?>  <div id="message" class="error">
        <p><strong><?php _e("Please enter a future date/time.", "wdm-ultimate-auction");?></strong></p>
    </div>
    <?php
    
    }
    else{
    ?> 
    <div id="message" class="error"><p><strong><?php _e("Auction title and Auction description cannot be left blank.", "wdm-ultimate-auction");?></strong></p></div>
    <?php
    }
}
else{
    die(__('Sorry, your nonce did not verify.', 'wdm-ultimate-auction'));
}
}
$wdm_post=$this->wdm_get_post();

//Currency Code
$currency_code = substr(get_option('wdm_currency'), -3);
?>

<!--form to add/update an auction-->
<form id="wdm-add-auction-form" class="auction_settings_section_style" action="" method="POST">
    <?php
    if($wdm_post["title"]!="")
    echo "<h3>".__("Update Auction", "wdm-ultimate-auction")."</h3>";
    else
    echo "<h3>".__("Add New Auction", "wdm-ultimate-auction")."</h3>";
    ?>
    <table class="form-table">
    
    <?php
    $after_thumb = "";
    $bidding_engine = array();
    
    $bidding_engine = apply_filters('ua_add_bidding_engine', $bidding_engine);
        
    if(!empty($bidding_engine)){
    $after_thumb = '<tr valign="top">
        <th scope="row">
            <label for="bidding_engine">'.__("Bidding Engine", "wdm-ultimate-auction").'</label>
        </th>
        <td>
            <select id="bidding_engine" name="bidding_engine">
            <option value="">'.__("Simple Bidding", "wdm-ultimate-auction").'</option>';
            
        foreach($bidding_engine as $be){
        
        $opt = get_option("wdm_bidding_engines");
        
        $opt_new = $this->wdm_post_meta("wdm_bidding_engine");
        
        if((isset($_GET['edit_auction']) && !empty($_GET['edit_auction'])) || isset($post_id))
            $opt = $opt_new;
        
        $select = $opt == $be["val"] ? "selected" : "";
        $after_thumb .= '<option value="'.$be["val"].'" '.$select.'>'.$be["text"].'</option>';
        }
                
        $after_thumb .= '</select>
        </td>
    </tr>';
    }
    echo $after_thumb;
    ?>
    
    <tr valign="top">
        <th scope="row">
            <label for="auction_title"><?php _e("Product Title", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <input name="auction_title" type="text" id="auction_title" class="regular-text" value="<?php echo $wdm_post["title"];?>"/>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="auction_description"><?php _e("Product Description", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <!--<textarea name="auction_description" type="text" id="auction_description" cols="50" rows="10" class="large-text code"><?php echo $wdm_post["content"];?></textarea>-->
        <?php
        $args = array(
                'media_buttons' => false,
                'textarea_name' => 'auction_description',
                'textarea_rows' => 10
            );
        wp_editor($wdm_post["content"], 'auction_description', $args);?>
    </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="auction_excerpt"><?php _e("Product Short Description", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <textarea name="auction_excerpt" id="auction_excerpt" class="regular-text ua_thin_textarea_field"><?php echo $wdm_post["excerpt"];?></textarea>
        <div class="ult-auc-settings-tip"><?php _e("Enter short description (excerpt) for the product. This description is shown on the auctions listing page.", "wdm-ultimate-auction");?></div>
    </td>
    </tr>
    <?php
        $after_desc = '';
        $after_desc = apply_filters('wdm_ua_after_product_desc', $after_desc);
        echo $after_desc;
        
    for($p=1; $p<=4; $p++)
    {
        $single_img = $this->wdm_post_meta("wdm-image-".$p);
        
        echo '<tr valign="top">
        <th scope="row">
            <label for="auction_image_'.$p.'">'.__("Product Image/Video", "wdm-ultimate-auction").' '.$p.'</label>
        </th>
        <td>
            <input name="auction_image_'.$p.'" type="text" id="auction_image_'.$p.'" class="regular-text wdm_image_'.$p.'_url url" value="'.$single_img.'"/>
            <input name="wdm_upload_image_'.$p.'_button" id="wdm_image_'.$p.'_url" class="button wdm_auction_image_upload" type="button" value="'.__('Select File', 'wdm-ultimate-auction').'"/>
        </td>
    </tr>';
    }
    ?>
    
        <tr valign="top">
        <th scope="row">
            <label for="auction_main_image"><?php _e("Thumbnail Image", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <select id="auction_main_image" name="auction_main_image">
                <?php for($m=1; $m<=4; $m++)
                {
                ?>
                <option value="main_image_<?php echo $m;?>" <?php echo $this->wdm_post_meta("wdm-main-image") == "main_image_".$m ? "selected" : "";?>><?php _e("Product Image/Video", "wdm-ultimate-auction"); echo " ".$m;?></option>
                <?php 
                }
                ?>
            </select>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row">
            <label for="opening_bid"><?php _e("Opening Price", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <?php echo $currency_code;?>
            <input name="opening_bid" type="text" id="opening_bid" class="small-text number" value="<?php echo $this->wdm_post_meta('wdm_opening_bid');?>"/>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="lowest_bid"><?php _e("Lowest Price to Accept", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <?php echo $currency_code;?>
            <input name="lowest_bid" type="text" id="lowest_bid" class="small-text number" value="<?php echo $this->wdm_post_meta('wdm_lowest_bid');?>"/>
        <div>
        <span class="ult-auc-settings-tip"><?php _e("Set Reserve price for your auction.", "wdm-ultimate-auction");?></span>
        <a href="" class="auction_fields_tooltip"><strong>?</strong>
        <span><?php _e("A reserve price is the lowest price at which you are willing to sell your item. If you don't want to sell your item below a certain price, you can a set a reserve price. The amount of your reserve price is not disclosed to your bidders, but they will see that your auction has a reserve price and whether or not the reserve has been met. If a bidder does not meet that price, you're not obligated to sell your item.", "wdm-ultimate-auction");?>
        <br /><strong><?php _e("Why have a reserve price?", "wdm-ultimate-auction");?></strong><br />
        <?php _e("Many sellers have found that too high a starting price discourages interest in their item, while an attractively low starting price makes them vulnerable to selling at an unsatisfactorily low price. A reserve price helps with this.", "wdm-ultimate-auction");?>
        </span>
        </a>
        </div>
    </td>
    </tr>
        <tr valign="top">
        <th scope="row">
            <label for="incremental_value"><?php _e("Incremental Value", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <?php echo $currency_code;?>
            <input name="incremental_value" type="text" id="incremental_value" class="small-text number" value="<?php echo $this->wdm_post_meta('wdm_incremental_val');?>"/>
        <div class="ult-auc-settings-tip"><?php _e("Set an amount from which next bid should start.", "wdm-ultimate-auction");?></div>
    </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="end_date"><?php _e("Ending Date", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <input name="end_date" type="text" id="end_date" class="regular-text" readOnly  value="<?php echo $this->wdm_post_meta('wdm_listing_ends');?>"/>
        <?php $def_timezone = get_option('wdm_time_zone');
        if(!empty($def_timezone)){
        printf(__('Current blog time is %s', 'wdm-ultimate-auction'),'<strong>'.date("Y-m-d H:i:s", time()).'</strong> ');
        echo __('Timezone:', 'wdm-ultimate-auction').' <strong>'.$def_timezone.'</strong>';
        }
        else
        printf(__('Please select your Timezone at %s Tab of the plugin.', 'wdm-ultimate-auction'), '<a href="'.admin_url('admin.php?page=ultimate-auction').'">'.__('Settings', 'wdm-ultimate-auction').'</a>');
        ?> 
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="buy_it_now_price"><?php _e("Buy Now Price", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <?php echo $currency_code;?>
            <input name="buy_it_now_price" type="text" id="buy_it_now_price" class="small-text number" value="<?php echo $this->wdm_post_meta('wdm_buy_it_now');?>"/>
            <div class="ult-auc-settings-tip" ><?php _e("Visitors can buy your auction by making payments via PayPal.", "wdm-ultimate-auction");?></div>
        
    </td>
    </tr>
    <?php do_action('ua_add_shipping_cost_input_field'); //SHP-ADD hook to add new price field ?>
    <tr valign="top">
        <th scope="row">
            <label for="payment_method"><?php _e("Payment Method", "wdm-ultimate-auction");?></label>
        </th>
        <td>
            <?php   /*$paypal_enabled = get_option('wdm_paypal_address');
                    $wire_enabled = get_option('wdm_wire_transfer');
                    $mailing_enabled = get_option('wdm_mailing_address');*/
            $pay_methods = array();
            $pay_methods = get_option('payment_options_enabled');
            ?>
            <select id="payment_method" name="payment_method">
                <option id="wdm_method_paypal" value="method_paypal" <?php if($this->wdm_post_meta('wdm_payment_method') == "method_paypal") echo "selected"; if(!array_key_exists("method_paypal", $pay_methods)) echo "disabled='disabled'";?>>PayPal</option>
                <option id="wdm_method_wire_transfer" value="method_wire_transfer" <?php if($this->wdm_post_meta('wdm_payment_method') == "method_wire_transfer") echo "selected"; if(!array_key_exists("method_wire_transfer", $pay_methods)) echo "disabled='disabled'";?>>Wire Transfer</option>
                <option id="wdm_method_mailing" value="method_mailing" <?php if($this->wdm_post_meta('wdm_payment_method') == "method_mailing") echo "selected"; if(!array_key_exists("method_mailing", $pay_methods)) echo "disabled='disabled'";?>>By Cheque</option>
            </select>
        <div class="ult-auc-settings-tip"><?php _e("Only those methods will be active for which you've entered details inside plugin's settings page.", "wdm-ultimate-auction");?></div>
        </td>
    </tr>
    </table>
    <?php
    global $post_id;
    if(isset($_GET["edit_auction"]) && !empty($_GET["edit_auction"]) && !isset($_GET["reactivate"])){
        echo "<input type='hidden' value='".$_GET["edit_auction"]."' name='update_auction'>";
    }
    else if($post_id != "")//user came here after clicking on submit button
    echo "<input type='hidden' value='".$post_id."' name='update_auction'>";
    echo wp_nonce_field('ua_wp_n_f','ua_wdm_add_auc');
    ?>
    
    <?php submit_button(__('Save Changes', 'wdm-ultimate-auction')); ?>
</form>

<!--script to handle image upload and date picker functionality-->
<script type="text/javascript">
    jQuery(document).ready(function($){
        //var x;
        //jQuery(".wdm_auction_image_upload").click(function(){
        //    tb_show('', 'media-upload.php?type=image&TB_iframe=true');
        //    x=jQuery(this).attr("id");
        //    return false;
        //    });
        //
        //window.send_to_editor = function(html) {
        //    imgurl = jQuery('img',html).attr('src');
        //    jQuery('.'+x).val(imgurl);
        //    tb_remove();
        //    }
        
    var custom_uploader;

    jQuery('.wdm_auction_image_upload').click(function(e) {

     var target_input = jQuery(this).attr('id');

    e.preventDefault();

        //If the uploader object has already been created, reopen the dialog
        //if (custom_uploader) {
        //    custom_uploader.open();
        //    return;
        //}

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        //When a file is selected, grab the URL and set it as the text field's value
    custom_uploader.on('select', function() {
    attachment = custom_uploader.state().get('selection').first().toJSON();
    jQuery('.' + target_input).val(attachment.url);
});

        //Open the uploader dialog
        custom_uploader.open();
    });
   
        jQuery('#end_date').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat : 'yy-mm-dd',
        minDateTime: 0
            });
        });
</script>
<?php get_footer(); ?>