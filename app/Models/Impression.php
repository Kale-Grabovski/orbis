<?php

namespace App\Models;

/**
 * Class Impression
 * @package App\Models
 * todo: implement another API methods
 */
class Impression extends BaseModel
{
    /**
     * @var string
     */
    protected $tableName = 'impressions';

    /**
     * Returns summary of impressions grouped by hour filtered with passed date range and user ID
     *
     * @param int $userId      User ID
     * @param array $dateRange Date range in timestamp format
     * @return array
     */
    public function getByUserId(int $userId, array $dateRange) : array
    {
        $query = "
            SELECT
                date_trunc('hour', created_at) AS hour,
                COUNT(*)                       AS impressions
            FROM $this->tableName
            WHERE user_id = :userId AND created_at BETWEEN :dateFrom AND :dateTo
            GROUP BY date_trunc('hour', created_at)
            ORDER BY hour
        ";

        $placeholders = [
            ':userId'   => $userId,
            ':dateFrom' => date('Y-m-d 00:00:00', strtotime($dateRange[0])),
            ':dateTo'   => date('Y-m-d 23:59:59', strtotime($dateRange[1])),
        ];

        $ret = [];
        foreach ($this->select($query, $placeholders) as $impression) {
            $ret[$impression['hour']] = $impression;
        }

        return $ret;
    }

    /**
     * Adds an impression with passed user ID
     *
     * @param int $userId
     */
    public function create(int $userId)
    {
        $query = "
            INSERT INTO $this->tableName (user_id, created_at)
            VALUES (:userId, NOW())
        ";

        $this->insert($query, [':userId' => $userId]);
    }
}
