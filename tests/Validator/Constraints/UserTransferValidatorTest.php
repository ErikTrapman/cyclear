<?php declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Repository\RennerRepository;
use App\Repository\TransferRepository;
use App\Validator\Constraints\UserTransfer;
use App\Validator\Constraints\UserTransferFixedValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserTransferValidatorTest extends WebTestCase
{
    private ExecutionContextInterface $context;

    private ConstraintValidatorInterface $validator;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->disableOriginalConstructor()->getMock();
        $this->validator = new UserTransferFixedValidator($this->em, 50);
        $this->validator->initialize($this->context);
    }

    private function getValidTransfer(): \App\Form\Entity\UserTransfer
    {
        $t = new \App\Form\Entity\UserTransfer();
        $s = new Seizoen();
        $t->setRennerIn(new Renner());
        $t->setRennerUit(new Renner());
        $t->setSeizoen($s);
        $datum = new \DateTime('2013-05-01');
        $datum->setTime(12, 0, 0);
        $t->setDatum($datum);
        return $t;
    }

    public function testInvalidSeason(): void
    {
        $this->context->expects($this->once())->method('addViolation')->with($this->equalTo('Het seizoen 1 is gesloten.'));
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setClosed(true);
        $t->getSeizoen()->setIdentifier('1');
        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidSeasonBeforeOpening(): void
    {
        $this->context->expects($this->once())->method('addViolation')->with($this->equalTo('Het huidige seizoen staat nog geen transfers toe.'));
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-05-21'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-11-21'));

        $this->transferRepo = $this->getMockBuilder(TransferRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo = $this->getMockBuilder(RennerRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo->method('getPloeg')->will($this->returnValue(null));
        $this->em->expects($this->exactly(2))->method('getRepository')->willReturnOnConsecutiveCalls($this->rennerRepo, $this->transferRepo);

        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidSeasonAfterClosing(): void
    {
        $this->context->expects($this->once())->method('addViolation')->with($this->equalTo('Het huidige seizoen staat geen transfers meer toe.'));
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-04-30'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-04-30'));

        $this->transferRepo = $this->getMockBuilder(TransferRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo = $this->getMockBuilder(RennerRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo->method('getPloeg')->will($this->returnValue(null));
        $this->em->expects($this->exactly(2))->method('getRepository')->willReturnOnConsecutiveCalls($this->rennerRepo, $this->transferRepo);

        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidMaxTransfers(): void
    {
        $this->context->expects($this->once())->method('addViolation')->with($this->stringContains('Je zit op het maximaal aantal transfers'));

        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-01-01'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-11-01'));

        $this->transferRepo = $this->getMockBuilder(TransferRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo = $this->getMockBuilder(RennerRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo->method('getPloeg')->will($this->returnValue(null));

        $this->em->expects($this->exactly(2))->method('getRepository')->willReturnOnConsecutiveCalls($this->rennerRepo, $this->transferRepo);

        $this->transferRepo->method('getTransferCountForUserTransfer')->will($this->returnValue(50));
        $this->validator->validate($t, new UserTransfer());
    }

    public function testValidTransferOnLastDayOfSeason(): void
    {
        $this->context->expects($this->never())->method('addViolation');
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-01-01'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-05-01'));

        $this->transferRepo = $this->getMockBuilder(TransferRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo = $this->getMockBuilder(RennerRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo->method('getPloeg')->will($this->returnValue(null));

        $this->em->expects($this->exactly(2))->method('getRepository')->willReturnOnConsecutiveCalls($this->rennerRepo, $this->transferRepo);

        $this->transferRepo->expects($this->once())->method('getTransferCountForUserTransfer')->will($this->returnValue(0));
        $this->validator->validate($t, new UserTransfer());
    }

    public function testValidTransferOnFirstDayOfPeriod(): void
    {
        $this->context->expects($this->never())->method('addViolation');
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-05-01'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-11-01'));

        $this->transferRepo = $this->getMockBuilder(TransferRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo = $this->getMockBuilder(RennerRepository::class)->disableOriginalConstructor()->getMock();
        $this->rennerRepo->method('getPloeg')->will($this->returnValue(null));
        $this->em->expects($this->exactly(2))->method('getRepository')->willReturnOnConsecutiveCalls($this->rennerRepo, $this->transferRepo);

        $this->transferRepo->expects($this->once())->method('getTransferCountForUserTransfer')->will($this->returnValue(0));
        $this->validator->validate($t, new UserTransfer());
    }
}
