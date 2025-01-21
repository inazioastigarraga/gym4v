<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use App\Repository\ActivityTypeRepository;
use App\Repository\MonitorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/activities', name: 'activities_')]
class ActivityController extends AbstractController
{
    #[Route('/{dateStart?}', methods: ['GET'], name: 'list')]
    public function list(?string $dateStart, ActivityRepository $repository): JsonResponse
    {
        if ($dateStart) {
            // Convertir la fecha del formato dd-MM-yyyy a un objeto DateTime
            $dateStartObject = \DateTime::createFromFormat('d-m-Y', $dateStart);

            if (!$dateStartObject) {
                return $this->json(['error' => 'Invalid date format, use dd-MM-yyyy'], 400);
            }

            // Crear rango para buscar actividades dentro del día
            $startOfDay = $dateStartObject->setTime(0, 0, 0); // Inicio del día
            $endOfDay = (clone $startOfDay)->setTime(23, 59, 59); // Fin del día

            // Buscar actividades en el rango
            $activities = $repository->createQueryBuilder('a')
                ->where('a.dateStart BETWEEN :start AND :end')
                ->setParameter('start', $startOfDay)
                ->setParameter('end', $endOfDay)
                ->getQuery()
                ->getResult();
        } else {
            // Si no se proporciona la fecha, devolver todas las actividades
            $activities = $repository->findAll();
        }

        return $this->json($activities, 200, [], ['groups' => 'activity:read']);
    }



    #[Route('/', methods: ['POST'], name: 'create')]
    public function create(
        Request $request,
        ActivityTypeRepository $activityTypeRepo,
        MonitorRepository $monitorRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validar el tipo de actividad
        $activityType = $activityTypeRepo->find($data['activity_type_id']);
        if (!$activityType) {
            return $this->json(['error' => 'Invalid activity type'], 400);
        }

        // Validar los monitores asociados
        $monitors = $monitorRepo->findBy(['id' => $data['monitors_id']]);
        if (count($monitors) < $activityType->getNumberMonitors()) {
            return $this->json(['error' => 'Insufficient monitors for the activity type'], 400);
        }

        // Validar la fecha de inicio y duración
        $validStartTimes = ['09:00', '13:30', '17:30'];

        // Intentar crear el objeto DateTime desde el formato esperado
        $startTime = \DateTime::createFromFormat('Y-m-d H:i:s', $data['date_start']);

        // Obtener errores y verificar si hay alguno
        $errors = \DateTime::getLastErrors();
        if (!$startTime || ($errors !== false && ($errors['error_count'] > 0 || $errors['warning_count'] > 0))) {
            return $this->json([
                'error' => 'Invalid date_start format. Use Y-m-d H:i:s (e.g., 2025-01-21 09:00:00).'
            ], 400);
        }

        // Validar si la hora no está en los horarios permitidos
        if (!in_array($startTime->format('H:i'), $validStartTimes)) {
            return $this->json([
                'error' => 'Invalid start time. Only 09:00, 13:30, and 17:30 are allowed.'
            ], 400);
        }


        // Calcular la fecha de finalización (90 minutos después)
        $endTime = (clone $startTime)->modify('+90 minutes');

        // Crear la nueva actividad
        $activity = new Activity();
        $activity->setActivityType($activityType)
            ->setDateStart($startTime)
            ->setDateEnd($endTime);

        // Asignar monitores a la actividad
        foreach ($monitors as $monitor) {
            $activity->addMonitor($monitor);
        }

        $em->persist($activity);
        $em->flush();

        return $this->json($activity, 201, [], ['groups' => 'activity:read']);
    }



    #[Route('/{activityId}', methods: ['PUT'], name: 'update')]
    public function update(
        int $activityId,
        Request $request,
        ActivityRepository $repository,
        ActivityTypeRepository $activityTypeRepo,
        MonitorRepository $monitorRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $activity = $repository->find($activityId);

        // Verificar si la actividad existe
        if (!$activity) {
            return $this->json(['error' => 'Activity not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Validar el tipo de actividad
        $activityType = $activityTypeRepo->find($data['activity_type_id']);
        if (!$activityType) {
            return $this->json(['error' => 'Invalid activity type'], 400);
        }

        // Validar los monitores asociados
        $monitors = $monitorRepo->findBy(['id' => $data['monitors_id']]);
        if (count($monitors) < $activityType->getNumberMonitors()) {
            return $this->json(['error' => 'Insufficient monitors for the activity type'], 400);
        }

        // Validar la fecha de inicio y duración
        $validStartTimes = ['09:00', '13:30', '17:30'];
        $startTime = \DateTime::createFromFormat('Y-m-d H:i:s', $data['date_start']);

        $errors = \DateTime::getLastErrors();
        if (!$startTime || ($errors !== false && ($errors['error_count'] > 0 || $errors['warning_count'] > 0))) {
            return $this->json([
                'error' => 'Invalid date_start format. Use Y-m-d H:i:s (e.g., 2025-01-21 09:00:00).'
            ], 400);
        }

        if (!in_array($startTime->format('H:i'), $validStartTimes)) {
            return $this->json([
                'error' => 'Invalid start time. Only 09:00, 13:30, and 17:30 are allowed.'
            ], 400);
        }

        // Calcular la fecha de finalización (90 minutos después de la fecha de inicio)
        $endTime = (clone $startTime)->modify('+90 minutes');

        // Actualizar los campos de la actividad
        $activity->setActivityType($activityType)
            ->setDateStart($startTime)
            ->setDateEnd($endTime);

        // Actualizar los monitores asignados
        foreach ($activity->getMonitors() as $monitor) {
            $activity->removeMonitor($monitor);
        }
        foreach ($monitors as $monitor) {
            $activity->addMonitor($monitor);
        }

        $em->flush();

        return $this->json($activity, 200, [], ['groups' => 'activity:read']);
    }


    #[Route('/{activityId}', methods: ['DELETE'], name: 'delete')]
    public function delete(int $activityId, ActivityRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $activity = $repository->find($activityId);

        if (!$activity) {
            return $this->json(['error' => 'Activity not found'], 404);
        }

        $em->remove($activity);
        $em->flush();

        return $this->json(null, 204);
    }
}
