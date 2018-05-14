<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class StartController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // Данные о текущем юзере
        $securityContext = $this->get('security.authorization_checker');
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($this->getUser());

        // Если юзер авторизован - рендерим страницу, иначе перекидыаем на страницу входа
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->render('@App/start/index.html.twig');
        } else {
            return $this->forward('FOSUserBundle:Security:login');
        }
    }
}
