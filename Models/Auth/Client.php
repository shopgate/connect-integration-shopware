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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity(repositoryClass="ShopgateCloudApi\Repositories\Auth\Client")
 * @ORM\Table(name="sg_cloudapi_oauth2_client")
 */
class Client extends ModelEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="client_id", type="string", length=80, nullable=false)
     */
    public $clientId;

    /**
     * @ORM\Column(name="client_secret", type="string", length=80, nullable=true)
     */
    public $clientSecret;

    /**
     * @ORM\Column(name="redirect_uri", type="string", length=2000, nullable=true)
     */
    public $redirectUri;

    /**
     * @ORM\Column(name="grant_types", type="string", length=80, nullable=true)
     */
    public $grantTypes;

    /**
     * @ORM\Column(name="scope", type="string", length=4000, nullable=true)
     */
    public $scope;

    /**
     * @ORM\Column(name="user_id", type="string", length=80, nullable=true)
     */
    public $userId;

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
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @return string
     */
    public function getGrantTypes()
    {
        return $this->grantTypes;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param string $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @param string $grantTypes
     */
    public function setGrantTypes($grantTypes)
    {
        $this->grantTypes = $grantTypes;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
}
