<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AnnounceController extends AbstractController
{
    #[Route('/announce', name: 'announce.index')]
    public function index(Request $request): Response
    {
        return $this->render('announce/index.html.twig');
    }
    #[Route('/announce/{slug}-{id}', name: 'announce.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id): Response
    {
        return $this->render('announce/show.html.twig', [
            'slug' => $slug,
            'id' => $id
        ]);
    }
}
