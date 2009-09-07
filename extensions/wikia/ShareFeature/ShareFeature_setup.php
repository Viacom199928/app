<?php

/**
 * Share Feature extension
 *
 * Extension allows users/anons to share a link to the page with popular sites
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Bartek Łapiński <bartek@wikia-inc.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * 
 */

if(!defined('MEDIAWIKI')) {
        exit(1);
}

$wgExtensionCredits['other'][] = array(
        'name' => 'ShareFeature',
        'author' => 'Bartek Łapiński',
        'version' => '0.12',
);

$dir = dirname(__FILE__).'/';

$wgExtensionFunctions[] = 'wfShareFeatureInit';
$wgExtensionMessagesFiles['ShareFeature'] = dirname(__FILE__) . '/ShareFeature.i18n.php';
$wgHooks['MonacoAfterArticleLinks'][] = 'wfShareFeatureMonacoAfterArticleLinks';

// display the links for the feature in the page controls bar
function wfShareFeatureMonacoAfterArticleLinks() {
	$function = '';
	echo "<li id=\"control_share_feature\"><a href=\"#\" onclick=\"" . $function . "\">" . wfMsg('sf-link') . "</a></li>";
	return true;
}

// initialize the extension
function wfShareFeatureInit() {
        global $wgExtensionMessagesFiles, $wgAjaxExportList;
        $wgExtensionMessagesFiles['ShareFeature'] = dirname(__FILE__).'/ShareFeature.i18n.php';
        wfLoadExtensionMessages('ShareFeature');

	$wgAjaxExportList[] = 'wfShareFeatureAjaxGetDialog';
}


function wfShareFeatureAjaxGetDialog() {	
	global $wgTitle, $wgCityId;
		
	$tpl = new EasyTemplate( dirname( __FILE__ )."/templates/" );
	$tpl->set_vars(array
		'title' => $wgTitle,
		'wiki' 	=> $wgCityId,
	);
	
	$text = $tpl->execute('dialog');
	$response = new AjaxResponse( $text );
	$response->setContentType('text/plain; charset=utf-8');

	return $response;
}


