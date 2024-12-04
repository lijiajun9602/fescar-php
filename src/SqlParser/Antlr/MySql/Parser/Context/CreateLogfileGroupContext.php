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

class CreateLogfileGroupContext extends ParserRuleContext
{
    /**
     * @var null|Token
     */
    public $undoFile;

    /**
     * @var null|Token
     */
    public $comment;

    /**
     * @var null|FileSizeLiteralContext
     */
    public $initSize;

    /**
     * @var null|FileSizeLiteralContext
     */
    public $undoSize;

    /**
     * @var null|FileSizeLiteralContext
     */
    public $redoSize;

    public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
    {
        parent::__construct($parent, $invokingState);
    }

    public function getRuleIndex(): int
    {
        return MySqlParser::RULE_createLogfileGroup;
    }

    public function CREATE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::CREATE, 0);
    }

    public function LOGFILE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::LOGFILE, 0);
    }

    public function GROUP(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::GROUP, 0);
    }

    /**
     * @return null|array<UidContext>|UidContext
     */
    public function uid(?int $index = null)
    {
        if ($index === null) {
            return $this->getTypedRuleContexts(UidContext::class);
        }

        return $this->getTypedRuleContext(UidContext::class, $index);
    }

    public function ADD(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::ADD, 0);
    }

    public function UNDOFILE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::UNDOFILE, 0);
    }

    public function ENGINE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::ENGINE, 0);
    }

    public function engineName(): ?EngineNameContext
    {
        return $this->getTypedRuleContext(EngineNameContext::class, 0);
    }

    /**
     * @return null|array<TerminalNode>|TerminalNode
     */
    public function STRING_LITERAL(?int $index = null)
    {
        if ($index === null) {
            return $this->getTokens(MySqlParser::STRING_LITERAL);
        }

        return $this->getToken(MySqlParser::STRING_LITERAL, $index);
    }

    public function INITIAL_SIZE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::INITIAL_SIZE, 0);
    }

    public function UNDO_BUFFER_SIZE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::UNDO_BUFFER_SIZE, 0);
    }

    public function REDO_BUFFER_SIZE(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::REDO_BUFFER_SIZE, 0);
    }

    public function NODEGROUP(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::NODEGROUP, 0);
    }

    public function WAIT(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::WAIT, 0);
    }

    public function COMMENT(): ?TerminalNode
    {
        return $this->getToken(MySqlParser::COMMENT, 0);
    }

    /**
     * @return null|array<TerminalNode>|TerminalNode
     */
    public function EQUAL_SYMBOL(?int $index = null)
    {
        if ($index === null) {
            return $this->getTokens(MySqlParser::EQUAL_SYMBOL);
        }

        return $this->getToken(MySqlParser::EQUAL_SYMBOL, $index);
    }

    /**
     * @return null|array<FileSizeLiteralContext>|FileSizeLiteralContext
     */
    public function fileSizeLiteral(?int $index = null)
    {
        if ($index === null) {
            return $this->getTypedRuleContexts(FileSizeLiteralContext::class);
        }

        return $this->getTypedRuleContext(FileSizeLiteralContext::class, $index);
    }

    public function enterRule(ParseTreeListener $listener): void
    {
        if ($listener instanceof MySqlParserListener) {
            $listener->enterCreateLogfileGroup($this);
        }
    }

    public function exitRule(ParseTreeListener $listener): void
    {
        if ($listener instanceof MySqlParserListener) {
            $listener->exitCreateLogfileGroup($this);
        }
    }
}
