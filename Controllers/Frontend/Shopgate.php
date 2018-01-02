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
use Shopware\Components\CSRFWhitelistAware;

class Shopware_Controllers_Frontend_Shopgate extends Enlight_Controller_Action implements CSRFWhitelistAware
{
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
        try {
            /** @var ShopgateCloudApi\Components\ClientCredentials $credentials */
            /** @var ShopgateCloudApi\Components\Translators\Sdk $sdkTranslator */
            /** @var ShopgateCloudApi\Components\Translators\Shopware $shopwareTranslator */
            $credentials        = $this->container->get('shopgate_cloudapi.client_credentials');
            $token              = $this->container->get('shopgate_cloudapi.repo_sdk_token');
            $user               = $this->container->get('shopgate_cloudapi.repo_sdk_user');
            $sdkTranslator      = $this->container->get('shopgate_cloudapi.translator_sdk');
            $shopwareTranslator = $this->container->get('shopgate_cloudapi.translator_shopware');
        } catch (\Exception $exception) {
            $this->response->renderExceptions(true);
            $this->response->setException($exception)->sendResponse();
            exit;
        }

        /*$tokenType = new ShopgateSdk\ValueObject\TokenType\AccessToken();
        $tokenId   = $token->generateTokenId($tokenType);
        $saveToken = new ShopgateSdk\ValueObject\Token(
            $tokenType,
            $tokenId,
            $credentials->getClientId(),
            new ShopgateSdk\ValueObject\UserId(111),
            new ShopgateSdk\ValueObject\Base\BaseString(date('Y-m-d H:i:s'))
        );
        $token->saveToken($saveToken);
        $loadedToken = $token->loadToken($tokenId, $tokenType);*/
        $path = new \ShopgateCloudApi\Components\Path();
        try {
            $router   = new ShopgateSdk\Service\Router\Router($credentials, $token, $user, $path);
            $response = $router->dispatch($sdkTranslator->getRequest($this->request));
            $shopwareTranslator->populateResponse($this->response, $response);
            $this->response->sendResponse();
        } catch (Exception $e) {
            $this->response->renderExceptions(true);
            $this->response->setException($e)->sendResponse();
        }
        // bind "POST /carts" to "MageCreateCartHandler" handler class
        //        try {
        //            $router->subscribe(
        //                new ShopgateSdk\CartsRoute(),
        //                new ShopgateSdk\RequestMethodPost(),
        //                new MageCreateCartHandler(new MageRepository($mageDb, $this->sgConfig))
        //            );
        //        } catch (ShopgateSdk\Service\UriParser\Exception\InvalidRoute $e) {
        //
        //        }

        // This route would be something like "POST /carts/me" or "POST /carts/387"
        // $router->subscribe(
        // 	new ShopgateSdk\CartRoute(),
        // 	new ShopgateSdk\RequestMethodPost(),
        // 	new MageSaveCartHandler(new MageRepository($mageDb, $this->sgConfig))
        // );

        // This route would stand for "GET /carts"
        // $router->subscribe(
        // 	new ShopgateSdk\CartsRoute(),
        // 	new ShopgateSdk\RequestMethodGet(),
        // 	new MageGetCartHandler(new MageRepository($mageDb, $this->sgConfig))
        // );

        //        $router->dispatch(
        //            new ShopgateSdk\Request(
        //                $this->Request()->getRequestUri(),
        //                new ShopgateSdk\RequestMethod($this->Request()->getMethod()),
        //                $this->Request()->getHeaders(),
        //			    $this->Request()->getRawBody()
        //		));
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
