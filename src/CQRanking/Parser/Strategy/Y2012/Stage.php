<?php declare(strict_types=1);

namespace App\CQRanking\Parser\Strategy\Y2012;

use App\CQRanking\Parser\Strategy\AbstractStrategy;
use App\CQRanking\Parser\Strategy\ParserStrategyInterface;
use Symfony\Component\DomCrawler\Crawler;

class Stage extends AbstractStrategy implements ParserStrategyInterface
{
    public function parseResults(Crawler $crawler)
    {
        return $this->parseResultsFromExpression($crawler, 'table.bordertop tr');
    }
}
