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

class Stage extends AbstractStrategy implements ParserStrategyInterface
{
    public function parseResults(Crawler $crawler)
    {
        return $this->parseResultsFromExpression($crawler, 'table.bordertop tr');
    }
}
