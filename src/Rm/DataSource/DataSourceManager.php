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
namespace Hyperf\Seata\Rm\DataSource;

use Hyperf\Context\ApplicationContext;
use Hyperf\Seata\Common\AddressTarget;
use Hyperf\Seata\Core\Context\RootContext;
use Hyperf\Seata\Core\Model\BranchStatus;
use Hyperf\Seata\Core\Model\BranchType;
use Hyperf\Seata\Core\Model\Resource;
use Hyperf\Seata\Core\Protocol\ResultCode;
use Hyperf\Seata\Core\Protocol\Transaction\GlobalLockQueryRequest;
use Hyperf\Seata\Core\Protocol\Transaction\GlobalLockQueryResponse;
use Hyperf\Seata\Core\Rpc\Runtime\RmRemotingClient;
use Hyperf\Seata\Exception\RuntimeException;
use Hyperf\Seata\Exception\ShouldNeverHappenException;
use Hyperf\Seata\Exception\TimeoutException;
use Hyperf\Seata\Exception\TransactionException;
use Hyperf\Seata\Exception\TransactionExceptionCode;
use Hyperf\Seata\Logger\LoggerFactory;
use Hyperf\Seata\Logger\LoggerInterface;
use Hyperf\Seata\Rm\AbstractResourceManager;
use Hyperf\Seata\Rm\DataSource\Undo\UndoLogManagerFactory;
use Hyperf\Seata\Rm\PDOProxy;

class DataSourceManager extends AbstractResourceManager implements Resource
{
    protected LoggerInterface $logger;

    protected array $dataSourceCache = [];

    protected UndoLogManagerFactory $undoLogManagerFactory;

    public function __construct(RmRemotingClient $rmRemotingClient)
    {
        parent::__construct($rmRemotingClient);
        $container = ApplicationContext::getContainer();
        $this->undoLogManagerFactory = $container->get(UndoLogManagerFactory::class);
        $this->logger = $container->get(LoggerFactory::class)->create(static::class);
    }

    public function lockQuery(int $branchType, string $resourceId, string $xid, string $lockKeys): bool
    {
        try {
            $request = new GlobalLockQueryRequest();
            $request->setXid($xid)->setLockKey($lockKeys)->setResourceId($resourceId);
            if (! RootContext::inGlobalTransaction() && ! RootContext::requireGlobalLock()) {
                throw new RuntimeException('Unknown situation');
            }
            $response = $this->rmRemotingClient->sendMsgWithResponse($request, AddressTarget::RM);
            if (! $response instanceof GlobalLockQueryResponse) {
                throw new RuntimeException('The response object is not valid');
            }
            if ($response->getResultCode() === ResultCode::Failed) {
                throw new TransactionException(sprintf('Response[%s]', $response->getMessage()), $response->getTransactionExceptionCode());
            }
            return $response->isLockable();
        } catch (TimeoutException $exception) {
            throw new TransactionException('RPC Timeout', TransactionExceptionCode::IO, $exception);
        } catch (\RuntimeException $exception) {
            throw new TransactionException('BranchRegisterFailed', TransactionExceptionCode::BranchRegisterFailed, $exception);
        }
    }

    public function registerResource(Resource $resource): void
    {
        if (! $resource instanceof PDOProxy) {
            throw new \InvalidArgumentException('Invalid data source passing');
        }
        $this->dataSourceCache[$resource->getResourceId()] = $resource;
        parent::registerResource($resource);
    }

    public function get(string $resourceId): ?MysqlConnectionProxy
    {
        return $this->dataSourceCache[$resourceId];
    }

    public function getManagedResources(): array
    {
        return $this->dataSourceCache;
    }

    public function getBranchType(): int
    {
        return BranchType::AT;
    }

    public function branchCommit(
        int $branchType,
        string $xid,
        int $branchId,
        string $resourceId,
        string $applicationData
    ): int {
    }

    public function branchRollback(
        int $branchType,
        string $xid,
        int $branchId,
        string $resourceId,
        string $applicationData
    ): int {
        $dataSourceProxy = $this->get($resourceId);
        if ($dataSourceProxy == null) {
            throw new ShouldNeverHappenException();
        }

        try {
            $this->undoLogManagerFactory->getUndoLogManager($dataSourceProxy->getResourceId())->undo($dataSourceProxy, $xid, $branchId);
        } catch (TransactionException $exception) {
            $this->logger->info(sprintf(
                'branchRollback failed. branchType:[%s], xid:[%s], branchId:[%s], resourceId:[%s], applicationData:[%s]. reason:[%s]',
                $branchType,
                $xid,
                $branchId,
                $resourceId,
                $applicationData,
                $exception->getMessage()
            ));

            if ($exception->getCode() == TransactionExceptionCode::BranchRollbackFailed_Unretriable) {
                return BranchStatus::PhaseTwo_RollbackFailed_Unretryable;
            }
            return BranchStatus::PhaseTwo_RollbackFailed_Retryable;
        }
        return BranchStatus::PhaseTwo_Rollbacked;
    }

    public function getResourceGroupId(): string
    {
        // TODO: Implement getResourceGroupId() method.
    }

    public function getResourceId(): string
    {
        // TODO: Implement getResourceId() method.
    }
}
