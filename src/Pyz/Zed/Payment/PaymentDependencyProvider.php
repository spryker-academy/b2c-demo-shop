<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\Payment;

use Spryker\Zed\GiftCard\Communication\Plugin\GiftCardPaymentMethodFilterPlugin;
use Spryker\Zed\Nopayment\Communication\Plugin\Payment\PriceToPayPaymentMethodFilterPlugin;
use Spryker\Zed\Payment\PaymentDependencyProvider as SprykerPaymentDependencyProvider;

class PaymentDependencyProvider extends SprykerPaymentDependencyProvider
{
    /**
     * @return \Spryker\Zed\PaymentExtension\Dependency\Plugin\PaymentMethodFilterPluginInterface[]
     */
    protected function getPaymentMethodFilterPlugins(): array
    {
        return [
            new PriceToPayPaymentMethodFilterPlugin(),
            new GiftCardPaymentMethodFilterPlugin(),
        ];
    }
}
