<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use \Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    /**
     * @Route("/main/{name}", name="main")
     */
    public function index(Request $request)
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => $request->get('name'),
            'message' => 'Welcome ' . $request->get('name'),
        ]);
    }
}
