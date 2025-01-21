<?php

namespace App\Controller;

use App\Entity\Monitor;
use App\Repository\MonitorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/monitors', name: 'monitors_')]
class MonitorController extends AbstractController
{
    #[Route('/', methods: ['GET'], name: 'list')]
    public function list(MonitorRepository $repository): JsonResponse
    {
        $monitors = $repository->findAll();
        return $this->json($monitors, 200, [], ['groups' => 'monitor:read']);
    }

    #[Route('/', methods: ['POST'], name: 'create')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $monitor = new Monitor();
        $monitor->setName($data['name'])
            ->setEmail($data['email'])
            ->setPhone($data['phone'])
            ->setPhoto($data['photo'] ?? null);

        $em->persist($monitor);
        $em->flush();

        return $this->json($monitor, 201, [], ['groups' => 'monitor:read']);
    }

    #[Route('/{monitorId}', methods: ['PUT'], name: 'update')]
    public function update(int $monitorId, Request $request, MonitorRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $monitor = $repository->find($monitorId);

        if (!$monitor) {
            return $this->json(['error' => 'Monitor not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $monitor->setName($data['name'])
            ->setEmail($data['email'])
            ->setPhone($data['phone'])
            ->setPhoto($data['photo'] ?? null);

        $em->flush();

        return $this->json($monitor, 200, [], ['groups' => 'monitor:read']);
    }

    #[Route('/{monitorId}', methods: ['DELETE'], name: 'delete')]
    public function delete(int $monitorId, MonitorRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $monitor = $repository->find($monitorId);

        if (!$monitor) {
            return $this->json(['error' => 'Monitor not found'], 404);
        }

        $em->remove($monitor);
        $em->flush();

        return $this->json(null, 204);
    }
}
