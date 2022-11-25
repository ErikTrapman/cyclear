<?php declare(strict_types=1);

namespace App\CQRanking;

use App\CQRanking\Parser\Crawler\CrawlerManager;
use App\Entity\Seizoen;
use App\Entity\Uitslag;
use App\Entity\Wedstrijd;
use App\EntityManager\UitslagManager;
use App\Form\DataTransformer\RennerNameToRennerIdTransformer;
use App\Repository\PloegRepository;
use App\Repository\WedstrijdRepository;
use Psr\Log\LoggerInterface;

class CQAutomaticResultsResolver
{
    public function __construct(
        private readonly RaceCategoryMatcher $categoryMatcher,
        private readonly UitslagManager $uitslagManager,
        private readonly CrawlerManager $crawlerManager,
        private readonly RennerNameToRennerIdTransformer $transformer,
        private readonly LoggerInterface $logger,
        private readonly WedstrijdRepository $wedstrijdRepository,
        private readonly PloegRepository $ploegRepository,
    ) {
    }

    public function resolve(array $races, Seizoen $seizoen, \DateTime $start, \DateTime $end, int $max = 1): array
    {
        $start = clone $start;
        $start->setTime(0, 0, 0);
        $end = clone $end;
        $end->setTime(0, 0, 0);
        $ret = [];
        foreach ($races as $race) {
            // we simply use the url as external identifier
            $wedstrijd = $this->wedstrijdRepository->findOneBy(['externalIdentifier' => $race->url]);
            if ($wedstrijd) {
                continue;
            }
            if ($race->date < $start || $race->date > $end) {
                continue;
            }
            // we skip Team Time Trials. Just try this...
            if (str_contains($race->name, 'T.T.T') || str_contains($race->name, 'TTT')) {
                continue;
            }
            $wedstrijd = new Wedstrijd();
            $date = clone $race->date;
            $date->setTime(0, 0, 0);
            $wedstrijd->setDatum($date);
            $wedstrijd->setExternalIdentifier($race->url);
            $wedstrijd->setNaam($race->name);
            $wedstrijd->setSeizoen($seizoen);

            $type = $this->categoryMatcher->getUitslagTypeAccordingToCategory($race->category);
            if (null === $type) {
                $this->logger->notice('Unable to resolve uitslagtype from category `' . $race->category . '``');
                continue;
            }
            $wedstrijd->setGeneralClassification($type->isGeneralClassification());
            $wedstrijdRefDate = null;

            // TODO parametrize CQ-urls!
            try {
                $needsRefStage = $this->categoryMatcher->needsRefStage($wedstrijd);
                if ($needsRefStage) {
                    $refStage = $this->categoryMatcher->getRefStage($wedstrijd);
                    $wedstrijdRefDate = $refStage->getDatum();
                }
                $uitslagen = $this->uitslagManager->prepareUitslagen(
                    $type,
                    $this->crawlerManager->getCrawler('https://cqranking.com/men/asp/gen/' . $race->url),
                    $wedstrijd,
                    $seizoen,
                    $wedstrijdRefDate);
                if (count($uitslagen) < $type->getMaxResults()) {
                    $this->logger->notice($race->url . ' has not enough content yet');
                    continue;
                }
            } catch (\Throwable $throwable) {
                $this->logger->error($race->url . ' / ' . $race->name . ' has error: ' . $throwable->getMessage());
                continue;
            }

            foreach ($uitslagen as $uitslagRow) {
                $uitslag = new Uitslag();
                $uitslag->setWedstrijd($wedstrijd);
                $uitslag->setPloeg($uitslagRow['ploeg'] ? $this->ploegRepository->find($uitslagRow['ploeg']) : null);
                $uitslag->setRennerPunten($uitslagRow['rennerPunten']);
                $uitslag->setPloegPunten($uitslagRow['ploegPunten']);
                $uitslag->setPositie($uitslagRow['positie']);
                $uitslag->setRenner($this->transformer->reverseTransform($uitslagRow['renner']));
                $wedstrijd->addUitslag($uitslag);
            }
            $ret[] = $wedstrijd;
            if (count($ret) == $max) {
                break;
            }
        }
        return $ret;
    }
}
