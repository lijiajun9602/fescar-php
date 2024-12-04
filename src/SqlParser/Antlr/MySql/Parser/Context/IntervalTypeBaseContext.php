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

class IntervalTypeBaseContext extends ParserRuleContext
{
    public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
    {
        parent::__construct($parent, $invokingState);
    }

    public function getRuleIndex(): int
    {
        return MySqlParser::RULE_intervalTypeBase;
    }

    public function QUARTER(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::QUARTER, 0);
    }

    public function MONTH(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::MONTH, 0);
    }

    public function DAY(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::DAY, 0);
    }

    public function HOUR(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::HOUR, 0);
    }

    public function MINUTE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::MINUTE, 0);
    }

    public function WEEK(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::WEEK, 0);
    }

    public function SECOND(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::SECOND, 0);
    }

    public function MICROSECOND(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::MICROSECOND, 0);
    }

    public function enterRule(ParseTreeListener $listener): void
    {
        if ($listener instanceof MySqlParserListener) {
            $listener->enterIntervalTypeBase($this);
        }
    }

    public function exitRule(ParseTreeListener $listener): void
    {
        if ($listener instanceof MySqlParserListener) {
            $listener->exitIntervalTypeBase($this);
        }
    }
}
