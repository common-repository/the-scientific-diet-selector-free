<?php
/*
Plugin Name:  The Scientific Diet Selector
Plugin URI:   https://scientificdiets.com
Description:  The Scientific Diet Selector
Version:      0.7.7
Author:       Choice Magic
Author URI:   https://scientificdiets.com
Text Domain:  scientific-diet-selector
*/

//when shipping a new version, update this too -- this way we can check if it's a diff one than in the options --> need to do an upgrade or not?
$tsdsf_current_version = "0.7.7";
$tsdsf_valid_licence = get_option("tsdsf_valid_licence", 0);

//calls tsdsf_do_upgrade_options IF we have a new version loaded now than the options think...
function tsdsf_update_check() {
    
    global $tsdsf_current_version;
    if (get_option( 'tsdsf_current_version' ) != $tsdsf_current_version) {
        tsdsf_do_upgrade_options();
    }
}

//we only run this when the plugin is updated, just to see if the owner is still using pro version 
function tsdsf_isLicenceValid($licence) {
    
    $siteUrl = get_site_url();
    $url = "https://api.scientificdiets.com/checklicence.php?licence=$licence&site=$siteUrl";

    $isValid = json_decode(wp_remote_retrieve_body(wp_remote_get(esc_url_raw($url)) ), true );
    if ($isValid["valid"] == "1"){
        add_flash_notice( __("That's a valid licence key! Enjoy all unlocked features."), "info", true );
        return "1";
        
    } else {
        //we only want to notify if the user is browsing the plugin settings, not e.g. when it is activated in the plugins screen
        //$screen = get_current_screen();
        //error_log($screen -> base);
        if( function_exists("get_current_screen") && get_current_screen()->base == "options" ) {
            add_flash_notice( __("Not a valid code -- consider purchasing a valid licence key."), "warning", true ); 
        }
        return "0";
    }
        
}

//this is done every time the plugin is updated, and also of course during the first launch
function tsdsf_do_upgrade_options(){
    //note that these are updated only if the options do not exist, e.e. first launch ever, or when new options are added in updates. 
    global $tsdsf_current_version;
    update_option("tsdsf_current_version", $tsdsf_current_version);
    
    if(!get_option('tsdsf_licence')){
        update_option('tsdsf_licence', 'Licence code...');
    }
    
    //we check this from the server, using isLicenceValid-function...calling this only during updates, just to avoid unnecessary traffic...
    if(!get_option('tsdsf_valid_licence')){
        $licence = get_option('tsdsf_licence');
        update_option('tsdsf_valid_licence', tsdsf_isLicenceValid($licence));
    }

    /*if(!get_option('tsdsf_clickbank_id')){
        update_option('tsdsf_clickbank_id', 'kaizendiet');
    }*/
    if(!get_option('tsdsf_promotionbody')){
        update_option('tsdsf_promotionbody', 'Craft manually any promotional content here.');
    }
    if(!get_option('tsdsf_nativetitle')){
        update_option('tsdsf_nativetitle', 'A native ad title. Some diet, most likely.');        
    }
    if(!get_option('tsdsf_nativebody')){
        update_option('tsdsf_nativebody', 'And its description... supports <a href="https://google.com">links!</a>');
    }
    if(!get_option('tsdsf_webhook')){
        update_option('tsdsf_webhook', 'http://yourwebhookurl.com/');
    }
    if(!get_option('tsdsf_check_donatedata')){
        update_option('tsdsf_check_donatedata', 'off');
    }
    if(!get_option('tsdsf_check_nativead')){
        update_option('tsdsf_check_nativead', 'on');
    }
    if(!get_option('tsdsf_check_promobox')){
        update_option('tsdsf_check_promobox', 'on');
    }
    if(!get_option('tsdsf_check_customproblems')){
        update_option('tsdsf_check_customproblems', 'off');
    }
    if(!get_option('tsdsf_check_leadcollector')){
        update_option('tsdsf_check_leadcollector', 'off');
    }
    
    if(!get_option('tsdsf_diet_problems')){
        $diet_problems = array(
            "1"=>array("Emotional eating – I end up overeating often", "Emotional eating solution here."), 
            "2"=>array("I do not have time and/or motivation to exercise", "Exercise solution here."),
            "3"=>array("Lack of mental strength", "Mental strength solution here.")
        );
        update_option('tsdsf_diet_problems', $diet_problems);
    }

    if(!get_option('tsdsf_diet_all_array')){
        //no diets in the local options, let's set it all up the first time
        //all diets have: primary_link, option_id, option_title, option_details

        //$url = 'http://localhost/scientificdiets_api_contribute/api/getalldiets.php';
        $url = 'https://api.scientificdiets.com/getalldiets.php';
        
        $all_diets_server = json_decode(wp_remote_retrieve_body(wp_remote_get(esc_url_raw($url)) ), true );
        $all_diets_local = array();
        //iterate, include an include-tag with val 1 to all diets, save
        $diets = $all_diets_server["diets"];
        foreach($diets as &$d) {
            $d["include"] = "1";
            $all_diets_local[$d["option_id"]] = $d;
        }
        update_option('tsdsf_diet_all_array', $all_diets_local);
        
        //NOTE: the server reply contains also the aggregated ratings... might come handy to minimize server overhead in extreme cases?
        //$diets_ratings = $all_diets_server["ratings"];
        //$all_ratings_local = array();
        /*foreach($diets_ratings as $d) {
            $all_ratings_local[$d["option_id"]] = $d;
        }*/
        //error_log(print_r($all_ratings_local, 1));
    } else {
        //we already have diets, but let's see if there's anything new!
        $all_diets_local = get_option("tsdsf_diet_all_array");
        $url = 'https://api.scientificdiets.com/getalldiets.php';
        $all_diets_server = json_decode(wp_remote_retrieve_body(wp_remote_get(esc_url_raw($url)) ), true );
        $remote_diets = $all_diets_server["diets"];
        //error_log(print_r($all_diets_local, 1));
        foreach($remote_diets as &$d) {
            $diet_id = $d["option_id"];
            //if the key does not exist in local repository of diets, add the diet!
            if(!array_key_exists($diet_id, $all_diets_local)){
                $d["include"] = "1";
                $all_diets_local[$d["option_id"]] = $d;
            }
        }
        update_option('tsdsf_diet_all_array', $all_diets_local);
    }
        
}


