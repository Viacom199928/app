<?php

namespace Wikia\AbPerfTesting\Criteria;

use Wikia\AbPerfTesting\Criterion;

/**
 * Define a "oasisArticles" criterion
 *
 * Applies to Oasis content namespaces articles only
 */
class OasisArticles extends Criterion {
	private $mContext;

	function __construct() {
		$this->mContext = \RequestContext::getMain();
	}

	/**
	 * @param bool $bucket
	 * @return boolean
	 */
	function applies( $bucket ) {
		$skin = $this->mContext->getSkin();
		$title = $this->mContext->getTitle();

		// return true if the oasis & content namespace article check returns the same value as $bucket argument is set to
		return ( \F::app()->checkSkin( 'oasis', $skin ) && $title->exists() && $title->isContentPage() ) === $bucket;
	}
}
