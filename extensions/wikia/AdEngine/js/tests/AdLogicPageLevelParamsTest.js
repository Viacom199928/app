/**
 * @test-require-asset extensions/wikia/AdEngine/js/AdLogicPageLevelParams.js
 */

describe('AdLogicPageLevelParams', function(){
	it('getPageLevelParams Simple params correct', function() {
		var logMock = function() {},
			windowMock = {
				location: {hostname: 'example.org'},
				cityShort: 'vertical',
				wgDBname: 'dbname',
				wgContentLanguage: 'xx'
			},
			documentMock,
			adLogicShortPageMock,
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock),
			params = adLogicPageLevelParams.getPageLevelParams();

		expect(params.s0).toBe('vertical');
		expect(params.s1).toBe('_dbname');
		expect(params.s2).toBe('article');
		expect(params.lang).toBe('xx');
	});

	it('getPageLevelParams hostprefix and domain params', function() {
		var logMock = function() {},
			windowMock = {location: {}},
			documentMock,
			adLogicShortPageMock,
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams,
			params;

		windowMock.location.hostname = 'an.example.org';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.dmn).toBe('exampleorg');
		expect(params.hostpre).toBe('an');

		windowMock.location.hostname = 'fallout.wikia.com';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.dmn).toBe('wikiacom');
		expect(params.hostpre).toBe('fallout');

		windowMock.location.hostname = 'www.wikia.com';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.dmn).toBe('wikiacom');
		expect(params.hostpre).toBe('www');

		windowMock.location.hostname = 'www.wowwiki.com';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.dmn).toBe('wowwikicom');
		expect(params.hostpre).toBe('www');

		windowMock.location.hostname = 'wowwiki.com';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.dmn).toBe('wowwikicom');
		expect(params.hostpre).toBe('wowwiki');

		windowMock.location.hostname = 'www.bbc.co.uk';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.dmn).toBe('bbccouk');
		expect(params.hostpre).toBe('www');

		windowMock.location.hostname = 'externaltest.fallout.wikia.com';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.dmn).toBe('wikiacom');
		expect(params.hostpre).toBe('externaltest');
	});

	it('getPageLevelParams wpage param', function() {
		var undef,
			logMock = function() {},
			windowMock = {location: {hostname: 'an.example.org'}},
			documentMock,
			adLogicShortPageMock,
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams,
			params;

		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.wpage).toBe(undef, 'undef');

		windowMock.wgPageName = 'Muppet_Wiki';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.wpage).toBe('muppet_wiki', 'Muppet_Wiki');

		windowMock.wgPageName = 'Assassin\'s_Creed_Wiki';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.wpage).toBe('assassin\'s_creed_wiki', 'Assassin\'s_Creed_Wiki');

		windowMock.wgPageName = 'Военная_база_Марипоза';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.wpage).toBe('военная_база_марипоза', 'Военная_база_Марипоза');
	});

	it('getPageLevelParams default DB name', function() {
		var logMock = function() {},
			windowMock = {location: {hostname: 'an.example.org'}},
			documentMock,
			adLogicShortPageMock,
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock),
			params = adLogicPageLevelParams.getPageLevelParams();

		expect(params.s1).toBe('_wikia', 's1=_wikia');
	});

	it('getPageLevelParams language', function() {
		var logMock = function() {},
			windowMock = {location: {hostname: 'an.example.org'}},
			documentMock,
			adLogicShortPageMock,
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams,
			params;

		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.lang).toBe('unknown', 'lang=unknown');

		windowMock.wgContentLanguage = 'xyz';
		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.lang).toBe('xyz', 'lang=xyz');
	});

	it('getPageLevelParams page type', function() {
		var logMock = function() {},
			windowMock = {
				location: {hostname: 'an.example.org'},
				wikiaPageType: 'pagetype'
			},
			documentMock,
			adLogicShortPageMock,
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock),
			params = adLogicPageLevelParams.getPageLevelParams();

		expect(params.s2).toBe('pagetype', 's2=pagetype');
	});

	it('getPageLevelParams article id', function() {
		var logMock = function() {},
			windowMock = {
				location: {hostname: 'an.example.org'},
				wgArticleId: 678
			},
			documentMock,
			adLogicShortPageMock,
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock),
			params = adLogicPageLevelParams.getPageLevelParams();

		expect(params.artid).toBe(678, 'artid=678');
	});

	it('getPageLevelParams has pre footers', function() {
		var logMock = function() {},
			windowMock = {location: {hostname: 'an.example.org'}},
			documentMock,
			adLogicShortPageMockTrue = {hasPreFooters: function() {return true;}},
			adLogicShortPageMockFalse = {hasPreFooters: function() {return false;}},
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams,
			params;

		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMockTrue, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.hasp).toBe('yes', 'yes');

		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMockFalse, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.hasp).toBe('no', 'no');
	});

	it('getPageLevelParams per-wiki custom DART params', function() {
		var logMock = function() {},
			windowMock = {
				location: {hostname: 'an.example.org'},
				wgDartCustomKeyValues: 'key1=value1;key2=value2;key3=value3;key3=value4'
			},
			documentMock,
			adLogicShortPageMock = {hasPreFooters: function() {return true;}},
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock),
			params = adLogicPageLevelParams.getPageLevelParams();

		expect(params.key1).toEqual(['value1'], 'key1=value1');
		expect(params.key2).toEqual(['value2'], 'key2=value2');
		expect(params.key3).toEqual(['value3', 'value4'], 'key3=value3;key3=value4');
	});

	it('getCustomKeyValues', function() {
		var logMock = function() {},
			windowMock = {location: {hostname: 'an.example.org'}},
			documentMock,
			adLogicShortPageMock = {},
			kruxMock,
			abTestMock,
			paramToTrim,
			dartUrlMock = {trimParam: function(param) {return param}},
			adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);

		expect(adLogicPageLevelParams.getCustomKeyValues()).toBe('');

		dartUrlMock.trimParam = function(param) {paramToTrim = param; return 'trimmed';};

		windowMock.wgDartCustomKeyValues = 'key1=value1;key2=value2';
		expect(adLogicPageLevelParams.getCustomKeyValues()).toBe('trimmed');
		expect(paramToTrim).toBe('key1=value1;key2=value2;');
	});

	it('getPageLevelParams Page categories', function() {
		var logMock = function() {},
			windowMock = {
				location: {hostname: 'an.example.org'},
				wgCategories: ['Category', 'Another Category', 'YetAnother Category']
			},
			documentMock,
			adLogicShortPageMock,
			kruxMock,
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams,
			params;

		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.cat).toEqual(['category', 'another_category', 'yetanother_category']);
	});

	it('getPageLevelParams abTest info', function() {
		var logMock = function() {},
			windowMock = {location: {hostname: 'an.example.org'}},
			documentMock,
			adLogicShortPageMock,
			kruxMock,
			abTestMock = {
				getExperiments: function() {
					return [
						{ id: 17, group: { id: 34 } },
						{ id: 19, group: { id: 45 } },
						{ id: 76, group: { id: 112 } }
					];
				}
			},
			abTestMockEmpty = {getExperiments: function() {return [];}},
			abTestMockNone,
			dartUrlMock,
			adLogicPageLevelParams,
			params,
			abParamEmpty;

		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		expect(params.ab).toEqual(['17_34', '19_45', '76_112'], 'ab params passed');

		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMockEmpty, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		abParamEmpty = !params.ab || params.ab.length === 0;
		expect(abParamEmpty).toBeTruthy('no ab param passed when no experiment is active');

		adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMockEmpty, dartUrlMock);
		params = adLogicPageLevelParams.getPageLevelParams();
		abParamEmpty = !params.ab || params.ab.length === 0;
		expect(abParamEmpty).toBeTruthy('no ab param passed when AbTesting is not passed to module');
	});


