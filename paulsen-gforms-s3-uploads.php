<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Paulsen_Gforms_S3_Uploads
 *
 * @wordpress-plugin
 * Plugin Name:       Paulsen Gravity Forms S3 Uplods
 * Description:       Move files uploaded through Gravity Forms to S3
 * Version:           1.0.0
 * Author:            Paulsen
 * Author URI:        http://www.paulsen.ag/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       paulsen-gforms-s3-uploads
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-paulsen-gforms-s3-uploads-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-paulsen-gforms-s3-uploads-deactivator.php';

/** This action is documented in includes/class-paulsen-gforms-s3-uploads-activator.php */
register_activation_hook( __FILE__, array( 'Paulsen_Gforms_S3_Uploads_Activator', 'activate' ) );

/** This action is documented in includes/class-paulsen-gforms-s3-uploads-deactivator.php */
register_deactivation_hook( __FILE__, array( 'Paulsen_Gforms_S3_Uploads_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-paulsen-gforms-s3-uploads.php';

	// Include the SDK using the Composer autoloader
	require 'vendor/autoload.php';

	use Aws\S3\S3Client;

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Paulsen_Gforms_S3_Uploads();
	$plugin->run();

	// Show the S3 URL when viewing entries
	add_filter("gform_get_input_value", "paulsen_update_field", 10, 4);
	function paulsen_update_field($value, $lead, $field, $input_id){
		if(is_admin() && $lead["form_id"] == GFORM_S3_FORM_ID && $field["id"] == GFORM_S3_FIELD_ID) {
			$value = str_replace($_SERVER['SERVER_NAME'], "//" . GFORM_S3_BUCKET . ".s3.amazonaws.com", $value);
			return $value;
		} else {
			return $value;
		}
	}

	// Upload to S3 after submission
	add_action('gform_after_submission', 'post_to_third_party', 10, 2);
	function post_to_third_party($entry, $form) {
		if ($entry['form_id'] == GFORM_S3_FORM_ID) {
			// Instantiate the S3 client with your AWS credentials
			$client = S3Client::factory(array(
			    'key'    => AWS_ACCESS_KEY_ID,
			    'secret' => AWS_SECRET_ACCESS_KEY,
			));

			$attachment_url = $entry[GFORM_S3_FIELD_ID];

			$wp_upload_dir = wp_upload_dir();

			$upload_path = str_replace(home_url(), '', $wp_upload_dir['baseurl']);

			$upload_filename = str_replace($wp_upload_dir['baseurl'], '', $attachment_url);

			$attachment_url = $wp_upload_dir['basedir'] . $upload_filename;

			// Upload an object by streaming the contents of a file
			// $pathToFile should be absolute path to a file on disk
			$result = $client->putObject(array(
			    'Bucket'     => GFORM_S3_BUCKET,
			    'Key'        => $upload_path . $upload_filename,
			    'SourceFile' => $attachment_url,
			    'ACL'        => 'public-read'
			));
		?><pre><?php print_r($entry);?></pre><?php
		}

	}


}
run_plugin_name();
