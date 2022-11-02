<?php declare(strict_types=1);

namespace App\CQRanking\Parser\Strategy\Y2013;

use App\CQRanking\Parser\Strategy\AbstractStrategy;
use App\CQRanking\Parser\Strategy\ParserStrategyInterface;
use Symfony\Component\DomCrawler\Crawler;

class Stage extends AbstractStrategy implements ParserStrategyInterface
{
    public function parseResults(Crawler $crawler)
    {
        $res = $this->parseResultsFromExpression($crawler, 'table.border tr');
        if (!empty($res)) {
            return $res;
        }

        $top = $this->parseResultsFromExpression($crawler, 'table.bordertop tr');
        $sides = $this->parseResultsFromExpression($crawler, 'table.bordersides tr');
        $bottom = $this->parseResultsFromExpression($crawler, 'table.borderbottom tr');
        return array_merge($top, $sides, $bottom);
    }
}
