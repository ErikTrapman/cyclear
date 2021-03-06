<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EntityManager;

use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class UserManager
{
    /**
     *
     * @var MutableAclProvider
     */
    private $aclprovider;

    public function __construct(AclProviderInterface $aclprovider)
    {
        $this->aclprovider = $aclprovider;
    }

    public function setOwnerAcl($user, $ploeg)
    {
        $securityIdentity = UserSecurityIdentity::fromAccount($user);
        $objectIdentity = ObjectIdentity::fromDomainObject($ploeg);
        try {
            $acl = $this->aclprovider->findAcl($objectIdentity);
            try {
                if (!$acl->isGranted(array(MaskBuilder::MASK_OWNER), array($securityIdentity))) {
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

    public function unsetOwnerAcl($user, $ploeg)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($ploeg);
        $this->aclprovider->deleteAcl($objectIdentity);
    }

    public function isOwner($user, $ploeg)
    {
        $securityIdentity = UserSecurityIdentity::fromAccount($user);
        $objectIdentity = ObjectIdentity::fromDomainObject($ploeg);
        try {
            $acl = $this->aclprovider->findAcl($objectIdentity);
            return $acl->isGranted(array(MaskBuilder::MASK_OWNER), array($securityIdentity));
        } catch (AclNotFoundException $e) {
            return false;
        } catch (\Symfony\Component\Security\Acl\Exception\NoAceFoundException $e) {
            return false;
        }
        return false;
    }
}
