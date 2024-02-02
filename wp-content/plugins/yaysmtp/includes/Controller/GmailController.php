<?php

namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class GmailController {
	public $smtpObj;

	public function __construct( $smtpObj ) {
		$this->smtpObj = $smtpObj;
	}

	public function send() {
		$use_fallback_smtp = Utils::conditionUseFallbackSmtp();
		$servVend          = new GmailServiceVendController( $use_fallback_smtp );
		$Service_Gmail     = new \Google_Service_Gmail( $servVend->getclientWebService( $use_fallback_smtp ) );

		try {
			$resp             = $Service_Gmail->users_settings_sendAs->listUsersSettingsSendAs( 'me' );
			$userSendFromAddr = array_map(
				function ( $sendAsObject ) {
					return $sendAsObject['sendAsEmail'];
				},
				(array) $resp->getSendAs()
			);
		} catch ( \Exception $excp ) {
			$userSendFromAddr = array();
		}

		if ( ! in_array( $this->smtpObj->From, $userSendFromAddr, true ) ) {
			$profileMe = array( 'email' => $Service_Gmail->users->getProfile( 'me' )->getEmailAddress() );
			if ( ! empty( $profileMe['email'] ) ) {
				$this->smtpObj->From   = $profileMe['email'];
				$this->smtpObj->Sender = $profileMe['email'];
			}
		}

		// Set wp_mail_from && wp_mail_from_name - start
		if ( $use_fallback_smtp ) {
			$settings = Utils::getYaySmtpSetting();
			$currentFromEmail = Utils::getCurrentFromEmailFallback();
			$currentFromName  = Utils::getCurrentFromNameFallback();
			
			if ( isset( $settings['fallback_force_from_email'] ) && 'yes' == $settings['fallback_force_from_email'] ) {
				$this->smtpObj->From   = $currentFromEmail;
				$this->smtpObj->Sender = $currentFromEmail;
			}
			if ( isset( $settings['fallback_force_from_name'] ) && 'yes' == $settings['fallback_force_from_name'] ) {
				$this->smtpObj->FromName = $currentFromName;
			}
		} else {
			$currentFromEmail = Utils::getCurrentFromEmail();
			$currentFromName  = Utils::getCurrentFromName();
	
			if ( Utils::getForceFromEmail() == 1 ) {
				$this->smtpObj->From   = $currentFromEmail;
				$this->smtpObj->Sender = $currentFromEmail;
			}
	
			if ( Utils::getForceFromName() == 1 ) {
				$this->smtpObj->FromName = $currentFromName;
			}
		}
		// Set wp_mail_from && wp_mail_from_name - end

		$dataLogsDB           = Utils::prepareDataLogInit( $this->smtpObj );
		$dataLogsDB['mailer'] = 'Gmail';

		$logId = Utils::insertEmailLogs( $dataLogsDB );
		do_action('yaysmtp_send_before', $this->smtpObj, $logId);
		
		try {
			$sent = false;
			// Fix Bcc
			$this->smtpObj->Mailer = 'mail'; 
			$this->smtpObj->preSend();
			$Service_Gmail_Message = new \Google_Service_Gmail_Message();
			$Service_Gmail_Message->setRaw( str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), base64_encode( $this->smtpObj->getSentMIMEMessage() ) ) );

			$response   = $Service_Gmail->users_messages->send( 'me', $Service_Gmail_Message );
			$responseId = $response->getId();
			if ( method_exists( $response, 'getId' ) && ! empty( $responseId ) ) {
				$sent = true;}

			if ( $sent ) {
				if ( $use_fallback_smtp ) {
					LogErrors::clearErrFallback();
				} else {
					LogErrors::clearErr();
				}
				
				if ( ! empty( $logId ) ) { 
					$updateData['id']        = $logId;
					$updateData['date_time'] = current_time( 'mysql', true );
					$updateData['status']    = 1;
					Utils::updateEmailLog( $updateData );
				}
			} 
			return $sent;

		} catch ( \Exception $e ) {
			if ( $use_fallback_smtp ) {
				LogErrors::clearErrFallback();
			} else {
				LogErrors::clearErr();
				LogErrors::setErr( 'Mailer: Gmail' );
			}

			$mess = $e->getMessage();
			if ( ! is_string( $mess ) ) {
				$mess = wp_json_encode( $mess );
			} else {
				$mess = wp_strip_all_tags( $mess, false );
			}

			
			if ( $use_fallback_smtp ) {
				LogErrors::setErrFallback( $mess );
			} else {
				LogErrors::setErr( $mess );
			}
		}
	}
}
