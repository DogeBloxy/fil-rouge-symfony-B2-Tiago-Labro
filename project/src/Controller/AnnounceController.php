<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Announces;
use App\Form\AddAnnouncementFormType;
use App\Repository\AnnouncesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;

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

    #[Route('/announce/add', name: 'announce.add')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $announce = new Announces();
        $user = $this->getUser();
        if ($user instanceof User) {
            $firstname = $user->getFirstname();
            $announce->setAuthor($firstname);
        } else {
            throw new \LogicException('L\'utilisateur connecté n\'est pas une instance de User.');
        }
        $announceForm = $this->createForm(AddAnnouncementFormType::class, $announce);

        $announceForm->handleRequest($request);

        if($announceForm->isSubmitted() && $announceForm->isValid()) {
            $announce = $announceForm->getData();
            
            $entityManager->persist($announce);
            $entityManager->flush();
            $this->addFlash('success', "L'annonce a été créée !");
            return $this->redirectToRoute('announce.index');
        }

        return $this->render('announce/add.html.twig', [
            'announceForm' => $announceForm->createView(),
        ]);
    }
}
