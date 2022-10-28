<?php declare(strict_types=1);

/*
 * This file is part of the CQ-ranking parser package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\CQRanking\Parser\Strategy\Y2012;

use App\CQRanking\Parser\Strategy\AbstractStrategy;
use App\CQRanking\Parser\Strategy\ParserStrategyInterface;
use Symfony\Component\DomCrawler\Crawler;

class GeneralClassification extends AbstractStrategy implements ParserStrategyInterface
{
    public function parseResults(Crawler $crawler)
    {
        $val1 = $this->parseResultsFromExpression($crawler, 'table.bordertop tr');
        $val2 = $this->parseResultsFromExpression($crawler, 'table.bordersides tr');
        $val3 = $this->parseResultsFromExpression($crawler, 'table.borderbottom tr');
        return array_merge($val1, $val2, $val3);
    }
}
