<?php
/**
 * On login (and right after registration) moves the guest wish list into the customer's wish list
 */
declare(strict_types=1);

namespace Codilar\GuestWishlist\Observer;

use Codilar\GuestWishlist\Model\GuestToken;
use Codilar\GuestWishlist\Model\GuestWishlist;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\WishlistFactory;
use Psr\Log\LoggerInterface;

class MergeGuestWishlist implements ObserverInterface
{
    /**
     * @param GuestToken $guestToken
     * @param GuestWishlist $guestWishlist
     * @param WishlistFactory $wishlistFactory
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly GuestToken $guestToken,
        private readonly GuestWishlist $guestWishlist,
        private readonly WishlistFactory $wishlistFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Copy the guest items to the customer wish list, then empty the guest wish list
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $token = $this->guestToken->get();
        $customer = $observer->getEvent()->getCustomer();
        if (!$token || !$customer || !$customer->getId()) {
            return;
        }

        $items = $this->guestWishlist->getItems($token);
        if (!$items) {
            return;
        }

        try {
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId((int) $customer->getId(), true);
            // Oldest first, so the customer's wish list keeps the order they were added in
            foreach (array_reverse($items) as $item) {
                try {
                    $product = $this->productRepository->getById($item['product_id'], false, $item['store_id']);
                } catch (NoSuchEntityException $e) {
                    continue;
                }
                $buyRequest = new DataObject($item['buy_request'] + ['product' => $item['product_id']]);
                // addNewItem() merges with an existing item for the same product and options
                $result = $wishlist->addNewItem($product, $buyRequest, true);
                if (is_string($result)) {
                    $this->logger->warning('Guest wish list merge skipped a product: ' . $result);
                }
            }
            $wishlist->save();
            $this->guestWishlist->clear($token);
        } catch (\Throwable $e) {
            // Never block the login; the guest items stay and are retried on the next login
            $this->logger->error('Guest wish list merge failed: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}
