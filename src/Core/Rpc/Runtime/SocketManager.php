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

use Hyperf\Engine\Constant as EngineConstant;
use Hyperf\Seata\Core\Rpc\Address;
use Hyperf\Seata\Core\Rpc\Runtime\Swoole\SocketChannel as SwooleSocketChannel;
use Hyperf\Seata\Core\Rpc\Runtime\Swow\SocketChannel as SwowSocketChannel;
use Hyperf\Seata\Discovery\Registry\RegistryFactory;
use Hyperf\Seata\Exception\RuntimeException;
use Hyperf\Seata\Logger\LoggerFactory;
use Hyperf\Seata\Logger\LoggerInterface;
use Swoole\Coroutine\Socket as SwooleSocket;
use Swow\Socket as SwowSocket;

class SocketManager
{
    public const SWOW = 'Swow';

    public const SWOOLE = 'Swoole';

    protected RegistryFactory $registryFactory;

    protected LoggerInterface $logger;

    protected array $socketChannels = [];

    protected array $processorTable = [];

    protected array $adapters = [
        self::SWOW => SwooleSocketChannel::class,
        self::SWOOLE => SwowSocketChannel::class,
    ];

    public function __construct(RegistryFactory $registryFactory, LoggerFactory $loggerFactory)
    {
        $this->registryFactory = $registryFactory;
        $this->logger = $loggerFactory->create(static::class);
    }

    public function acquireChannel(Address $address): SocketChannelInterface
    {
        $key = (string) $address;
        if (! isset($this->socketChannels[$key])) {
            $socketChannel = $this->createSocketChannel($this->createSocket($address), $address);
            $this->socketChannels[$key] = $socketChannel;
        }
        return $this->socketChannels[$key];
    }

    public function reconnect(string $transactionServiceGroup, string $target)
    {
        $availList = $this->getAvailServerList($transactionServiceGroup);
        if (empty($availList)) {
            $this->logger->error('No available server to connect');
            return;
        }
        foreach ($availList as $address) {
            try {
                $address->setTarget($target);
                $this->acquireChannel($address);
            } catch (\Throwable $exception) {
                $this->logger->error(sprintf('Cannot connect to %s cause: %s', (string) $address, $exception->getMessage()));
            }
        }
    }

    public function getAvailServerList(string $transactionServiceGroup): array
    {
        $availList = [];
        $availInetSocketAddressList = $this->registryFactory->getInstance()->lookup($transactionServiceGroup);
        if (! empty($availInetSocketAddressList)) {
            foreach ($availInetSocketAddressList as $address) {
                $availList[] = $address;
            }
        }
        return $availList;
    }

    protected function createSocket(Address $address): SwowSocket|SwooleSocket
    {
        if (EngineConstant::ENGINE == self::SWOOLE) {
            $socket = new SwooleSocket(AF_INET, SOCK_STREAM, 0);
        } elseif (EngineConstant::ENGINE == self::SWOW) {
            $socket = new SwowSocket(SwowSocket::TYPE_TCP);
        } else {
            throw new RuntimeException('Invalid runtime engine');
        }

        $socket->connect($address->getHost(), $address->getPort(), 100);
        return $socket;
    }

    protected function createSocketChannel(SwooleSocket|SwowSocket $socket, Address $address): SocketChannelInterface
    {
        if (EngineConstant::ENGINE == self::SWOOLE) {
            return new SwooleSocketChannel($socket, $address);
        }
        if (EngineConstant::ENGINE == self::SWOW) {
            return new SwowSocketChannel($socket, $address);
        }
        throw new RuntimeException('Invalid runtime engine');
    }
}
