<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\PointService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
    public function index(UserRepository $repository): Response
    {
        $users = $repository->findAll();
        $scores = [];

        foreach ($users as $user) {
            $scores[] = [
                'score' => $this->pointService->calculate($user),
                'user' => $user,
            ];
        }

        usort($scores, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $currentUserScore = null;
        $currentUserRank = null;

        foreach ($scores as $index => $score) {
            if ($score['user'] === $this->getUser()) {
                $currentUserScore = $score['score'];
                $currentUserRank = $index + 1;
                break;
            }
        }

        $scores = array_slice($scores, 0, 10);

        return $this->render('leaderboard/index.html.twig', [
            'scores' => $scores,
            'currentUserScore' => $currentUserScore,
            'currentUserRank' => $currentUserRank,
        ]);
    }
}