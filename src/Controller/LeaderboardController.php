<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\PointService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/leaderboard')]
#[IsGranted("IS_AUTHENTICATED")]
class LeaderboardController extends AbstractController
{
    public function __construct(private PointService $pointService)
    {
    }

    #[Route(name: 'app_leaderboard_index', methods: ['GET'])]
    public function index(UserRepository $repository)
    {
        $users = $repository->findAll();
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


        return $this->render('leaderboard/index.html.twig', [
            'users' => $users,
            'scores' => $scores,
        ]);
    }
}
