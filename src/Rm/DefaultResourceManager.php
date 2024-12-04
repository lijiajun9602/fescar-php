<?php

declare(strict_types=1);
/**
 * Copyright 2019-2022 Seata.io Group.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */
namespace Hyperf\Seata\Rm;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Seata\Core\Model\Resource;
use Hyperf\Seata\Core\Model\ResourceManagerInterface;
use Hyperf\Seata\Exception\SeataException;
use Hyperf\Seata\Rm\DataSource\DataSourceManager;

class DefaultResourceManager implements ResourceManagerInterface
{
    /**
     * All resource managers.
     *
     * @var \Hyperf\Seata\Core\Model\ResourceManagerInterface[]
     */
    protected array $resourceManagers = [];

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->initResourceManagers();
    }

    public function registerResource(Resource $resource): void
    {
        $this->getResourceManager($resource->getBranchType())->registerResource($resource);
    }

    public function unregisterResource(Resource $resource): void
    {
        $this->getResourceManager($resource->getBranchType())->unregisterResource($resource);
    }

    public function getManagedResources(): array
    {
        $resources = [];
        $resourceManagers = $this->resourceManagers;
        foreach ($resourceManagers as $resourceManager) {
            $managedResources = $resourceManager->getManagedResources();
            $resources = array_merge($resources, $managedResources);
        }
        return $resources;
    }

    /**
     * Get ResourceManager by branch Type.
     */
    public function getResourceManager(int $branchType): ResourceManagerInterface
    {
        if (! isset($this->resourceManagers[$branchType])) {
            throw new SeataException('No ResourceManager for BranchType:' . $branchType);
        }
        return $this->resourceManagers[$branchType];
    }

    public function getBranchType(): int
    {
        throw new SeataException("DefaultResourceManager isn't a real ResourceManager");
    }

    public function branchCommit(
        int $branchType,
        string $xid,
        int $branchId,
        string $resourceId,
        string $applicationData
    ): int {
        return $this->getResourceManager($branchType)
            ->branchCommit($branchType, $xid, $branchId, $resourceId, $applicationData);
    }

    public function branchRollback(
        int $branchType,
        string $xid,
        int $branchId,
        string $resourceId,
        string $applicationData
    ): int {
        return $this->getResourceManager($branchType)
            ->branchRollback($branchType, $xid, $branchId, $resourceId, $applicationData);
    }

    public function branchRegister(
        int $branchType,
        string $resourceId,
        string $clientId,
        string $xid,
        string $applicationData,
        string $lockKeys
    ): int {
        return $this->getResourceManager($branchType)
            ->branchRegister($branchType, $resourceId, $clientId, $xid, $applicationData, $lockKeys);
    }

    public function branchReport(
        int $branchType,
        string $xid,
        int $branchId,
        int $status,
        string $applicationData
    ): void {
        $this->getResourceManager($branchType)->branchReport($branchType, $xid, $branchId, $status, $applicationData);
    }

    public function lockQuery(int $branchType, string $resourceId, string $xid, string $lockKeys): bool
    {
        return $this->getResourceManager($branchType)->lockQuery($branchType, $resourceId, $xid, $lockKeys);
    }

    protected function initResourceManagers(): void
    {
        $at = $this->container->get(DataSourceManager::class);
        $this->resourceManagers = [
            $at->getBranchType() => $at,
        ];
    }
}
