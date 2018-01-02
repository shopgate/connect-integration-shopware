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
use Shopware\Components\Password\Manager as PasswordManager;
use Shopware\Models\Customer\Customer;

class User extends AbstractUser
{

    /** @var ModelManager */
    private $modelManager;
    /** @var PasswordManager */
    private $passwordManager;

    /**
     * @param ModelManager    $modelManager
     * @param PasswordManager $passwordManager
     */
    public function __construct(ModelManager $modelManager, PasswordManager $passwordManager)
    {
        $this->modelManager    = $modelManager;
        $this->passwordManager = $passwordManager;
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
        /** @var \Shopware\Models\Customer\Repository $repo */
        $repo = $this->modelManager->getRepository(Customer::class);

        //todo-sg: Shop ID filter needed!
        /** @var \Shopware\Models\Customer\Customer $user */
        $user = $repo->getValidateEmailQueryBuilder((string) $login->getValue())->getQuery()->getOneOrNullResult();

        if (!$user) {
            return null;
        }

        if (!$this->passwordManager->isPasswordValid(
            (string) $password,
            $user->getPassword(),
            $user->getEncoderName()
        )) {
            return null;
        }

        return new UserId($user->getId());
    }
}
