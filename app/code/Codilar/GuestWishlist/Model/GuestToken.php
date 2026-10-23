<?php
/**
 * Anonymous cookie that identifies a guest's wish list
 */
declare(strict_types=1);

namespace Codilar\GuestWishlist\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class GuestToken
{
    public const COOKIE_NAME = 'guest_wishlist';
    private const LIFETIME = 2592000; // 30 days

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param RequestInterface $request
     * @param Random $random
     */
    public function __construct(
        private readonly CookieManagerInterface $cookieManager,
        private readonly CookieMetadataFactory $cookieMetadataFactory,
        private readonly RequestInterface $request,
        private readonly Random $random
    ) {
    }

    /**
     * Current guest token, or null when the visitor has no guest wish list yet
     *
     * @return string|null
     */
    public function get(): ?string
    {
        $token = (string) $this->cookieManager->getCookie(self::COOKIE_NAME);

        return preg_match('/^[a-zA-Z0-9]{32}$/', $token) ? $token : null;
    }

    /**
     * Current guest token, creating the cookie when needed
     *
     * @return string
     */
    public function getOrCreate(): string
    {
        $token = $this->get() ?? $this->random->getRandomString(32);

        // (Re)set on every add so the guest wish list lives 30 days after the last change
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration(self::LIFETIME)
            ->setPath('/')
            ->setHttpOnly(true)
            ->setSecure($this->request->isSecure())
            ->setSameSite('Lax');
        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $token, $metadata);

        return $token;
    }
}
