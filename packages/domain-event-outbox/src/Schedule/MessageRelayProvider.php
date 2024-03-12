<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/domain-event-src package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Outbox\Schedule;

use Rekalogika\DomainEvent\Outbox\Message\MessageRelayStartMessage;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

final class MessageRelayProvider implements ScheduleProviderInterface
{
    /**
     * @param array<string,string> $entityManagers
     */
    public function __construct(
        private array $entityManagers
    ) {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();

        foreach ($this->entityManagers as $name => $service) {
            $schedule->add(RecurringMessage::every('1 hour', new MessageRelayStartMessage($name)));
        }

        return $schedule;
    }
}
