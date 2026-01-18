# Configuration

Every option in the configuration is optional. Not defining a default for request decoder, DTO constructor or response constructor will lead to an exception when there is no component defined in the route.

```php
<?php

declare(strict_types=1);

use App\Application\CQSRouting\DTOValidator\AccessValidator;
use App\Application\CQSRouting\DTOValidator\DTO\AccessValidatorParameters;
use App\Application\CQSRouting\HandlerWrapper\ConnectionTransactionWrapper;
use DigitalCraftsman\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyJsonResponseConstructor;
use DigitalCraftsman\CQSRouting\ResponseConstructor\SerializerJsonResponseConstructor;
use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'cqs_routing' => [
        'query' => [
            'default_request_validator_classes' => [
                GuardAgainstTokenInHeaderRequestValidator::class => null,
            ],
            'default_request_decoder_class' => JsonRequestDecoder::class,
            'default_request_data_transformer_classes' => [
                AddActionIdRequestDataTransformer::class => null,
            ],
            'default_dto_constructor_class' => SerializerDTOConstructor::class,
            'default_dto_validator_classes' => [
                CourseIdValidator::class => null,
                UserIdValidator::class => null,
            ],
            'default_handler_wrapper_classes' => [
                ConnectionTransactionWrapper::class => null,
            ],
            'default_response_constructor_class' => SerializerJsonResponseConstructor::class,
        ],
        'command' => [
            'default_request_validator_classes' => [
                GuardAgainstTokenInHeaderRequestValidator::class => null,
            ],
            'default_request_decoder_class' => JsonRequestDecoder::class,
            'default_request_data_transformer_classes' => [
                AddActionIdRequestDataTransformer::class => null,
            ],
            'default_dto_constructor_class' => SerializerDTOConstructor::class,
            'default_dto_validator_classes' => [
                CourseIdValidator::class => null,
                UserIdValidator::class => null,
            ],
            'default_handler_wrapper_classes' => [
                ConnectionTransactionWrapper::class => null,
            ],
            'default_response_constructor_class' => EmptyJsonResponseConstructor::class,
        ],
    ],
]);
```

Or if your configuration still uses yaml, it looks like this:

```yaml
cqs_routing:

  command:

    # Classes of the default request validator of command controller when there is none defined for the route
    default_request_validator_classes:
      'App\CQSRouting\RequestValidator\GuardAgainstTokenInHeaderRequestValidator': null

    # Class of the default request decoder of command controller when there is none defined for the route
    default_request_decoder_class: 'DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder'

    # Classes of the default request data transformer of command controller when there is none defined for the route
    default_request_data_transformer_classes:
      'App\CQSRouting\RequestDataTransformer\AddActionIdRequestDataTransformer': null
    
    # Class of the default DTO constructor of command controller when there is none defined for the route
    default_dto_constructor_class: 'DigitalCraftsman\CQSRouting\DTOConstructor\SerializerDTOConstructor'

    # Classes of the default DTO validator of command controller when there is none defined for the route
    default_dto_validator_classes:
      'App\CQSRouting\DTOValidator\CourseIdValidator': null
      'App\CQSRouting\DTOValidator\UserIdValidator': null

    # Classes of the default wrapper handler of command controller when there is none defined for the route
    default_handler_wrapper_classes:
      'App\CQSRouting\HandlerWrapper\ConnectionTransactionWrapper': null

    # Class of the default response constructor of command controller when there is none defined for the route
    default_response_constructor_class: 'DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyJsonResponseConstructor'

  query:

    # Classes of the default request validator of query controller when there is none defined for the route
    default_request_validator_classes:
      'App\CQSRouting\RequestValidator\GuardAgainstTokenInHeaderRequestValidator': null
    
    # Class of the default request decoder of query controller when there is none defined for the route
    default_request_decoder_class: 'DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder'

    # Classes of the default request data transformer of query controller when there is none defined for the route
    default_request_data_transformer_classes:
      'App\CQSRouting\RequestDataTransformer\AddActionIdRequestDataTransformer': null
    
    # Class of the default DTO constructor of query controller when there is none defined for the route
    default_dto_constructor_class: 'DigitalCraftsman\CQSRouting\DTOConstructor\SerializerDTOConstructor'
    
    # Classes of the default DTO validator of query controller when there is none defined for the route
    default_dto_validator_classes:
      'App\CQSRouting\DTOValidator\CourseIdValidator': null
      'App\CQSRouting\DTOValidator\UserIdValidator': null

    # Classes of the default wrapper handler of query controller when there is none defined for the route
    default_handler_wrapper_classes:
      'App\CQSRouting\HandlerWrapper\ConnectionTransactionWrapper': null
    
    # Class of the default response constructor of query controller when there is none defined for the route
    default_response_constructor_class: 'DigitalCraftsman\CQSRouting\ResponseConstructor\SerializerJsonResponseConstructor'
```
