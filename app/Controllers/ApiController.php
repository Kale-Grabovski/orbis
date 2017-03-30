<?php

namespace App\Controllers;

use App\Controllers\Responses\ApiResponse;
use App\Exceptions\ValidationException;
use App\Models\Impression;

/**
 * Class ApiController
 * @package App\Controllers
 * todo: implement another API methods
 */
class ApiController
{
    /**
     * @var ApiResponse
     */
    private $response;

    /**
     * @param ApiResponse $response
     */
    public function __construct(ApiResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Returns summary by passed user id and date range
     *
     * @return string
     */
    public function getByUserId() : string
    {
        $userId          = (int)$_GET['user_id'];
        $dateRange       = [$_GET['date_from'], $_GET['date_to']];
        $impressionStats = Impression::model()->getByUserId($userId, $dateRange);
        $conversionStats = [];
        // todo: implement Conversion model and make merge by hour
        $stats = $impressionStats + $conversionStats;

        return $this->response->getBody($stats);
    }

    /**
     * @throws ValidationException
     */
    public function create()
    {
        $userId = (int)$_POST['user_id'];

        if (!$userId) {
            throw new ValidationException('Invalid user_id param passed');
        }

        // todo: add to queue?
        Impression::model()->create($userId);
    }
}
