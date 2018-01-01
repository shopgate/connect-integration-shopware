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

namespace ShopgateCloudApi\Repositories\Sdk;

use Shopgate\CloudIntegrationSdk\Repository\AbstractUser;
use Shopgate\CloudIntegrationSdk\ValueObject\Password;
use Shopgate\CloudIntegrationSdk\ValueObject\UserId;
use Shopgate\CloudIntegrationSdk\ValueObject\Username;
use Shopware\Components\Model\ModelManager;

class User extends AbstractUser
{

    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param Username $login
     * @param Password $password
     *
     * @return UserId | null Returns null only if the credentials are wrong or no user exists for them
     *
     * @throws \Exception Throws a custom exception if accessing the data source fails
     */
    public function getUserIdByCredentials(Username $login, Password $password)
    {
        /** @var \Shopware\Models\User\Repository $repo */
        $repo = $this->modelManager->getRepository(\Shopware\Models\User\User::class);

        //todo-sg: possible website filter needed?
        /** @var \Shopware\Models\User\User $user */
        $user = $repo->getUsersQuery(['email' => $login->getValue(), 'password' => $password->getValue()])
                     ->getSingleResult();

        return $user ? new UserId($user->getId()) : null;
    }
}
