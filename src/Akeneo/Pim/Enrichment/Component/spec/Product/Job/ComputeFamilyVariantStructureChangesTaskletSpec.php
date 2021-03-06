<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComputeFamilyVariantStructureChangesTaskletSpec extends ObjectBehavior
{
    function let(
        EntityRepository $familyVariantRepository,
        ObjectRepository $variantProductRepository,
        ProductModelRepositoryInterface $productModelRepository,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver,
        KeepOnlyValuesForVariation $keepOnlyValuesForVariation,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $familyVariantRepository,
            $variantProductRepository,
            $productModelRepository,
            $productSaver,
            $productModelSaver,
            $keepOnlyValuesForVariation,
            $validator
        );
    }

    function it_executes_the_family_variant_structure_computation_on_1_level(
        $familyVariantRepository,
        $variantProductRepository,
        $productModelRepository,
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct,
        ProductModelInterface $rootProductModel,
        ConstraintViolationListInterface $variantProductViolations,
        ConstraintViolationListInterface $rootProductModelViolations
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['tshirt']);

        $familyVariantRepository->findBy(['code' => ['tshirt']])->willReturn([$familyVariant]);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        // Process the variant products
        $variantProductRepository->findBy(['familyVariant' => $familyVariant])
            ->willReturn([$variantProduct]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])
            ->shouldBeCalled();
        $validator->validate($variantProduct)->willReturn($variantProductViolations);
        $variantProductViolations->count()->willReturn(0);
        $productSaver->save($variantProduct)->shouldBeCalled();

        // Process the root product models
        $productModelRepository->findRootProductModels($familyVariant)
            ->willReturn([$rootProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])
            ->shouldBeCalled();
        $validator->validate($rootProductModel)->willReturn($rootProductModelViolations);
        $rootProductModelViolations->count()->willReturn(0);
        $productModelSaver->save($rootProductModel)->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_executes_the_family_variant_structure_computation_on_2_levels(
        $familyVariantRepository,
        $variantProductRepository,
        $productModelRepository,
        $productSaver,
        $productModelSaver,
        $keepOnlyValuesForVariation,
        $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct,
        ProductModelInterface $subProductModel,
        ProductModelInterface $rootProductModel,
        ConstraintViolationListInterface $variantProductViolations,
        ConstraintViolationListInterface $subProductModelViolations,
        ConstraintViolationListInterface $rootProductModelViolations
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['tshirt']);

        $familyVariantRepository->findBy(['code' => ['tshirt']])->willReturn([$familyVariant]);
        $familyVariant->getNumberOfLevel()->willReturn(2);

        // Process the variant products
        $variantProductRepository->findBy(['familyVariant' => $familyVariant])
            ->willReturn([$variantProduct]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])
            ->shouldBeCalled();
        $validator->validate($variantProduct)->willReturn($variantProductViolations);
        $variantProductViolations->count()->willReturn(0);
        $productSaver->save($variantProduct)->shouldBeCalled();

        // Process the sub product models
        $productModelRepository->findSubProductModels($familyVariant)
            ->willReturn([$subProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$subProductModel])
            ->shouldBeCalled();
        $validator->validate($subProductModel)->willReturn($subProductModelViolations);
        $subProductModelViolations->count()->willReturn(0);
        $productModelSaver->save($subProductModel)->shouldBeCalled();


        // Process the root product models
        $productModelRepository->findRootProductModels($familyVariant)
            ->willReturn([$rootProductModel]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$rootProductModel])
            ->shouldBeCalled();
        $validator->validate($rootProductModel)->willReturn($rootProductModelViolations);
        $rootProductModelViolations->count()->willReturn(0);
        $productModelSaver->save($rootProductModel)->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_throws_an_exception_if_there_is_a_validation_error(
        $familyVariantRepository,
        $variantProductRepository,
        $productSaver,
        $keepOnlyValuesForVariation,
        $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyVariantInterface $familyVariant,
        ProductInterface $variantProduct,
        ConstraintViolationListInterface $variantProductViolations
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('family_variant_codes')->willReturn(['tshirt']);

        $familyVariantRepository->findBy(['code' => ['tshirt']])->willReturn([$familyVariant]);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        // Process the variant products
        $variantProductRepository->findBy(['familyVariant' => $familyVariant])
            ->willReturn([$variantProduct]);
        $keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$variantProduct])
            ->shouldBeCalled();
        $validator->validate($variantProduct)->willReturn($variantProductViolations);
        $variantProductViolations->count()->willReturn(1);
        $productSaver->save($variantProduct)->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->shouldThrow(\LogicException::class)->during('execute');
    }
}
