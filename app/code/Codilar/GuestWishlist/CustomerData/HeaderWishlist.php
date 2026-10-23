<?php
/**
 * "header-wishlist" private content section: item count and link for the header heart icon
 */
declare(strict_types=1);

namespace Codilar\GuestWishlist\CustomerData;

use Codilar\GuestWishlist\Model\GuestToken;
use Codilar\GuestWishlist\Model\GuestWishlist;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\UrlInterface;
use Magento\Wishlist\Model\WishlistFactory;

class HeaderWishlist implements SectionSourceInterface
{
    /**
     * @param CustomerSession $customerSession
     * @param WishlistFactory $wishlistFactory
     * @param GuestToken $guestToken
     * @param GuestWishlist $guestWishlist
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly WishlistFactory $wishlistFactory,
        private readonly GuestToken $guestToken,
        private readonly GuestWishlist $guestWishlist,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        if ($this->customerSession->isLoggedIn()) {
            $wishlist = $this->wishlistFactory->create()
                ->loadByCustomerId((int) $this->customerSession->getCustomerId());

            return [
                'count' => $wishlist->getId() ? (int) $wishlist->getItemsCount() : 0,
                'url'   => $this->urlBuilder->getUrl('wishlist'),
            ];
        }

        $token = $this->guestToken->get();

        return [
            'count' => $token ? $this->guestWishlist->count($token) : 0,
            'url'   => $this->urlBuilder->getUrl('guestwishlist'),
        ];
    }
}
