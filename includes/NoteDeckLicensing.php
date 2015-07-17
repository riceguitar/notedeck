<?php
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

/*
 |-----------------------------------------------------------
 | Handles automatic updating for the NoteDeck plugin.
 |----------------------------------------------------------- 
 */
class NoteDeckLicensing {

	/*
	 * Static property to hold singelton instance
	 */
	static $instance = false;

	protected $licenseKey; // ACF Field

	protected $licenseKeyOption; // Wp option stored in DB

	protected $licenseStatus; // Current status of the license.

	protected $action; // Supplements license status for better detection of activation and deactivation.

	protected $lastCheck; // Last time the active license was checked.

	protected $checkInterval = 86400; // Inverval of how long between license revalidations.


	/**
	 * Creates and sets all the class properties.
	 */
	private function __construct()
	{
		$this->licenseKey = get_field('license_key', 'option');
		$this->licenseKeyOption = $this->getLicenseOption();
		$this->lastCheck = $this->getLastCheck();
		$this->licenseStatus = $this->validateLicense();
	}

	/**
	 * Getter function for the license status. Will display notification if needed.
	 */
	public function getStatus()
	{
		$this->licenseMessage();
		return $this->licenseStatus;
	}

	/**
	 * Getter function for the license key.
	 */
	public function getLicenseKey()
	{
		return $this->licenseKeyOption;
	}

	/**
	 * Gets the license key stored in WordPress DB as wp option. Will add the option if not present.
	 */
	private function getLicenseOption()
	{
		$key = get_option('nd_95w_license');

		// Wp option doesn't exist, so one is made.
		if ( is_null($key) ) {
			add_option('nd_95w_license', '');
			$key = '';
		}
		return $key;
	}

	/**
	 * Syncs the licenseKey and licenseKeyOptions properties as well as the ACF field on the NoteDeck options page
	 * and the wp option, to the value provided..
	 */
	private function setLicenseKey($value)
	{
		// Updates license key values
		$this->licenseKey = $value;
		update_field('license_key', $value, 'options');
		
		// Updates license key options values.
		$this->licenseKeyOption = $value;
		update_option('nd_95w_license', $value);
	}

	/**
	 * Gets the last check stored in WordPress DB as wp option. Will add the option if not present.
	 */
	private function getLastCheck()
	{
		$check = get_option('nd_95w_checktime');
		if ( is_null($check) ) {
			add_option('nd_95w_checktime', '');
			return 0;
		}
		return $check;
	}

	/**
	 * Sets the lastChecked property on the class and the wp option in the database to the time given.
	 */

	private function resetLastCheck($set_to)
	{
		update_option('nd_95w_checktime', $set_to);
		$this->lastCheck = $set_to;			
	}

	/**
	 * Validates the license and determines if any actions are needed by looking at the relationship between
	 * the license stored in the db as a wp option, and the ACF option field.
	 */
	private function validateLicense()
	{

		if (!$this->licenseKeyOption && !$this->licenseKey) { 
			
			// License Key has not been activated.
			return 'empty';

		} elseif ($this->licenseKeyOption && !$this->licenseKey) { 
			
			// License Key is being deactivated
			return $this->deactivateLicense();

		} elseif (!$this->licenseKeyOption && $this->licenseKey) { 
			
			// License Key is being activated
			return $this->activateLicense();

		} elseif ($this->licenseKeyOption != $this->licenseKey) {

			// Forces a check of the New License Key
			return $this->updateLicense();

		} elseif ($this->licenseKeyOption == $this->licenseKey) {
			
			// Check to see if License Key is still valid.
			return $this->checkLicense();

		}
	}

	/**
	 * Attempts to activate the license, and refreshes the license options based on the attempted activation's outcome.
	 */
	private function activateLicense()
	{
		$status = $this->makeApiRequest('activate_license');
		$this->refreshLicenseOptions('activate', $status);
		return $status;
	}

	/**
	 * Attempts to deactivate the license, and refreshes the license options based on the attempted deactivation's outcome.
	 */
	private function deactivateLicense()
	{
		$status = $this->makeApiRequest('deactivate_license');
		$this->refreshLicenseOptions('deactivate', $status);
		return $status;
	}

	/**
	 * Attempts to deactivate the currently activated license and on success activate the new license. It will refresh the
	 * license options based on the attempted license update's outcome.
	 */
	private function updateLicense()
	{
		$status = $this->makeApiRequest('deactivate_license');
		if ($status == 'deactivated') {
			$status = $this->makeApiRequest('activate_license');
			$this->refreshLicenseOptions('activate', $status);
			return $status;
		}
		$this->refreshLicenseOptions('deactivate', $status);
		return $status;
	}

