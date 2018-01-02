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

/**
 * Helps translating SDK objects to Shopware
 *
 * @package ShopgateCloudApi\Components\Translators
 */
class Shopware
{
    /**
     * Translates Shopgate SDK response to the system's
     *
     * @param \Enlight_Controller_Response_Response $shopwareResponse - modified by reference
     * @param ShopgateSdk\ValueObject\Response      $response
     */
    public function populateResponse(
        \Enlight_Controller_Response_Response $shopwareResponse,
        ShopgateSdk\ValueObject\Response $response
    ) {
        $shopwareResponse->setBody($response->getBody());
        foreach ($response->getHeaders() as $key => $header) {
            $shopwareResponse->setHeader($key, $header);
        }
        $shopwareResponse->setHttpResponseCode($response->getCode());
    }
}