function tsdsf_enqueue_admin_scripts( $hook ) {
    if ( 'settings_page_tsdsf_admin_options' != $hook ) {
        return;
    } else {
        wp_enqueue_script( 'tsdsf_admin_scripts_js', plugin_dir_url( __FILE__ ) . '/js/adminscripts.js', array(), '1.0' );
    } 
}

function tsdsf_enqueue_scripts() {

    // JS registers
    wp_register_script('tsdsf_scripts_js', plugin_dir_url(__FILE__) . '/js/scripts.js');

    // CSS registers
    wp_register_style('tsdsf_custom_css', plugin_dir_url(__FILE__) . '/css/custom.css');
    //wp_register_style('tsdsf_custom_css', plugin_dir_url(__FILE__) . '/css/custom.min.css');

    wp_localize_script('tsdsf_scripts_js', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
    wp_localize_script('tsdsf_scripts_js', 'all_problems_array', get_option("tsdsf_diet_problems"));
    wp_localize_script('tsdsf_scripts_js', 'all_diets_array', get_option("tsdsf_diet_all_array"));

    
    // enqueue wp built-in scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-accordion');
    	
    // enqueue other scripts
    wp_enqueue_script('tsdsf_scripts_js');
    
    // enqueue CSSs
    wp_enqueue_style('tsdsf_custom_css');
    

}

function scientific_diet_selector_widget() {
    ob_start();
    include(plugin_dir_path( __FILE__ ).'/thewidget.php');
    return ob_get_clean();  
}

function tsdsf_fetch_localresults() {
    //we want to limit results to the diet ids that exist in the diets that the user wants to include
    $diet_links = get_option("tsdsf_diet_all_array");
    $includeIDs = array();
    foreach($diet_links as $key => $diet) {
        if($diet["include"] == "1"){
            array_push($includeIDs, $diet["option_id"]);
        }
    }
    $includeIDs = implode (",", $includeIDs);
        
    $post_values = stripslashes_deep($_POST);
    $criteria = $post_values["criteria_importances"];
    //now we have which criteria matter, and how much, and which options to only consider
    //get the options and their ratings from the local DB 
    //organize results in the same format as the getrecommendations.php does, and echo them, 
    $results = "TODO";
    echo $results;    
	wp_die();   
}

function tsdsf_fetch_results() {
    $meta = array();
    $meta['site'] = get_site_url();
    $metaStr = json_encode($meta);
    
    $licence = get_option("tsdsf_licence");
    $diet_links = get_option("tsdsf_diet_all_array");
    $includeIDs = array();
    //this will limit the desired diet options only to those that the user has chosen to include in the results
    foreach($diet_links as $key => $diet) {
        if($diet["include"] == "1"){
            array_push($includeIDs, $diet["option_id"]);
        }
    }
    $includeIDs = implode (",", $includeIDs);
        
    $post_values = stripslashes_deep($_POST);
    //$url = "http://localhost/scientificdiets_api_contribute/api/getrecommendations.php";
    $url = "https://api.scientificdiets.com/getrecommendations.php";
    $criteria = $post_values["criteria_importances"];
    $results = wp_remote_retrieve_body( wp_remote_post($url, array(
        'body' => array(
            'criteria_importances' => $criteria,
            'num_of_options' => '5',
            'meta' => $metaStr,
            'licence_code' => $licence,
            'include_ids' => $includeIDs
        )
    )));
    //error_log($results);
    echo $results;    
	wp_die();   
}

function tsdsf_submit_email() {
    
    $webHook = get_option("tsdsf_webhook");
    unset($_POST['action']);
    $json = json_encode($_POST);
    $headers = array('Accept: application/json', 'Content-Type: application/json');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webHook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $output = curl_exec($ch);
    echo $output;
    curl_close($ch);
    wp_die();
}


function tsdsf_admin_menu() {
	add_options_page( 'Configure The Diet Selector', 'The Diet Selector', 'manage_options', 'tsdsf_admin_options', 'tsdsf_setup_admin_page' );
}

function tsdsf_setup_admin_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    $active_tab = "";
    if( isset( $_GET[ 'tab' ] ) ) {
        $active_tab = $_GET[ 'tab' ];
    }
    
	echo "<div class='wrap'>";
    if ($active_tab == "custom_links") {
        global $tsdsf_valid_licence;
        //Customise diet links
        if($tsdsf_valid_licence == 1){
            echo "<h2 class='nav-tab-wrapper'><a href='?page=tsdsf_admin_options&tab=basic_options' class='nav-tab'>Basic Options</a><a href='?page=tsdsf_admin_options&tab=custom_links' class='nav-tab nav-tab-active'>Custom Links</a><a href='?page=tsdsf_admin_options&tab=custom_problems' class='nav-tab'>Custom Problems and Solutions</a></h2><p>Set custom destinations for the diets currently offered by the plugin. At the very minimum, you will want to set custom destinations for <i>the Mediterranean Diet</i>, <i>Weight Watchers</i>, and <i>low-fat</i> as well as <i>low-carb diets</i>, as those are typically the most popular options.</p><p>The include-checkbox simply controls if the diet is ever recommended to users, with all diets included by default.</p>";
            echo "<form method='post' action='admin-post.php'>";
            settings_fields("links-section");
            echo "<input type='hidden' name='action' value='customlinks_cb'>";
            do_settings_sections("wp_sdc-custom-links-options");   
            submit_button(); 
            echo "</form>"; 
        } else {
            echo "<h2 class='nav-tab-wrapper'><a href='?page=tsdsf_admin_options&tab=basic_options' class='nav-tab'>Basic Options</a><a href='?page=tsdsf_admin_options&tab=custom_links' class='nav-tab nav-tab-active'>Custom Links</a><a href='?page=tsdsf_admin_options&tab=custom_problems' class='nav-tab'>Custom Problems and Solutions</a></h2><p>Set custom destinations for the diets currently offered by the plugin. At the very minimum, you might want to set custom destinations for <i>the Mediterranean Diet</i>, <i>Weight Watchers</i>, and <i>low-fat</i> as well as <i>low-carb diets</i>, as those are typically the most popular options.</p>";
            settings_fields("links-section");
            do_settings_sections("wp_sdc-custom-links-options");   
            echo "<button style='height:40px;' class='getPremiumButton' target='_blank' href='https://scientificdiets.com/getpremium'>Get premium to save custom destination links!</button>";
            echo "</form>"; 
        }
            
            
    } else if ($active_tab == "custom_problems") {
        
        global $tsdsf_valid_licence;
        if($tsdsf_valid_licence == 1){
            //Customise solutions
            echo "<h2 class='nav-tab-wrapper'><a href='?page=tsdsf_admin_options&tab=basic_options' class='nav-tab'>Basic Options</a><a href='?page=tsdsf_admin_options&tab=custom_links' class='nav-tab'>Custom Links</a><a href='?page=tsdsf_admin_options&tab=custom_problems' class='nav-tab nav-tab-active'>Custom Problems and Solutions</a></h2><p>The users choose a problem, an the solution is displayed in the diet results screen. Keep these short: No complicated code or syles – a simple copy will work the best, with one link to the solution. Fields below support HTML, so use <a href='https://www.w3schools.com/tags/tag_a.asp' target='_blank'>standard &lt;a&gt; tags for links!</a></p>";
            echo "<form method='post' action='admin-post.php'>";
            settings_fields("problems-section");
            echo "<input type='hidden' name='action' value='customproblems_cb'>";
            do_settings_sections("wp_sdc-custom-problems-options");   
            submit_button(); 
            echo "</form>";  
        } else {
            //Customise solutions
            echo "<h2 class='nav-tab-wrapper'><a href='?page=tsdsf_admin_options&tab=basic_options' class='nav-tab'>Basic Options</a><a href='?page=tsdsf_admin_options&tab=custom_links' class='nav-tab'>Custom Links</a><a href='?page=tsdsf_admin_options&tab=custom_problems' class='nav-tab nav-tab-active'>Custom Problems and Solutions</a></h2><p>The users choose a problem, an the solution is displayed in the diet results screen. Keep these short: No complicated code or syles – a simple copy will work the best, with one link to the solution. Fields below support HTML, so use <a href='https://www.w3schools.com/tags/tag_a.asp' target='_blank'>standard &lt;a&gt; tags for links!</a></p><p>This is a premium feature</p>";
            settings_fields("problems-section");
            do_settings_sections("wp_sdc-custom-problems-options");
            echo "<button style='height:40px;' target='_blank' class='getPremiumButton' href='https://scientificdiets.com/getpremium'>Get premium to save custom problems and solutions!</button>";
            echo "</form>";
        }
   
    } else {
        //default, all basic settings
        echo "<h2 class='nav-tab-wrapper'><a href='?page=tsdsf_admin_options&tab=basic_options' class='nav-tab nav-tab-active'>Basic Options</a><a href='?page=tsdsf_admin_options&tab=custom_links' class='nav-tab'>Custom Links</a><a href='?page=tsdsf_admin_options&tab=custom_problems' class='nav-tab'>Custom Problems and Solutions</a></h2><p>For help on these settings and other monetization, please refer to <a href='https://scientificdiets.com/monetizing/' target='_blank'>https://scientificdiets.com/monetizing/</a></p>";
        echo "<form method='post' action='options.php'>";
        settings_fields("basic-section");
        do_settings_sections("wp_sdc-basic-options");      
        submit_button(); 
        echo "</form>";
    }
    global $tsdsf_valid_licence;
    if($tsdsf_valid_licence == 1){
         echo "<p>As a premium user you can now leverage the marketing superpowers of <a href='?page=tsdsf_admin_options&tab=custom_links'>custom destination links</a> and <a href='?page=tsdsf_admin_options&tab=custom_problems'>custom problems and solutions</a></p></div>.";
    } else {
        echo "<p><a target='_blank' href='https://scientificdiets.com/getpremium'>Become a premium user</a> to leverage marketing superpowers: <a href='?page=tsdsf_admin_options&tab=custom_links'>custom destination links</a> and <a href='?page=tsdsf_admin_options&tab=custom_problems'>custom problems and solutions</a></p></div>.";

    }
   

}

