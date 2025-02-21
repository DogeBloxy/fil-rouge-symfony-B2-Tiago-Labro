<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEditFormType;
use App\Form\ProfileEditPasswordFormType;
use App\Repository\AnnouncesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: "profile.index")]
    #[IsGranted('ROLE_USER')]
    public function index(AnnouncesRepository $announceRepository): Response
    {
        $user = $this->getUser();
        
        if ($user instanceof User) {
            $announces = $announceRepository->findBy(['author' => $user->getFirstname()]);
        } else {
            throw new \LogicException('L\'utilisateur connecté n\'est pas une instance de User.');
        }

        return $this->render('profile/index.html.twig', [
            'announces' => $announces,
        ]);
    }

    #[Route('/profile/edit', name: 'profile.edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $profileForm = $this->createForm(ProfileEditFormType::class, $user);
        $profileForm->handleRequest($request);
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', "Votre profil a été modifié !");
            return $this->redirectToRoute('profile.index');
        }

        return $this->render('profile/modify.html.twig', [
            'profileForm' => $profileForm->createView(),
            'profile' => $user
        ]);
    }

    #[Route('/profile/edit-password', name: 'profile.edit-password', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('L\'utilisateur doit être connecté.');
        }
        $profileForm = $this->createForm(ProfileEditPasswordFormType::class, $user);
        $profileForm->handleRequest($request);
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            if ($userPasswordHasher->isPasswordValid($user, $user->getPlainPassword())) {
                $hashedPassword = $userPasswordHasher->hashPassword($user, $user->getNewPassword());
                $user->setPassword($hashedPassword);
                $entityManager->flush();
                $this->addFlash('success', "Votre mot de passe a été modifié !");
                return $this->redirectToRoute('profile.index');
            } else {
                $this->addFlash('error', "Le mot de passe actuel est incorrect.");
            }
        }
        return $this->render('profile/modify_password.html.twig', [
            'passwordForm' => $profileForm->createView(),
            'profile' => $user
        ]);
    }
}
