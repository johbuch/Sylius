<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\CoreBundle\Fixture\Factory;

use Faker\Factory;
use Faker\Generator;
use Sylius\Bundle\CoreBundle\Fixture\OptionsResolver\LazyOption;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionActionInterface;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionExampleFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{
    private Generator $faker;

    private OptionsResolver $optionsResolver;

    public function __construct(
        private FactoryInterface $promotionFactory,
        private ExampleFactoryInterface $promotionRuleExampleFactory,
        private ExampleFactoryInterface $promotionActionExampleFactory,
        private ChannelRepositoryInterface $channelRepository,
        private ?FactoryInterface $couponFactory = null,
    ) {
        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);

        if ($this->couponFactory === null) {
            @trigger_error(sprintf('Not passing a $couponFactory to %s constructor is deprecated since Sylius 1.8 and will be removed in Sylius 2.0.', self::class), \E_USER_DEPRECATED);
        }
    }

    public function create(array $options = []): PromotionInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var PromotionInterface $promotion */
        $promotion = $this->promotionFactory->createNew();
        $promotion->setCode($options['code']);
        $promotion->setName($options['name']);
        $promotion->setDescription($options['description']);
        $promotion->setCouponBased($options['coupon_based']);
        $promotion->setUsageLimit($options['usage_limit']);
        $promotion->setExclusive($options['exclusive']);
        $promotion->setPriority((int) $options['priority']);

        if (isset($options['starts_at'])) {
            $promotion->setStartsAt(new \DateTime($options['starts_at']));
        }

        if (isset($options['ends_at'])) {
            $promotion->setEndsAt(new \DateTime($options['ends_at']));
        }

        foreach ($options['channels'] as $channel) {
            $promotion->addChannel($channel);
        }

        foreach ($options['rules'] as $rule) {
            /** @var PromotionRuleInterface $promotionRule */
            $promotionRule = $this->promotionRuleExampleFactory->create($rule);
            $promotion->addRule($promotionRule);
        }

        foreach ($options['actions'] as $action) {
            /** @var PromotionActionInterface $promotionAction */
            $promotionAction = $this->promotionActionExampleFactory->create($action);
            $promotion->addAction($promotionAction);
        }

        foreach ($options['coupons'] as $coupon) {
            $promotion->addCoupon($coupon);
        }

        return $promotion;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('code', fn (Options $options): string => StringInflector::nameToCode($options['name']))
            ->setDefault('name', $this->faker->words(3, true))
            ->setDefault('description', $this->faker->sentence())
            ->setDefault('usage_limit', null)
            ->setDefault('coupon_based', false)
            ->setDefault('exclusive', $this->faker->boolean(25))
            ->setDefault('priority', 0)
            ->setDefault('starts_at', null)
            ->setAllowedTypes('starts_at', ['null', 'string'])
            ->setDefault('ends_at', null)
            ->setAllowedTypes('ends_at', ['null', 'string'])
            ->setDefault('channels', LazyOption::all($this->channelRepository))
            ->setAllowedTypes('channels', 'array')
            ->setNormalizer('channels', LazyOption::findBy($this->channelRepository, 'code'))
            ->setDefined('rules')
            ->setNormalizer('rules', function (Options $options, array $rules): array {
                if (empty($rules)) {
                    return [[]];
                }

                return $rules;
            })
            ->setDefined('actions')
            ->setNormalizer('actions', function (Options $options, array $actions): array {
                if (empty($actions)) {
                    return [[]];
                }

                return $actions;
            })

            ->setDefault('coupons', [])
            ->setAllowedTypes('coupons', 'array')
            ->setNormalizer('coupons', self::getCouponNormalizer($this->couponFactory))
        ;
    }

    private static function getCouponNormalizer(?FactoryInterface $couponFactory): \Closure
    {
        return function (Options $options, array $couponDefinitions) use ($couponFactory): array {
            if (null === $couponFactory) {
                throw new \RuntimeException('You must configure a $couponFactory');
            }

            if (!$options['coupon_based'] && count($couponDefinitions) > 0) {
                throw new InvalidArgumentException('Cannot define coupons for promotion that is not coupon based');
            }

            $coupons = [];
            foreach ($couponDefinitions as $couponDefinition) {
                /** @var PromotionCouponInterface $coupon */
                $coupon = $couponFactory->createNew();
                $coupon->setCode($couponDefinition['code']);
                $coupon->setPerCustomerUsageLimit($couponDefinition['per_customer_usage_limit']);
                $coupon->setReusableFromCancelledOrders($couponDefinition['reusable_from_cancelled_orders']);
                $coupon->setUsageLimit($couponDefinition['usage_limit']);

                if (null !== $couponDefinition['expires_at']) {
                    $coupon->setExpiresAt(new \DateTime($couponDefinition['expires_at']));
                }

                $coupons[] = $coupon;
            }

            return $coupons;
        };
    }
}
