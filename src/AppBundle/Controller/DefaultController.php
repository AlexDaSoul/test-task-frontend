<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /** @var  User */
    private $user;

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $this->initUser();
        $handler = $this->get('frontend.form_handlers.user')->setModel($this->user);

        return $this->render('default/index.html.twig', array(
            'form' => $handler->getBuiltFormView(),
            'user' => $this->user,
        ));
    }

    /**
     * Handles user form ajax submission
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Route("/process-form", name="process.form", options={"expose"=true})
     */
    public function processFormAction()
    {
        $this->initUser();
        $handler = $this->get('frontend.form_handlers.user')->setModel($this->user);

        return $handler->handle();
    }

    /**
     * Inits current user
     */
    private function initUser()
    {
        $userRepo = $this->get('doctrine')->getRepository('AppBundle:User');
        $user = $userRepo->findOneBy(array('idToken' => '5dsf4dsf5sdf4'));
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found, please load fixtures');
        }

        $this->user = $user;
    }
}
