<?php

namespace App\Controller;

use App\Entity\ActivityType;
use App\Repository\ActivityTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/activity-types', name: 'activity_types_')]
class ActivityTypeController extends AbstractController
{
    #[Route('/', methods: ['GET'], name: 'list')]
    public function list(ActivityTypeRepository $repository): JsonResponse
    {
        $activityTypes = $repository->findAll();
        return $this->json($activityTypes, 200, [], ['groups' => 'activity_type:read']);
    }
}
