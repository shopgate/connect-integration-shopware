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

class Shopware_Controllers_Frontend_Shopgate extends Enlight_Controller_Action
{

    public function indexAction()
    {
        $this->View()->setTemplate();
        die('hello');
    }

    private $sgConfig;

    public function cartsAction()
    {
        $this->View()->setTemplate();
        try {
            /** @var \ShopgateCloudApi\Components\ClientCredentials $credentials */
            $credentials = $this->container->get('shopgate_cloudapi.client_credentials');
        } catch (\Exception $exception) {
            //todo-sg: throw API exception
        }

        $db = $this->getModelManager()->getConnection();
        /** @var \ShopgateCloudApi\Repositories\Sdk\Token $token */
        $token = $this->container->get('shopgate_cloudapi.repo_sdk_token');

        $tokenType = new ShopgateSdk\ValueObject\TokenType\AccessToken();
        $tokenId   = $token->generateTokenId($tokenType);
        $saveToken = new ShopgateSdk\ValueObject\Token(
            $tokenType,
            $tokenId,
            $credentials->getClientId(),
            new ShopgateSdk\ValueObject\UserId(111),
            new ShopgateSdk\ValueObject\Base\BaseString(date('Y-m-d H:i:s'))
        );
        $token->saveToken($saveToken);
        $loadedToken = $token->loadToken($tokenId, $tokenType);
        $r           = new ShopgateSdk\Service\Router\Router($credentials, $token);

        // bind "POST /carts" to "MageCreateCartHandler" handler class
        //        try {
        //            $r->subscribe(
        //                new ShopgateSdk\CartsRoute(),
        //                new ShopgateSdk\RequestMethodPost(),
        //                new MageCreateCartHandler(new MageRepository($mageDb, $this->sgConfig))
        //            );
        //        } catch (ShopgateSdk\Service\UriParser\Exception\InvalidRoute $e) {
        //
        //        }

        // This route would be something like "POST /carts/me" or "POST /carts/387"
        // $r->subscribe(
        // 	new ShopgateSdk\CartRoute(),
        // 	new ShopgateSdk\RequestMethodPost(),
        // 	new MageSaveCartHandler(new MageRepository($mageDb, $this->sgConfig))
        // );

        // This route would stand for "GET /carts"
        // $r->subscribe(
        // 	new ShopgateSdk\CartsRoute(),
        // 	new ShopgateSdk\RequestMethodGet(),
        // 	new MageGetCartHandler(new MageRepository($mageDb, $this->sgConfig))
        // );

        //        $r->dispatch(
        //            new ShopgateSdk\Request(
        //                $this->Request()->getRequestUri(),
        //                new ShopgateSdk\RequestMethod($this->Request()->getMethod()),
        //                $this->Request()->getHeaders(),
        //			    $this->Request()->getRawBody()
        //		));
    }

    public function preDispatch()
    {
        // load shopgate config
        $this->sgConfig['client_id']     = '1234';
        $this->sgConfig['client_secret'] = 'abcd4321';

        // more init stuff
    }

    public function postDispatch()
    {
        // cleanup stuff
    }
}
