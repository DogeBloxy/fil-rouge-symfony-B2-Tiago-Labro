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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Security\Voter\AnnounceVoter;

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

    #[Route('/announce/add', name: 'announce.add', methods:['GET', 'POST'])]
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

    #[Route('/announce/{id}/edit', name: 'announce.edit', requirements: ['id' => '\d+'], methods:['GET', 'POST'])]
    #[IsGranted(AnnounceVoter::EDIT, subject: 'announce')]
    public function edit(Announces $announce, Request $request, EntityManagerInterface $entityManager): Response
    {
        $announceForm = $this->createForm(AddAnnouncementFormType::class, $announce);
        $announceForm->handleRequest($request);
        if ($announceForm->isSubmitted() && $announceForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', "L'annonce a été modifiée !");
            return $this->redirectToRoute('announce.index');
        }

        return $this->render('announce/modify.html.twig', [
            'announceForm' => $announceForm->createView(),
            'announce' => $announce
        ]);
    }

    #[Route('/announce/{id}/delete', name: 'announce.delete', requirements: ['id' => '\d+'], methods:['DELETE'])]
    #[IsGranted(AnnounceVoter::DELETE, subject: 'announce')]
    public function delete(Announces $announce, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($announce);
        $entityManager->flush();
        $this->addFlash('success', "L'annonce a été supprimée !");
        return $this->redirectToRoute('announce.index');
    }
}
