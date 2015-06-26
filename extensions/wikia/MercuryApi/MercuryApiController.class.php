<?php

use Wikia\Logger\WikiaLogger;

class MercuryApiController extends WikiaController {

	const PARAM_ARTICLE_ID = 'id';
	const PARAM_PAGE = 'page';
	const PARAM_ARTICLE_TITLE = 'title';

	const NUMBER_CONTRIBUTORS = 5;
	const DEFAULT_PAGE = 1;

	const WIKI_VARIABLES_CACHE_TTL = 60;

	private $mercuryApi = null;

	public function __construct() {
		parent::__construct();
		$this->mercuryApi = new MercuryApi();
	}

	/**
	 * @desc Gets smart banner config from WF and cleans it up
	 */
	private function getSmartBannerConfig() {
		if ( !empty( $this->wg->EnableWikiaMobileSmartBanner )
			&& !empty( $this->wg->WikiaMobileSmartBannerConfig )
		) {
			$smartBannerConfig = $this->wg->WikiaMobileSmartBannerConfig;

			unset( $smartBannerConfig[ 'author' ] );

			if ( !empty( $smartBannerConfig[ 'icon' ] )
				&& !isset( parse_url( $smartBannerConfig[ 'icon' ] )[ 'scheme' ] ) // it differs per wiki
			) {
				$smartBannerConfig[ 'icon' ] = $this->wg->extensionsPath . $smartBannerConfig[ 'icon' ];
			}

			$meta = $smartBannerConfig[ 'meta' ];
			unset( $smartBannerConfig[ 'meta' ] );
			$smartBannerConfig[ 'appId' ] = [
				'ios' => str_replace( 'app-id=', '', $meta[ 'apple-itunes-app' ] ),
				'android' => str_replace( 'app-id=', '', $meta[ 'google-play-app' ] ),
			];

			$smartBannerConfig[ 'appScheme' ] = [
				'ios' => $meta[ 'ios-scheme' ],
				'android' => $meta[ 'android-scheme' ]
			];

			return $smartBannerConfig;
		}

		return null;
	}

	/**
	 * @desc Returns user ids for top contributors
	 *
	 * @param int $articleId
	 *
	 * @return int[]
	 */
	private function getTopContributorsPerArticle( $articleId ) {
		$usersIds = $this->mercuryApi->topContributorsPerArticle( $articleId, self::NUMBER_CONTRIBUTORS );
		return $usersIds;
	}

	/**
	 * @desc returns article details
	 *
	 * @param int $articleId
	 * @return mixed
	 */
	private function getArticleDetails( $articleId ) {
		$articleDetails = $this->sendRequest( 'ArticlesApi', 'getDetails', [ 'ids' => $articleId ] )
			->getData()[ 'items' ][ $articleId ];

		$description = $this->getArticleDescription( $articleId );

		$articleDetails[ 'abstract' ] = htmlspecialchars( $articleDetails[ 'abstract' ] );
		$articleDetails[ 'description' ] = htmlspecialchars( $description );

		return $articleDetails;
	}

	/**
	 * @desc Returns description for the article's meta tag.
	 *
	 * This is mostly copied from the ArticleMetaDescription extension.
	 *
	 * @param int $articleId
	 * @param int $descLength
	 * @return string
	 * @throws WikiaException
	 */
	private function getArticleDescription( $articleId, $descLength = 100 ) {
		$article = Article::newFromID( $articleId );

		if ( !( $article instanceof Article ) ) {
			throw new NotFoundApiException();
		}

		$title = $article->getTitle();
		$sMessage = null;

		if ( $title->isMainPage() ) {
			// we're on Main Page, check MediaWiki:Description message
			$sMessage = wfMessage( 'Description' )->text();
		}

		if ( ( $sMessage == null ) || wfEmptyMsg( 'Description', $sMessage ) ) {
			$articleService = new ArticleService( $article );
			$description = $articleService->getTextSnippet( $descLength );
		} else {
			// MediaWiki:Description message found, use it
			$description = $sMessage;
		}

		return $description;
	}

