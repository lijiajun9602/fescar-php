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
namespace Hyperf\Seata\SqlParser\Antlr\MySql;

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\Tree\ParseTreeWalker;
use Hyperf\Seata\SqlParser\Antlr\MySql\Listener\UpdateSpecificationSqlListener;
use Hyperf\Seata\SqlParser\Antlr\MySql\Parser\MySqlLexer;
use Hyperf\Seata\SqlParser\Antlr\MySql\Parser\MySqlParser;
use Hyperf\Seata\SqlParser\Antlr\MySql\Stream\ANTLRNoCaseStringStream;
use Hyperf\Seata\SqlParser\Antlr\MySqlContext;
use Hyperf\Seata\SqlParser\Core\ParametersHolder;
use Hyperf\Seata\SqlParser\Core\SQLType;
use Hyperf\Seata\SqlParser\Core\SQLUpdateRecognizer;

class AntlrMySQLUpdateRecognizer implements SQLUpdateRecognizer
{
    private MySqlContext $sqlContext;

    public function __construct(string $sql)
    {
        $mySqlLexer = new MySqlLexer(new ANTLRNoCaseStringStream($sql));

        $commonTokenStream = new CommonTokenStream($mySqlLexer);

        $parser2 = new MySqlParser($commonTokenStream);

        $root = $parser2->root();

        $walker2 = new ParseTreeWalker();

        $this->sqlContext = new MySqlContext();
        $this->sqlContext->setOriginalSQL($sql);
        $walker2->walk(new UpdateSpecificationSqlListener($this->sqlContext), $root);
    }

    public function getSQLType(): SQLType
    {
        return new SQLType(SQLType::UPDATE);
    }

    public function getTableAlias(): string
    {
        return $this->sqlContext->getTableAlias();
    }

    public function getTableName(): string
    {
        return $this->sqlContext->getTableName();
    }

    public function getOriginalSQL(): string
    {
        return $this->sqlContext->getOriginalSQL();
    }

    public function getUpdateColumns(): array
    {
        return $this->sqlContext->getUpdateFoColumnNames();
    }

    public function getUpdateValues(): array
    {
        $updateForValues = $this->sqlContext->getUpdateForValues();
        if (empty($updateForValues)) {
            return [];
        }

        return $updateForValues;
    }

    public function getWhereConditionWithParametersHolderAndList(ParametersHolder $parametersHolder, array $paramAppenderList): string
    {
        return $this->sqlContext->getWhereCondition();
    }

    public function getWhereCondition(): string
    {
        return $this->sqlContext->getWhereCondition();
    }

    public function getLimit(ParametersHolder $parametersHolder, array $paramAppenderList): string
    {
        // TODO: Implement getLimit() method.
    }

    public function getOrderBy(): string
    {
        // TODO: Implement getOrderBy() method.
    }
}
