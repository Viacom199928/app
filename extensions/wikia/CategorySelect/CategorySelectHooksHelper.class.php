<?php

/**
 * CategorySelect hooks helper class.
 *
 * @author Maciej Błaszkowski <marooned@wikia-inc.com>
 * @author Lucas Garczewski <tor@wikia-inc.com>
 * @author Kyle Florence <kflorence@wikia-inc.com>
 */

class CategorySelectHooksHelper {
	private static $categoriesWikitext;

	/**
	 * Embed CategorySelect on edit pages. It will be moved later via JavaScript
	 * into the right rail. See: /extensions/wikia/EditPageLayout/modules/Categories.js
	 */
	function onEditFormMultiEditForm( $rows, $cols, $ew, $textbox ) {
		$app = F::app();
		$action = $app->wg->Request->getVal( 'action', 'view' );

		if ( $action != 'view' && $action != 'purge' ) {
			$app->wg->Out->addHTML( $app->renderView( 'CategorySelect', 'editPage' ) );
		}

		return true;
	}

	/**
	 * Remove hidden category box from edit page.
	 * Returning false ensures formatHiddenCategories will not be called.
	 */
	function onEditPageCategoryBox( &$editform ) {
		return false;
	}

	/**
	 * Replace content of edited article [with cut out categories]
	 */
	function onEditPageGetContentEnd( $editPage, &$wikitext ) {
		if ( !$editPage->isConflict ) {
			$data = CategorySelect::extractCategoriesFromWikitext( $wikitext );
			$wikitext = $data[ 'wikitext' ];
		}

		return true;
	}

	/**
	 * Add categories to article for DiffEngine (when editing entire article)
	 */
	function onEditPageGetDiffText( $editPage, &$newtext ) {
		if ( $editPage->section == '' && isset( self::$categoriesWikitext ) ) {
			$newtext .= self::$categoriesWikitext;
		}

		return true;
	}

	/**
	 * Concatenate categories on EditPage POST
	 *
	 * @param EditPage $editPage
	 * @param WebRequest $request
	 *
	 */
	function onEditPageImportFormData( $editPage, $request ) {
		$app = F::app();

		if ( $request->wasPosted() ) {
			$categories = $editPage->safeUnicodeInput( $request, 'categories' );
			$categories = CategorySelect::getUniqueCategories( $categories, 'json', 'wikitext' );

			// Prevents whitespace being added when no categories are present
			if ( trim( $categories ) == '' ) {
				$categories = '';
			}

			if ( $editPage->preview || $editPage->diff ) {
				$data = CategorySelect::extractCategoriesFromWikitext( $editPage->textbox1 . $categories );
				$editPage->textbox1 = $data[ 'wikitext' ];
				$categories = CategorySelect::changeFormat( $data[ 'categories' ], 'array', 'wikitext' );

			// Saving article
			} else if ( !empty( $categories ) ) {
				// TODO: still necessary?
				if ( !empty( $app->wg->EnableAnswers ) ) {
					// don't add categories if the page is a redirect
					$magicWords = $app->wg->ContLang->getMagicWords();
					$redirects = $magicWords[ 'redirect' ];

					// first element doesn't interest us
					array_shift( $redirects );

					// check for localized versions of #REDIRECT
					foreach ( $redirects as $alias ) {
						if ( stripos( $editPage->textbox1, $alias ) === 0 ) {
							return true;
						}
					}
				}

				// rtrim needed because of BugId:11238
				$editPage->textbox1 .= rtrim( $categories );
			}

			self::$categoriesWikitext = $categories;
		}

		return true;
	}

	/**
	 * Add hidden field with category metadata
	 *
	 * @param EditPage $editPage
	 * @param OutputPage $out
	 */
	function onEditPageShowEditFormFields( $editPage, $out ) {
		$out->addHTML( F::app()->renderView( 'CategorySelect', 'editPageMetadata' ) );
		return true;
	}

	/**
	 * Allow toggling CategorySelect in user preferences
	 */
	function onGetPreferences( $user, &$preferences ) {
		$app = F::app();

		if ( $app->wg->EnableUserPreferencesV2Ext ) {
			$section = 'editing/starting-an-edit';
			$message = $app->wf->Message( 'tog-disablecategoryselect-v2' );

		} else {
			$section = 'editing/editing-experience';
			$message = $app->wf->Message( 'tog-disablecategoryselect' );
		}

		$preferences[ 'disablecategoryselect' ] = array(
			'type' => 'toggle',
			'section' => $section,
			'label' => $message,
		);

		return true;
	}

	/**
	 * Set global variables for javascript
	 */
	function onMakeGlobalVariablesScript( Array &$vars ) {
		$app = F::app();
		$action = $app->wg->Request->getVal( 'action', 'view' );
		$data = CategorySelect::getExtractedCategoryData();

		$catgories = array();
		if ( !empty( $data ) && is_array( $data[ 'categories' ] ) ) {
			$categories = $data[ 'categories' ];
		}

		$vars[ 'wgCategorySelect' ] = array(
			'categories' => $categories,
			'defaultNamespace' => $app->wg->ContLang->getNsText( NS_CATEGORY ),
			'defaultNamespaces' => CategorySelect::getDefaultNamespaces(),
			'defaultSeparator' => trim( $app->wf->Message( 'colon-separator' )->escaped() ),
			'defaultSortKey' => $app->wg->Parser->getDefaultSort() ?: $app->wg->Title->getText()
		);

		return true;
	}

	/**
	 * Add hooks for view and edit pages
	 */
	public static function onMediaWikiPerformAction( $output, $article, $title, $user, $request, $mediawiki, $force = false ) {
		wfProfileIn( __METHOD__ );

		if ( $force || CategorySelect::isEnabled() ) {
			$app = F::app();
			$action = $app->wg->Request->getVal( 'action', 'view' );

			F::build( 'JSMessages' )->enqueuePackage( 'CategorySelect', JSMessages::INLINE );

			$app->registerHook( 'MakeGlobalVariablesScript', 'CategorySelectHooksHelper', 'onMakeGlobalVariablesScript' );

			// Add hooks for view pages
			if ( $action == 'view' || $action == 'purge' ) {
				$app->registerHook( 'OutputPageMakeCategoryLinks', 'CategorySelectHooksHelper', 'onOutputPageMakeCategoryLinks' );

			// Add hooks for edit pages
			} else if ( $action == 'edit' || $action == 'submit' || $force ) {
				$app->registerHook( 'EditForm::MultiEdit:Form', 'CategorySelectHooksHelper', 'onEditFormMultiEditForm' );
				$app->registerHook( 'EditPage::CategoryBox', 'CategorySelectHooksHelper', 'onEditPageCategoryBox' );
				$app->registerHook( 'EditPage::getContent::end', 'CategorySelectHooksHelper', 'onEditPageGetContentEnd' );
				$app->registerHook( 'EditPage::importFormData', 'CategorySelectHooksHelper', 'onEditPageImportFormData' );
				$app->registerHook( 'EditPage::showEditForm:fields', 'CategorySelectHooksHelper', 'onEditPageShowEditFormFields' );
				$app->registerHook( 'EditPageGetDiffText', 'CategorySelectHooksHelper', 'onEditPageGetDiffText' );
			}
		}

		wfProfileOut( __METHOD__ );
		return true;
	}

	/**
	 * Set category types for view pages (either "normal" or "hidden").
	 */
	public static function onOutputPageMakeCategoryLinks( &$out, $categories, &$categoryLinks ) {
		CategorySelect::setCategoryTypes( $categories );
		return true;
	}
}