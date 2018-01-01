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

use Shopgate\CloudIntegrationSdk\Repository\AbstractToken;
use Shopgate\CloudIntegrationSdk\ValueObject\Base\BaseString;
use Shopgate\CloudIntegrationSdk\ValueObject\ClientId;
use Shopgate\CloudIntegrationSdk\ValueObject\TokenId;
use Shopgate\CloudIntegrationSdk\ValueObject\TokenType\AbstractTokenType;
use Shopgate\CloudIntegrationSdk\ValueObject\UserId;
use ShopgateCloudApi\Models\Auth\AccessToken;
use ShopgateCloudApi\Models\Auth\RefreshToken;
use Shopware\Components\Model\ModelManager;

class Token extends AbstractToken
{
    /** @var ModelManager */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * Generates a TokenId of the given type that is unique for the system, where it's created in
     *
     * @param AbstractTokenType $type
     *
     * @return TokenId
     * @throws \InvalidArgumentException
     */
    public function generateTokenId(AbstractTokenType $type)
    {
        if (function_exists('random_bytes')) {
            $randomData = random_bytes(20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return new TokenId(bin2hex($randomData));
            }
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes(20, $strong);
            if ($randomData !== false && false === $strong && strlen($randomData) === 20) {
                return new TokenId(bin2hex($randomData));
            }
        }
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(MCRYPT_DEV_URANDOM, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return new TokenId(bin2hex($randomData));
            }
        }
        if (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return new TokenId(bin2hex($randomData));
            }
        }

        $hash = substr(hash('sha512', mt_rand(40, 100)), 0, 40);

        return new TokenId($hash);
    }

    /**
     * @param TokenId           $token
     * @param AbstractTokenType $type
     *
     * @return \Shopgate\CloudIntegrationSdk\ValueObject\Token | null Returns null only if there was no Token found or
     *                                                         it's expired
     *
     * @throws \Exception Throws a custom exception if trying to load the token fails for some reason
     */
    public function loadToken(TokenId $token, AbstractTokenType $type)
    {
        $class = $type->getValue() === AbstractTokenType::ACCESS_TOKEN ? AccessToken::class : RefreshToken::class;
        /** @var \Shopware\Components\Model\QueryBuilder $builder */
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select('access_token_db')
                ->from($class, 'access_token_db')
                ->where('access_token_db.accessToken = :accessToken')
                ->setParameter('accessToken', $token->getValue());

        /** @var AccessToken $returned */
        $returned = $builder->getQuery()->getSingleResult();

        if (!$returned->getAccessToken()) {
            return null;
        }

        $tokenId  = new TokenId($returned->getAccessToken());
        $clientId = new ClientId($returned->getClientId());
        $userId   = new UserId($returned->getUserId());
        /** @var \DateTime $dateTime */
        $dateTime = $returned->getExpires();
        $expires  = new BaseString($dateTime->format('Y-m-d H:i:s'));

        return new \Shopgate\CloudIntegrationSdk\ValueObject\Token($type, $tokenId, $clientId, $userId, $expires);
    }

    /**
     * @param UserId            $userId
     * @param AbstractTokenType $type
     *
     * @return \Shopgate\CloudIntegrationSdk\ValueObject\Token | null Returns null only if there was no Token found for
     *                                                         the given UserId
     *
     * @throws \Exception Throws a custom exception if trying to load the token fails for some reason
     */
    public function loadTokenByUserId($userId, AbstractTokenType $type)
    {
        // todo-sg: Implement loadTokenByUserId() method.
    }

    /**
     * Creates a new token in the data source or overwrites it, if the TokenId already exists
     *
     * @param \Shopgate\CloudIntegrationSdk\ValueObject\Token $tokenData
     *
     * @throws \Exception
     */
    public function saveToken(\Shopgate\CloudIntegrationSdk\ValueObject\Token $tokenData)
    {
        if ($tokenData->getType()->getValue() === AbstractTokenType::ACCESS_TOKEN) {
            $token = new AccessToken();
            $token->setAccessToken($tokenData->getTokenId()->getValue()); //todo-sg: refactor Doctrine to setToken()
        } else {
            $token = new RefreshToken();
            $token->setRefreshToken($tokenData->getTokenId()->getValue());
        }

        $token->setClientId($tokenData->getClientId()->getValue());
        if ($tokenData->getExpires()) {
            $token->setExpires($tokenData->getExpires()->getValue());
        }

        if ($tokenData->getUserId()) {
            $token->setUserId($tokenData->getUserId()->getValue()); //todo-sg: library problems?
        }

        if ($tokenData->getScope()) {
            $token->setScope($tokenData->getScope()->getValue());
        }

        /** @var \Shopware\Components\Model\QueryBuilder $builder */
        $this->modelManager->persist($token);
        $this->modelManager->flush($token);
        $this->modelManager->refresh($token);
    }
}
