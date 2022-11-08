<?php declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\Periode;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\PeriodeRepository;
use App\Repository\RennerRepository;
use App\Repository\TransferRepository;
use App\Validator\Constraints\UserTransfer;
use App\Validator\Constraints\UserTransferFixedValidator;
use App\Validator\Constraints\UserTransferValidator;
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
        $repo = $this->getMockBuilder(PeriodeRepository::class)->disableOriginalConstructor()->getMock();
        $this->em->expects($this->at(0))->method('getRepository')->with(Periode::class)->will($this->returnValue($repo));
        $this->periode = new Periode();
        $this->periode->setStart(new \DateTime('2013-05-01'));
        $this->periode->setEind(new \DateTime('2013-07-31'));
        $repo->expects($this->once())->method('getCurrentPeriode')->will($this->returnValue($this->periode));
        $this->initRepos();
        return $t;
    }

    private function initRepos(): void
    {
        $this->transferRepo = $this->getMockBuilder(TransferRepository::class)->disableOriginalConstructor()->getMock();
        $this->em->expects($this->at(1))->method('getRepository')->with(Transfer::class)->will($this->returnValue($this->transferRepo));
        $this->rennerRepo = $this->getMockBuilder(RennerRepository::class)->disableOriginalConstructor()->getMock();
        $this->em->expects($this->at(2))->method('getRepository')->with(Renner::class)->will($this->returnValue($this->rennerRepo));
        $this->rennerRepo->expects($this->once())->method('getPloeg')->will($this->returnValue(null));
    }

    public function testNoRider(): void
    {
        $this->context->expects($this->at(0))->method('addViolation')->with($this->equalTo('Onbekende renner opgegeven.'));
        $t = $this->getValidTransfer();
        $t->setRennerIn(null);
        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidSeason(): void
    {
        $this->context->expects($this->at(0))->method('addViolation')->with($this->equalTo('Het seizoen 1 is gesloten.'));
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setClosed(true);
        $t->getSeizoen()->setIdentifier('1');
        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidPeriodBeforeOpening(): void
    {
        $this->context->expects($this->at(0))->method('addViolation')->with($this->equalTo('De huidige periode staat nog geen transfers toe'));
        $t = $this->getValidTransfer();
        $this->periode->setStart(new \DateTime('2013-05-02'));
        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidPeriodAfterClosing(): void
    {
        $this->context->expects($this->at(0))->method('addViolation')->with($this->equalTo('De huidige periode staat geen transfers meer toe'));
        $t = $this->getValidTransfer();
        $this->periode->setEind(new \DateTime('2013-04-30'));
        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidMaxTransfers(): void
    {
        $this->context->expects($this->once())->method('addViolation')->with($this->stringContains('Je zit op het maximaal aantal transfers'));

        $t = $this->getValidTransfer();
        $this->transferRepo->expects($this->once())->method('getTransferCountForUserTransfer')->will($this->returnValue(5));
        $this->validator->validate($t, new UserTransfer());
    }

    public function testValidTransferOnLastDayOfPeriod(): void
    {
        $this->context->expects($this->never())->method('addViolation');
        $t = $this->getValidTransfer();
        $d = new \DateTime('2013-07-31');
        $d->setTime(23, 59, 59);
        $t->setDatum($d);
        $this->periode->setTransfers(1);
        $this->transferRepo->expects($this->once())->method('getTransferCountForUserTransfer')->will($this->returnValue(0));
        $this->validator->validate($t, new UserTransfer());
    }

    public function testValidTransferOnFirstDayOfPeriod(): void
    {
        $this->context->expects($this->never())->method('addViolation');
        $t = $this->getValidTransfer();
        $d = new \DateTime('2013-05-01');
        $d->setTime(0, 0, 1);
        $t->setDatum($d);
        $this->periode->setTransfers(1);
        $this->transferRepo->expects($this->once())->method('getTransferCountForUserTransfer')->will($this->returnValue(0));
        $this->validator->validate($t, new UserTransfer());
    }
}
