<?php
class AnalyticsProviderGoogleUA implements iAnalyticsProvider {
	static private function isEnabled() {
		global $wgCityId;

		return WikiFactory::getVarValueByName( 'wgAnalyticsProviderUseUA', $wgCityId );
	}

	public function getSetupHtml($params=array()){
		return '';
	}

    public function trackEvent($event, $eventDetails=array()){
        return '';
    }

	static public function onWikiaMobileAssetsPackages( Array &$jsStaticPackages, Array &$jsExtensionPackages, Array &$scssPackages ){
		//should be added unprocessed as per Cardinal Path's request
		//but screw it, that's an additional single request that adds overhead
		//and the main experiment is done on Oasis :P
		if (self::isEnabled()) {
			array_unshift( $jsStaticPackages, 'universal_analytics_js' );
		}

		return true;
	}

	static public function onWikiaSkinTopScripts( &$vars, &$scripts, $skin ){
		if (!self::isEnabled()) {
			return true;
		}

		$app = F::app();

		//do not proceed if skin is WikiaMobile, see onWikiaMobileAssetsPackages
		if ( !( $app->checkSkin( array( 'wikiamobile', 'oasis', 'venus' ), $skin ) ) ) {
			//needs to be added unprocessed as per Cardinal Path's request
			//so AssetsManager is not an option here
			$scripts .= "\n<script type=\"{$app->wg->JsMimeType}\" src=\"{$app->wg->ExtensionsPath}/wikia/AnalyticsEngine/js/universal_analytics.js\"></script>";
		}

		// setup User-ID hash for Universal Analytics user tracking across different devices
		$user = $app->wg->User;

		if (!$user->isAnon()) {
			$vars['wgGAUserIdHash'] = md5($user->getId() . '2eyPRZ8VvrMMui');
		}


		return true;
	}

	static public function onVenusAssetsPackages( &$jsHeadGroups, &$jsBodyGroups, &$cssGroups) {
		if (self::isEnabled()) {
			$jsHeadGroups[] = 'universal_analytics_js';
		}

		return true;
	}

	static public function onOasisSkinAssetGroupsBlocking( &$jsAssetGroups ) {
		// this is only called in Oasis, so there's no need to double-check it
		if (self::isEnabled()) {
			$jsAssetGroups[] = 'universal_analytics_js';
		}
		return true;
	}
}
