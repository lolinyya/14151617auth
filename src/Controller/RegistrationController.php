<?php

namespace App\Controller;

use App\Entity\People;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_order_index');
        }

        $user = new People();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $plainPassword = $form->get('plainPassword')->getData();
            
            $hashedPassword = $userPasswordHasher->hashPassword(
                $user,
                $plainPassword
            );
            
            $user->setPassword($hashedPassword);
            
            if (!$user->getPassword()) {
                throw new \RuntimeException('Пароль не установлен!');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Регистрация успешна! Теперь вы можете войти в систему.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}