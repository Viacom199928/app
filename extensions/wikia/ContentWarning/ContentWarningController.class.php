<?php

/**
 * Content Warning Controller
 * @author Kyle Florence, Saipetch Kongkatong, Tomasz Odrobny
 */
class ContentWarningController extends WikiaController {

	public function init() {
//		$this->response->addAsset( 'extensions/wikia/ContentWarning/js/ContentWarning.js' );
//		$this->response->addAsset( 'extensions/wikia/ContentWarning/css/ContentWarning.scss' );
	}

	/**
	 * render index template
	 */
	public function index() {
		$this->title = $this->wf->Msg('content-warning-title');
		$this->body = $this->wf->MsgExt( 'content-warning-body', array('parse'), $this->wg->Server );
		$this->btnContinue = $this->wf->Msg( 'content-warning-button-continue' );
		$this->btnCancel = $this->wf->Msg( 'content-warning-button-cancel' );
	}

	/**
	 * approve content warning
	 * @responseParam string result [ok/error]
	 * @responseParam string msg - result message
	 */
	public function approveContentWarning() {
		if( !$this->wg->User->isLoggedIn() ) {
			$this->result = 'error';
			$this->msg = 'Logged in user only.';	// for debuging
			return;
		}

		$userId = $this->wg->User->getId();
		$this->wf->SetWikiaPageProp( WPP_CONTENT_WARNING, $userId, 1);

		// clear cache
		$memKey = $this->getMemKeyContentWarning( $userId );
		$this->wg->Memc->delete( $memKey );

		$this->result = 'ok';
	}

	/**
	 * get content warning approved
	 * @responseParam integer contentWarningApproved [0/1]
	 */
	public function getContentWarningApproved() {
		$this->wf->profileIn( __METHOD__ );

		$contentWarningApproved = 0;
		if( $this->wg->User->isLoggedIn() ) {
			$userId = $this->wg->User->getId();
			$memKey = $this->getMemKeyContentWarning( $userId );
			$contentWarningApproved = $this->wg->Memc->get( $memKey );
			if ( is_null($contentWarningApproved) ) {
				$contentWarningApproved = intval( $this->wf->GetWikiaPageProp( WPP_CONTENT_WARNING, $userId ) );

				$this->wg->Memc->set( $memKey, $contentWarningApproved, 60*60*12 );
			}
		}

		$this->wf->profileOut( __METHOD__ );

		$this->contentWarningApproved = intval( $contentWarningApproved );
	}

	/**
	 * get memcache key for content warning
	 * @param integer $userId
	 * @return type 
	 */
	protected function getMemKeyContentWarning( $userId ) {
		return $this->wf->MemcKey( 'content_warning_'.$userId );
	}
}
