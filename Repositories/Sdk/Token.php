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
        //@todo-sg: adjust naming in Doctrine
        $returned = $this->getTokenByParameters(['accessToken' => $token->getValue()], $type->getValue());

        //@todo-sg: adjust naming in Doctrine
        if (!$returned->getAccessToken()) {
            return null;
        }

        //todo-sg: inject container class
        /** @var \ShopgateCloudApi\Components\Translators\Sdk $sdk */
        $sdk = Shopware()->Container()->get('shopgate_cloudapi.translator_sdk');

        return $sdk->getToken($returned, $type);
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

    /**
     * Provide an array of parameters for the WHERE clause
     *
     * @param array  $params - list of params, e.g. 'access_token' => '1235'
     * @param string $type   - type of token, either refresh or access
     *
     * @return AccessToken | RefreshToken
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTokenByParameters(array $params = array(), $type = AbstractTokenType::ACCESS_TOKEN)
    {
        $class = $type === AbstractTokenType::ACCESS_TOKEN ? AccessToken::class : RefreshToken::class;
        /** @var \Shopware\Components\Model\QueryBuilder $builder */
        $builder = $this->modelManager->createQueryBuilder();
        $and     = $builder->expr()->andX();

        foreach ($params as $key => $value) {
            $and->add($builder->expr()->eq('token_db.' . $key, ':' . $key));
        }
        $builder->select('token_db')
                ->from($class, 'token_db')
                ->where($and)
                ->setParameters($params);

        return $builder->getQuery()->getSingleResult();
    }
}
