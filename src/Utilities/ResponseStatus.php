<?php

namespace MediciVN\Core\Utilities;

use MediciVN\Core\Exceptions\MediciException;

class ResponseStatus
{
	/**
	 * HTTP Response status code and internal app response status code
	 *
	 * 2xx - Successful responses
	 * 4xx - Client error responses
	 * 3xx - Redirection messages
	 * 5xx - Server error responses
	 *
	 * The pattern of status code constant looks like "400|1001". 400 means the server cannot or will not process the request due to something that is perceived
	 * to be a client error. This part of our status code for HTTP response code. 1001 is for internal error of the application. It has specific meaning which
	 * depend on developer.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
	 */

	/*
    |--------------------------------------------------------------------------
    | General server or network issues
    |--------------------------------------------------------------------------
    */

	// Internal error. unable to process your request. Please try again.
	const INTERNAL_ERROR = '500|1000';

	/*
    |--------------------------------------------------------------------------
    | General client request issues
    |--------------------------------------------------------------------------
    */
	const BAD_REQUEST = '400|2000';

	// You are not authorized to execute this request.
	const UNAUTHORIZED = '401|2001';

	// The specified URL cannot be found
	const ROUTE_NOT_DEFINED = '404|2000';

	// The request method is known by the server but is not supported by the target resource
	const METHOD_NOT_ALLOWED = '405|2001';

	// Timeout waiting for response from backend server. Send status unknown; execution status unknown
	const REQUEST_TIMEOUT = '408|2008';

	// The user has sent too many requests in a given amount of time
	const TOO_MANY_REQUESTS = '429|2002';

	const INVALID_PARAMETERS  = '400|2004';

    // Validation failed
    const UNPROCESSABLE_ENTITY = '422|2005';

	/*
    |--------------------------------------------------------------------------
    | General application issues
    |--------------------------------------------------------------------------
    */
	const INVALID_STATUS_CODE = '500|3000';
	const STATUS_CODE_MESSAGE_EMPTY = '500|3001';

    /**
     * @TODO message key should not duplicated
     * @return array
     */
    public static function getMessages(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | General server or network messages
            |--------------------------------------------------------------------------
            */
            self::INTERNAL_ERROR => __('exceptions.internal_error'),

            /*
            |--------------------------------------------------------------------------
            | General application messages
            |--------------------------------------------------------------------------
            */
            // Error code has incorrect pattern.
            self::INVALID_STATUS_CODE => __('exceptions.invalid_status_code'),

            /*
            |--------------------------------------------------------------------------
            | General client request messages
            |--------------------------------------------------------------------------
            */
            self::UNAUTHORIZED => __('exceptions.unauthorized'),
            self::BAD_REQUEST => __('exceptions.bad_request'),
            self::ROUTE_NOT_DEFINED => __('exceptions.route_not_defined'),
            self::METHOD_NOT_ALLOWED => __('exceptions.method_not_allowed'),
            self::TOO_MANY_REQUESTS => __('exceptions.too_many_request'),
            self::INVALID_PARAMETERS => __('exceptions.invalid_parameters'),
            self::REQUEST_TIMEOUT => __('exceptions.request_timeout'),
        ];
    }

   	/**
   	 * Get the message of status code
   	 *
   	 * @param  string $code
   	 * @return string
   	 *
   	 * @throws MediciException
   	 */
    public static function messagesBag(string $code): string
    {
    	if (array_key_exists($code, self::getMessages())) {
    		return self::getMessages()[$code];
    	}

    	throw new MediciException(self::STATUS_CODE_MESSAGE_EMPTY, 'Status code ' . $code . ' does not have message.');
    }
}
