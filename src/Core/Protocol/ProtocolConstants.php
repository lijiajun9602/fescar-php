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
namespace Hyperf\Seata\Core\Protocol;

use Hyperf\Seata\Core\Codec\CodecType;
use Hyperf\Seata\Core\Compressor\CompressorType;

class ProtocolConstants
{
    /**
     * Magic code.
     */
    public const MAGIC_CODE_BYTES = [0xDA, 0xDA];

    /**
     * Protocol version.
     */
    public const VERSION = 1;

    /**
     * Max frame length.
     */
    public const MAX_FRAME_LENGTH = 8 * 1024 * 1024;

    /**
     * HEAD_LENGTH of protocol v1.
     */
    public const V1_HEAD_LENGTH = 16;

    /**
     * Message type: Request.
     */
    public const MSGTYPE_RESQUEST_SYNC = 0;

    /**
     * Message type: Response.
     */
    public const MSGTYPE_RESPONSE = 1;

    /**
     * Message type: Request which no need response.
     */
    public const MSGTYPE_RESQUEST_ONEWAY = 2;

    /**
     * Message type: Heartbeat Request.
     */
    public const MSGTYPE_HEARTBEAT_REQUEST = 3;

    /**
     * Message type: Heartbeat Response.
     */
    public const MSGTYPE_HEARTBEAT_RESPONSE = 4;

    // public const byte MSGTYPE_NEGOTIATOR_REQUEST = 5;
    // public const byte MSGTYPE_NEGOTIATOR_RESPONSE = 6;

    /**
     * Configured codec by user, default is SEATA.
     *
     * @see CodecType#SEATA
     * @todo 允许从配置文件中读取
     */
    public const CONFIGURED_CODEC = CodecType::SEATA;

    /**
     * Configured compressor by user, default is NONE.
     *
     * @see CompressorType#NONE
     * @todo 允许从配置文件中读取
     */
    public const CONFIGURED_COMPRESSOR = CompressorType::NONE;
}
