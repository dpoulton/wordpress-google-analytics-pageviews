<?php

// Include the Google API PHP Client for PHP Version 8
require_once 'google-api-php-client--PHP8.0/vendor/autoload.php';

// Define the class
class DanPdotNetGoogleAnalytics {
  // Define initial properties
  public $profile =  false; // Google Analytics profile ID
  public $admin_slug = 'danp-dot-net-google-analytics'; // Slug of the admin page
  public $the_title = 'Google Analytics Pageview Sync'; // Page title and menu item text
  public $key_location = false; // Location of the JSON key file
  public $key_uploaded = false; // Whether a key has been uploaded
  public $post_meta_key = 'danp-dot-net-ga-page-views'; // Define meta key to save pageviews under
  public $cron_name = 'danp_dot_net_ga_pv_cron';
  public $cron_frequency = 'o'; // default cron frequency "o" (off)
  // on calling the class...
  function __construct() {
    // Add the menu item for the admin page
    add_action('admin_menu', function() {
      // Adding the menu item -- content comes from "admin_page()" method
      add_menu_page( $this->the_title, $this->the_title, 'manage_options', $this->admin_slug, array($this,'admin_page'), 'dashicons-chart-pie' );
    });
    // Add a page views column to the list of posts
    add_filter( 'manage_posts_columns' , array($this,'add_column') );
    add_action( 'manage_posts_custom_column' , array($this,'column_value'), 10, 2 );
    // Add a page views column to the list of pages
    add_filter( 'manage_pages_columns' , array($this,'add_column') );
    add_action( 'manage_pages_custom_column' , array($this,'column_value'), 10, 2 );
    // Retrieve the profile ID setting and update the corresponding class property
    $this->profile = get_option('dpdotnet-ga-profile-id',false);
    // Set the location of the JSON key
    $this->key_location = plugin_dir_path( __FILE__ ) . 'the-key.json';
    // Update the class property with whether a JSON key exists
    $this->key_uploaded = file_exists(plugin_dir_path( __FILE__ ) . 'the-key.json');
    // Update the class property with the cron frequency
    $this->cron_frequency = get_option('dpdotnet-ga-cron-freq','o');
   }
   function cron_updater() {
     // Run the method to get the statistics
     $results = $this->update_stats($this->profile);
     // Run the method to save the statistics
     $this->save_results($results['rows']);
   }
   // Convert cron setting to WordPress recurrence value
   function cron_recurrence() {
     if($this->cron_frequency == 'd') return 'daily';
     if($this->cron_frequency == 'w') return 'weekly';
   }
   // Add pages/posts column - column heading
   function add_column( $columns ) {
     return array_merge( $columns,
         array( 'dp_ga_views' => 'Views' ) );
   }
   // Add pages/posts column - column value
   function column_value( $column, $post_id ) {
     if($column == 'dp_ga_views') {
       echo get_post_meta( $post_id, $this->post_meta_key, true );
     }
   }
   function update_cron() {
     // Update cron event setting
     if(isset($_POST['dpdotnet-ga-cron']) && !empty($_POST['dpdotnet-ga-cron'])) {
       // Sanitize the user inputted value
       $cron_frequency = sanitize_text_field($_POST['dpdotnet-ga-cron']);
       if($cron_frequency !== 'o' && $cron_frequency !== 'd' && $cron_frequency !== 'w' && $cron_frequency !== 'm') {
         $cron_frequency = 'o';
       }
       // Add the user input to the database
       update_option('dpdotnet-ga-cron-freq',$cron_frequency);
       // Update the class property
       $this->cron_frequency = $cron_frequency;
     }
     // Get current cron timestamp, false if no current cron
     $timestamp = wp_next_scheduled( $this->cron_name );
     // Add cron if doesn't exist
     if ( !$timestamp ) {
         wp_schedule_event( time(), $this->cron_recurrence(), $this->cron_name );
     }
     // Amend cron if does exist
     if( $timestamp > 0) { // because timestamp is positive integer
       // Unschedule
       wp_unschedule_event( $timestamp, $this->cron_name );
       // Reschedule
       wp_schedule_event( time(), $this->cron_recurrence(), $this->cron_name );
     }
     // Add cron action
     add_action($this->cron_name,array($this,'cron_updater'));
     // Remove cron if setting is "o" = off
     if($this->cron_frequency == 'o') {
       // Unschedule
       wp_unschedule_event( $timestamp, $this->cron_name );
     }
   }
   function admin_page() {
     // Update cron method
     $this->update_cron();
     // Update profile ID on form submission
     if(isset($_POST['dpdotnet-ga-profile-id']) && !empty($_POST['dpdotnet-ga-profile-id'])) {
       // Sanitize the user inputted value
       $new_profile_id = sanitize_text_field($_POST['dpdotnet-ga-profile-id']);
       // Keep numbers only
       $new_profile_id = preg_replace('/[^0-9]/', '', $new_profile_id);
       // Add the user input to the database
       update_option('dpdotnet-ga-profile-id',$new_profile_id);
       // Update the class property
       $this->profile = $new_profile_id;
     }
     // Upload a new JSON key
     if(isset($_FILES['dpdotnet-ga-key-upload']['tmp_name']) && file_exists($_FILES['dpdotnet-ga-key-upload']['tmp_name'])) {
       // Check the upload is valid JSON
       $valid_json = json_decode(file_get_contents($_FILES['dpdotnet-ga-key-upload']['tmp_name']),true);
       // If key location is set, a key already exists, and uploaded JSON is valud
       if($this->key_location !== false && $valid_json !== null && $this->key_uploaded) {
         // Delete old key
         unlink($this->key_location);
       }
       // If key location is set and the uploaded JSON is valid
       if($this->key_location !== false && $valid_json !== null) {
         // Move uploaded file from temporary location to plugin/the-key.json
         move_uploaded_file($_FILES['dpdotnet-ga-key-upload']['tmp_name'],$this->key_location);
         // Update the key uploaded property with either success (1) or failure (0)
         $this->key_uploaded = file_exists(plugin_dir_path( __FILE__ ) . 'the-key.json');
       }
     }
     // Set number of updated posts to zero as a fallback
     $updated = 0;
     // Execute =1 if we're running an update, =0 if we're not
     $execute = (isset($_POST['dpdotnet-ga-update']) && !empty($_POST['dpdotnet-ga-update']) ? 1 : 0);
     // Execute an update
     if($execute) {
       // Run the method to get the statistics
       $results = $this->update_stats($this->profile);
       // Run the method to save the statistics and return the number of updated pages/posts (overwriting the fallback value a few lines  above)
       $updated = $this->save_results($results['rows']);
     }
     // Div page wrapper
     echo '<div class="wrap">';
     // Heading
     echo '<h1>' . esc_html($this->the_title) . '</h1>';
     // Form opening tag, action to same admin page, allow file upload
     echo '<form method="post" action="admin.php?page=' . esc_html($this->admin_slug) . '" enctype="multipart/form-data">';
     // Table open tag
     echo '<table class="form-table">';
     // Table body open tag
     echo '<tbody>';
     // Table row containing profile ID setting
     echo '<tr><th scope="row"><label for="dpdotnet-ga-profile-id">Profile ID</label></th><td><input name="dpdotnet-ga-profile-id" type="text" id="dpdotnet-ga-profile-id" value="' . $this->profile. '" class="regular-text"></td></tr>';
     // Table row containing file upload for JSON API key
     echo '<tr><th scope="row"><label for="dpdotnet-ga-key-upload">Upload JSON Key</label></th><td><input name="dpdotnet-ga-key-upload" type="file" id="dpdotnet-ga-key-upload" accept=".json">' . ($this->key_uploaded ? '<span style="color: green">Key uploaded</span>' : '<span style="color: red">Key required</span>') . '</td></tr>';
     // Cron interval
     echo '<tr><th scope="row"><label for="dpdotnet-ga-cron">Automatically Update</label></th><td><select name="dpdotnet-ga-cron" id="dpdotnet-ga-key-upload"><option value="o"' . ($this->cron_frequency === 'o' ? ' selected' : '') . '>Off</option><option value="d"' . ($this->cron_frequency === 'd' ? ' selected' : '') . '>Daily</option><option value="w"' . ($this->cron_frequency === 'w' ? ' selected' : '') . '>Weekly</option></select></td></tr>';
     // Table body close tag
     echo '</tbody>';
     // Table footer open tag
     echo '<tfoot>';
     // Form buttons
     echo '<tr><th colspan="2">';
     // Save settings button is always shown
     echo '<input class="button button-primary" type="submit" value="Save Settings"> ';
     // Button to save settings *and* exectute API sync shown if JSON API key is uploaded and profile ID is set
     if($this->key_uploaded && $this->profile !== false) {
       echo '<input class="button" type="submit" name="dpdotnet-ga-update" value="Save Settings &amp; Update Statistics">';
     }
     echo '</th></tr>';
     // Table footer close tag
     echo '</tfoot>';
     // Table close tag
     echo '</table>';
     // Form close tag
     echo '</form>';
     // If we're executing an update, output the result
     if($execute) {
       // Sync results subheading
       echo '<hr style="margin: 25px 0">';
       echo '<h2>Sync Results</h2>';
       if($updated > 0) {
         // If more than one page/post is updated, output the number
         echo '<p>The statistics for ' . esc_html($updated) . ' pages/posts have been updated.</p>';
       }
       else {
         // Else nothing was updated
         echo '<p>No pages/posts were updated.</p>';
       }
     }
     // Help
     echo '<hr style="margin: 25px 0">';
     echo '<h2>Help</h2>';
     echo '<p>You will need to set up accounts with Google Analytics and Google Cloud Platform to use this plugin.</p>';
     echo '<p>You need to create API credentials with Google Cloud Platform, and then login to Google Analytics to grant your API credentials access to the data.</p>';
     echo '<p><a href="https://dan-p.net/wordpress-plugins/danp-google-analytics-pageview-sync" target="_blank" class="button">Quick Start Guide</a></p>';
     // Close page wrapper
     echo '</div>';
   }
   function update_stats() {
     // New Google API PHP Client
     $client = new Google_Client();
     // Set the application name
     $client->setApplicationName($this->the_title);
     // Provide the JSON API key
     $client->setAuthConfig($this->key_location);
     // Set readonly analyics scope
     $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
     // New instance of Google Service Analytics class
     $analytics = new Google_Service_Analytics($client);
     // Return the statistics from the Google Analytics API
     return $analytics->data_ga->get(
       'ga:' . $this->profile, // Google Analytics Profile ID
       '2005-01-01', // Date range start
       'today', // Date range end
       'ga:pageviews', // Choose page views dimension
       array(
         'dimensions' => 'ga:pagePath' // Retrieve page path for matching purposes later on
       )
     );
   }
   // Method to save the fetched statistics to the WordPress database
   function save_results($rows) {
     // Array to merge duplicates
     $unique_stats = array();
     // Loop each row of results (pages/posts are one row each)
     foreach($rows as $row) {
       // Get the path and number of page views from the $row variable
       list($path,$views) = $row;
       // Convert path to page/post ID
       $id = url_to_postid($path);
       // Cast views as an integer
       $views = (int) $views;
       // If we have an ID at least one page view for the corresponding ID, then...
       if($id > 0 && $views > 0) {
         // Add the response from Google Analytics to a unique array
         if(isset($unique_stats[$id])) {
           // Because there is an array key with the ID, add the views on
           $unique_stats[$id] = $views + $unique_stats[$id];
         }
         else {
           // Because there isn't an array key with the ID, define it and set to views
           $unique_stats[$id] = $views;
         }
       }
     }
     // Define a variable to use as a counter
     $updates = 0;
     // Loop through the $unique_stats array
     foreach($unique_stats as $id => $total_views) {
       // Update the meta field in the WordPress database
       $success = update_post_meta($id,$this->post_meta_key,$total_views);
       // If success equals true, a new page view value was added to the WordPress database
       if($success !== false) {
         // Increment the counter
         $updates++;
       }
     }
     // Return the counter value
     return $updates;
   }
}

?>
