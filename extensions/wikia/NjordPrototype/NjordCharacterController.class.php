<?php

class NjordCharacterController extends WikiaController {

	public function index() {
		$characterModel = new CharacterModuleModel( Title::newMainPage()->getText() );
		$characterModel->getFromProps();
		$this->characterModel = $characterModel;
		if ( !$this->wg->user->isLoggedIn() && $characterModel->isEmpty() ) {
			return $this->skipRendering();
		}
		$this->wg->out->addStyle( AssetsManager::getInstance()->getSassCommonURL( 'extensions/wikia/NjordPrototype/css/NjCharacter.scss' ) );
		$this->wg->Out->addScriptFile( $this->wg->ExtensionsPath . '/wikia/NjordPrototype/scripts/jquery.caret.js' );
		$this->wg->Out->addScriptFile( $this->wg->ExtensionsPath . '/wikia/NjordPrototype/scripts/NjCharacter.js' );
		$this->isAllowedToEdit = $this->wg->user->isAllowed( 'njordeditmode' );
	}


	public function saveModuleTitle() {
		$request = $this->getRequest();
		$moduleTitle = $request->getVal( 'moduletitle', false );
		$success = false;

		$characterModel = new CharacterModuleModel( Title::newMainPage()->getText() );
		$characterModel->getFromProps();

		if ( !empty( $moduleTitle ) ) {
			$characterModel->title = $moduleTitle;
			$characterModel->storeInPage();
			$characterModel->storeInProps();
			$characterModel->initializeImagePaths();
			$success = true;
		}

		$this->getResponse()->setVal( 'success', $success );
		$this->getResponse()->setVal( 'characterModel', $characterModel );
	}

	public function saveModuleItems() {
		$request = $this->getRequest();
		$moduleItems = $request->getVal( 'moduleitems', [ ] );
		$success = false;

		$characterModel = new CharacterModuleModel( Title::newMainPage()->getText() );
		$characterModel->getFromProps();

		if ( !empty( $moduleItems ) && is_array( $moduleItems ) ) {
			$items = [ ];
			foreach ( $moduleItems as $moduleItem ) {
				$item = new ContentEntity();
				$item->link = $moduleItem['link'];
				$item->image = $moduleItem['image'];
				$item->title = $moduleItem['title'];
				$item->description = $moduleItem['description'];
				$items [] = $item;
			}

			$characterModel->contentSlots = $items;
			$characterModel->storeInPage();
			$characterModel->storeInProps();
			$characterModel->initializeImagePaths();
			$success = true;
		}

		$this->getResponse()->setVal( 'success', $success );
		$this->getResponse()->setVal( 'characterModel', $characterModel );
	}
}