function tsdsf_admin_display_customlinks()
{
    $diet_links = get_option("tsdsf_diet_all_array");
    foreach($diet_links as $key => $value) {
        //option_id --> array, key = $value[option_id]
        $realName = $value["option_title"];
        $URL = $value["primary_link"];        
        $checkId = "check_" . $key;
        $checked = "";
        if($value["include"] == "1"){
            $checked = "checked";
        }
        echo "<b>$realName:</b><br/><input size='60' type='text' name='tsdsf_diet_all_array[$key]' value='$URL' /><br><input type='checkbox' id='$checkId' $checked name='tsdsf_diet_include[$key]' value='$key'><label for='$checkId'> Include?</label><br></br><br/>";
    }
}

function tsdsf_admin_display_customproblems()
{
    $diet_problems = get_option("tsdsf_diet_problems");
    echo "<div id='dietProblemInputsDiv'>";
    foreach($diet_problems as $key => $value) {
        $problem = esc_html($value[0]);
        $solution = esc_html($value[1]);
        echo "<div>Problem:<br><input size='100' type='text' name='tsdsf_diet_problems[$key]' value='$problem' /><br/>Solution:<br><input size='100' type='text' name='tsdsf_diet_solutions[$key]' value='$solution' /><br/><a href='' class='tsdsf_remove_problem'>Delete this</a><br/><br/></div>";
    }
    echo "</div><button id='addProblemButton'>add one</button>";
}

