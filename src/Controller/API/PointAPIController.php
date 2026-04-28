<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PointService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("IS_AUTHENTICATED")]
#[Route('/api/points/user')]
class PointAPIController extends AbstractController
{
    public function __construct(private PointService $pointService)
    {
    }

    #[Route('/{id}', name: 'app_api_points', methods: ['GET'])]
    public function index(User $user, UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $scores = [];
        array_map(function ($user) use (&$scores) {
            $scores[$user->getId()] = [
                'score' => $this->pointService->calculate($user),
                'user' => $user
            ];
        }, $users);

        usort($scores, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $placement = null;
        foreach ($scores as $key => $score) {
            if ($score['user']->getId() === $user->getId()) {
                $placement = $key;
                break;
            }
        }

        return $this->json([
            'score' => $this->pointService->calculate($user),
            'placement' => $placement + 1 // offset the 0 index
        ]);
    }
}
