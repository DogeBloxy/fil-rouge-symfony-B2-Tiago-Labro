<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminEditFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin.index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(User::class);

        $users = $repository->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/{id}/edit', name: 'admin.edit', requirements: ['id' => '\d+'], methods:['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $userForm = $this->createForm(AdminEditFormType::class, $user);
        $userForm->handleRequest($request);
        $newPassword = $userForm->get('newPassword')->getData();
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->updatePassword($hashedPassword);
            }
            $entityManager->flush();
            $this->addFlash('success', "Compte modifié !");
            return $this->redirectToRoute('admin.index');
        }

        return $this->render('admin/modify.html.twig', [
            'userForm' => $userForm->createView(),
            'user' => $user
        ]);
    }

    #[Route('/admin/{id}/delete', name: 'admin.delete', requirements: ['id' => '\d+'], methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', "Compte supprimé !");
        return $this->redirectToRoute('admin.index');
    }
}
