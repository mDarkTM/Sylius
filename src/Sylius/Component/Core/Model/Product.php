<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Core\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface as BaseChannelInterface;
use Sylius\Component\Product\Model\Product as BaseProduct;
use Sylius\Component\Review\Model\ReviewInterface;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface as BaseTaxonInterface;

/**
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 * @author Gonzalo Vilaseca <gvilaseca@reiss.co.uk>
 * @author Anna Walasek <anna.walasek@lakion.com>
 */
class Product extends BaseProduct implements ProductInterface, ReviewableProductInterface
{
    /**
     * @var string
     */
    protected $variantSelectionMethod;

    /**
     * @var Collection|BaseTaxonInterface[]
     */
    protected $taxons;

    /**
     * @var ShippingCategoryInterface
     */
    protected $shippingCategory;

    /**
     * @var ChannelInterface[]|Collection
     */
    protected $channels;

    /**
     * @var BaseTaxonInterface
     */
    protected $mainTaxon;

    /**
     * @var Collection|ReviewInterface[]
     */
    protected $reviews;

    /**
     * @var float
     */
    protected $averageRating = 0;

    /**
     * @var Collection|ImageInterface[]
     */
    protected $images;

    public function __construct()
    {
        parent::__construct();

        $this->taxons = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->images = new ArrayCollection();

        $this->variantSelectionMethod = self::VARIANT_SELECTION_CHOICE;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantSelectionMethod()
    {
        return $this->variantSelectionMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function setVariantSelectionMethod($variantSelectionMethod)
    {
        if (!in_array($variantSelectionMethod, [self::VARIANT_SELECTION_CHOICE, self::VARIANT_SELECTION_MATCH])) {
            throw new \InvalidArgumentException(sprintf('Wrong variant selection method "%s" given.', $variantSelectionMethod));
        }

        $this->variantSelectionMethod = $variantSelectionMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function isVariantSelectionMethodChoice()
    {
        return self::VARIANT_SELECTION_CHOICE === $this->variantSelectionMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantSelectionMethodLabel()
    {
        $labels = self::getVariantSelectionMethodLabels();

        return $labels[$this->variantSelectionMethod];
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxons($rootTaxonCode = null)
    {
        if (null !== $rootTaxonCode) {
            return $this->taxons->filter(function (BaseTaxonInterface $taxon) use ($rootTaxonCode) {
                return $rootTaxonCode === strtolower($taxon->getRoot()->getCode());
            });
        }

        return $this->taxons;
    }

    /**
     * {@inheritdoc}
     */
    public function addTaxon(BaseTaxonInterface $taxon)
    {
        if (!$this->hasTaxon($taxon)) {
            $this->taxons->add($taxon);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeTaxon(BaseTaxonInterface $taxon)
    {
        if ($this->hasTaxon($taxon)) {
            $this->taxons->removeElement($taxon);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasTaxon(BaseTaxonInterface $taxon)
    {
        return $this->taxons->contains($taxon);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingCategory()
    {
        return $this->shippingCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingCategory(ShippingCategoryInterface $category = null)
    {
        $this->shippingCategory = $category;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * {@inheritdoc}
     */
    public function addChannel(BaseChannelInterface $channel)
    {
        if (!$this->hasChannel($channel)) {
            $this->channels->add($channel);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeChannel(BaseChannelInterface $channel)
    {
        if ($this->hasChannel($channel)) {
            $this->channels->removeElement($channel);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasChannel(BaseChannelInterface $channel)
    {
        return $this->channels->contains($channel);
    }

    /**
     * {@inheritdoc}
     */
    public static function getVariantSelectionMethodLabels()
    {
        return [
            self::VARIANT_SELECTION_CHOICE => 'Variant choice',
            self::VARIANT_SELECTION_MATCH => 'Options matching',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return $this->getTranslation()->getShortDescription();
    }

    /**
     * {@inheritdoc}
     */
    public function setShortDescription($shortDescription)
    {
        $this->getTranslation()->setShortDescription($shortDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function getMainTaxon()
    {
        return $this->mainTaxon;
    }

    /**
     * {@inheritdoc}
     */
    public function setMainTaxon(TaxonInterface $mainTaxon = null)
    {
        $this->mainTaxon = $mainTaxon;
    }

    /**
     * {@inheritdoc}
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * {@inheritdoc}
     */
    public function addReview(ReviewInterface $review)
    {
        $this->reviews->add($review);
    }

    /**
     * {@inheritdoc}
     */
    public function removeReview(ReviewInterface $review)
    {
        $this->reviews->remove($review);
    }

    /**
     * {@inheritdoc}
     */
    public function getAverageRating()
    {
        return $this->averageRating;
    }

    /**
     * {@inheritdoc}
     */
    public function setAverageRating($averageRating)
    {
        $this->averageRating = $averageRating;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        if ($this->variants->isEmpty()) {
            return null;
        }

        return $this->variants->first()->getPrice();
    }

    /**
     * {@inheritdoc}
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageByCode($code)
    {
        foreach ($this->images as $image) {
            if ($code === $image->getCode()) {
                return $image;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasImages()
    {
        return !$this->images->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function hasImage(ImageInterface $image)
    {
        return $this->images->contains($image);
    }

    /**
     * {@inheritdoc}
     */
    public function addImage(ImageInterface $image)
    {
        $image->setOwner($this);
        $this->images->add($image);
    }

    /**
     * {@inheritdoc}
     */
    public function removeImage(ImageInterface $image)
    {
        if ($this->hasImage($image)) {
            $image->setOwner(null);
            $this->images->removeElement($image);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createTranslation()
    {
        return new ProductTranslation();
    }
}
