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
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * Populates a Shopware response using the Exception data
     *
     * @todo-sg: set to produce JSON error
     *
     * @param \Enlight_Controller_Response_Response $shopwareResponse
     * @param \Exception                            $e
     */
    public function populateException(\Enlight_Controller_Response_Response $shopwareResponse, \Exception $e)
    {
        $code = $this->isHttpCodeInvalid((int) $e->getCode()) ? Response::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
        $shopwareResponse->renderExceptions(true);
        $shopwareResponse->setHttpResponseCode($code)
                         ->setException($e)
                         ->setBody($e->getMessage());
    }

    /**
     * Checks if the HTTP code is valid or not
     *
     * @param int $code
     *
     * @return bool
     */
    private function isHttpCodeInvalid($code)
    {
        return $code < 100 || $code >= 600;
    }
}
