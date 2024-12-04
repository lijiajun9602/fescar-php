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
use Antlr\Antlr4\Runtime\Token;
use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;
use Antlr\Antlr4\Runtime\Tree\TerminalNode;
use Hyperf\Seata\SqlParser\Antlr\MySql\Listener\MySqlParserListener;
use Hyperf\Seata\SqlParser\Antlr\MySql\Parser\MySqlParser;

class AnalyzeTableContext extends ParserRuleContext
{
    /**
     * @var null|Token
     */
    public $actionOption;

    public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
    {
        parent::__construct($parent, $invokingState);
    }

    public function getRuleIndex(): int
    {
        return MySqlParser::RULE_analyzeTable;
    }

    public function ANALYZE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::ANALYZE, 0);
    }

    public function TABLE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::TABLE, 0);
    }

    public function tables(): ?TablesContext
    {
        return $this->getTypedRuleContext(TablesContext::class, 0);
    }

    public function UPDATE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::UPDATE, 0);
    }

    /**
     * @return null|array<TerminalNode>|TerminalNode
     */
    public function HISTOGRAM(?int $index = null)
    {
        if ($index === null) {
            return $this->getTokens(MySqlParser::HISTOGRAM);
        }

        return $this->getToken(MySqlParser::HISTOGRAM, $index);
    }

    /**
     * @return null|array<TerminalNode>|TerminalNode
     */
    public function ON(?int $index = null)
    {
        if ($index === null) {
            return $this->getTokens(MySqlParser::ON);
        }

        return $this->getToken(MySqlParser::ON, $index);
    }

    /**
     * @return null|array<FullColumnNameContext>|FullColumnNameContext
     */
    public function fullColumnName(?int $index = null)
    {
        if ($index === null) {
            return $this->getTypedRuleContexts(FullColumnNameContext::class);
        }

        return $this->getTypedRuleContext(FullColumnNameContext::class, $index);
    }

    public function DROP(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::DROP, 0);
    }

    public function NO_WRITE_TO_BINLOG(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::NO_WRITE_TO_BINLOG, 0);
    }

    public function LOCAL(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::LOCAL, 0);
    }

    /**
     * @return null|array<TerminalNode>|TerminalNode
     */
    public function COMMA(?int $index = null)
    {
        if ($index === null) {
            return $this->getTokens(MySqlParser::COMMA);
        }

        return $this->getToken(MySqlParser::COMMA, $index);
    }

    public function WITH(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::WITH, 0);
    }

    public function decimalLiteral(): ?DecimalLiteralContext
    {
        return $this->getTypedRuleContext(DecimalLiteralContext::class, 0);
    }

    public function BUCKETS(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::BUCKETS, 0);
    }

    public function enterRule(ParseTreeListener $listener): void
    {
        if ($listener instanceof MySqlParserListener) {
            $listener->enterAnalyzeTable($this);
        }
    }

    public function exitRule(ParseTreeListener $listener): void
    {
        if ($listener instanceof MySqlParserListener) {
            $listener->exitAnalyzeTable($this);
        }
    }
}