	/**
	 * @desc returns an article in simplified json structure
	 *
	 * @param int $articleId
	 * @return array
	 */
	private function getArticleJson( $articleId, Title $title ) {
		$redirect = $this->request->getVal( 'redirect' );

		$articleAsJson = $this->sendRequest( 'ArticlesApi', 'getAsJson', [
			'id' => $articleId,
			'redirect' => $redirect
		] )->getData();

		$articleType = WikiaPageType::getArticleType( $title );

		if ( !empty( $articleType ) ) {
			$articleAsJson[ 'type' ] = $articleType;
		}

		return $articleAsJson;
	}

	/**
	 * @desc returns top contributors user details
	 *
	 * @param int[] $ids
	 * @return mixed
	 */
	private function getTopContributorsDetails( Array $ids ) {
		if ( empty( $ids ) ) {
			return [];
		}
		try {
			return $this->sendRequest( 'UserApi', 'getDetails', [ 'ids' => implode( ',', $ids ) ] )
				->getData()[ 'items' ];
		} catch ( NotFoundApiException $e ) {
			// getDetails throws NotFoundApiException when no contributors are found
			// and we want the article even if we don't have the contributors
			return [];
		}
	}

	/**
	 * @desc Returns local navigation data for current wiki
	 *
	 * @return array
	 */
	private function getNavigationData() {
		return $this->sendRequest( 'NavigationApi', 'getData' )->getData();
	}

	/**
	 * @desc Returns related pages
	 *
	 * @param int $articleId
	 * @param int $limit
	 * @return mixed
	 */
	private function getRelatedPages( $articleId, $limit = 6 ) {
		if ( class_exists( 'RelatedPages' ) ) {
			return RelatedPages::getInstance()->get( $articleId, $limit );
		} else {
			return false;
		}
	}

	/**
	 * @return Title Article Title
	 * @throws NotFoundApiException
	 * @throws BadRequestApiException
	 */
	private function getTitleFromRequest() {
		$articleId = $this->request->getInt( self::PARAM_ARTICLE_ID, NULL );
		$articleTitle = $this->request->getVal( self::PARAM_ARTICLE_TITLE, NULL );
		$articleTitleValidator = new WikiaValidatorString( [ 'min' => 1, 'required' => true ] );

		if ( !empty( $articleId ) && $articleTitleValidator->isValid( $articleTitle ) ) {
			throw new BadRequestApiException( 'Can\'t use id and title in the same request' );
		}

		if ( empty( $articleId ) && !$articleTitleValidator->isValid( $articleTitle ) ) {
			throw new BadRequestApiException( 'You need to pass title or id of an article' );
		}

		if ( empty( $articleId ) ) {
			$title = Title::newFromText( $articleTitle, NS_MAIN );
		} else {
			$title = Title::newFromId( $articleId, NS_MAIN );
		}

		if ( !$title instanceof Title || !$title->isKnown() ) {
			$title = false;
		}

		if ( empty( $title ) ) {
			throw new NotFoundApiException( 'Unable to find any article' );
		}

		return $title;
	}

	/**
	 * @desc Returns article comments in JSON format
	 *
	 * @throws NotFoundApiException
	 * @throws BadRequestApiException
	 * @throws InvalidParameterApiException
	 */
	public function getArticleComments() {
		$title = $this->getTitleFromRequest();
		$articleId = $title->getArticleId();

		$page = $this->request->getInt( self::PARAM_PAGE, self::DEFAULT_PAGE );

		$commentsResponse = $this->app->sendRequest( 'ArticleComments', 'WikiaMobileCommentsPage', [
			'articleID' => $articleId,
			'page' => $page,
			'format' => WikiaResponse::FORMAT_JSON
		] );

		if ( empty( $commentsResponse ) ) {
			throw new BadRequestApiException();
		}

		$commentsData = $commentsResponse->getData();
		$comments = $this->mercuryApi->processArticleComments( $commentsData );

		$this->response->setVal( 'payload', $comments );
		$this->response->setVal( 'pagesCount', $commentsData[ 'pagesCount' ] );
		$this->response->setVal( 'basePath', $this->wg->Server );
		$this->response->setFormat( WikiaResponse::FORMAT_JSON );
	}