	/**
	 * Revalidates the license at an interval set by the checkInterval property of the class. It will refresh the license 
	 * options based on the attempted revalidation of the license.
	 */
	private function checkLicense()
	{
		$expired = $this->isCheckExpired();
		if ($expired) {
			$status = $this->makeApiRequest('check_license');
			$this->refreshLicenseOptions('check', $status);
			return $status;
		}
		return 'valid';
	}

	/**
	 * Compares the current time to the last check time to see if it is time to revalidate the license.
	 */
	private function isCheckExpired()
	{
		$difference = time() - $this->lastCheck;
		if ($difference > $this->checkInterval) {
			return true;
		}
		return false;
	}

	/**
	 * Refreshes the different class properties and wp options based off of the action performed and the results of the action.
	 */
	private function refreshLicenseOptions($action, $result)
	{
		if ($action == 'activate') {
			// If the license was activated
			$value = ($result == 'valid') ? $this->licenseKey : '';
			$reset_last_check = ($result == 'valid') ? true : false;
			$set_check_to = time();
			$action = ($result == 'valid') ? 'activated' : '';

		} else if ($action == 'deactivate') {
			// If the license was deactivated
			$value = ($result == 'deactivated') ? '' : $this->licenseKey;
			$reset_last_check = ($result == 'deactivated') ? true : false;
			$set_check_to = '';

		} else if ($action == 'check') {
			// If the license was revalidated.
			$value = ($result == 'valid') ? $this->licenseKey : '';
			$reset_last_check = true;
			$set_check_to = ($result == 'valid') ? time() : '';

		}

		// Refreshes all the properties.
		$this->setLicenseKey($value);
		$this->action = $action;

		// Resets the last check if need be.
		if ($reset_last_check) {
			$this->resetLastCheck($set_check_to);
		}
	}

	/**
	 * Checks to see if NoteDeck needs to display a license message to the user and registeres the action if it does.
	 */
	private function licenseMessage()
	{
		if ($this->licenseStatus != 'valid' || $this->action) {
			add_action( 'admin_notices', array($this, 'displayLicenseMessage') ); 
		}
	}

	/**
	 * Creates the needed message for NoteDeck's license related alerts based off of the license status and action properties.
	 */
	public function displayLicenseMessage()
	{
		if ($this->licenseStatus == 'valid' && $this->action == 'activated') {
			// License was activated. Will last for one page request.
			$class = "update-nag nd-success";
			$url = get_site_url() . '/wp-admin/edit.php?post_type=deck_decks';
			$message = "Thank you for registering your version of NoteDeck! Start adding new decks <a href=\"$url\">here</a>!";
		
		} else if ($this->licenseStatus == 'deactivated') {
			// License was deactivated. Will last for one page request.
			$class = "update-nag";
			$message = "Your NoteDeck license has been deactivated successfully.";

		} elseif ($this->licenseStatus == 'error') {
			// An error occured with the request to the server. Usually happens if there is poor internet connection or the server is down.
			$class = "update-nag";
			$message = "An error occured while processing your NoteDeck license. Please try again later.</br> If error persists, please contact NoteDeck support.";

		} else if ($this->licenseStatus == 'empty') {
			// No license has been entered. Will stay there until the user registers their copy of the NoteDeck plugin.
			$class = "update-nag";
			$url = get_site_url() . '/wp-admin/admin.php?page=notedeck-settings';
			$message = "Thank you for installing NoteDeck! To get started please register you copy by activate your license <a href=\"$url\">here.</a>";

		} else if ($this->licenseStatus == 'invalid') {
			// The license the user entered was invalid. Will stay there for one page reqeust.
			$class = "update-nag nd-error";
			$message = "Woops! Looks like that isn't a valid License Key...";

		} else if ($this->licenseStatus == 'failed') {
			// The plugin is unable to deactivate the license.
			$class = "update-nag nd-error";
			$message = "An error occured trying to deactivate your license. Please try again later.</br> If error persists, please contact NoteDeck support.";

		} else if ($this->licenseStatus == 'inactive') {
			// The revalidation of the license failed so the license deactivated.
			$class = "update-nag";
			$message = "Your NoteDeck License has expired and was deactivated.";
		}

		// Echos the message for wordpress to display.
		echo "<div class=\"$class\"><p>$message</p></div>";
	}

	private function makeApiRequest($action)
	{
		$license = trim($this->licenseKey);
		if ($action == 'deactivate_license') {
			$license = $this->licenseKeyOption;
		};

		$api_params = array( 
					'edd_action'=> $action, 
					'license' 	=> $license, 
					'item_name' => urlencode(ND_95W_SOFTWARE_NAME),
					'url'       => home_url()
				);
		
		$response = wp_remote_post(ND_95W_REMOTE_URL . 'edd-api', array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		if (is_wp_error( $response )){
			return 'error';
		}

		$license_data = json_decode(wp_remote_retrieve_body($response));

		return $license_data->license;
	}

	/**
	 * Wrapper function used to keep a singleton instance.
	 */
	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

}

NoteDeckLicensing::getInstance();