function tsdsf_admin_displayLicence()
{
    $val = get_option("tsdsf_licence");
    echo "<input size='30' type='text' name='tsdsf_licence' id='tsdsf_licence' value='$val' /><hr>";
}

/*function tsdsf_admin_displayCBID()
{
    $val = get_option("tsdsf_clickbank_id");
    echo "<input size='30' type='text' name='tsdsf_clickbank_id' id='tsdsf_clickbank_id' value='$val' /><hr>";
}*/

function tsdsf_admin_displayPromotionBody()
{
    $val = esc_html(get_option("tsdsf_promotionbody"));
    $content = html_entity_decode($val);
    wp_editor( $content, 'tsdsf_promotionbody', array( 
        'textarea_name' => 'tsdsf_promotionbody',
        'media_buttons' => true,
        'textarea_rows' => 4
    ) );
}

function tsdsf_admin_displayNativeTitle()
{
    $val = esc_html(get_option("tsdsf_nativetitle"));
    echo "<input size='100' type='text' name='tsdsf_nativetitle' id='tsdsf_nativetitle' value='$val' />";
}

function tsdsf_admin_displayNativeBody()
{
    $val = esc_html(get_option("tsdsf_nativebody"));
    echo "<input size='100' type='text' name='tsdsf_nativebody' id='tsdsf_nativebody' value='$val' /><hr>";
}

