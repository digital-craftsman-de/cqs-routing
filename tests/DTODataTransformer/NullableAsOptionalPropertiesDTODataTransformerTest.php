<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTODataTransformer;

use DigitalCraftsman\CQRS\DTODataTransformer\Exception\UntypedProperty;
use DigitalCraftsman\CQRS\Test\Command\CreateUserCommand;
use DigitalCraftsman\CQRS\Test\Command\UntypedCreateUserCommand;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\CQRS\DTODataTransformer\NullableAsOptionalPropertiesDTODataTransformer */
final class NullableAsOptionalPropertiesDTODataTransformerTest extends TestCase
{
    /**
     * @test
     * @covers ::transformDTOData
     */
    public function nullable_as_optional_properties_dto_data_transformer_works(): void
    {
        // -- Arrange
        $dtoDataTransformer = new NullableAsOptionalPropertiesDTODataTransformer();

        $expectedId = 'e2547ccd-b832-4acb-a216-d3c9cd028ab3';
        $expectedEmailAddress = 'john.doe@example.com';
        $dtoData = [
            'id' => $expectedId,
            'emailAddress' => $expectedEmailAddress,
            // "name" DTO is missing
            // "registrationReference" string is missing
        ];

        // -- Act
        $dtoData = $dtoDataTransformer->transformDTOData(CreateUserCommand::class, $dtoData);

        // -- Assert
        self::assertSame($expectedId, $dtoData['id']);
        self::assertSame($expectedEmailAddress, $dtoData['emailAddress']);

        self::arrayHasKey('name');
        self::assertNull($dtoData['name']);

        self::arrayHasKey('registrationReference');
        self::assertNull($dtoData['registrationReference']);
    }

    /**
     * @test
     * @covers ::transformDTOData
     */
    public function nullable_as_optional_properties_dto_data_transformer_fails_on_untyped_properties_in_dto_class(): void
    {
        // -- Assert
        $this->expectException(UntypedProperty::class);

        // -- Arrange
        $dtoDataTransformer = new NullableAsOptionalPropertiesDTODataTransformer();

        // -- Act
        $dtoDataTransformer->transformDTOData(UntypedCreateUserCommand::class, []);
    }
}
