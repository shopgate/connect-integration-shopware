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

namespace ShopgateCloudApi\Components\Translators;

use Shopgate\CloudIntegrationSdk as ShopgateSdk;
use Shopgate\CloudIntegrationSdk\ValueObject;
use ShopgateCloudApi\Models\Auth\AccessToken;
use ShopgateCloudApi\Models\Auth\RefreshToken;

/**
 * Helps translating Shopware objects to SDK
 *
 * @package ShopgateCloudApi\Components\Translators
 */
class Sdk
{
    protected static $headerTypes = ['Content-Type', 'Accept', 'Authorization'];

    /**
     * Retrieve SDK request using Shopware request
     *
     * @param \Enlight_Controller_Request_Request $request
     *
     * @return ValueObject\Request\Request
     * @throws \Exception
     */
    public function getRequest(\Enlight_Controller_Request_Request $request)
    {
        $uri     = $request->getRequestUri();
        $method  = $request->getMethod();
        $headers = [];
        foreach (self::$headerTypes as $headerType) {
            $header = $request->getHeader($headerType);
            if (!empty($header)) {
                $headers[$headerType] = $header;
            }
        }

        return new ValueObject\Request\Request($uri, $method, $headers, $request->getRawBody());
    }

    /**
     * Translate Shopware token into SDK token
     *
     * @param AccessToken | RefreshToken              $token
     * @param ValueObject\TokenType\AbstractTokenType $type
     *
     * @return ShopgateSdk\ValueObject\Token
     * @throws \InvalidArgumentException
     */
    public function getToken($token, ValueObject\TokenType\AbstractTokenType $type)
    {
        $tokenId  = new ValueObject\TokenId($token->getToken());
        $clientId = new ValueObject\ClientId($token->getClientId());
        $userId   = new ValueObject\UserId($token->getUserId());
        /** @var \DateTime $dateTime */
        $dateTime = $token->getExpires();
        $expires  = new ValueObject\Base\BaseString($dateTime->format('Y-m-d H:i:s'));

        return new ValueObject\Token($type, $tokenId, $clientId, $userId, $expires);
    }
}
