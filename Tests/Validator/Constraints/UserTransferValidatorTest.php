<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cyclear\GameBundle\Tests\Validator\Constraints;

use Cyclear\GameBundle\Entity\Periode;
use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Entity\Renner;
use Cyclear\GameBundle\Entity\Seizoen;
use Cyclear\GameBundle\Entity\Transfer;
use Cyclear\GameBundle\Validator\Constraints\UserTransfer;
use Cyclear\GameBundle\Validator\Constraints\UserTransferValidator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;


class UserTransferValidatorTest extends WebTestCase
{

    private $context;

    /**
     * @var ConstraintValidatorInterface
     */
    private $validator;

    private $em;

    private $transferRepo;

    private $rennerRepo;

    /**
     * @var Periode
     */
    private $periode;

    public function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $this->validator = new UserTransferValidator($this->em);
        $this->validator->initialize($this->context);
    }

    private function getValidTransfer()
    {
        $t = new \Cyclear\GameBundle\Form\Entity\UserTransfer();
        $s = new Seizoen();
        $t->setRennerIn(new Renner());
        $t->setRennerUit(new Renner());
        $t->setSeizoen($s);
        $datum = new \DateTime("2013-05-01");
        $datum->setTime(12, 0, 0);
        $t->setDatum($datum);
        $repo = $this->getMockBuilder('Cyclear\GameBundle\Entity\PeriodeRepository')->disableOriginalConstructor()->getMock();
        $this->em->expects($this->at(0))->method("getRepository")->with("CyclearGameBundle:Periode")->will($this->returnValue($repo));
        $this->periode = new Periode();
        $this->periode->setStart(new \DateTime("2013-05-01"));
        $this->periode->setEind(new \DateTime("2013-07-31"));
        $repo->expects($this->once())->method("getCurrentPeriode")->will($this->returnValue($this->periode));
        $this->initRepos();
        return $t;
    }

    private function initRepos()
    {
        $this->transferRepo = $this->getMockBuilder('Cyclear\GameBundle\Entity\TransferRepository')->disableOriginalConstructor()->getMock();
        $this->em->expects($this->at(1))->method("getRepository")->with("CyclearGameBundle:Transfer")->will($this->returnValue($this->transferRepo));
        $this->rennerRepo = $this->getMockBuilder('Cyclear\GameBundle\Entity\RennerRepository')->disableOriginalConstructor()->getMock();
        $this->em->expects($this->at(2))->method("getRepository")->with("CyclearGameBundle:Renner")->will($this->returnValue($this->rennerRepo));
        $this->rennerRepo->expects($this->once())->method("getPloeg")->will($this->returnValue(null));
    }

    public function testNoRider()
    {
        $this->context->expects($this->at(0))->method('addViolationAt')->with($this->equalTo('renner'), $this->equalTo('Onbekende renner opgegeven'));
        $t = $this->getValidTransfer();
        $t->setRennerIn(null);
        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidSeason()
    {
        $this->context->expects($this->at(0))->method('addViolation')->with($this->equalTo('Het seizoen 1 is gesloten'));
        $t = $this->getValidTransfer();
        $t->getSeizoen()->setClosed(true);
        $t->getSeizoen()->setIdentifier("1");
        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidPeriodBeforeOpening()
    {
        $this->context->expects($this->at(0))->method('addViolation')->with($this->equalTo('De huidige periode staat nog geen transfers toe'));
        $t = $this->getValidTransfer();
        $this->periode->setStart(new \DateTime("2013-05-02"));
        $this->validator->validate($t, new UserTransfer());
    }

    public function testInvalidPeriodAfterClosing()
    {
        $this->context->expects($this->at(0))->method('addViolation')->with($this->equalTo('De huidige periode staat geen transfers meer toe'));
        $t = $this->getValidTransfer();
        $this->periode->setEind(new \DateTime("2013-04-30"));
        $this->validator->validate($t, new UserTransfer());
    }


    public function testInvalidMaxTransfers()
    {
        $this->context->expects($this->once())->method('addViolation')->with($this->stringContains('Je zit op het maximaal aantal transfers'));

        $t = $this->getValidTransfer();
        $this->transferRepo->expects($this->once())->method("getTransferCountForUserTransfer")->will($this->returnValue(5));
        $this->validator->validate($t, new UserTransfer());
    }

    public function testValidTransferOnLastDayOfPeriod()
    {
        $this->context->expects($this->never())->method('addViolation');
        $t = $this->getValidTransfer();
        $d = new \DateTime('2013-07-31');
        $d->setTime(23, 59, 59);
        $t->setDatum($d);
        $this->periode->setTransfers(1);
        $this->transferRepo->expects($this->once())->method("getTransferCountForUserTransfer")->will($this->returnValue(0));
        $this->validator->validate($t, new UserTransfer());
    }

    public function testValidTransferOnFirstDayOfPeriod()
    {
        $this->context->expects($this->never())->method('addViolation');
        $t = $this->getValidTransfer();
        $d = new \DateTime('2013-05-01');
        $d->setTime(0, 0, 1);
        $t->setDatum($d);
        $this->periode->setTransfers(1);
        $this->transferRepo->expects($this->once())->method("getTransferCountForUserTransfer")->will($this->returnValue(0));
        $this->validator->validate($t, new UserTransfer());
    }


}