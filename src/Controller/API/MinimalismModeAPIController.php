<?php

namespace App\Controller\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/minimalism')]
#[IsGranted('IS_AUTHENTICATED')]
class MinimalismModeAPIController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route(name: 'app_api_minimalism_toggle', methods: ['GET'])]
    public function toggle(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $user->setIsMinimalMode(!($user->isMinimalMode()));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json(['user' => $user], Response::HTTP_OK, context: ['groups' => ['user:ref']]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
