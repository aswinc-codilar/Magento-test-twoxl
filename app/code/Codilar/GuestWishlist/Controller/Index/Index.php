<?php
/**
 * Guest wish list page (customers are sent to their normal wish list)
 */
declare(strict_types=1);

namespace Codilar\GuestWishlist\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly RedirectFactory $redirectFactory,
        private readonly CustomerSession $customerSession
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->redirectFactory->create()->setPath('wishlist');
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('My Wish List'));

        return $page;
    }
}
