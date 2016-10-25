<?php

namespace Palmtree\WordPress\Form;

use AndyPalmer\FormLogger;
use Palmtree\Form\Form;
use TixAstronauta\AccIp\AccIp;

abstract class AbstractForm {
	/** @var  Form $form */
	protected $form;
	public $args = [];
	protected $errors = [];
	protected $successMessage = 'Thank you for your message.';
	protected $errorMessage = 'Oops! Something went wrong there, please check the form for errors.';
	protected $logger;

	public function __construct( FormLogger $logger = null ) {
		$this->logger = $logger;
		add_action( 'wp_loaded', [ $this, '_parseRequest' ] );
	}

	public function _parseRequest() {
		$form = $this->getForm();
		$form->handleRequest();

		if ( ! $form->isSubmitted() ) {
			return;
		}

		if ( $form->isValid() ) {
			$redirectField = $form->getField( 'redirect_to' );
			$redirectTo    = ( $redirectField ) ? $redirectField->getData() : false;

			$this->onSuccess();

			if ( $form->isAjax() && Form::isAjaxRequest() ) {
				wp_send_json_success( [ 'message' => $this->successMessage ] );
			}

			if ( $redirectTo ) {
				wp_safe_redirect( $redirectTo );
				exit;
			}
		} else {
			$this->errors = $form->getErrors();

			$this->onFailure();

			if ( $form->isAjax() && Form::isAjaxRequest() ) {
				wp_send_json_error( [ 'message' => $this->errorMessage, 'errors' => $this->errors ] );
			}
		}
	}

	abstract protected function createForm();

	protected function onSuccess() {
		$this->logger->log( $this->getMailBody() );
	}

	protected function getMailBody() {
		$message = '';

		$message .= "----- START OF MESSAGE -----\n\n";

		foreach ( $this->form->getFields( [ 'userInput' => true ] ) as $field ) {
			$message .= $field->getLabel() . ': ';

			if ( $field->getTag() === 'textarea' ) {
				$message .= "\n";
			}

			$message .= $field->getData() . "\n\n";
		}

		$message .= "----- END OF MESSAGE -----\n\n";

		$accIp = new AccIp();

		$message .= "IP Address: " . $accIp->getIpAddress() . "\n";
		$message .= "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

		return $message;
	}

	protected function onFailure() {

	}

	/**
	 * @param mixed $successMessage
	 *
	 * @return AbstractForm
	 */
	public function setSuccessMessage( $successMessage ) {
		$this->successMessage = $successMessage;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSuccessMessage() {
		return $this->successMessage;
	}

	/**
	 * @return Form
	 */
	public function getForm() {
		if ( $this->form === null ) {
			$this->form = $this->createForm();
		}

		return $this->form;
	}
}
