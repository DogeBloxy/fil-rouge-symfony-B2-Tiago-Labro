<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Announces;
use App\Repository\AnnouncesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AnnounceController extends AbstractController
{
    #[Route('/announce', name: 'announce.index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Announces::class);

        $announces = $repository->findAll();
        return $this->render('announce/index.html.twig', [
            'announces' => $announces
        ]);
    }
    #[Route('/announce/{id}', name: 'announce.show', requirements: ['id' => '\d+'])]
    public function show(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {

        $repository = $entityManager->getRepository(Announces::class);

        $announce = $repository->find($id);

        return $this->render('announce/show.html.twig', [
            'announce' => $announce
        ]);
    }
}
