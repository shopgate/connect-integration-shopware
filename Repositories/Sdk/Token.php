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
use Shopgate\CloudIntegrationSdk\ValueObject;
use Shopgate\CloudIntegrationSdk\ValueObject\TokenType\AbstractTokenType;
use ShopgateCloudApi\Models\Auth\AccessToken;
use ShopgateCloudApi\Models\Auth\RefreshToken;
use Shopware\Components\Model\ModelManager;

class Token extends AbstractToken
{
    /** @var \ShopgateCloudApi\Components\Translators\Sdk */
    protected $translator;
    /** @var ModelManager */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     *
     * @throws \Exception
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        //todo-sg: inject container class
        $this->translator = Shopware()->Container()->get('shopgate_cloudapi.translator_sdk');
    }

    /**
     * @inheritdoc
     */
    public function generateTokenId(AbstractTokenType $type)
    {
        if (function_exists('random_bytes')) {
            $randomData = random_bytes(20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return new ValueObject\TokenId(bin2hex($randomData));
            }
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes(20, $strong);
            if ($randomData !== false && false === $strong && strlen($randomData) === 20) {
                return new ValueObject\TokenId(bin2hex($randomData));
            }
        }
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(MCRYPT_DEV_URANDOM, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return new ValueObject\TokenId(bin2hex($randomData));
            }
        }
        if (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return new ValueObject\TokenId(bin2hex($randomData));
            }
        }

        $hash = substr(hash('sha512', mt_rand(40, 100)), 0, 40);

        return new ValueObject\TokenId($hash);
    }

    /**
     * @inheritdoc
     */
    public function loadToken(ValueObject\TokenId $token, AbstractTokenType $type)
    {
        $returned = $this->getTokenByParameters(['token' => $token->getValue()], $type->getValue());

        if (!$returned->getToken()) {
            return null;
        }

        return $this->translator->getToken($returned, $type);
    }

    /**
     * @inheritdoc
     */
    public function loadTokenByUserId($userId, AbstractTokenType $type)
    {
        $returned = $this->getTokenByParameters(['user_id' => $userId], $type->getValue());

        if (!$returned->getToken()) {
            return null;
        }

        return $this->translator->getToken($returned, $type);
    }

    /**
     * @inheritdoc
     */
    public function saveToken(ValueObject\Token $tokenData)
    {
        $token = $tokenData->getType()->getValue() === AbstractTokenType::ACCESS_TOKEN
            ? new AccessToken()
            : new RefreshToken();
        $token->setToken($tokenData->getTokenId()->getValue());
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
