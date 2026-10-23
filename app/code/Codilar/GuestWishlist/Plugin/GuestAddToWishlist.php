<?php
/**
 * Lets guests use every "Add to Wish List" button: the product goes to the guest wish list instead of
 * Magento redirecting to the login page. Logged-in customers continue to the normal wish list.
 */
declare(strict_types=1);

namespace Codilar\GuestWishlist\Plugin;

use Codilar\GuestWishlist\Model\GuestToken;
use Codilar\GuestWishlist\Model\GuestWishlist;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\Index\Add;

class GuestAddToWishlist
{
    /**
     * Request params that are not part of the buy request
     */
    private const SKIP_PARAMS = ['form_key', 'uenc', 'login', 'product'];

    /**
     * @param CustomerSession $customerSession
     * @param GuestToken $guestToken
     * @param GuestWishlist $guestWishlist
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param FormKeyValidator $formKeyValidator
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly GuestToken $guestToken,
        private readonly GuestWishlist $guestWishlist,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly RedirectFactory $redirectFactory,
        private readonly ManagerInterface $messageManager,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Add to the guest wish list when nobody is logged in
     *
     * @param Add $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(Add $subject, callable $proceed, RequestInterface $request)
    {
        // Customers, or wish list switched off (Magento then answers "Page not found"): default behaviour
        if ($this->customerSession->isLoggedIn()
            || !$this->scopeConfig->isSetFlag('wishlist/general/active', ScopeInterface::SCOPE_STORES)
        ) {
            return $proceed($request);
        }

        $redirect = $this->redirectFactory->create()->setPath('guestwishlist');
        if (!$this->formKeyValidator->validate($request)) {
            return $redirect;
        }

        $productId = (int) $request->getParam('product');
        try {
            $product = $productId ? $this->productRepository->getById($productId) : null;
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            return $redirect;
        }

        $buyRequest = array_diff_key($request->getParams(), array_flip(self::SKIP_PARAMS));
        $this->guestWishlist->add(
            $this->guestToken->getOrCreate(),
            (int) $product->getId(),
            (int) $this->storeManager->getStore()->getId(),
            $buyRequest
        );
        $this->messageManager->addSuccessMessage(
            __('%1 has been added to your Wish List. Log in or create an account to keep it.', $product->getName())
        );

        return $redirect;
    }
}
