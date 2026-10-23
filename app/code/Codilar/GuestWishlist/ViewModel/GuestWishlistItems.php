<?php
/**
 * Products in the current guest's wish list, for the guest wish list page
 */
declare(strict_types=1);

namespace Codilar\GuestWishlist\ViewModel;

use Codilar\GuestWishlist\Model\GuestToken;
use Codilar\GuestWishlist\Model\GuestWishlist;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class GuestWishlistItems implements ArgumentInterface
{
    /**
     * @var Product[]|null
     */
    private ?array $products = null;

    /**
     * @param GuestToken $guestToken
     * @param GuestWishlist $guestWishlist
     * @param CollectionFactory $collectionFactory
     * @param Visibility $visibility
     * @param StoreManagerInterface $storeManager
     * @param ImageFactory $imageFactory
     * @param PostHelper $postHelper
     * @param CartHelper $cartHelper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        private readonly GuestToken $guestToken,
        private readonly GuestWishlist $guestWishlist,
        private readonly CollectionFactory $collectionFactory,
        private readonly Visibility $visibility,
        private readonly StoreManagerInterface $storeManager,
        private readonly ImageFactory $imageFactory,
        private readonly PostHelper $postHelper,
        private readonly CartHelper $cartHelper,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * Products, newest first (disabled / hidden products are skipped)
     *
     * @return Product[]
     */
    public function getProducts(): array
    {
        if ($this->products !== null) {
            return $this->products;
        }

        $token = $this->guestToken->get();
        $ids = $token ? array_column($this->guestWishlist->getItems($token), 'product_id') : [];
        if (!$ids) {
            return $this->products = [];
        }

        $collection = $this->collectionFactory->create()
            ->setStoreId($this->storeManager->getStore()->getId())
            ->addIdFilter($ids)
            // Price attributes are needed for the price box to show special prices
            ->addAttributeToSelect(['name', 'small_image', 'thumbnail', 'url_key', 'price', 'special_price',
                'special_from_date', 'special_to_date', 'tax_class_id', 'msrp', 'msrp_display_actual_price_type'])
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->setVisibility($this->visibility->getVisibleInSiteIds())
            ->addPriceData()
            ->addUrlRewrite();

        $byId = [];
        foreach ($collection as $product) {
            $byId[(int) $product->getId()] = $product;
        }
        $this->products = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $this->products[] = $byId[$id];
            }
        }

        return $this->products;
    }

    /**
     * Product image HTML
     *
     * @param Product $product
     * @return string
     */
    public function getImageHtml(Product $product): string
    {
        return $this->imageFactory->create($product, 'category_page_grid')->toHtml();
    }

    /**
     * data-post JSON for "Add to Cart", or null when options must be chosen on the product page first
     *
     * @param Product $product
     * @return string|null
     */
    public function getAddToCartPostData(Product $product): ?string
    {
        if (!$product->isSaleable() || $product->getTypeInstance()->hasRequiredOptions($product)
            || $product->getTypeId() !== 'simple' && $product->getTypeId() !== 'virtual'
        ) {
            return null;
        }

        return $this->postHelper->getPostData($this->cartHelper->getAddUrl($product), ['qty' => 1]);
    }

    /**
     * data-post JSON for "Remove"
     *
     * @param Product $product
     * @return string
     */
    public function getRemovePostData(Product $product): string
    {
        return $this->postHelper->getPostData(
            $this->urlBuilder->getUrl('guestwishlist/index/remove'),
            ['product' => (int) $product->getId()]
        );
    }

    /**
     * Login URL that comes back to the account wish list (where the guest items will be after login)
     *
     * @return string
     */
    public function getLoginUrl(): string
    {
        return $this->urlBuilder->getUrl('customer/account/login');
    }
}
