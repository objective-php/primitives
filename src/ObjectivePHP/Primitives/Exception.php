<?php

    namespace ObjectivePHP\Primitives;

    class Exception extends \Exception
    {
        // common
        const INVALID_PARAMETER = 0x01;
        const INVALID_CALLBACK = 0x02;
        const INVALID_REGEXP = 0x03;

        // Collections
        const COLLECTION_TYPE_IS_INVALID = 0x10;
        const COLLECTION_VALUE_DOES_NOT_MATCH_TYPE = 0x12;
        const COLLECTION_FORBIDDEN_KEY = 0x13;
    }