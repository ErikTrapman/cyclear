<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @Route("/admin")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="admin_index")
     * @Template("CyclearGameBundle:Default/Admin:index.html.twig")
     */
    public function indexAction(\Symfony\Component\HttpFoundation\Request $request)
    {

        $form = $this->createForm('eriktrapman_commandselectortype');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $event = new \ErikTrapman\Bundle\WebCommandBundle\Event\CommandRunEvent($form->get('command')->getData(), $form->get('options')->getData());
                $this->get('event_dispatcher')->dispatch('eriktrapman.command.run', $event);
                return $this->redirect($this->generateUrl('admin_index'));
            }
        }
        return array('form' => $form->createView());
    }
}
