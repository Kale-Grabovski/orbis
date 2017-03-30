<?php

namespace App\Controllers\Responses;

/**
 * Interface ApiResponse
 * @package App\Controllers\Responses
 */
interface ApiResponse
{
    /**
     * Returns the response body in corresponding format
     * @param array $output
     * @return string
     */
    public function getBody(array $output) : string;
}
