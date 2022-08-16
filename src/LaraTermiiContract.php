<?php

namespace Zeevx\LaraTermii;

interface LaraTermiiContract
{
    public const STATUS = [
        200 => [
            'success' => true,
            'message' => 'OK: Request was successful.'
        ],
        400 => [
            'success' => false,
            'message' => 'Bad Request: Indicates that the server cannot or will not process the request due to something that is perceived to be a client error'
        ],
        401 => [
            'success' => false,
            'message' => 'Unauthorized: No valid API key provided'
        ],
        403 => [
            'success' => false,
            'message' => 'Forbidden: The API key doesn\'t have permissions to perform the request.'
        ],
        404 => [
            'success' => false,
            'message' => 'Not Found: The requested resource doesn\'t exist.'
        ],
        405 => [
            'success' => false,
            'message' => 'Method Not allowed: The selected http method is not allowed'
        ],
        422 => [
            'success' => false,
            'message' => 'Unprocessable entity: indicates that the server understands the content type of the request entity, and the syntax of the request entity is correct, but it was unable to process the contained instructions'
        ],
        429 => [
            'success' => false,
            'message' => 'Too Many Requests: Indicates the user has sent too many requests in a given amount of time'
        ]
    ];

    public const ERROR_RESPONSE = [
        'success' => false,
        'message' => 'Server Errors: Something went wrong on Termii\'s end OR status was not returned'
    ];
}