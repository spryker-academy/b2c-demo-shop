<?php declare(strict_types = 1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Service\Monitoring;

use Spryker\Service\Monitoring\MonitoringDependencyProvider as SprykerMonitoringDependencyProvider;
use Pyz\Service\NewRelic\Plugin\NewRelicMonitoringExtensionPlugin;

class MonitoringDependencyProvider extends SprykerMonitoringDependencyProvider
{
    /**
     * @return \Spryker\Service\MonitoringExtension\Dependency\Plugin\MonitoringExtensionPluginInterface[]
     */
    protected function getMonitoringExtensions(): array
    {
        return [
            new NewRelicMonitoringExtensionPlugin(),
        ];
    }
}
