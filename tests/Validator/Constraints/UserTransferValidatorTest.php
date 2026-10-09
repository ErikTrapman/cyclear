<?php declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Repository\RennerRepository;
use App\Repository\SeizoenRepository;
use App\Repository\TransferRepository;
use App\Validator\Constraints\UserTransfer;
use App\Validator\Constraints\UserTransferFixedValidator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserTransferValidatorTest extends WebTestCase
{
    private ExecutionContextInterface $context;

    private ConstraintValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
    }

    protected function getRepos(bool $mockTransferRepository = false): array
    {
        $rennerRepository = $this->createStub(RennerRepository::class);
        $transferRepository = $mockTransferRepository ? $this->createMock(TransferRepository::class) : $this->createStub(TransferRepository::class);
        $seizoenRepository = $this->createStub(SeizoenRepository::class);
        $seizoenRepository->method('getMaxTransfers')->willReturn(50);
        return [$rennerRepository, $transferRepository, $seizoenRepository];
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

        list($rennerRepository, $transferRepository, $seizoenRepsitory) = $this->getRepos();
        $validator = new UserTransferFixedValidator($rennerRepository, $transferRepository, $seizoenRepsitory);
        $validator->initialize($this->context);
        $validator->validate($t, new UserTransfer());
    }

    public function testInvalidSeasonBeforeOpening(): void
    {
        $this->context->expects($this->once())->method('addViolation')->with($this->equalTo('Het huidige seizoen staat nog geen transfers toe.'));
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-05-21'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-11-21'));

        list($rennerRepository, $transferRepository, $seizoenRepsitory) = $this->getRepos();
        $rennerRepository->method('getPloeg')->willReturn(null);

        $validator = new UserTransferFixedValidator($rennerRepository, $transferRepository, $seizoenRepsitory);
        $validator->initialize($this->context);
        $validator->validate($t, new UserTransfer());
    }

    public function testInvalidSeasonAfterClosing(): void
    {
        $this->context->expects($this->once())->method('addViolation')->with($this->equalTo('Het huidige seizoen staat geen transfers meer toe.'));
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-04-30'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-04-30'));

        list($rennerRepository, $transferRepository, $seizoenRepsitory) = $this->getRepos();
        $rennerRepository->method('getPloeg')->willReturn(null);

        $validator = new UserTransferFixedValidator($rennerRepository, $transferRepository, $seizoenRepsitory);
        $validator->initialize($this->context);
        $validator->validate($t, new UserTransfer());
    }

    public function testInvalidMaxTransfers(): void
    {
        $this->context->expects($this->once())->method('addViolation')->with($this->stringContains('Je zit op het maximaal aantal transfers'));

        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-01-01'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-11-01'));

        list($rennerRepository, $transferRepository, $seizoenRepsitory) = $this->getRepos();
        $rennerRepository->method('getPloeg')->willReturn(null);

        $transferRepository->method('getTransferCountForUserTransfer')->willReturn(50);
        $validator = new UserTransferFixedValidator($rennerRepository, $transferRepository, $seizoenRepsitory);
        $validator->initialize($this->context);
        $validator->validate($t, new UserTransfer());
    }

    public function testValidTransferOnLastDayOfSeason(): void
    {
        $this->context->expects($this->never())->method('addViolation');
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-01-01'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-05-01'));

        list($rennerRepository, $transferRepository, $seizoenRepsitory) = $this->getRepos(true);
        $rennerRepository->method('getPloeg')->willReturn(null);

        $transferRepository->expects($this->once())->method('getTransferCountForUserTransfer')->willReturn(0);
        $validator = new UserTransferFixedValidator($rennerRepository, $transferRepository, $seizoenRepsitory);
        $validator->initialize($this->context);
        $validator->validate($t, new UserTransfer());
    }

    public function testValidTransferOnFirstDayOfPeriod(): void
    {
        $this->context->expects($this->never())->method('addViolation');
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setStart(new \DateTime('2013-05-01'));
        $t->getSeizoen()->setEnd(new \DateTime('2013-11-01'));

        list($rennerRepository, $transferRepository, $seizoenRepsitory) = $this->getRepos(true);
        $rennerRepository->method('getPloeg')->willReturn(null);

        $transferRepository->expects($this->once())->method('getTransferCountForUserTransfer')->willReturn(0);
        $validator = new UserTransferFixedValidator($rennerRepository, $transferRepository, $seizoenRepsitory);
        $validator->initialize($this->context);
        $validator->validate($t, new UserTransfer());
    }
}
