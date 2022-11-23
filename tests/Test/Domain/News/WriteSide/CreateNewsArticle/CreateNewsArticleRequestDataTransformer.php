<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQRS\RequestDataTransformer\RequestDataTransformerInterface;

final class CreateNewsArticleRequestDataTransformer implements RequestDataTransformerInterface
{
    /**
     * @param class-string $dtoClass
     * @param array{
     *   content: string,
     * } $requestData
     * @param null $parameters
     */
    public function transformRequestData(string $dtoClass, array $requestData, mixed $parameters): array
    {
        $requestData['content'] = strip_tags($requestData['content'], '<p><br><strong>');

        return $requestData;
    }
}
