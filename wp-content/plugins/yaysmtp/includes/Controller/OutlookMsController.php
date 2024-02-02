<?php

namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class OutlookMsController {
	private $headers = array();
	private $body    = array();
	private $log_id            = null;

	public function getAccessToken() {
		$apiKey          = '';
		$yaysmtpSettings = Utils::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['outlookms'] ) && ! empty( $yaysmtpSettings['outlookms']['outlookms_access_token'] ) ) {
				$apiKey = $yaysmtpSettings['outlookms']['outlookms_access_token']['access_token'];
			}
		}
		return $apiKey;
	}

	public function getUserInf() {
		$user            = '';
		$yaysmtpSettings = Utils::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['outlookms'] ) && ! empty( $yaysmtpSettings['outlookms']['outlookms_auth_email'] ) ) {
				$user = $yaysmtpSettings['outlookms']['outlookms_auth_email'];
			}
		}
		return $user;
	}

	public function __construct( $smtpObj ) {
		new OutlookMsServicesController();

		$this->headers['content-type']  = 'application/json';
		$this->headers['Authorization'] = 'Bearer ' . $this->getAccessToken();

		$this->body['message']['subject'] = $smtpObj->Subject;

		$headers = $smtpObj->getCustomHeaders();

		$headersData = array();
		foreach ( $headers as $head ) {
			$nameHead  = isset( $head[0] ) ? sanitize_text_field( $head[0] ) : false;
			$valueHead = isset( $head[1] ) ? $head[1] : false;
			if ( ! empty( $nameHead ) ) {
				$headersData[] = array(
					'name'  => $nameHead,
					'value' => Utils::saniVal( $valueHead ),
				);
			}
		}

		if ( ! empty( $headersData ) ) {
			$this->body['message']['internetMessageHeaders'] = $headersData;
		}

		$userFrom      = $this->getUserInf();
		$email_address = array(
			'emailAddress' => array(
				'name'    => isset( $userFrom['name'] ) ? sanitize_text_field( $userFrom['name'] ) : '',
				'address' => isset( $userFrom['email'] ) ? $userFrom['email'] : '',
			),
		);

		// Set wp_mail_from && wp_mail_from_name - start
		$currentFromEmail = Utils::getCurrentFromEmail();
		$currentFromName  = Utils::getCurrentFromName();
		if ( ! empty( $userFrom['email'] ) ) {
			$smtpObj->From   = $userFrom['email'];
			$smtpObj->Sender = $userFrom['email'];
		}

		if ( Utils::getForceFromEmail() == 1 ) {
			$smtpObj->From   = $currentFromEmail;
			$smtpObj->Sender = $currentFromEmail;
		}

		if ( Utils::getForceFromName() == 1 ) {
			$smtpObj->FromName = $currentFromName;
		}
		// Set wp_mail_from && wp_mail_from_name - end

		$dataLogsDB           = Utils::prepareDataLogInit( $smtpObj );
		$dataLogsDB['mailer'] = 'Outlook Microsoft';
		$this->log_id         = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $smtpObj, $this->log_id);

		$this->body['message']['from']   = $email_address;
		$this->body['message']['sender'] = $email_address;

		$toAddresses = $smtpObj->getToAddresses();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			$dataRecips = array();
			foreach ( $toAddresses as $toEmail ) {
				$address          = isset( $toEmail[0] ) ? $toEmail[0] : false;
				$name             = isset( $toEmail[1] ) ? $toEmail[1] : false;
				$arrTo            = array();
				$arrTo['address'] = $address;
				if ( ! empty( $name ) ) {
					$arrTo['name'] = $name;
				}

				$dataRecips[] = array( 'emailAddress' => $arrTo );
			}
			if ( ! empty( $dataRecips ) ) {
				$this->body['message']['toRecipients'] = $dataRecips;
			}
		}

		$ccAddresses = $smtpObj->getCcAddresses();
		if ( ! empty( $ccAddresses ) && is_array( $ccAddresses ) ) {
			$dataRecips = array();
			foreach ( $ccAddresses as $ccEmail ) {
				$address          = isset( $ccEmail[0] ) ? $ccEmail[0] : false;
				$name             = isset( $ccEmail[1] ) ? $ccEmail[1] : false;
				$arrCc            = array();
				$arrCc['address'] = $address;
				if ( ! empty( $name ) ) {
					$arrCc['name'] = $name;
				}
				$dataRecips[] = array( 'emailAddress' => $arrCc );
			}
			if ( ! empty( $dataRecips ) ) {
				$this->body['message']['ccRecipients'] = $dataRecips;
			}
		}

		$bccAddresses = $smtpObj->getBccAddresses();
		if ( ! empty( $bccAddresses ) && is_array( $bccAddresses ) ) {
			$dataRecips = array();
			foreach ( $bccAddresses as $bccEmail ) {
				$address           = isset( $bccEmail[0] ) ? $bccEmail[0] : false;
				$name              = isset( $bccEmail[1] ) ? $bccEmail[1] : false;
				$arrBcc            = array();
				$arrBcc['address'] = $address;
				if ( ! empty( $name ) ) {
					$arrBcc['name'] = $name;
				}
				$dataRecips[] = array( 'emailAddress' => $arrBcc );
			}
			if ( ! empty( $dataRecips ) ) {
				$this->body['message']['bccRecipients'] = $dataRecips;
			}
		}

		if ( 'text/plain' === $smtpObj->ContentType ) {
			$content                       = $smtpObj->Body;
			$dataContent['contentType']    = 'text';
			$dataContent['content']        = $content;
			$this->body['message']['body'] = $dataContent;
		} else {
			$content = array(
				'text' => $smtpObj->AltBody,
				'html' => $smtpObj->Body,
			);

			if ( ! empty( $content['html'] ) ) {
				$dataContent = array(
					'contentType' => 'html',
					'content'     => $content['html'],
				);
			} else {
				$dataContent = array(
					'contentType' => 'text',
					'content'     => $content['text'],
				);
			}

			$this->body['message']['body'] = $dataContent;
		}

		// Reply to
		$replyToAddresses = $smtpObj->getReplyToAddresses();
		if ( ! empty( $replyToAddresses ) ) {
			$dataReplyTo = array();

			foreach ( $replyToAddresses as $emailReplys ) {
				if ( empty( $emailReplys ) || ! is_array( $emailReplys ) ) {
					continue;
				}

				$addrReplyTo = isset( $emailReplys[0] ) ? $emailReplys[0] : false;
				$nameReplyTo = isset( $emailReplys[1] ) ? $emailReplys[1] : false;

				if ( ! filter_var( $addrReplyTo, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$dataReplyToTemp            = array();
				$dataReplyToTemp['address'] = $addrReplyTo;
				if ( ! empty( $nameReplyTo ) ) {
					$dataReplyToTemp['name'] = $nameReplyTo;
				}

				$dataReplyTo[] = array( 'emailAddress' => $dataReplyToTemp );
			}

			if ( ! empty( $dataReplyTo ) ) {
				$this->body['message']['replyTo'] = $dataReplyTo;
			}
		}

		// Set attachments.
		$attachments = $smtpObj->getAttachments();
		if ( ! empty( $attachments ) ) {
			$dataAttachments = array();
			foreach ( $attachments as $attach ) {
				$attachFile = false;
				try {
					if ( is_file( $attach[0] ) && is_readable( $attach[0] ) ) {
						$attachFile = file_get_contents( $attach[0] );
					}
				} catch ( \Exception $except ) {
					$attachFile = false;
				}

				if ( false === $attachFile ) {
					continue;
				}

				$dataAttachments[] = array(
					'@odata.type'  => '#microsoft.graph.fileAttachment',
					'name'         => $attach[2],
					'contentBytes' => base64_encode( $attachFile ),
					'contentType'  => $attach[4],
				);
			}

			if ( ! empty( $dataAttachments ) ) {
				$this->body['message']['hasAttachments'] = true;
				$this->body['message']['attachments']    = $dataAttachments;
			}
		}
	}

	public function send() {
		$response = wp_safe_remote_post(
			'https://graph.microsoft.com/v1.0/me/sendMail',
			array(
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => $this->headers,
				'body'        => wp_json_encode( $this->body ),
				'timeout'     => ini_get( 'max_execution_time' ) ? (int) ini_get( 'max_execution_time' ) : 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			$errors = $response->get_error_messages();
			foreach ( $errors as $error ) {
				LogErrors::setErr( $error );
			}
			return;
		}

		// var_dump($response);
		$sent = false;
		if ( ! empty( $response['response'] ) && ! empty( $response['response']['code'] ) ) {
			$code        = (int) $response['response']['code'];
			$codeSucArrs = array( 200, 201, 202, 203, 204, 205, 206, 207, 208, 300, 301, 302, 303, 304, 305, 306, 307, 308 );
			if ( ! in_array( $code, $codeSucArrs ) && ! empty( $response['response'] ) ) {
				$errorBody = json_decode( $response['body'] );
				$message   = '';
				if ( ! empty( $errorBody ) ) {
					$message = '[' . $code . ']: ' . $errorBody->error->message;
				}
				LogErrors::clearErr();
				LogErrors::setErr( 'Mailer: Outlook Microsoft' );
				LogErrors::setErr( $message );

				if ( ! empty( $this->log_id ) ) {
					$updateData['id']           = $this->log_id;
					$updateData['date_time']    = current_time( 'mysql', true );
					$updateData['reason_error'] = $message;
					Utils::updateEmailLog( $updateData );
				}
			} else {
				$sent = true;
				LogErrors::clearErr();

				if ( ! empty( $this->log_id ) ) { 
					$updateData['id']        = $this->log_id;
					$updateData['date_time'] = current_time( 'mysql', true );
					$updateData['status']    = 1;
					Utils::updateEmailLog( $updateData );
				}
			}
		}
		return $sent;
	}
}
