<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQRS\DTODataTransformer\DTODataTransformerInterface;

final class CreateNewsArticleDTODataTransformer implements DTODataTransformerInterface
{
    /**
     * @param class-string $dtoClass
     * @param array{
     *   content: string,
     * } $dtoData
     */
    public function transformDTOData(string $dtoClass, array $dtoData): array
    {
        $dtoData['content'] = strip_tags($dtoData['content'], '<p><br><strong>');

        return $dtoData;
    }
}
