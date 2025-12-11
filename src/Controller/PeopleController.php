<?php

namespace App\Controller;

use App\Entity\People;
use App\Form\PeopleType;
use App\Repository\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/people')]
final class PeopleController extends AbstractController
{
    #[Route('', name: 'app_people_index', methods: ['GET'])]
    public function index(PeopleRepository $repo): Response
    {
        return $this->render('people/index.html.twig', [
            'peoples' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_people_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $people = new People();
        $form = $this->createForm(PeopleType::class, $people);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($people);
            $em->flush();
            return $this->redirectToRoute('app_people_index');
        }

        return $this->render('people/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_people_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, People $people, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PeopleType::class, $people);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_people_index');
        }

        return $this->render('people/edit.html.twig', [
            'form' => $form,
            'people' => $people,
        ]);
    }

    #[Route('/{id}', name: 'app_people_delete', methods: ['POST'])]
    public function delete(Request $request, People $people, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $people->getId(), $request->request->get('_token'))) {
            $em->remove($people);
            $em->flush();
        }
        return $this->redirectToRoute('app_people_index');
    }
}
