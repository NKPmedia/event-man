<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserController extends AbstractController
{
    #[Route('/user')]
    public function show(EntityManagerInterface $entityManager): Response
    {
        $userRepo = $entityManager->getRepository(User::class);
        if ($this->isGranted("ROLE_ADMIN")) {
            $users = $userRepo->findAll();
        }
        else {
            $users = $userRepo->findBy(['id' => $this->getUser()->getId()]);
        }

        return $this->render('user/show.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/create')]
    public function create(Request $request,
                           UserPasswordHasherInterface $passwordHasher,
                           EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('telegramId', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create User'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user_data = $form->getData();
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user_data->getPassword()
            );
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User created successfully');
            return $this->redirectToRoute("app_user_show");
        }

        return $this->render('user/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/{id}')]
    public function showOne(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $userRepo = $entityManager->getRepository(User::class);
        $user = $userRepo->find($id);

        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class, ['disabled' => true])
            ->add('telegramId', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Update User'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully');
            return $this->redirectToRoute("app_user_showone", ['id' => $id]);
        }

        return $this->render('user/showOne.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/{id}/change-password')]
    public function changePassword(Request $request,
                                   int $id,
                                   UserPasswordHasherInterface $passwordHasher,
                                   EntityManagerInterface $em): Response
    {
        if ($id !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException("You can't change password for another user");
        }

        $form = $this->createForm(ChangePasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password_data = $form->getData();
            if ($password_data['newPassword'] !== $password_data['confirmPassword']) {
                $this->addFlash('error', 'Passwords do not match');
                return $this->redirectToRoute("app_user_changepassword", ['id' => $id]);
            }

            $user = $this->getUser();
            #Validate old password
            if (!$passwordHasher->isPasswordValid($user, $password_data['oldPassword'])) {
                $this->addFlash('error', 'Old password is incorrect');
                return $this->redirectToRoute("app_user_changepassword", ['id' => $id]);
            }

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password_data['newPassword']
            );
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Password changed successfully');
            return $this->redirectToRoute("app_user_show", ['id' => $id]);
        }

        return $this->render('user/changePassword.html.twig', [
            'form' => $form,
        ]);
    }
}