function tsdsf_admin_displayDonateData()
{
    $val = get_option("tsdsf_check_donatedata");
    $checked = ( $val == "on" ) ? 'checked="checked"' : '';
    echo "<input id='tsdsf_check_donatedata' name='tsdsf_check_donatedata' type='checkbox' value='$val' $checked />";
}

function tsdsf_admin_displayNativeAd()
{
    $val = get_option("tsdsf_check_nativead");
    $checked = ( $val == "on" ) ? 'checked="checked"' : '';
    echo "<input id='tsdsf_check_nativead' name='tsdsf_check_nativead' type='checkbox' value='$val' $checked />";
}

function tsdsf_admin_displayPromoBox()
{
    $val = get_option("tsdsf_check_promobox");
    $checked = ( $val == "on" ) ? 'checked="checked"' : '';
    echo "<input id='tsdsf_check_promobox' name='tsdsf_check_promobox' type='checkbox' value='$val' $checked />";
}
//if premium, we can enable this -- otherwise nope
function tsdsf_admin_displayCustomProblems()
{
    global $tsdsf_valid_licence;
    if ($tsdsf_valid_licence == 1){
        $val = get_option("tsdsf_check_customproblems");
        $checked = ( $val == "on" ) ? 'checked="checked"' : '';
        echo "<input id='tsdsf_check_customproblems' name='tsdsf_check_customproblems' type='checkbox' value='$val' $checked />";
    } else {
        $val = get_option("tsdsf_check_customproblems");
        $checked = ( $val == "on" ) ? 'checked="checked"' : '';
        echo "<input disabled id='tsdsf_check_customproblems' name='tsdsf_check_customproblems' type='checkbox' value='$val' $checked /><br/><p><a href='https://scientificdiets.com/getpremium' target='_blank'>This is a premium feature</a>.</p>";
    }

}
//if premium, we can enable this -- otherwise nope
function tsdsf_admin_displayLeadCollector()
{
    global $tsdsf_valid_licence;
    if ($tsdsf_valid_licence == 1){
        $val = get_option("tsdsf_check_leadcollector");
        $checked = ( $val == "on" ) ? 'checked="checked"' : '';
        echo "<input id='tsdsf_check_leadcollector' name='tsdsf_check_leadcollector' type='checkbox' value='$val' $checked />";
    } else {
        $val = get_option("tsdsf_check_leadcollector");
        $checked = ( $val == "on" ) ? 'checked="checked"' : '';
        echo "<input disabled id='tsdsf_check_leadcollector' name='tsdsf_check_leadcollector' type='checkbox' value='$val' $checked /><br/><p><a href='https://scientificdiets.com/getpremium' target='_blank'>This is a premium feature</a>.</p>";
    }

}

function tsdsf_admin_displayWebHook()
{
    $val = esc_html(get_option("tsdsf_webhook"));
    echo "<input size='50' type='text' name='tsdsf_webhook' id='tsdsf_webhook' value='$val' /><hr>";
}

function tsdsf_admin_menu_callback($args){
}

