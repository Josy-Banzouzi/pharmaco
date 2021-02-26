<?php


namespace App\Extensions\Doctrine;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class MatchAgainst extends FunctionNode
{
    /**
     * @var array
     */
    protected $pathExp = null;
    /**
     * @var
     */
    protected $against = null;

    /**
     * @var bool
     */
    protected $booleanMode = false;
    /**
     * @var bool
     */
    protected $queryExpansion = false;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $fields = [];
        foreach ($this->pathExp as $pathExp){
            $fields[] = $pathExp->dispatch($sqlWalker);
        }
        $against = $sqlWalker->walkStringPrimary($this->against)
            .($this->booleanMode ? 'IN BOOLEAN MODE' : '')
            .($this->queryExpansion? 'WITH QUERY EXPANSION' : '');

        return sprintf('MATCH (%s) AGAINST (%s)', implode(',', $fields), $against);
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->pathExp = [];
        $this->pathExp[] = $parser->StateFieldPathExpression();

        $lexer = $parser->getLexer();
        while ($lexer->isNextToken(Lexer::T_COMMA)){
            $parser->match(Lexer::T_COMMA);
            $this->pathExp[] = $parser->StateFieldPathExpression();
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);

        if(strtolower($lexer->lookahead['value']) === 'against'){
            $parser->syntaxError('against');
        }
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->againts = $parser->StringPrimary();
        if (strtolower($lexer->lookahead['value']) === 'boolean'){
            $parser->match(Lexer::T_IDENTIFIER);
            $this->booleanMode = true;
        }

        if (strtolower($lexer->lookahead['value']) === 'expand'){
            $parser->match(Lexer::T_IDENTIFIER);
            $this->queryExpansion = true;
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);


    }
}