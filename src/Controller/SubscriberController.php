<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SubscriberController extends AbstractController
{
    /**
     * @Route("/odberatele", name="subscriber")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('subscriber/index.html.twig', [
            'controller_name' => 'SubscriberController',
        ]);
    }
}