// Very specific tests for hubs:

	it('getPageLevelParams Hub page: video games', function() {
		var logMock = function() {},
			windowMock = {
				location: {hostname: 'www.wikia.com'},
				cityShort: 'wikia',
				cscoreCat: 'Gaming',
				wgDBname: 'wikiaglobal',
				wgContentLanguage: 'en',
				wgWikiaHubType: 'gaming',
				wikiaPageIsHub: true
			},
			documentMock,
			kruxMock,
			adLogicShortPageMock = {hasPreFooters: function() {return true;}},
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock),
			params = adLogicPageLevelParams.getPageLevelParams();

		expect(params.s0).toBe('hub');
		expect(params.s1).toBe('_gaming_hub');
		expect(params.s2).toBe('hub');
		expect(params.dmn).toBe('wikiacom');
		expect(params.hostpre).toBe('www');
		expect(params.lang).toBe('en');
		expect(params.hasp).toBe('yes');
	});

	it('getUrl Hub page: entertainment', function() {
		var logMock = function() {},
			windowMock = {
				location: {hostname: 'www.wikia.com'},
				cityShort: 'wikia',
				cscoreCat: 'Entertainment',
				wgDBname: 'wikiaglobal',
				wgContentLanguage: 'en',
				wgWikiaHubType: 'ent',
				wikiaPageIsHub: true
			},
			documentMock,
			kruxMock,
			adLogicShortPageMock = {hasPreFooters: function() {return true;}},
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock),
			params = adLogicPageLevelParams.getPageLevelParams();

		expect(	params.s0).toBe('hub');
		expect(	params.s1).toBe('_ent_hub');
		expect(	params.s2).toBe('hub');
		expect(	params.dmn).toBe('wikiacom');
		expect(	params.hostpre).toBe('www');
		expect(	params.lang).toBe('en');
		expect(	params.hasp).toBe('yes');
	});

	it('getUrl Hub page: lifestyle', function() {
		var logMock = function() {},
			windowMock = {
				location: {hostname: 'www.wikia.com'},
				cityShort: 'wikia',
				cscoreCat: 'Lifestyle',
				wgDBname: 'wikiaglobal',
				wgContentLanguage: 'en',
				wgWikiaHubType: 'life',
				wikiaPageIsHub: true
			},
			documentMock,
			kruxMock,
			adLogicShortPageMock = {hasPreFooters: function() {return true;}},
			abTestMock,
			dartUrlMock,
			adLogicPageLevelParams = AdLogicPageLevelParams(logMock, windowMock, documentMock, kruxMock, adLogicShortPageMock, abTestMock, dartUrlMock),
			params = adLogicPageLevelParams.getPageLevelParams();

		expect(params.s0).toBe('hub');
		expect(params.s1).toBe('_life_hub');
		expect(params.s2).toBe('hub');
		expect(params.dmn).toBe('wikiacom');
		expect(params.hostpre).toBe('www');
		expect(params.lang).toBe('en');
		expect(params.hasp).toBe('yes');
	});
});
