<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TestController extends AbstractController
{
    #[Route('/', name: "home")]
    public function index(): Response
    {
        $message = "Moi-même";
        return $this->render('test/index.html.twig', [
            'message' => $message,
        ]);
    }
}
