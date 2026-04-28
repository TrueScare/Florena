<?php

namespace App\Tests\Service;

use App\Entity\CareTask;
use App\Entity\Plants;
use App\Enum\CareType;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\StressScoreStatus;
use App\Enum\TemperatureRequirement;
use App\Service\StressScoreService;
use PHPUnit\Framework\TestCase;

final class StressScoreServiceTest extends TestCase
{
    private StressScoreService $service;

    protected function setUp(): void
    {
        $this->service = new StressScoreService();
    }

    public function testScoreIsHundredWhenAllTasksAreNotYetDue(): void
    {
        $plant = $this->createPlantWithTasks(
            waterDue: new \DateTimeImmutable('+1 day'),
            fertilizeDue: new \DateTimeImmutable('+1 day'),
            repotDue: new \DateTimeImmutable('+1 day'),
        );

        self::assertSame(100.0, $this->service->calculate($plant));
    }

    public function testScoreIsHundredWhenDueTodayExact(): void
    {
        $plant = $this->createPlantWithTasks(
            waterDue: new \DateTimeImmutable('today'),
            fertilizeDue: new \DateTimeImmutable('today'),
            repotDue: new \DateTimeImmutable('today'),
        );

        self::assertSame(100.0, $this->service->calculate($plant));
    }

    public function testScoreDecreasesWhenWaterTaskIsOverdue(): void
    {
        $waterInterval = 7;
        $overdueBy = $waterInterval * 2 + 1;

        $plant = $this->createPlantWithTasks(
            waterDue: new \DateTimeImmutable("-{$overdueBy} days"),
            fertilizeDue: new \DateTimeImmutable('+1 day'),
            repotDue: new \DateTimeImmutable('+1 day'),
            waterInterval: $waterInterval,
        );

        $score = $this->service->calculate($plant);
        self::assertLessThan(100.0, $score);
        self::assertGreaterThanOrEqual(0.0, $score);
    }

    public function testScoreIsNearZeroWhenAllTasksHeavilyOverdue(): void
    {
        $plant = $this->createPlantWithTasks(
            waterDue: new \DateTimeImmutable('-1000 days'),
            fertilizeDue: new \DateTimeImmutable('-1000 days'),
            repotDue: new \DateTimeImmutable('-1000 days'),
        );

        self::assertLessThanOrEqual(5.0, $this->service->calculate($plant));
    }

    public function testScoreWithNoCareTasks(): void
    {
        // No tasks → all fallback weights are 1.0 → score = 100
        self::assertSame(100.0, $this->service->calculate($this->buildPlant()));
    }

    public function testMapScoreHealthy(): void
    {
        self::assertSame(StressScoreStatus::healthy, $this->service->mapScore(100.0));
        self::assertSame(StressScoreStatus::healthy, $this->service->mapScore(80.0));
    }

    public function testMapScoreSlightlyStressed(): void
    {
        self::assertSame(StressScoreStatus::slightlyStressed, $this->service->mapScore(79.9));
        self::assertSame(StressScoreStatus::slightlyStressed, $this->service->mapScore(50.0));
    }

    public function testMapScoreProblem(): void
    {
        self::assertSame(StressScoreStatus::problem, $this->service->mapScore(49.9));
        self::assertSame(StressScoreStatus::problem, $this->service->mapScore(0.0));
    }

    private function createPlantWithTasks(
        \DateTimeImmutable $waterDue,
        \DateTimeImmutable $fertilizeDue,
        \DateTimeImmutable $repotDue,
        int $waterInterval = 7,
        int $fertilizeInterval = 30,
        int $repotInterval = 365,
    ): Plants {
        $plant = $this->buildPlant($waterInterval, $fertilizeInterval, $repotInterval);

        $water = new CareTask(CareType::water, $plant);
        $water->setDueDate($waterDue);
        $plant->addCareTask($water);

        $fertilize = new CareTask(CareType::fertilice, $plant);
        $fertilize->setDueDate($fertilizeDue);
        $plant->addCareTask($fertilize);

        $repot = new CareTask(CareType::repot, $plant);
        $repot->setDueDate($repotDue);
        $plant->addCareTask($repot);

        return $plant;
    }

    private function buildPlant(int $w = 7, int $f = 30, int $r = 365): Plants
    {
        $plant = new Plants();
        $plant->setName('StressTestPlant');
        $plant->setDescription('desc');
        $plant->setBotanicalName('Stressicus testicus');
        $plant->setLightRequirement(LightRequirement::halfshady);
        $plant->setTemperatureRequirement(TemperatureRequirement::cool);
        $plant->setHumidityRequirement(HumidityRequirement::medium);
        $plant->setSoilType('Erde');
        $plant->setPotSize('10 cm');
        $plant->setLastWateredAt(new \DateTimeImmutable());
        $plant->setLastFertilizedAt(new \DateTimeImmutable());
        $plant->setLastRepottedAt(new \DateTimeImmutable());
        $plant->setWateringIntervalDays($w);
        $plant->setFertilizingIntervalDays($f);
        $plant->setRepottingIntervalDays($r);
        $plant->setToxicForHumans(false);
        $plant->setToxicForAnimals(false);
        return $plant;
    }
}
