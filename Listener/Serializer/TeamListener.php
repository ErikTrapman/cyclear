<?php

namespace Cyclear\GameBundle\Listener\Serializer;

class TeamListener extends \JMS\Serializer\EventDispatcher\Event
{
    
    
    
    public function mediumSerialization( \JMS\Serializer\EventDispatcher\ObjectEvent $objectEvent )
    {
        
        var_dump($objectEvent);die;
        $groups = $objectEvent->getContext()->getGroups();
        //var_dump($groups);die;
        
    }
}
