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

namespace ShopgateCloudApi;


use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Plugin;
use ShopgateCloudApi\Models\Auth;

class ShopgateCloudApi extends Plugin
{

    /**
     * Adds the widget to the database and creates the database schema.
     *
     * @param Plugin\Context\InstallContext $installContext
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function install(Plugin\Context\InstallContext $installContext)
    {
        parent::install($installContext);

        $this->createSchema();
    }

    /**
     * Remove widget and remove database schema.
     *
     * @param Plugin\Context\UninstallContext $uninstallContext
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function uninstall(Plugin\Context\UninstallContext $uninstallContext)
    {
        parent::uninstall($uninstallContext);

        $this->removeSchema();
    }

    /**
     * Creates database tables on base of doctrine models
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    private function createSchema()
    {
        $em      = $this->container->get('models');
        $tool    = new SchemaTool($em);
        $classes = [
            $em->getClassMetadata(Auth\AccessToken::class),
            $em->getClassMetadata(Auth\RefreshToken::class),
            $em->getClassMetadata(Auth\Client::class),
        ];
        $tool->createSchema($classes);
    }

    /**
     * Removes database tables on base of doctrine models
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    private function removeSchema()
    {
        $em   = $this->container->get('models');
        $tool = new SchemaTool($em);
        $classes = [
            $em->getClassMetadata(Auth\AccessToken::class),
            $em->getClassMetadata(Auth\RefreshToken::class),
            $em->getClassMetadata(Auth\Client::class),
        ];
        $tool->dropSchema($classes);
    }
}
