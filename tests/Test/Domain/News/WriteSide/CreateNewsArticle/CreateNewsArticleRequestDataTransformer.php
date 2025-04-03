<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQSRouting\RequestDataTransformer\RequestDataTransformer;

final readonly class CreateNewsArticleRequestDataTransformer implements RequestDataTransformer
{
    /**
     * @param class-string $dtoClass
     * @param array{
     *   content: string,
     * } $requestData
     * @param null $parameters
     */
    #[\Override]
    public function transformRequestData(string $dtoClass, array $requestData, mixed $parameters): array
    {
        $requestData['content'] = strip_tags($requestData['content'], '<p><br><strong>');

        return $requestData;
    }

    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