	/**
	 * @desc Returns wiki variables for the current wiki
	 *
	 */
	public function getWikiVariables() {
		global $egFacebookAppId;

		$wikiVariables = $this->mercuryApi->getWikiVariables();

		try {
			$wikiVariables[ 'navData' ] = $this->getNavigationData();
		} catch ( Exception $e ) {
			\Wikia\Logger\WikiaLogger::instance()->error( 'Fallback to empty navigation', [
				'exception' => $e
			] );
			$wikiVariables[ 'navData' ] = [];
		}

		$wikiVariables[ 'vertical' ] = WikiFactoryHub::getInstance()->getWikiVertical( $this->wg->CityId )['short'];
		$wikiVariables[ 'basePath' ] = $this->wg->Server;

		// Used to determine GA tracking
		if ( !empty( $this->wg->IsGASpecialWiki ) ) {
			$wikiVariables[ 'isGASpecialWiki' ] = true;
		}

		if ( !empty( $this->wg->ArticlePath ) ) {
			$wikiVariables[ 'articlePath' ] = str_replace( '$1', '', $this->wg->ArticlePath );
		} else {
			$wikiVariables[ 'articlePath' ] = '/wiki/';
		}

		$smartBannerConfig = $this->getSmartBannerConfig();
		if ( !is_null( $smartBannerConfig ) ) {
			$wikiVariables[ 'smartBanner' ] = $smartBannerConfig;
		}

		if ( !is_null( $egFacebookAppId ) ) {
			$wikiVariables[ 'facebookAppId' ] = $egFacebookAppId;
		}

		$this->response->setVal( 'data', $wikiVariables );
		$this->response->setFormat( WikiaResponse::FORMAT_JSON );

		// cache wikiVariables for 1 minute
		$this->response->setCacheValidity( self:: WIKI_VARIABLES_CACHE_TTL );
	}

	public function getRedirectTitle() {
		$title = $this->getTitleFromRequest();

		if ($title->isRedirect()) {
			$article = Article::newFromID( $title->getArticleId() );
			return $article->getRedirectTarget();
		}

		return null;
	}

	/**
	 * @throws NotFoundApiException
	 * @throws BadRequestApiException
	 */
	public function getArticle() {
		global $wgEnableMainPageDataMercuryApi;

		try {
			$title = $this->getRedirectTitle();
			$data['redirected'] = true;

			if ($title === null) {
				$title = $this->getTitleFromRequest();
				unset($data['redirected']);
			}

			$articleId = $title->getArticleId();
			$data['details'] = $this->getArticleDetails( $articleId );
			$data['topContributors'] = $this->getTopContributorsDetails(
				$this->getTopContributorsPerArticle( $articleId )
			);
			$isMainPage = $title->isMainPage();
			$data['isMainPage'] = $isMainPage;

			if ( $isMainPage && !empty( $wgEnableMainPageDataMercuryApi ) ) {
				$data['mainPageData'] = $this->getMainPageData();
			} else {
				$articleAsJson = $this->getArticleJson( $articleId, $title );
				$data['article'] = $articleAsJson;
			}

			$relatedPages = $this->getRelatedPages( $articleId );

			if ( !empty( $relatedPages ) ) {
				$data['relatedPages'] = $relatedPages;
			}
		} catch ( WikiaHttpException $exception ) {
			$this->response->setCode( $exception->getCode() );

			$data = [];

			$this->response->setVal( 'exception', [
				'message' => $exception->getMessage(),
				'code' => $exception->getCode(),
				'details' => $exception->getDetails()
			] );

			$title = $this->wg->Title;
		}

		$data['adsContext'] = $this->mercuryApi->getAdsContext( $title );

		$this->response->setFormat( WikiaResponse::FORMAT_JSON );
		$this->response->setCacheValidity( WikiaResponse::CACHE_STANDARD );

		$this->response->setVal( 'data', $data );
	}

