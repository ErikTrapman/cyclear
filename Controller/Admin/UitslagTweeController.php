<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @Route("admin/uitslagtwee")
 *
 */
class UitslagTweeController extends Controller
{

    /**
     * @Route("/", name="admin_uitslagtwee")
     * @Template("CyclearGameBundle:UitslagTwee/Admin:index.html.twig")
     * @Method({"POST","GET"})
     */
    public function indexAction(Request $request)
    {
        $uitslagManager = $this->get('cyclear_game.manager.uitslag');
        $wedstrijdManager = $this->get('cyclear_game.manager.wedstrijd');
        $crawlerManager = $this->get('eriktrapman_cqparser.crawler_manager');
        $options = array();
        $options['crawler_manager'] = $crawlerManager;
        $options['wedstrijd_manager'] = $wedstrijdManager;
        $options['uitslag_manager'] = $uitslagManager;
        $options['request'] = $request;
        $form = $this->createForm(new \Cyclear\GameBundle\Form\Type\UitslagTweeType(), null, $options);
        if ($request->isXmlHttpRequest()) {
            $form->bind($request);
            
            $twig = $this->get('twig');
            $templateFile = "CyclearGameBundle:UitslagTwee/Admin:_ajaxTemplate.html.twig";
            $templateContent = $twig->loadTemplate($templateFile);

            // Render the whole template including any layouts etc
            $body = $templateContent->render(array("form" => $form->createView()));
            return new \Symfony\Component\HttpFoundation\Response($body);
        }
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $wedstrijd = $form->get('wedstrijd')->getData();
            var_dump($wedstrijd);
            die;
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/", name="admin_uitslagtwee_ajax")
     * @Method({"POST"})
     */
    public function ajaxAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            echo 'POST';
        } else {
            echo 'GET';
        }
    }
}