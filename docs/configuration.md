# Configuration

```yaml
cqrs:

  command_controller:

    # Default request decoder of command controller when there is none defined for the route
    default_request_decoder_class: 'DigitalCraftsman\CQRS\RequestDecoder\JsonRequestDecoder'

    # Default DTO constructor of command controller when there is none defined for the route
    default_dto_constructor_class: 'DigitalCraftsman\CQRS\DTOConstructor\SerializerDTOConstructor'

    # Default DTO validator of command controller when there is none defined for the route
    default_dto_validator_classes:
      - 'App\CQRS\DTOValidator\CourseIdValidator'
      - 'App\CQRS\DTOValidator\UserIdValidator'

    # Default wrapper handler of command controller when there is none defined for the route
    default_handler_wrapper_classes:
      - 'App\CQRS\HandlerWrapper\ConnectionTransactionWrapper'

    # Default response constructor of command controller when there is none defined for the route
    default_response_constructor_class: 'DigitalCraftsman\CQRS\ResponseConstructor\EmptyJsonResponseConstructor'

  query_controller:
    
    # Default request decoder of query controller when there is none defined for the route
    default_request_decoder_class: 'DigitalCraftsman\CQRS\RequestDecoder\JsonRequestDecoder'
    
    # Default DTO constructor of query controller when there is none defined for the route
    default_dto_constructor_class: 'DigitalCraftsman\CQRS\DTOConstructor\SerializerDTOConstructor'
    
    # Default DTO validator of query controller when there is none defined for the route
    default_dto_validator_classes:
      - 'App\CQRS\DTOValidator\CourseIdValidator'
      - 'App\CQRS\DTOValidator\UserIdValidator'

    # Default wrapper handler of query controller when there is none defined for the route
    default_handler_wrapper_classes:
      - 'App\CQRS\HandlerWrapper\ConnectionTransactionWrapper'
    
    # Default response constructor of query controller when there is none defined for the route
    default_response_constructor_class: 'DigitalCraftsman\CQRS\ResponseConstructor\SerializerJsonResponseConstructor'

  # Define the context that will be used by the JSON serializer
  # See https://symfony.com/doc/current/components/serializer.html#option-2-using-the-context
  serializer_context:
    skip_null_values: true
    preserve_empty_objects: true
```
