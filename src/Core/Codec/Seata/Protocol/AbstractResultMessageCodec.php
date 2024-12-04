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
namespace Hyperf\Seata\Core\Codec\Seata\Protocol;

use Hyperf\Seata\Core\Protocol\AbstractMessage;
use Hyperf\Seata\Core\Protocol\AbstractResultMessage;
use Hyperf\Seata\Core\Protocol\Codec\Strings;
use Hyperf\Seata\Utils\Buffer\ByteBuffer;

abstract class AbstractResultMessageCodec extends AbstractMessageCodec
{
    public function getMessageClassType(): string
    {
        return AbstractResultMessage::class;
    }

    public function encode(AbstractMessage $message, ByteBuffer $buffer): ByteBuffer
    {
        if (! $message instanceof AbstractResultMessage) {
            throw new \InvalidArgumentException('Invalid message');
        }
        $resultCode = $message->getResultCode();
        $resultMessage = new Strings($message->getMessage());

        $buffer->putUByte($resultCode);
        if ($resultCode === 0) {
            if ($resultMessage !== null) {
                if ($resultMessage->length() > 128) {
                    $msg = $resultMessage->substring(0, 128);
                } else {
                    $msg = $resultMessage;
                }
                $bytes = $msg->getBytes();
                if (count($bytes) > 400 && $resultMessage->length() > 64) {
                    $msg = $resultMessage->substring(0, 64);
                    $bytes = $msg->getBytes();
                }
                $bytesLength = count($bytes);
                $buffer->putUShort($bytesLength);
                if ($bytesLength > 0) {
                    foreach ($bytes as $byte) {
                        $buffer->putUInt8($byte);
                    }
                }
            } else {
                $buffer->putUShort(0);
            }
        }
        return $buffer;
    }

    public function decode(AbstractMessage $message, ByteBuffer $buffer): AbstractMessage
    {
        if (! $message instanceof AbstractResultMessage) {
            throw new \InvalidArgumentException('Invalid message');
        }
        $resultCode = $buffer->readUByte();
        if ($resultCode == 0) {
            $length = $buffer->readUShort();
            if ($length > 0) {
                $message->setMessage($buffer->readString($length));
            }
        }
        return $message;
    }
}
