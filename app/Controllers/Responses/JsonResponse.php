<?php

namespace App\Controllers\Responses;

/**
 * Class JsonResponse
 * @package App\Controllers\Responses
 */
class JsonResponse implements ApiResponse
{
    /**
     * Returns the response body in JSON format
     * @param array $output
     * @return string
     */
    public function getBody(array $output) : string
    {
        return json_encode($output);
    }
}
