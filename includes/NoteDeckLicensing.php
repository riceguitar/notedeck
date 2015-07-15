<?php
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

/**
 * Handles Licensing for NoteDeck Plugin
 */
class NoteDeckLicensing {

	/*
	 * Static property to hold singelton instance
	 */
	static $instance = false;

	protected $licenseKey;

	protected $licenseKeyOption;

	protected $licenseStatus;

	protected $debugMessage;

	private function __construct()
	{
		$this->licenseKey = get_field('license_key', 'option');
		$this->licenseKeyOption = $this->getLicenseOption();
		$this->licenseStatus = $this->validateLicense();
	}

	public function getStatus()
	{
		$this->licenseMessage();

		return $this->licenseStatus;
	}

	private function getLicenseOption()
	{
		$key = get_option('nd_95w_license');
		if ( is_null($key) ) {
			add_option('nd_95w_license', '');
			$key = '';
		}
		return $key;
	}

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

	private function activateLicense()
	{
		$status = $this->makeApiRequest('activate_license');
		$this->refreshLicenseOptions('activate', $status);
		return $status;
	}

	private function deactivateLicense()
	{
		$status = $this->makeApiRequest('deactivate_license');
		$this->refreshLicenseOptions('deactivate', $status);
		return $status;
	}

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

	private function isCheckExpired()
	{
		$check = $this->getLastCheck();
		$difference = time() - $check;
		$this->debugMessage = $difference;
		if ($difference > 86400) {
			return true;
		}
		return false;

	}

	private function getLastCheck()
	{
		$check = get_option('nd_95w_checktime');
		if ( $check  === false ) {
			add_option('nd_95w_checktime', time());
			return 1;
		} else if ( $check === '' )	{
			update_option('nd_95w_checktime', time());
			return 1;
		}
		return $check;
	}

	private function refreshLicenseOptions($action, $result)
	{
		$open_tag = '<p style="margin-bottom: 0; margin-left: 180px;">';
		$close_tag = '</p>';
		if ($action == 'activate') {
			$value = ($result == 'valid') ? $this->licenseKey : '';
			$set_last_check = ($result == 'valid') ? true : false;
			$time = time();
		} else if ($action == 'deactivate') {
			$value = ($result == 'deactivated') ? '' : $this->licenseKey;
			$set_last_check = ($result == 'deactivated') ? true : false;
			$time = '';
		} else if ($action == 'check') {
			$value = ($result == 'valid') ? $this->licenseKey : '';
			$set_last_check = true;
			$time = ($result == 'valid') ? time() : '';
		}

		update_field('license_key', $value, 'options');
		update_option('nd_95w_license', $value);
		$this->licenseKey = $value;
		$this->licenseKeyOption = $value;
		if ($set_last_check) {
			update_option('nd_95w_checktime', $time);
		}
	}

	private function licenseMessage()
	{
		if ($this->licenseStatus != 'valid') {
			add_action( 'admin_notices', array($this, 'displayLicenseMessage') ); 
		}
	}

	public function displayLicenseMessage()
	{
		if ($this->licenseStatus == 'error') {
			$class = "update-nag";
			$message = "There was an error processing your NoteDeck license. Please delete it, save the page, and then re-enter it.";
		} else if ($this->licenseStatus == 'empty') {
			$class = "update-nag";
			$url = get_site_url() . '/wp-admin/admin.php?page=notedeck-settings';
			$message = "Thank you for installing NoteDeck! To get started please activate your license <a href=\"$url\">here!</a>";
		} else if ($this->licenseStatus == 'invalid') {
			$class = "update-nag nd-error";
			$message = "Woops! Looks like that isn't a valid License Key...";
		} else if ($this->licenseStatus == 'failed') {
			$class = "update-nag nd-error";
			$message = "An error occured trying to deactivate your license. Please contact NoteDeck support to manually deactivate your license.";
		} else if ($this->licenseStatus == 'inactive') {
			$class = "update-nag";
			$message = "Your NoteDeck License was deactivated remotely.";
		}
		echo "<div class=\"$class\"><p>$message</p></div>";
	}

	private function makeApiRequest($action)
	{
		$license = trim($this->licenseKey);
		if ($action == 'deactivate_license') {
			$license = $this->licenseKeyOption;
		};

		// die();

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