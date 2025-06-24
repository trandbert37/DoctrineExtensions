<?php

namespace DoctrineExtensions\Query\Mysql;

use Doctrine\ORM\Query\AST\ArithmeticExpression;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

use function array_map;
use function implode;

/**
 * @author Vas N <phpvas@gmail.com>
 * @author Guven Atbakan <guven@atbakan.com>
 */
class Greatest extends FunctionNode
{
    private $values = [];

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $lexer = $parser->getLexer();

        do {
            $parser->match(TokenType::T_COMMA);
            $this->values[] = $parser->ArithmeticExpression();
        } while ($lexer->lookahead->type !== TokenType::T_CLOSE_PARENTHESIS);

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $values = array_map(static function (ArithmeticExpression $value) use ($sqlWalker): string {
            return $value->dispatch($sqlWalker);
        }, $this->values);

        return 'GREATEST(' . implode(', ', $values) . ')';
    }
}
