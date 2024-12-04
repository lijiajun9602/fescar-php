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
namespace Hyperf\Seata\Core\Rpc\Runtime;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Seata\Common\AddressTarget;
use Hyperf\Seata\Common\Constants;
use Hyperf\Seata\Core\Model\ResourceManagerInterface;
use Hyperf\Seata\Core\Protocol\AbstractMessage;
use Hyperf\Seata\Core\Protocol\HeartbeatMessage;
use Hyperf\Seata\Core\Protocol\MessageType;
use Hyperf\Seata\Core\Protocol\RegisterRMRequest;
use Hyperf\Seata\Core\Protocol\Transaction\GlobalBeginResponse;
use Hyperf\Seata\Core\Rpc\Address;
use Hyperf\Seata\Core\Rpc\Processor\Client\ClientHeartbeatProcessor;
use Hyperf\Seata\Core\Rpc\Processor\Client\ClientOnResponseProcessor;
use Hyperf\Seata\Core\Rpc\Processor\Client\RmBranchCommitProcessor;
use Hyperf\Seata\Core\Rpc\Processor\Client\RmBranchRollbackProcessor;
use Hyperf\Seata\Core\Rpc\Processor\Client\RmUndoLogProcessor;
use Hyperf\Seata\Core\Rpc\TransactionMessageHandler;
use Hyperf\Seata\Core\Rpc\TransactionRole;
use Hyperf\Seata\Exception\TodoException;
use Hyperf\Context\ApplicationContext;;


class RmRemotingClient extends AbstractRemotingClient
{
    protected const KEEP_ALIVE_TIME = PHP_INT_MAX;

    protected const MAX_QUEUE_SIZE = 20000;

    protected ResourceManagerInterface $resourceManager;

    protected string $customerKeys = '';

    protected bool $initialized = false;

    protected string $applicationId = '';

    protected string $transactionServiceGroup = '';

    protected SocketManager $socketManager;

    public function __construct(int $transactionRole = TransactionRole::RMROLE)
    {
        parent::__construct($transactionRole);
        $container = ApplicationContext::getContainer();
        $this->socketManager = $container->get(SocketManager::class);
    }

    public function init()
    {
        $this->initRegisterProcessor();
        $this->initialized = true;
        parent::init();
        if ($this->resourceManager && ! empty($this->resourceManager->getManagedResources()) && $this->transactionServiceGroup) {
            $this->socketManager->reconnect($this->transactionServiceGroup, 'rm');
        }
        $this->createHeartbeatLoop();
        $this->registerResource($this->applicationId, $this->transactionServiceGroup);
    }

    public function sendRegisterMessage(SocketChannelInterface $socketChannel, string $resourceId)
    {
        $request = new RegisterRMRequest($this->applicationId, $this->transactionServiceGroup);
        $request->setResourceIds($resourceId);
        return $this->sendMsgWithResponse($request, AddressTarget::RM);
    }

    public function registerResource(string $resourceGroupId, string $resourceId): void
    {
        if ($this->transactionServiceGroup !== '') {
            $this->socketManager->reconnect($this->transactionServiceGroup, 'rm');
        }
        $addresses = $this->socketManager->getAvailServerList($this->transactionServiceGroup);
        foreach ($addresses as $address) {
            $address->setTarget('rm');
            $socketChannel = $this->socketManager->acquireChannel($address);
            $this->sendRegisterMessage($socketChannel, $resourceId);
        }
    }

