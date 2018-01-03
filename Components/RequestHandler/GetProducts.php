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

namespace ShopgateCloudApi\Components\RequestHandler;

use Shopgate\CloudIntegrationSdk\Repository;
use Shopgate\CloudIntegrationSdk\Service\Authenticator;
use Shopgate\CloudIntegrationSdk\Service\RequestHandler\RequestHandlerInterface;
use Shopgate\CloudIntegrationSdk\ValueObject\Request;
use Shopgate\CloudIntegrationSdk\ValueObject\Response;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;

class GetProducts implements RequestHandlerInterface
{
    /** @var Authenticator\TokenRequest */
    private $authenticator;
    /** @var ModelManager */
    private $modelManager;

    /**
     * @param Repository\AbstractToken $tokenRepository
     * @param ModelManager             $modelManager
     */
    public function __construct(Repository\AbstractToken $tokenRepository, ModelManager $modelManager)
    {
        $this->authenticator = new Authenticator\ResourceAccess($tokenRepository);
        $this->modelManager  = $modelManager;
    }

    /**
     * @inheritdoc
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }

    /**
     * @param Request\Request $request
     * @param string[]        $uriParams
     *
     * @return Response
     * @throws \Exception
     */
    public function handle(Request\Request $request, $uriParams)
    {
        /** @var Article $product */
        //todo-sg: move to a helper/translator
        $product = $this->modelManager->getRepository(Article::class)->find($uriParams['productId']);
        if (!$product) {
            //todo-sg: customize exceptions
            throw new \Exception('Product not found');
        }
        $responseBody    = json_encode(
            [
                'id'               => $product->getId(),
                'name'             => $product->getName(),
                'active'           => $product->getActive(),
                'description'      => $product->getDescription(),
                'description_long' => $product->getDescriptionLong()
            ]
        );
        $responseHeaders = array(
            'Content-Type'     => 'application/json; charset=utf-8',
            'Cache-Control'    => 'no-cache',
            'Content-Language' => 'en',
            'Content-Length'   => (string) strlen($responseBody)
        );

        return new Response(200, $responseHeaders, $responseBody);
    }
}