function tsdsf_admin_menu_add_contents()
{
    
	add_settings_section("basic-section", 
                         "Customize basic settings", 
                         "tsdsf_admin_menu_callback", 
                         "wp_sdc-basic-options");
    
    add_settings_section("links-section", 
                         "Customize diet links", 
                         "tsdsf_admin_menu_callback", 
                         "wp_sdc-custom-links-options");
    
    add_settings_section("problems-section", 
                         "Customize problems and solutions", 
                         "tsdsf_admin_menu_callback", 
                         "wp_sdc-custom-problems-options");

    add_settings_field("tsdsf_licence", 
                       "Licence code (for the premium version)", 
                       "tsdsf_admin_displayLicence", 
                       "wp_sdc-basic-options", 
                       "basic-section");
    
    /*add_settings_field("tsdsf_clickbank_id", 
                       "Your ClickBank ID (account nickname)", 
                       "tsdsf_admin_displayCBID", 
                       "wp_sdc-basic-options", 
                       "basic-section");*/
    
        
    add_settings_field("tsdsf_check_promobox", 
                       "Show the promotion box (appears in the results screen)?", 
                       "tsdsf_admin_displayPromoBox", 
                       "wp_sdc-basic-options", 
                       "basic-section");
        
    add_settings_field("tsdsf_promotionbody", 
                       "Promotion snippet", 
                       "tsdsf_admin_displayPromotionBody", 
                       "wp_sdc-basic-options", 
                       "basic-section");
    
    add_settings_field("tsdsf_check_nativead", 
                       "Show the native ad (a promoted diet) as the 6th result?", 
                       "tsdsf_admin_displayNativeAd", 
                       "wp_sdc-basic-options", 
                       "basic-section");
    
    add_settings_field("tsdsf_nativetitle", 
                       "Native ad title", 
                       "tsdsf_admin_displayNativeTitle", 
                       "wp_sdc-basic-options", 
                       "basic-section");

    add_settings_field("tsdsf_nativebody", 
                       "Native ad body (supports HTML, so remember to include a link using a <a href='https://www.w3schools.com/tags/tag_a.asp' target='_blank'>standard &lt;a&gt; tag</a>)", 
                       "tsdsf_admin_displayNativeBody", 
                       "wp_sdc-basic-options", 
                       "basic-section");
    
    add_settings_field("tsdsf_check_donatedata", 
                       "Show links to donate data and to literature on the splash screen?", 
                       "tsdsf_admin_displayDonateData", 
                       "wp_sdc-basic-options", 
                       "basic-section");
    
    add_settings_field("tsdsf_check_customproblems", 
                       "Display custom problems and solutions?", 
                       "tsdsf_admin_displayCustomProblems", 
                       "wp_sdc-basic-options", 
                       "basic-section");
    
    add_settings_field("tsdsf_check_leadcollector", 
                       "Send leads using to the webhook below?", 
                       "tsdsf_admin_displayLeadCollector", 
                       "wp_sdc-basic-options", 
                       "basic-section");
    
    add_settings_field("tsdsf_webhook", 
                       "Webhook URL to post the lead to (name, email, age, gender, weight loss goal, biggest problem index)", 
                       "tsdsf_admin_displayWebhook", 
                       "wp_sdc-basic-options", 
                       "basic-section");
    
    add_settings_field("tsdsf_customdietlinks", 
                   "Set destination URLs", 
                   "tsdsf_admin_display_customlinks", 
                   "wp_sdc-custom-links-options", 
                   "links-section");

    add_settings_field("tsdsf_customdietproblems", 
                   "Define problems and solutions", 
                   "tsdsf_admin_display_customproblems", 
                   "wp_sdc-custom-problems-options", 
                   "problems-section");
    
    register_setting("basic-section", "tsdsf_licence", "tsdsf_validate_licence_cb");
    //register_setting("basic-section", "tsdsf_clickbank_id");
    register_setting("basic-section", "tsdsf_promotionbody");
    register_setting("basic-section", "tsdsf_nativetitle");
    register_setting("basic-section", "tsdsf_nativebody");
    register_setting("basic-section", "tsdsf_webhook");
    register_setting("basic-section", "tsdsf_check_donatedata", "tsdsf_validate_cb");
    register_setting("basic-section", "tsdsf_check_nativead", "tsdsf_validate_cb");
    register_setting("basic-section", "tsdsf_check_promobox", "tsdsf_validate_cb");
    register_setting("basic-section", "tsdsf_check_leadcollector", "tsdsf_validate_cb");
    register_setting("basic-section", "tsdsf_check_customproblems", "tsdsf_validate_cb");
    
    register_setting("links-section", "tsdsf_customdietlinks");
    register_setting("problems-section", "tsdsf_customdietproblems");

}