    public function initRegisterProcessor()
    {
        // 1.registry rm client handle branch commit processor
        $rmBranchCommitProcessor = new RmBranchCommitProcessor($this->getTransactionMessageHandler(), $this);
        $this->processorManager->registerProcessor(MessageType::TYPE_BRANCH_COMMIT, $rmBranchCommitProcessor);
        // 2.registry rm client handle branch commit processor
        $rmBranchRollbackProcessor = new RmBranchRollbackProcessor($this->getTransactionMessageHandler(), $this);
        $this->processorManager->registerProcessor(MessageType::TYPE_BRANCH_ROLLBACK, $rmBranchRollbackProcessor);
        // 3.registry rm handler undo log processor
        $rmUndoLogProcessor = new RmUndoLogProcessor($this->getTransactionMessageHandler());
        $this->processorManager->registerProcessor(MessageType::TYPE_RM_DELETE_UNDOLOG, $rmUndoLogProcessor);
        // 4.registry TC response processor
        $onResponseProcessor = new ClientOnResponseProcessor($this->getTransactionMessageHandler());
        $this->processorManager->registerProcessor(MessageType::TYPE_SEATA_MERGE_RESULT, $onResponseProcessor, null);
        $this->processorManager->registerProcessor(MessageType::TYPE_BRANCH_REGISTER_RESULT, $onResponseProcessor, null);
        $this->processorManager->registerProcessor(MessageType::TYPE_BRANCH_STATUS_REPORT_RESULT, $onResponseProcessor, null);
        $this->processorManager->registerProcessor(MessageType::TYPE_GLOBAL_LOCK_QUERY_RESULT, $onResponseProcessor, null);
        $this->processorManager->registerProcessor(MessageType::TYPE_REG_RM_RESULT, $onResponseProcessor, null);
        // 5.registry heartbeat message processor
        $clientHeartbeatProcessor = new ClientHeartbeatProcessor();
        $this->processorManager->registerProcessor(MessageType::TYPE_HEARTBEAT_MSG, $clientHeartbeatProcessor, null);
    }

    public function getResourceManager(): ResourceManagerInterface
    {
        return $this->resourceManager;
    }

    public function getTransactionMessageHandler(): TransactionMessageHandler
    {
        return $this->transactionMessageHandler;
    }

    public function setResourceManager(ResourceManagerInterface $resourceManager): static
    {
        $this->resourceManager = $resourceManager;
        return $this;
    }

    public function getCustomerKeys(): string
    {
        return $this->customerKeys;
    }

    public function setCustomerKeys(string $customerKeys): static
    {
        $this->customerKeys = $customerKeys;
        return $this;
    }

    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    public function setApplicationId(string $applicationId): static
    {
        $this->applicationId = $applicationId;
        return $this;
    }

    public function getTransactionServiceGroup(): string
    {
        return $this->transactionServiceGroup;
    }

    public function setTransactionServiceGroup(string $transactionServiceGroup): static
    {
        $this->transactionServiceGroup = $transactionServiceGroup;
        return $this;
    }

    public function destroy(): void
    {
        throw new TodoException();
    }

    public function sendSyncRequest(SocketChannelInterface $socketChannel, object $message): GlobalBeginResponse
    {
        return $this->sendMsgWithResponse($message, AddressTarget::RM);
    }

    public function onRegisterMsgSuccess(
        string $serverAddress,
        $channel,
        object $response,
        AbstractMessage $requestMessage
    ) {
        throw new TodoException();
    }

    public function onRegisterMsgFail(
        string $serverAddress,
        $channel,
        object $response,
        AbstractMessage $requestMessage
    ) {
        throw new TodoException();
    }

    protected function createHeartbeatLoop()
    {
        Coroutine::create(function () {
            while (true) {
                try {
                    $response = $this->sendMsgWithResponse(HeartbeatMessage::ping(), AddressTarget::RM);
                } catch (\InvalidArgumentException|\Throwable $exception) {
//                    var_dump($exception->getMessage());
                }
                sleep(5);
            }
        });
    }

    protected function getMergedResourceKeys(): string
    {
        $resourceIds = [];
        $managedResources = $this->getResourceManager()->getManagedResources();
        foreach ($managedResources as $resource) {
            $resourceIds[] = $resource->getResourceId();
        }
        return implode(Constants::DBKEYS_SPLIT_CHAR, $resourceIds);
    }

    protected function acquireChannel(Address $address): SocketChannelInterface
    {
        return $this->socketManager->acquireChannel($address);
    }
}
