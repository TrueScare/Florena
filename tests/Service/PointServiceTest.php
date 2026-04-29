<?php

namespace App\Tests\Service;

use App\Entity\CareHistory;
use App\Entity\Plants;
use App\Entity\User;
use App\Enum\CareType;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use App\Repository\CareHistoryRepository;
use App\Service\PointService;
use PHPUnit\Framework\TestCase;

final class PointServiceTest extends TestCase
{
    private function buildUser(int $id): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);
        return $user;
    }

    private function buildPlant(User $owner): Plants
    {
        $plant = $this->createMock(Plants::class);
        $plant->method('getUser')->willReturn($owner);
        return $plant;
    }

    private function buildHistory(User $performedBy, Plants $plant): CareHistory
    {
        $history = $this->createMock(CareHistory::class);
        $history->method('getUser')->willReturn($performedBy);
        $history->method('getPlant')->willReturn($plant);
        return $history;
    }

    public function testZeroPointsForUserWithNoHistory(): void
    {
        $user = $this->buildUser(1);

        $repo = $this->createMock(CareHistoryRepository::class);
        $repo->method('findBy')->willReturn([]);

        $service = new PointService($repo);
        self::assertSame(0, $service->calculate($user));
    }

    public function testBasePointsForOwnTasks(): void
    {
        $user = $this->buildUser(1);
        $plant = $this->buildPlant($user);

        $h1 = $this->buildHistory($user, $plant);
        $h2 = $this->buildHistory($user, $plant);

        $repo = $this->createMock(CareHistoryRepository::class);
        $repo->method('findBy')->willReturn([$h1, $h2]);

        $service = new PointService($repo);
        // 2 own tasks × BASE_POINTS(5)
        self::assertSame(2 * PointService::BASE_POINTS, $service->calculate($user));
    }

    public function testBonusPointsForAssignedTasks(): void
    {
        $owner = $this->buildUser(1);
        $assignee = $this->buildUser(2);
        $plant = $this->buildPlant($owner); // plant belongs to $owner

        $h = $this->buildHistory($assignee, $plant); // done by assignee

        $repo = $this->createMock(CareHistoryRepository::class);
        $repo->method('findBy')->willReturn([$h]);

        $service = new PointService($repo);
        // 1 assigned task × BASE_POINTS × BONUS_MULTIPLIER
        self::assertSame(
            PointService::BASE_POINTS * PointService::BONUS_MULITPLIER,
            $service->calculate($assignee)
        );
    }

    public function testMixedOwnAndAssignedTasks(): void
    {
        $owner = $this->buildUser(1);
        $assignee = $this->buildUser(2);

        $ownPlant = $this->buildPlant($assignee);       // plant belongs to assignee
        $foreignPlant = $this->buildPlant($owner);      // plant belongs to owner

        $ownTask = $this->buildHistory($assignee, $ownPlant);
        $assignedTask = $this->buildHistory($assignee, $foreignPlant);

        $repo = $this->createMock(CareHistoryRepository::class);
        $repo->method('findBy')->willReturn([$ownTask, $assignedTask]);

        $service = new PointService($repo);
        $expected = PointService::BASE_POINTS                                    // 1 own task
                    + PointService::BASE_POINTS * PointService::BONUS_MULITPLIER; // 1 assigned task
        self::assertSame($expected, $service->calculate($assignee));
    }
}
