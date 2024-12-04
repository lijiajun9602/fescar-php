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
namespace Hyperf\Seata\SqlParser\Antlr\MySql\Parser\Context;

use Antlr\Antlr4\Runtime\ParserRuleContext;
use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;
use Antlr\Antlr4\Runtime\Tree\TerminalNode;
use Hyperf\Seata\SqlParser\Antlr\MySql\Listener\MySqlParserListener;
use Hyperf\Seata\SqlParser\Antlr\MySql\Parser\MySqlParser;

class EngineNameContext extends ParserRuleContext
{
    public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
    {
        parent::__construct($parent, $invokingState);
    }

    public function getRuleIndex(): int
    {
        return MySqlParser::RULE_engineName;
    }

    public function ARCHIVE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::ARCHIVE, 0);
    }

    public function BLACKHOLE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::BLACKHOLE, 0);
    }

    public function CSV(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::CSV, 0);
    }

    public function FEDERATED(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::FEDERATED, 0);
    }

    public function INNODB(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::INNODB, 0);
    }

    public function MEMORY(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::MEMORY, 0);
    }

    public function MRG_MYISAM(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::MRG_MYISAM, 0);
    }

    public function MYISAM(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::MYISAM, 0);
    }

    public function NDB(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::NDB, 0);
    }

    public function NDBCLUSTER(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::NDBCLUSTER, 0);
    }

    public function PERFORMANCE_SCHEMA(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::PERFORMANCE_SCHEMA, 0);
    }

    public function TOKUDB(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::TOKUDB, 0);
    }

    public function ID(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::ID, 0);
    }

    public function STRING_LITERAL(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::STRING_LITERAL, 0);
    }

    public function REVERSE_QUOTE_ID(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::REVERSE_QUOTE_ID, 0);
    }

    public function CONNECT(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::CONNECT, 0);
    }

    public function enterRule(ParseTreeListener $listener): void
    {
        if ($listener instanceof MySqlParserListener) {
            $listener->enterEngineName($this);
        }
    }

    public function exitRule(ParseTreeListener $listener): void
    {
        if ($listener instanceof MySqlParserListener) {
            $listener->exitEngineName($this);
        }
    }
}
