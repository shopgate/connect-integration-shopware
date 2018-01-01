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

namespace ShopgateCloudApi\Components;

use Shopgate\CloudIntegrationSdk\Repository\AbstractClientCredentials;
use Shopgate\CloudIntegrationSdk\ValueObject\ClientId;

class ClientCredentials extends AbstractClientCredentials
{

    /** @var \Shopware_Components_Config */
    protected $config;

    public function __construct(\Shopware_Components_Config $config)
    {
        $this->config = $config;
    }

    /**
     * @todo-sg: customize per store retrieval
     *
     * @return ClientId
     * @throws \Exception
     */
    public function getClientId()
    {
        $shopNumber     = $this->config->getByNamespace('shopgatecloudapi', 'shopgate-cloudapi-shop-number');
        $customerNumber = $this->config->getByNamespace('shopgatecloudapi', 'shopgate-cloudapi-customer-number');

        if (!$shopNumber || !$customerNumber) {
            //todo-sg: customize exceptions
            throw new \Exception('Having issue retrieving client_id');
        }

        return new ClientId($shopNumber . '-' . $customerNumber);
    }

    /**
     * @todo-sg: customize per store retrieval
     *
     * @return string
     * @throws \Exception
     */
    public function getClientSecret()
    {
        //
        $api = $this->config->getByNamespace('shopgatecloudapi', 'shopgate-cloudapi-api-key');

        if (!$api) {
            //todo-sg: customize exceptions
            throw new \Exception('Unable to retrieve ');
        }

        return $api;
    }
}
