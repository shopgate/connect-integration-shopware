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

namespace ShopgateCloudApi\Subscriber;

use Enlight\Event\SubscriberInterface;
use ShopgateCloudApi\ShopgateCloudApi;

class EndpointSubscriber implements SubscriberInterface
{

    /** @var \Enlight_Loader */
    private $loader;

    /**
     * EndpointSubscriber constructor.
     *
     * @param \Enlight_Loader $loader
     */
    public function __construct(\Enlight_Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Front_StartDispatch' => 'onRegisterSubscriber'
        ];
    }

    /**
     * Load significant methods
     *
     * @throws \Exception
     */
    public function onRegisterSubscriber()
    {
        $this->registerNamespaces();
    }

    /**
     * Namespace registration
     *
     * @throws \Exception
     */
    private function registerNamespaces()
    {
        $this->loader->registerNamespace(
            'ShopgateCloudApi',
            $this->getPath()
        );
        $this->loader->registerNamespace(
            'Shopgate\CloudIntegrationSdk',
            $this->getPath() . 'vendor/shopgate/cloud-integration-sdk/src/'
        );
    }

    /**
     * Returns a path to the current plugin's directory
     *
     * @return string
     * @throws \Exception
     */
    private function getPath()
    {
        /** @var ShopgateCloudApi $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['ShopgateCloudApi'];

        return $plugin->getPath() . DIRECTORY_SEPARATOR;
    }
}
