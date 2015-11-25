<?php

    namespace ObjectivePHP\Primitives;

    class Exception extends \Exception
    {
        // common
        const INVALID_PARAMETER = 0x01;
        const INVALID_CALLBACK = 0x02;
        const INVALID_REGEXP   = 0x03;

        // Collections
        const COLLECTION_INVALID_TYPE  = 0x10;
        const COLLECTION_FORBIDDEN_VALUE = 0x12;
        const COLLECTION_FORBIDDEN_KEY = 0x13;

        // Strings

        // Number

        // Normalizers
        const NORMALIZER_INCOMPATIBLE_CLASS = 0x40;
        const NORMALIZER_INVALID_CLASS = 0x41;
    }