//special treatment for licence..if changed, let's check if it's valid!
function tsdsf_validate_licence_cb($newLicence){
    $oldLicence = get_option('tsdsf_licence');
    if($oldLicence != $newLicence) {
        update_option('tsdsf_valid_licence', tsdsf_isLicenceValid($newLicence));
    }
    return $newLicence;
}

//validate function for some of the settings = checkboxes, as those won't otherwise return any value --> nothing will get saved in the database
function tsdsf_validate_cb($input){
    if(isset($input)){
        $out = "on";
    } else {
        $out = "off";
    }
    return $out;
}

function tsdsf_activated(){
    tsdsf_do_upgrade_options();
}

function tsdsf_deactivated(){
    delete_option('tsdsf_current_version');
    //delete_option('tsdsf_licence');
    delete_option('tsdsf_valid_licence');
    //delete_option('tsdsf_clickbank_id');
    delete_option('tsdsf_promotionbody');
    delete_option('tsdsf_nativetitle');
    delete_option('tsdsf_nativebody');
    delete_option('tsdsf_webhook');
    delete_option('tsdsf_check_donatedata');
    delete_option('tsdsf_check_nativead');
    delete_option('tsdsf_check_promobox');
    delete_option('tsdsf_check_leadcollector');
    delete_option('tsdsf_check_customproblems');
    delete_option('tsdsf_diet_problems');
    delete_option('tsdsf_diet_all_array');  
}

function customlinks_callback(){
    
    $allDiets = get_option("tsdsf_diet_all_array");
    $newLinks = $_POST["tsdsf_diet_all_array"];
    $newIncludes = $_POST["tsdsf_diet_include"];
    
    foreach($allDiets as $key => &$diet) {
        //we iterate over all diets, changing links as we go and checking if this should be included (by checking if the diet's key is in the newIncludes array as a key)
        $diet["primary_link"] = $newLinks[$key];
        if(array_key_exists($key, $newIncludes)){
            //the diet with this key should be included
            $diet["include"] = "1";
        } else {
            $diet["include"] = "0";
        }
    }
    update_option("tsdsf_diet_all_array", $allDiets);
    wp_redirect("options-general.php?page=tsdsf_admin_options&tab=custom_links");
    
}

//saves the problems and their solutions as an option -- stripping slashes first...
function customproblems_callback(){
    $newProblems = $_POST["tsdsf_diet_problems"];
    $newSolutions = $_POST["tsdsf_diet_solutions"];
    $newProblemsToStore = array();
    foreach($newProblems as $index => $value) {
        $newProblemsToStore[$index][0] =  stripslashes($newProblems[$index]);
        $newProblemsToStore[$index][1] =  stripslashes($newSolutions[$index]);
    }
    update_option("tsdsf_diet_problems", $newProblemsToStore);
    wp_redirect("options-general.php?page=tsdsf_admin_options&tab=custom_problems");
}

function tsdsf_register_query_vars( $vars ) {
	$vars[] = 'tsdspt';
	return $vars;
}

/*function tsdsf_invalid_licence_notice() {
    ?>
    <div class="error notice">
        <p><?php _e( 'Not a valid licence. Bummer!', 'tsdsf' ); ?></p>
    </div>
    <?php
}
function tsdsf_valid_licence_notice() {
    ?>
    <div class="updated notice">
        <p><?php _e( 'The plugin has been updated, excellent!', 'tsdsf' ); ?></p>
    </div>
    <?php
}*/

/**
 * Add a flash notice to {prefix}options table until a full page refresh is done
 *
 * @param string $notice our notice message
 * @param string $type This can be "info", "warning", "error" or "success", "warning" as default
 * @param boolean $dismissible set this to TRUE to add is-dismissible functionality to your notice
 * @return void
 */
 
function add_flash_notice( $notice = "", $type = "warning", $dismissible = true ) {
    // Here we return the notices saved on our option, if there are not notices, then an empty array is returned
    $notices = get_option( "tsdsf_flash_notices", array() );
 
    $dismissible_text = ( $dismissible ) ? "is-dismissible" : "";
 
    // We add our new notice.
    array_push( $notices, array( 
            "notice" => $notice, 
            "type" => $type, 
            "dismissible" => $dismissible_text
        ) );
 
    // Then we update the option with our notices array
    update_option("tsdsf_flash_notices", $notices );
}
 
