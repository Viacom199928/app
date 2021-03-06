<?php
/**
 * TCSStatsApi
 * PHP version 5
 *
 * @category Class
 * @package  Swagger\Client
 * @author   http://github.com/swagger-api/swagger-codegen
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link     https://github.com/swagger-api/swagger-codegen
 */
/**
 *  Copyright 2015 SmartBear Software
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program. 
 * https://github.com/swagger-api/swagger-codegen 
 * Do not edit the class manually.
 */

namespace Swagger\Client\TemplateClassification\Storage\Api;

use \Swagger\Client\Configuration;
use \Swagger\Client\ApiClient;
use \Swagger\Client\ApiException;
use \Swagger\Client\ObjectSerializer;

/**
 * TCSStatsApi Class Doc Comment
 *
 * @category Class
 * @package  Swagger\Client
 * @author   http://github.com/swagger-api/swagger-codegen
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class TCSStatsApi
{

    /**
     * API Client
     * @var \Swagger\Client\ApiClient instance of the ApiClient
     */
    protected $apiClient;
  
    /**
     * Constructor
     * @param \Swagger\Client\ApiClient|null $apiClient The api client to use
     */
    function __construct($apiClient = null)
    {
        if ($apiClient == null) {
            $apiClient = new ApiClient();
            $apiClient->getConfig()->setHost('https://localhost/');
        }
  
        $this->apiClient = $apiClient;
    }
  
    /**
     * Get API client
     * @return \Swagger\Client\ApiClient get the API client
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }
  
    /**
     * Set the API client
     * @param \Swagger\Client\ApiClient $apiClient set the API client
     * @return TCSStatsApi
     */
    public function setApiClient(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }
  
    
    /**
     * getClassifiedTemplatesCount
     *
     * Returns number of classified templates on all wikis
     *
     * @return \Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getClassifiedTemplatesCount()
    {
        
  
        // parse inputs
        $resourcePath = "/stats";
        $resourcePath = str_replace("{format}", "json", $resourcePath);
        $method = "GET";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array());
  
        
        
        
        
        
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } else if (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        
        // make the API Call
        try
        {
            list($response, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, $method,
                $queryParams, $httpBody,
                $headerParams, '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats'
            );
            
            if (!$response) {
                return null;
            }

            return $this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats', $httpHeader);
            
        } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
        
        return null;
        
    }
    
    /**
     * getClassifiedTemplatesByProviderCount
     *
     * Returns number of classified templates on all wikis by provider
     *
     * @param string $provider Provider (required)
     * @return \Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getClassifiedTemplatesByProviderCount($provider)
    {
        
        // verify the required parameter 'provider' is set
        if ($provider === null) {
            throw new \InvalidArgumentException('Missing the required parameter $provider when calling getClassifiedTemplatesByProviderCount');
        }
  
        // parse inputs
        $resourcePath = "/stats/{provider}";
        $resourcePath = str_replace("{format}", "json", $resourcePath);
        $method = "GET";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array());
  
        
        
        // path params
        if ($provider !== null) {
            $resourcePath = str_replace(
                "{" . "provider" . "}",
                $this->apiClient->getSerializer()->toPathValue($provider),
                $resourcePath
            );
        }
        
        
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } else if (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        
        // make the API Call
        try
        {
            list($response, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, $method,
                $queryParams, $httpBody,
                $headerParams, '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats'
            );
            
            if (!$response) {
                return null;
            }

            return $this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats', $httpHeader);
            
        } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
        
        return null;
        
    }
    
    /**
     * getClassifiedTemplatesOnWikiCount
     *
     * Returns number of classified templates on given wiki
     *
     * @param int $wiki_id Wikia ID (required)
     * @return \Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getClassifiedTemplatesOnWikiCount($wiki_id)
    {
        
        // verify the required parameter 'wiki_id' is set
        if ($wiki_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $wiki_id when calling getClassifiedTemplatesOnWikiCount');
        }
  
        // parse inputs
        $resourcePath = "/stats/{wiki_id }";
        $resourcePath = str_replace("{format}", "json", $resourcePath);
        $method = "GET";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array());
  
        
        
        // path params
        if ($wiki_id !== null) {
            $resourcePath = str_replace(
                "{" . "wiki_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($wiki_id),
                $resourcePath
            );
        }
        
        
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } else if (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        
        // make the API Call
        try
        {
            list($response, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, $method,
                $queryParams, $httpBody,
                $headerParams, '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats'
            );
            
            if (!$response) {
                return null;
            }

            return $this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats', $httpHeader);
            
        } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
        
        return null;
        
    }
    
    /**
     * getClassifiedTemplatesByProviderOnWikiCount
     *
     * Returns number of classified templates on given wiki by provider
     *
     * @param int $wiki_id Wikia ID (required)
     * @param string $provider Provider (required)
     * @return \Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getClassifiedTemplatesByProviderOnWikiCount($wiki_id, $provider)
    {
        
        // verify the required parameter 'wiki_id' is set
        if ($wiki_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $wiki_id when calling getClassifiedTemplatesByProviderOnWikiCount');
        }
        // verify the required parameter 'provider' is set
        if ($provider === null) {
            throw new \InvalidArgumentException('Missing the required parameter $provider when calling getClassifiedTemplatesByProviderOnWikiCount');
        }
  
        // parse inputs
        $resourcePath = "/stats/{wiki_id}/{provider}";
        $resourcePath = str_replace("{format}", "json", $resourcePath);
        $method = "GET";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array());
  
        
        
        // path params
        if ($wiki_id !== null) {
            $resourcePath = str_replace(
                "{" . "wiki_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($wiki_id),
                $resourcePath
            );
        }// path params
        if ($provider !== null) {
            $resourcePath = str_replace(
                "{" . "provider" . "}",
                $this->apiClient->getSerializer()->toPathValue($provider),
                $resourcePath
            );
        }
        
        
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } else if (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        
        // make the API Call
        try
        {
            list($response, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, $method,
                $queryParams, $httpBody,
                $headerParams, '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats'
            );
            
            if (!$response) {
                return null;
            }

            return $this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats', $httpHeader);
            
        } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\TemplateClassification\Storage\Models\TemplateTypeStats', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
        
        return null;
        
    }
    
}
