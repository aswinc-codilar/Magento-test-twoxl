<?php
/**
 * Remove a product from the guest wish list
 */
declare(strict_types=1);

namespace Codilar\GuestWishlist\Controller\Index;

use Codilar\GuestWishlist\Model\GuestToken;
use Codilar\GuestWishlist\Model\GuestWishlist;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;

class Remove implements HttpPostActionInterface
{
    /**
     * @param RequestInterface $request
     * @param FormKeyValidator $formKeyValidator
     * @param GuestToken $guestToken
     * @param GuestWishlist $guestWishlist
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly GuestToken $guestToken,
        private readonly GuestWishlist $guestWishlist,
        private readonly RedirectFactory $redirectFactory,
        private readonly ManagerInterface $messageManager
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $token = $this->guestToken->get();
        $productId = (int) $this->request->getParam('product');

        if ($token && $productId && $this->formKeyValidator->validate($this->request)) {
            $this->guestWishlist->remove($token, $productId);
            $this->messageManager->addSuccessMessage(__('The item has been removed from your Wish List.'));
        }

        return $this->redirectFactory->create()->setPath('guestwishlist');
    }
}