/**
 * Function executed when the 'admin_notices' action is called, here we check if there are notices on
 * our database and display them, after that, we remove the option to prevent notices being displayed forever.
 * @return void
 */
 
function display_flash_notices() {
    $notices = get_option( "tsdsf_flash_notices", array() );
     
    // Iterate through our notices to be displayed and print them.
    foreach ( $notices as $notice ) {
        printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            $notice['type'],
            $notice['dismissible'],
            $notice['notice']
        );
    }
 
    // Now we reset our options to prevent notices being displayed forever.
    if( ! empty( $notices ) ) {
        delete_option( "tsdsf_flash_notices", array() );
    }
}
//add_flash_notice( __("My notice message, this is a warning and is dismissible"), "warning", true );
//add_flash_notice( __("My notice message, this is an info, but, it is not dismissible"), "info", false );
//add_flash_notice( __("My notice message, this is an error, but, it is not dismissible"), "error", false );

/**
 * @param array $array
 * @param string $value
 * @param bool $asc - ASC (true) or DESC (false) sorting
 * @param bool $preserveKeys
 * @return array
 * */
function sortBySubValue($array, $value, $asc = true, $preserveKeys = false)
{
    if (is_object(reset($array))) {
        $preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
            return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} - $b->{$value}) * ($asc ? 1 : -1);
        }) : usort($array, function ($a, $b) use ($value, $asc) {
            return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} - $b->{$value}) * ($asc ? 1 : -1);
        });
    } else {
        $preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
            return $a[$value] == $b[$value] ? 0 : ($a[$value] - $b[$value]) * ($asc ? 1 : -1);
        }) : usort($array, function ($a, $b) use ($value, $asc) {
            return $a[$value] == $b[$value] ? 0 : ($a[$value] - $b[$value]) * ($asc ? 1 : -1);
        });
    }
    return $array;
}



// We add our display_flash_notices function to the admin_notices
add_action( 'admin_notices', 'display_flash_notices', 12 );
//let's add scripts
add_action( 'wp_enqueue_scripts', 'tsdsf_enqueue_scripts', 10000 );
add_action( 'admin_enqueue_scripts', 'tsdsf_enqueue_admin_scripts' );

//ajax to get diet recommendations from a remote server
add_action( 'wp_ajax_nopriv_tsdsf_fetch_results', 'tsdsf_fetch_results' );
add_action( 'wp_ajax_tsdsf_fetch_results', 'tsdsf_fetch_results' );
//ajax to get diets from local aggregated values
add_action( 'wp_ajax_nopriv_tsdsf_fetch_localresults', 'tsdsf_fetch_localresults' );
add_action( 'wp_ajax_tsdsf_fetch_localresults', 'tsdsf_fetch_localresults' );
//ajax to send email to a webhook
add_action( 'wp_ajax_nopriv_tsdsf_submit_email', 'tsdsf_submit_email' );
add_action( 'wp_ajax_tsdsf_submit_email', 'tsdsf_submit_email' );


//render plugin when shortcode detected!
add_shortcode( 'thescientificdietselector', 'scientific_diet_selector_widget' );

//add an admin-side menu that is used to configure the plugin
add_action( 'admin_menu', 'tsdsf_admin_menu' );
//and actually adding contents to the admin menu upon plugin init
add_action("admin_init", "tsdsf_admin_menu_add_contents");


//custom endpoint for managing the custom URLs admin settings tab (stuff stored as array)
add_action("admin_post_customlinks_cb", "customlinks_callback");
//custom endpoint for managing the custom problems admin settings tab (stuff stored as array)
add_action("admin_post_customproblems_cb", "customproblems_callback");

//are we...up to date?
add_action( 'plugins_loaded', 'tsdsf_update_check' );

//here's when we activate the plugin!
register_activation_hook( __FILE__, 'tsdsf_activated' );
//here's when we deactivate the plugin!
register_deactivation_hook( __FILE__, 'tsdsf_deactivated' );

//we will want to have some custom get params as well, for passing through / affiliate work
add_filter( 'query_vars', 'tsdsf_register_query_vars' );


//let's include a sidebar solution too!
//include(plugin_dir_path( __FILE__ ).'/sidebarwidget.php');
// Register and load the widget
//function tsdsf_load_widget() {
    //register_widget( 'tsdsf_widget' );
//}
//add_action( 'widgets_init', 'tsdsf_load_widget' );