	/**
	 * @desc HG-377: Returns search suggestions
	 *
	 * @throws NotFoundApiException
	 * @throws MissingParameterApiException
	 */
	public function getSearchSuggestions() {
		$this->response->setFormat( WikiaResponse::FORMAT_JSON );
		$this->response->setValues(
			$this->sendRequest( 'SearchSuggestionsApi', 'getList', $this->request->getParams() )->getData()
		);
	}

	private function getMainPageData() {
		$mainPageData = [];
		$curatedContent = $this->getCuratedContentData();
		$trendingArticles = $this->getTrendingArticlesData();
		$trendingVideos = $this->getTrendingVideosData();
		$wikiaStats = $this->getWikiaStatsData();

		if ( !empty( $curatedContent[ 'items' ] ) ) {
			$mainPageData[ 'curatedContent' ] = $curatedContent[ 'items' ];
		}

		if ( !empty( $curatedContent[ 'featured' ] ) ) {
			$mainPageData[ 'featuredContent' ] = $curatedContent[ 'featured' ];
		}

		if ( !empty( $trendingArticles ) ) {
			$mainPageData[ 'trendingArticles' ] = $trendingArticles;
		}

		if ( !empty( $trendingVideos ) ) {
			$mainPageData[ 'trendingVideos' ] = $trendingVideos;
		}

		if ( !empty( $wikiaStats ) ) {
			$mainPageData[ 'wikiaStats' ] = $wikiaStats;
		}

		return $mainPageData;
	}

	public function getCuratedContentSection() {
		$section = $this->getVal( 'section' );
		$this->response->setFormat( WikiaResponse::FORMAT_JSON );

		if ( empty( $section ) ) {
			$this->response->setVal( 'items', false );
		} else {
			$data = $this->getCuratedContentData( $section );
			$this->response->setVal( 'items', $data[ 'items' ] );
		}
	}

	private function getCuratedContentData( $section = null ) {
		$params = [];
		$data = [];

		if ( $section ) {
			$params[ 'section' ] = $section;
		}

		try {
			$rawData = $this->sendRequest( 'CuratedContent', 'getList', $params )->getData();
			$data = $this->mercuryApi->processCuratedContent( $rawData );
		} catch ( NotFoundException $ex ) {
			WikiaLogger::instance()->info( 'Curated content and categories are empty' );
		}

		return $data;
	}

	private function getTrendingArticlesData() {
		global $wgContentNamespaces;

		$params = [
			'abstract' => false,
			'expand' => true,
			'limit' => 10,
			'namespaces' => implode( ',', $wgContentNamespaces )
		];
		$data = [];

		try {
			$rawData = $this->sendRequest( 'ArticlesApi', 'getTop', $params )->getData();
			$data = $this->mercuryApi->processTrendingArticlesData( $rawData, [ 'title', 'thumbnail', 'url' ] );
		} catch ( NotFoundException $ex ) {
			WikiaLogger::instance()->info( 'Trending articles data is empty' );
		}

		return $data;
	}

	private function getTrendingVideosData() {
		$params = [
			'sort' => 'trend',
			'getThumbnail' => false,
			'format' => 'json',
		];
		$data = [];

		try {
			$rawData = $this->sendRequest( 'SpecialVideosSpecial', 'getVideos', $params )->getData();
			$data = $this->mercuryApi->processTrendingVideoData( $rawData );
		} catch ( NotFoundException $ex ) {
			WikiaLogger::instance()->info( 'Trending videos data is empty' );
		}

		return $data;
	}

	private function getWikiaStatsData() {
		global $wgCityId;

		$service = new WikiDetailsService();
		$wikiDetails = $service->getWikiDetails( $wgCityId );
		return $wikiDetails[ 'stats' ];
	}
}
