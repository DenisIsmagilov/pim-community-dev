<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetAssociatedProductCodesByProduct;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize associations into an array
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationsNormalizer implements NormalizerInterface
{
    /** @var GetAssociatedProductCodesByProduct */
    private $getAssociatedProductCodeByProduct;

    public function __construct(GetAssociatedProductCodesByProduct $getAssociatedProductCodeByProduct)
    {
        $this->getAssociatedProductCodeByProduct = $getAssociatedProductCodeByProduct;
    }

    /**
     * {@inheritdoc}
     *
     * @param EntityWithAssociationsInterface $associationAwareEntity
     */
    public function normalize($associationAwareEntity, $format = null, array $context = [])
    {
        $data = [];

        foreach ($associationAwareEntity->getAllAssociations() as $association) {
            $code = $association->getAssociationType()->getCode();

            $data[$code]['groups'] = $data[$code]['groups'] ?? [];
            foreach ($association->getGroups() as $group) {
                $data[$code]['groups'][] = $group->getCode();
            }

            $data[$code]['products'] = $data[$code]['products'] ?? [];
            if ($associationAwareEntity instanceof ProductModelInterface) {
                foreach ($association->getProducts() as $product) {
                    $data[$code]['products'][] = $product->getReference();
                }
            } else {
                $data[$code]['products'] = array_merge($data[$code]['products'], $this->getAssociatedProductCodeByProduct->getCodes(
                    $association
                ));
            }

            $data[$code]['product_models'] = $data[$code]['product_models'] ?? [];
            foreach ($association->getProductModels() as $productModel) {
                $data[$code]['product_models'][] = $productModel->getCode();
            }
        }

        $data = array_map(function ($association) {
            $association['products'] = array_unique($association['products']);
            return $association;
        }, $data);

        ksort($data);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithAssociationsInterface && 'standard' === $format;
    }
}
