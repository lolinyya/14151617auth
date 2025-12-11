<?php

namespace App\Controller;

use App\Entity\OrderEntity;
use App\Entity\OrderDocument;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Row;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/order')]
final class OrderController extends AbstractController
{
    #[Route('', name: 'app_order_index', methods: ['GET'])]
    public function index(OrderRepository $repo): Response
    {

        if ($this->getUser()) {
            $orders = $repo->findBy(['people' => $this->getUser()], ['id' => 'DESC']);
        } else {

            $orders = $repo->findAll();
        }

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

     #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $order = new OrderEntity();

        $user = $this->getUser();
        if ($user) {
            $order->setPeople($user);
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user && !$form->has('people')) {
                $order->setPeople($user);
            }

            $em->persist($order);
            $em->flush();

            $this->addFlash('success', 'Заказ успешно создан');
            return $this->redirectToRoute('app_order_index');
        }

        return $this->render('order/new.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
        ]);
    }


    #[Route('/{id<\d+>}', name: 'app_order_show', methods: ['GET'])]
    public function edit(Request $request, int $id, OrderRepository $repo, EntityManagerInterface $em): Response
    {
        $order = $repo->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Заказ #' . $id . ' не найден');
        }


        $user = $this->getUser();
        if ($user && $order->getPeople() !== $user) {
            throw $this->createAccessDeniedException('Вы можете редактировать только свои заказы');
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Заказ успешно обновлен');
            return $this->redirectToRoute('app_order_show', ['id' => $id]);
        }

        return $this->render('order/edit.html.twig', [
            'form'  => $form->createView(),
            'order' => $order,
        ]);
    }

    #[Route('/document/{id}', name: 'app_order_delete_doc', methods: ['POST'])]
    public function deleteDoc(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $doc = $em->getRepository(OrderDocument::class)->find($id);

        if (!$doc) {
            throw $this->createNotFoundException('Документ не найден');
        }

        $order = $doc->getOrder();
        if ($this->getUser() && $order->getPeople() !== $this->getUser()) {
            throw new AccessDeniedException('У вас нет прав для удаления этого документа');
        }

        if (!$this->isCsrfTokenValid('delete' . $doc->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный CSRF-токен');
        }

        $orderId = $doc->getOrder()->getId();

        $em->remove($doc);
        $em->flush();

        $this->addFlash('success', 'Документ успешно удалён');

        return $this->redirectToRoute('app_order_show', ['id' => $orderId]);
    }

    #[Route('/export-excel', name: 'app_export_order', methods: ['GET'])]
    public function exportOrder(Connection $connection): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        dd([
            'user_class' => get_class($user),
            'user_data' => $user,
            'methods' => get_class_methods($user)
        ]);


        $sql = "SELECT
            o.id,
            COALESCE(p.name, 'Аноним') as people_name,
            COALESCE(p.phone, '—') as people_phone,
            GROUP_CONCAT(d.name) as dishes,
            COALESCE(SUM(d.price), 0) as total_price
        FROM orders o
        LEFT JOIN people p ON o.people_id = p.id
        LEFT JOIN order_entity_dish oed ON o.id = oed.order_entity_id
        LEFT JOIN dish d ON oed.dish_id = d.id
        WHERE o.people_id = :userId
        GROUP BY o.id, p.name, p.phone
        ORDER BY o.id DESC";

        $orders = $connection->executeQuery($sql, ['userId' => $userId])->fetchAllAssociative();

        $writer = new Writer();
        $tempFile = tempnam(sys_get_temp_dir(), 'order_') . '.xlsx';
        $writer->openToFile($tempFile);

        $headerStyle = (new Style())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(204, 0, 112, 1));

        $writer->addRow(Row::fromValues([
            'ID заказа',
            'Клиент',
            'Телефон',
            'Блюда',
            'Сумма'
        ], $headerStyle));

        foreach ($orders as $order) {
            $dishes = $order['dishes'] ? str_replace(',', ', ', $order['dishes']) : '—';

            $writer->addRow(Row::fromValues([
                $order['id'],
                $order['people_name'],
                $order['people_phone'],
                $dishes,
                $order['total_price'] . ' ₽'
            ]));
        }

        $writer->close();

        $response = new Response(file_get_contents($tempFile));
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'my_orders_' . date('Y-m-d_H-i') . '.xlsx'
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        @unlink($tempFile);

        return $response;
    }
}
