<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

use Shopgate\CloudIntegrationSdk as ShopgateSdk;
use Shopgate\CloudIntegrationSdk\Service\Router\Router;
use Shopware\Components\CSRFWhitelistAware;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Frontend_Shopgate extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /** @var ShopgateCloudApi\Components\ClientCredentials */
    private $credentials;
    /** @var ShopgateCloudApi\Repositories\Sdk\User */
    private $userRepo;
    /** @var ShopgateCloudApi\Components\Translators\Shopware */
    private $shopwareTranslator;
    /** @var ShopgateCloudApi\Components\Translators\Sdk */
    private $sdkTranslator;
    /** @var ShopgateCloudApi\Repositories\Sdk\Token */
    private $tokenRepo;

    /**
     * Registers properties
     *
     * @throws Exception
     */
    public function preDispatch()
    {
        $this->credentials        = $this->container->get('shopgate_cloudapi.client_credentials');
        $this->tokenRepo          = $this->container->get('shopgate_cloudapi.repo_sdk_token');
        $this->userRepo           = $this->container->get('shopgate_cloudapi.repo_sdk_user');
        $this->sdkTranslator      = $this->container->get('shopgate_cloudapi.translator_sdk');
        $this->shopwareTranslator = $this->container->get('shopgate_cloudapi.translator_shopware');
    }

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return ['v2'];
    }

    /**
     * Main entry point
     */
    public function v2Action()
    {
        $this->View()->setTemplate();

        $path = new \ShopgateCloudApi\Components\Path();
        try {
            $router = new Router($this->credentials, $this->tokenRepo, $this->userRepo, $path);
            $this->subscribeToRoutes($router);
            $sdkResponse = $router->dispatch($this->sdkTranslator->getRequest($this->request));
            $this->shopwareTranslator->populateResponse($this->response, $sdkResponse);
            $this->response->sendResponse();
        } catch (Exception $e) {
            $this->shopwareTranslator->populateException($this->response, $e);
            $this->response->sendResponse();
        }
    }

    /**
     * Subscribe to all possible routes
     *
     * @param Router $router
     * todo-sg: see how we can use the event system instead
     */
    private function subscribeToRoutes(Router $router)
    {
        try {
            $router->subscribe(
                new ShopgateSdk\ValueObject\Route\V2\Product(),
                new ShopgateSdk\ValueObject\RequestMethod\Get(),
                $this->container->get('shopgate_cloudapi.request_handler_get_products')
            );
        } catch (ShopgateSdk\Service\UriParser\Exception\InvalidRoute $e) {
            $this->response->setHttpResponseCode(Response::HTTP_BAD_REQUEST)
                           ->setBody($e->getMessage())
                           ->sendResponse();
        } catch (Exception $e) {
            $this->shopwareTranslator->populateException($this->response, $e);
            $this->response->sendResponse();
        }
    }

    /**
     * Since we are in the front controller now,
     * we need to avoid template printing and just
     * send response immediately.
     *
     * @todo-sg: see if switching to API controller is cleaner
     */
    public function postDispatch()
    {
        exit();
    }
}
