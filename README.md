## Working with CQRS in Symfony

This bundle contains all necessary interfaces to work with CQRS as described in the [Craftsman Compendium](https://www.digital-craftsman.de/craftsman-compendium/cqrs-construct).

Setup:

```
composer require digital-craftsman/cqrs
```

Add the following `cqrs.yaml` file to your `config/packages` and replace it with your instances:

```yaml
cqrs:

  query_controller:
    default_request_decoder_class: 'App\CQRS\RequestDecoder\JsonRequestDecoder'
    default_dto_constructor_class: 'App\CQRS\DTOConstructor\SerializerDTOConstructor'
    default_dto_validator_classes:
      - 'App\Application\CourseMapping\DTOValidator\CourseIdValidator'
      - 'App\CQRS\DTOValidator\UserIdValidator'
    default_response_constructor_class: 'App\CQRS\ResponseConstructor\JsonResponseConstructor'

  command_controller:
    default_request_decoder_class: 'App\CQRS\RequestDecoder\JsonRequestDecoder'
    default_dto_constructor_class: 'App\CQRS\DTOConstructor\SerializerDTOConstructor'
    default_dto_validator_classes:
      - 'App\Application\CourseMapping\DTOValidator\CourseIdValidator'
      - 'App\CQRS\DTOValidator\UserIdValidator'
    default_handler_wrapper_classes:
      - 'App\CQRS\HandlerWrapper\ConnectionTransactionWrapper'
    default_response_constructor_class: 'App\CQRS\ResponseConstructor\EmptyJsonResponseConstructor'
```
