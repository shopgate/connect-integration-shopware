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

namespace ShopgateCloudApi\Models\Auth;

use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="ShopgateCloudApi\Repositories\Auth\RefreshToken")
 * @ORM\Table(name="sg_cloudapi_oauth2_refresh_token")
 */
class RefreshToken extends ModelEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=40, nullable=false)
     */
    public $refreshToken;

    /**
     * @ORM\Column(type="string", length=80, nullable=false)
     */
    public $clientId;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    public $userId;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $expires;

    /**
     * @ORM\Column(type="string", length=4000, nullable=true)
     */
    public $scope;

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }
}
