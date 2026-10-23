<?php
/**
 * Storage for guest wish list items (table codilar_guest_wishlist_item)
 */
declare(strict_types=1);

namespace Codilar\GuestWishlist\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

class GuestWishlist
{
    private const TABLE = 'codilar_guest_wishlist_item';

    /**
     * @param ResourceConnection $resource
     * @param Json $json
     */
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly Json $json
    ) {
    }

    /**
     * Add a product (or update its selected options when it's already there)
     *
     * @param string $token
     * @param int $productId
     * @param int $storeId
     * @param array $buyRequest
     * @return void
     */
    public function add(string $token, int $productId, int $storeId, array $buyRequest = []): void
    {
        $this->resource->getConnection()->insertOnDuplicate(
            $this->resource->getTableName(self::TABLE),
            [
                'guest_token' => $token,
                'product_id'  => $productId,
                'store_id'    => $storeId,
                'buy_request' => $buyRequest ? $this->json->serialize($buyRequest) : null,
            ],
            ['store_id', 'buy_request']
        );
    }

    /**
     * Remove a product
     *
     * @param string $token
     * @param int $productId
     * @return void
     */
    public function remove(string $token, int $productId): void
    {
        $this->resource->getConnection()->delete(
            $this->resource->getTableName(self::TABLE),
            ['guest_token = ?' => $token, 'product_id = ?' => $productId]
        );
    }

    /**
     * Remove everything (after it was moved to a customer wish list)
     *
     * @param string $token
     * @return void
     */
    public function clear(string $token): void
    {
        $this->resource->getConnection()->delete(
            $this->resource->getTableName(self::TABLE),
            ['guest_token = ?' => $token]
        );
    }

    /**
     * Items, newest first: [['product_id' => int, 'store_id' => int, 'buy_request' => array], ...]
     *
     * @param string $token
     * @return array
     */
    public function getItems(string $token): array
    {
        $connection = $this->resource->getConnection();
        $rows = $connection->fetchAll(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLE), ['product_id', 'store_id', 'buy_request'])
                ->where('guest_token = ?', $token)
                ->order('added_at DESC')
                ->order('item_id DESC')
        );

        return array_map(function (array $row) {
            return [
                'product_id'  => (int) $row['product_id'],
                'store_id'    => (int) $row['store_id'],
                'buy_request' => $row['buy_request'] ? (array) $this->json->unserialize($row['buy_request']) : [],
            ];
        }, $rows);
    }

    /**
     * Number of items
     *
     * @param string $token
     * @return int
     */
    public function count(string $token): int
    {
        $connection = $this->resource->getConnection();

        return (int) $connection->fetchOne(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLE), ['COUNT(*)'])
                ->where('guest_token = ?', $token)
        );
    }
}
