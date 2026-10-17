<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hashids Salt
    |--------------------------------------------------------------------------
    |
    | This is the salt that will be used to generate the Hashids. It should
    | be a random string that is kept secret.
    |
    */

    'salt' => env('HASHIDS_SALT', 'your-secret-salt'),

    /*
    |--------------------------------------------------------------------------
    | Hashids Minimum Length
    |--------------------------------------------------------------------------
    |
    | This is the minimum length of the generated Hashids. If set to 0,
    | the Hashids will be as short as possible.
    |
    */

    'min_length' => env('HASHIDS_MIN_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | Hashids Alphabet
    |--------------------------------------------------------------------------
    |
    | This is the alphabet that will be used to generate the Hashids.
    | It must contain at least 16 unique characters.
    |
    */

    'alphabet' => env('HASHIDS_ALPHABET', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'),

];