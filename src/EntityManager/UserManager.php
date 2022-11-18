<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\Ploeg;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

class UserManager
{
    public function __construct(private readonly AclProviderInterface $aclprovider)
    {
    }

    public function setOwnerAcl(UserInterface $user, Ploeg $ploeg): void
    {
        $securityIdentity = UserSecurityIdentity::fromAccount($user);
        $objectIdentity = ObjectIdentity::fromDomainObject($ploeg);
        try {
            $acl = $this->aclprovider->findAcl($objectIdentity);
            try {
                if (!$acl->isGranted([MaskBuilder::MASK_OWNER], [$securityIdentity])) {
                    $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
                    $this->aclprovider->updateAcl($acl);
                }
            } catch (\Symfony\Component\Security\Acl\Exception\NoAceFoundException $e) {
                $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
                $this->aclprovider->updateAcl($acl);
            }
        } catch (AclNotFoundException $exc) {
            $acl = $this->aclprovider->createAcl($objectIdentity);
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $this->aclprovider->updateAcl($acl);
        }
    }

    public function unsetOwnerAcl($user, $ploeg): void
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($ploeg);
        $this->aclprovider->deleteAcl($objectIdentity);
    }

    public function isOwner(UserInterface $user, Ploeg $ploeg): bool
    {
        $securityIdentity = UserSecurityIdentity::fromAccount($user);
        $objectIdentity = ObjectIdentity::fromDomainObject($ploeg);
        try {
            $acl = $this->aclprovider->findAcl($objectIdentity);
            return $acl->isGranted([MaskBuilder::MASK_OWNER], [$securityIdentity]);
        } catch (\Exception $e) {
        }
        return false;
    }
}
