<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * SMS
 *
 * Allows you to send notifications as SMS messages
 *
 * @author		Yuri Salimovskiy
 * @link 		http://www.intoeetive.com/
 * @version		1.0
 */
 

class Sms_postmaster_service extends Base_service {

	public $name = 'SMS';
	
	public $default_settings = array(
        'sms_gateway'	=> 'twilio',
        'sms_username'	=> '',
        'sms_password'	=> '',
        //'sms_api_id'	=> '',
        //'sms_from_number'=> ''
	);

	public $fields = array(
		'sms_gateway' => array(
			'type'  => 'select',
			'id'	=> 'sms_gateway',
			'label' => 'SMS Gateway',
			'settings' => array(
				'options' => array(
					'twilio'     => 'Twilio'
				)		
			)
		),
		'sms_username' => array(
			'label' => 'Account SID'			
		),
        'sms_password' => array(
			'label' => 'Auth token'			
		),
        /*'sms_api_id' => array(
			'label' => 'API ID',	
            'id' => 'sms_api_id'	
		),
        'sms_from_number' => array(
			'label' => 'Sender phone number'			
		)*/
	);

	public $description = 'Send SMS using selected gateway. From email and To email should contain sender/recipient phone numbers in international format (numbers only prefixed with +, e.g. +1234567890)';

	public function __construct()
	{
		parent::__construct();
	}



	public function send($parsed_object, $parcel)
	{		
		$settings = $this->get_settings();

		$message = strip_tags($parsed_object->message);

		if(isset($parsed_object->plain_message) && !empty($parsed_object->plain_message))
		{
			$message = $parsed_object->plain_message;
		}
        
        $settings_a = array(
            'sms_username'  => $settings->sms_username,
            'sms_password'  => $settings->sms_password,
            'sms_from_number' => $parsed_object->from_email
        );
        
        $gateway = $settings->sms_gateway;

        $this->EE->load->library($gateway, $settings_a);
        $ok = $this->EE->$gateway->send($parsed_object->to_email, $message);

		return new Postmaster_Service_Response(array(
			'status'     => $ok == 'ok' ? POSTMASTER_SUCCESS : POSTMASTER_FAILED,
			'parcel_id'  => $parcel->id,
			'channel_id' => isset($parcel->channel_id) ? $parcel->channel_id : FALSE,
			'author_id'  => isset($parcel->entry->author_id) ? $parcel->entry->author_id : FALSE,
			'entry_id'   => isset($parcel->entry->entry_id) ? $parcel->entry->entry_id : FALSE,
			'gmt_date'   => $this->now,
			'service'    => $parcel->service,
			'to_name'    => $parsed_object->to_name,
			'to_email'   => $parsed_object->to_email,
			'from_name'  => $parsed_object->from_name,
			'from_email' => $parsed_object->from_email,
			'cc'         => $parsed_object->cc,
			'bcc'        => $parsed_object->bcc,
			'subject'    => $parsed_object->subject,
			'message'    => $parsed_object->message,
			'parcel'     => $parcel
		));
	}

	public function display_settings($settings, $parcel)
	{	
		return $this->build_table($settings);
	}
}