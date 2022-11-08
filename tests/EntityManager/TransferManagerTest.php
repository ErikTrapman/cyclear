<?php declare(strict_types=1);



namespace App\Tests\EntityManager;

use App\Entity\Contract;
use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\EntityManager\TransferManager;
use DateTime;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransferManagerTest extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \App\EntityManager\TransferManager
     */
    private $transferManager;

    private $ploegRepo;

    private $rennerRepo;

    private $contractRepo;

    private $transferRepo;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->databaseTool = self::$container->get(DatabaseToolCollection::class)->get();

        $this->databaseTool->loadFixtures([
            'App\Tests\Fixtures\LoadPloegData',
            'App\Tests\Fixtures\LoadRennerData',
            'App\Tests\Fixtures\LoadSeizoenData',
        ]);

        $this->em = self::$container->get('doctrine')->getManager();
        $this->transferManager = self::$container->get(TransferManager::class);
        $this->ploegRepo = $this->em->getRepository(Ploeg::class);
        $this->rennerRepo = $this->em->getRepository(Renner::class);
        $this->contractRepo = $this->em->getRepository(Contract::class);
        $this->transferRepo = $this->em->getRepository(Transfer::class);
    }

    private function getSeizoen(): Seizoen|null
    {
        return $this->em->getRepository(Seizoen::class)->find(1);
    }

    private function createTransfer($renner, $ploeg, int $type = Transfer::DRAFTTRANSFER): Transfer
    {
        $t = new Transfer();
        $t->setDatum(new DateTime());
        $t->setPloegNaar($ploeg);
        $t->setRenner($renner);
        $t->setSeizoen($this->getSeizoen());
        $t->setTransferType($type);
        return $t;
    }

    public function testDraftTransferAndContractCreation(): void
    {
        $em = $this->em;

        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $t = $this->createTransfer($r1, $p1);
        $em->persist($t);

        $this->transferManager->doDraftTransfer($t);

        $em->flush();

        $c = $this->contractRepo->findAll()[0];
        $this->assertEquals($t->getDatum()->format('dmyhi'), $c->getStart()->format('dmyhi'));
        $this->assertEquals($c->getRenner(), $r1);
        $this->assertEquals(null, $c->getEind());
    }

    /**
     * @dataProvider revertExchangeTransfersDataProvider
     *
     * @param mixed $revertId1
     * @param mixed $revertId2
     */
    public function testRevertExchangeTransfer($revertId1, $revertId2): void
    {
        $p1 = $this->ploegRepo->findOneByAfkorting('pl1');
        $r1 = $this->rennerRepo->findOneByNaam('RENNER Voornaam');

        $p2 = $this->ploegRepo->findOneByAfkorting('pl2');
        $r2 = $this->rennerRepo->findOneByNaam('RENNER2 Voornaam');

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1);
        $draftTransfer2 = $this->createTransfer($r2, $p2);
        $this->em->persist($draftTransfer1);
        $this->em->persist($draftTransfer2);
        $this->em->flush();

        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->transferManager->doDraftTransfer($draftTransfer2);
        $this->em->flush();

        $this->transferManager->doExchangeTransfer($r1, $r2, new DateTime(), $seizoen);
        $this->em->flush();

        // find the last transfer for rider 1
        $renner1PloegNaarTransfer = $this->transferRepo->find($revertId1);
        $this->transferManager->revertTransfer($renner1PloegNaarTransfer);
        $this->em->flush();

        $renner2PloegNaarTransfer = $this->transferRepo->find($revertId2);
        $this->transferManager->revertTransfer($renner2PloegNaarTransfer);
        $this->em->flush();

        $contracts = $this->contractRepo->findAll();
        $transfers = $this->transferRepo->findAll();
        $this->assertEquals(2, count($transfers));
        $this->assertEquals(2, count($contracts));
        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r1, $seizoen));
        $this->assertEquals($p2, $this->rennerRepo->getPloeg($r2, $seizoen));
        $this->assertEquals(1, $contracts[0]->getId());
        $this->assertEquals(2, $contracts[1]->getId());
        $this->assertEquals(1, $transfers[0]->getId());
        $this->assertEquals(2, $transfers[1]->getId());
    }

    /**
     * @return int[][]
     *
     * @psalm-return array{0: array{0: 6, 1: 4}, 1: array{0: 5, 1: 3}, 2: array{0: 3, 1: 5}, 3: array{0: 4, 1: 6}}
     */
    public function revertExchangeTransfersDataProvider(): array
    {
        return [
            [6, 4],
            [5, 3],
            [3, 5],
            [4, 6],
        ];
    }

    /**
     * @dataProvider revertUserTransfersDataProvider
     *
     * @param mixed $transferIdToRevert
     */
    public function testRevertUserTransfer($transferIdToRevert): void
    {
        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $r2 = $this->rennerRepo->find(2);

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1, Transfer::DRAFTTRANSFER);
        $this->em->persist($draftTransfer1);
        $this->em->flush();

        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->em->flush();

        $this->transferManager->doUserTransfer($p1, $r1, $r2, $seizoen);
        $this->em->flush();

        $revertTransfer = $this->transferRepo->find($transferIdToRevert);
        $this->transferManager->revertTransfer($revertTransfer);
        $this->em->flush();

        $transfers = $this->transferRepo->findAll();
        $contracts = $this->contractRepo->findAll();
        $this->assertEquals(1, count($transfers));
        $this->assertEquals(1, count($contracts));
        $this->assertEquals(1, $transfers[0]->getId());
        $this->assertEquals(1, $contracts[0]->getId());
        $this->assertEquals(null, $contracts[0]->getEind());
        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r1, $seizoen));
        $this->assertEquals(null, $this->rennerRepo->getPloeg($r2, $seizoen));
    }

    /**
     * @return int[][]
     *
     * @psalm-return array{0: array{0: 3}, 1: array{0: 2}}
     */
    public function revertUserTransfersDataProvider(): array
    {
        return [
            [3],
            [2],
        ];
    }

    public function testRevertDraftTransfer(): void
    {
        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1, Transfer::DRAFTTRANSFER);
        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->em->flush();

        $this->transferManager->revertTransfer($this->transferRepo->find(1));
        $this->em->flush();

        $this->assertEquals(0, count($this->transferRepo->findAll()));
        $this->assertEquals(0, count($this->contractRepo->findAll()));
        $this->assertEquals(null, $this->rennerRepo->getPloeg($r1, $seizoen));
    }

    public function testDraftTransfer(): void
    {
        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $draftTransfer1 = $this->createTransfer($r1, $p1, Transfer::DRAFTTRANSFER);
        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->em->flush();
        $this->assertEquals(1, count($this->transferRepo->findAll()));
        $this->assertEquals(1, count($this->contractRepo->findAll()));

        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r1, $this->getSeizoen()));
    }

    public function testUserTransfer(): void
    {
        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $r2 = $this->rennerRepo->find(2);

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1, Transfer::DRAFTTRANSFER);
        $this->em->persist($draftTransfer1);
        $this->em->flush();

        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->em->flush();

        $this->transferManager->doUserTransfer($p1, $r1, $r2, $seizoen);
        $this->em->flush();

        $this->assertEquals(3, count($this->transferRepo->findAll()));
        $this->assertEquals(2, count($this->contractRepo->findAll()));
        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r2, $this->getSeizoen()));
        $this->assertEquals(null, $this->rennerRepo->getPloeg($r1, $this->getSeizoen()));
    }

    public function testExchangeTransfer(): void
    {
        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $p2 = $this->ploegRepo->find(2);
        $r2 = $this->rennerRepo->find(2);

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1);
        $draftTransfer2 = $this->createTransfer($r2, $p2);
        $this->em->persist($draftTransfer1);
        $this->em->persist($draftTransfer2);
        $this->em->flush();

        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->transferManager->doDraftTransfer($draftTransfer2);
        $this->em->flush();

        $this->transferManager->doExchangeTransfer($r1, $r2, new DateTime(), $seizoen);
        $this->em->flush();

        $this->assertEquals(6, count($this->transferRepo->findAll()));
        $this->assertEquals(4, count($this->contractRepo->findAll()));

        $this->assertEquals($p2, $this->rennerRepo->getPloeg($r1, $seizoen));
        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r2, $seizoen));
    }

//    public function testReleaseTransfersAreCreated()
//    {
//
//    }
//
//    public function testInversionTransfersAreAsExpected()
//    {
//
//    }
}
