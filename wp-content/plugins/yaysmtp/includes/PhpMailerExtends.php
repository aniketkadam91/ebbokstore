<?php
namespace YaySMTP;

use YaySMTP\Helper\Utils;

defined( 'ABSPATH' ) || exit;

class PhpMailerExtends extends \PHPMailer\PHPMailer\PHPMailer {
	public function send() {
		$currentMailer 			 = Utils::getCurrentMailer();
		$currentMailerFallback   = Utils::getCurrentMailerFallback();
		$useFallbackSmtp 		 = Utils::conditionUseFallbackSmtp();
		$disable_emails_delivery = Utils::getDisableEmailsDeliverySett();
		$mailer_list 			 = Utils::getAllMailer();

		if ( 'yes' !==  $disable_emails_delivery ) {
			$dataLogsDB = Utils::prepareDataLogInit( $this );

			if ( $useFallbackSmtp ) {
				if( 'mail' === $currentMailerFallback || 'smtp' === $currentMailerFallback) {
					$settings = Utils::getYaySmtpSetting();
					Utils::setFromFallback($this, $settings);
				
					$dataLogsDB['mailer'] = $mailer_list[$currentMailerFallback];
					$logId 				  = Utils::insertEmailLogs( $dataLogsDB );
					do_action('yaysmtp_send_before', $this, $logId);
				} 	
			} else {
				if ( 'mail' === $currentMailer || 'smtp' === $currentMailer ) {
					Utils::setFrom($this);

					$dataLogsDB['mailer'] = $mailer_list[$currentMailer];
					$logId 				  = Utils::insertEmailLogs( $dataLogsDB );
					do_action('yaysmtp_send_before', $this, $logId);
				}
			}
		}

		if ( ! $this->preSend() ) {
			return false;
		}

		// disable emails delivery - start
		if ( 'yes' ===  $disable_emails_delivery ) {
			Utils::setFrom($this);
			$dataLogsDB           = Utils::prepareDataLogInit( $this );
			$dataLogsDB['mailer'] = '[' . $mailer_list[$currentMailer] . '] - Development Mode';
			Utils::insertEmailLogs( $dataLogsDB, 'yes' );
			return true;
		}
		// disable emails delivery - end

		if ( $useFallbackSmtp ) {
			if( 'mail' === $currentMailerFallback || 'smtp' === $currentMailerFallback) {
				try {
					$result = $this->postSend();
	
					if ( true === $result && ! empty( $logId )) {
						$updateData['id']        = $logId;
						$updateData['date_time'] = current_time( 'mysql', true );
						$updateData['status']    = 1;
						Utils::updateEmailLog( $updateData );
					} 
	
					return $result;
				} catch ( \Exception $exc ) {
					$this->mailHeader = '';
					$this->setError( $exc->getMessage() );
					if ( $this->exceptions ) {
						throw $exc;
					}
	
					return false;
				}
			} else {
				$smtperObj = $this->getSMTPerObj( $currentMailerFallback );
				if ( $smtperObj ) {
					return $smtperObj->send();
				}
			}	
		} else {
			if ( 'mail' === $currentMailer || 'smtp' === $currentMailer ) {
				try {
					$result = $this->postSend();

					if ( true === $result && ! empty( $logId )) {
						$updateData['id']        = $logId;
						$updateData['date_time'] = current_time( 'mysql', true );
						$updateData['status']    = 1;
						Utils::updateEmailLog( $updateData );
					} 

					return $result;
				} catch ( \Exception $exc ) {
					$this->mailHeader = '';
					$this->setError( $exc->getMessage() );
					if ( $this->exceptions ) {
						throw $exc;
					}

					return false;
				}
			} else {
				$smtperObj = $this->getSMTPerObj( $currentMailer );
				if ( $smtperObj ) {
					return $smtperObj->send();
				}
			}
		}

		return false;
	}

	public function getSMTPerObj( $provider ) {
		$providers = array(
			'sendgrid'   => 'Sendgrid',
			'sendinblue' => 'Sendinblue',
			'gmail'      => 'Gmail',
			'zoho'       => 'Zoho',
			'mailgun'    => 'Mailgun',
			'smtpcom'    => 'SMTPcom',
			'amazonses'  => 'AmazonSES',
			'postmark'   => 'Postmark',
			'sparkpost'  => 'SparkPost',
			'mailjet'    => 'Mailjet',
			'pepipost'   => 'PepiPost',
			'sendpulse'  => 'SendPulse',
			'outlookms'  => 'OutlookMs',
			'mandrill'   => 'Mandrill'
		);
		$tyleFile  = $providers[ $provider ] . 'Controller';
		return $this->getObject( $tyleFile );
	}

	protected function getObject( $fileType ) {
		$obj = null;
		try {
			$class = 'YaySMTP\Controller\\' . $fileType;
			$file  = YAY_SMTP_PLUGIN_PATH . 'includes/Controller/' . $fileType . '.php';
			if ( file_exists( $file ) && class_exists( $class ) ) {
				$obj = $this ? new $class( $this ) : new $class();
			}
		} catch ( \Exception $e ) {
		}

		return $obj;
	}
}
