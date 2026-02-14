<?php
/**
 * Plugin Name: Resume Auto Parser
 * Description: Automatically extract data from Resumes (PDF, DOCX, TXT, MD) to auto-fill application forms.
 * Version: 1.0
 * Author: Anisur Rahman
 * Text Domain: resume-auto-parser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFP_PATH', plugin_dir_path( __FILE__ ) );
define( 'AFP_URL', plugin_dir_url( __FILE__ ) );

// Include Composer autoloader
if ( file_exists( AFP_PATH . 'vendor/autoload.php' ) ) {
	require_once AFP_PATH . 'vendor/autoload.php';
}

require_once AFP_PATH . 'includes/class-afp-parser.php';

class AutoFormParser {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_shortcode( 'auto_form_parser', array( $this, 'render_form' ) );
		add_action( 'wp_ajax_afp_parse_document', array( $this, 'ajax_parse_document' ) );
		add_action( 'wp_ajax_nopriv_afp_parse_document', array( $this, 'ajax_parse_document' ) );
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'afp-style', AFP_URL . 'assets/css/style.css', array(), '1.0' );
		wp_enqueue_script( 'afp-uploader', AFP_URL . 'assets/js/uploader.js', array( 'jquery' ), '1.0', true );

		wp_localize_script( 'afp-uploader', 'afp_vars', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'afp_nonce' ),
		) );
	}

	public function render_form() {
		ob_start();
		?>
		<div class="afp-container">
			<div id="afp-upload-zone" class="afp-upload-zone">
				<p>Drag & Drop your Resume (PDF, DOCX, MD) Here</p>
				<span>Fast & Automated Data Extraction</span>
			</div>
			<div id="afp-status" class="afp-status" style="display:none;"></div>

			<form id="afp-main-form" class="afp-form">
				<div class="afp-fields-grid">
					<div class="afp-field">
						<label for="afp_full_name">Full Name:</label>
						<input type="text" id="afp_full_name" name="full_name">
					</div>
					<div class="afp-field">
						<label for="afp_email">Email:</label>
						<input type="email" id="afp_email" name="email">
					</div>
					<div class="afp-field">
						<label for="afp_phone">Phone:</label>
						<input type="text" id="afp_phone" name="phone">
					</div>
					<div class="afp-field">
						<label for="afp_linkedin">LinkedIn:</label>
						<input type="text" id="afp_linkedin" name="linkedin">
					</div>
					<div class="afp-field">
						<label for="afp_github">GitHub:</label>
						<input type="text" id="afp_github" name="github">
					</div>
					<div class="afp-field">
						<label for="afp_website">Website:</label>
						<input type="text" id="afp_website" name="website">
					</div>
					<div class="afp-field">
						<label for="afp_dob">Date of Birth:</label>
						<input type="text" id="afp_dob" name="dob">
					</div>
				</div>
				<div class="afp-field">
					<label for="afp_skills">Technical Skills:</label>
					<textarea id="afp_skills" name="skills" rows="3"></textarea>
				</div>
				<div class="afp-field">
					<label for="afp_summary">Professional Summary:</label>
					<textarea id="afp_summary" name="summary" rows="5"></textarea>
				</div>
				<div class="afp-field">
					<label for="afp_experience">Work Experience:</label>
					<textarea id="afp_experience" name="experience" rows="6"></textarea>
				</div>
				<div class="afp-field">
					<label for="afp_education">Education:</label>
					<textarea id="afp_education" name="education" rows="3"></textarea>
				</div>
				<button type="submit" class="afp-submit">COMPLETE APPLICATION</button>
				<button type="button" id="afp-reset" class="afp-reset">Reset Form</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public function ajax_parse_document() {
		check_ajax_referer( 'afp_nonce', 'nonce' );

		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_error( 'No file uploaded.' );
		}

		$file = $_FILES['file'];
		$file_path = $file['tmp_name'];
		$file_name = $file['name'];
		$extension = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );

		$allowed_extensions = array( 'md', 'txt', 'pdf', 'docx' );
		if ( ! in_array( $extension, $allowed_extensions ) ) {
			wp_send_json_error( 'Invalid file type. Supported: ' . implode( ', ', $allowed_extensions ) );
		}

		$parser = new AFP_Parser();
		$data = $parser->parse_file( $file_path, $extension );

		wp_send_json_success( $data );
	}
}

new AutoFormParser();
