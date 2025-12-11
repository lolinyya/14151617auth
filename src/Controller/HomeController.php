<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\PeopleRepository;
use App\Repository\DishRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        OrderRepository $orderRepository,
        PeopleRepository $peopleRepository,
        DishRepository $dishRepository
    ): Response
    {
        $recentOrders = $orderRepository->createQueryBuilder('o')
            ->leftJoin('o.people', 'p')
            ->addSelect('p')
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('home/index.html.twig', [
            'total_orders' => $orderRepository->count([]),
            'total_peoples' => $peopleRepository->count([]),
            'total_dishes' => $dishRepository->count([]),
            'recent_orders' => $recentOrders,
        ]);
    }
}   